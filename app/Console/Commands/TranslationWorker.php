<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\HomepageSection;
use App\Models\HomepageSectionTranslation;
use App\Models\ModelDescription;
use App\Models\ModelFaq;
use App\Models\ModelTipMenuItem;
use App\Models\Tag;
use App\Models\TagTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Background translation worker that continuously translates content.
 * 
 * This worker runs indefinitely, translating:
 * 1. Tags and countries (first priority)
 * 2. Model descriptions
 * 3. Model FAQs
 * 4. Tip menu items
 * 
 * It respects rate limits and can be safely killed and restarted.
 */
class TranslationWorker extends Command
{
    protected $signature = 'translate:worker
                            {--rate=10 : API calls per minute (default 10)}
                            {--batch=5 : Items per batch}
                            {--locales= : Comma-separated locales (default: priority)}
                            {--type=all : What to translate: all, tags, models}
                            {--once : Run once then exit (don\'t loop forever)}
                            {--dry-run : Show what would be translated without API calls}';

    protected $description = 'Background translation worker - runs continuously translating site content';

    protected string $apiKey;
    protected string $model = 'claude-3-haiku-20240307';
    protected int $apiCallsThisMinute = 0;
    protected float $minuteStart;
    protected array $stats = [
        'tags' => 0,
        'countries' => 0,
        'homepage_sections' => 0,
        'descriptions' => 0,
        'faqs' => 0,
        'tip_menus' => 0,
        'errors' => 0,
    ];

    public function handle(): int
    {
        $this->apiKey = config('services.anthropic.api_key', '');

        if (empty($this->apiKey) && !$this->option('dry-run')) {
            $this->error('ANTHROPIC_API_KEY not set in .env file');
            return Command::FAILURE;
        }

        $rateLimit = (int) $this->option('rate');
        $runOnce = $this->option('once');
        $type = $this->option('type');

        $this->info('🌍 Translation Worker Started');
        $this->info("   Rate limit: {$rateLimit} API calls/minute");
        $this->info("   Mode: " . ($runOnce ? 'Single pass' : 'Continuous'));
        $this->newLine();

        $locales = $this->getLocales();
        $this->info('Target locales: ' . implode(', ', $locales));
        $this->newLine();

        $this->minuteStart = microtime(true);

        do {
            // Phase 1: Translate tags, countries, and homepage sections
            if ($type === 'all' || $type === 'tags') {
                $this->translateMissingTags($locales, $rateLimit);
                $this->translateMissingCountries($locales, $rateLimit);
                $this->translateMissingHomepageSections($locales, $rateLimit);
            }

            // Phase 2: Translate model content
            if ($type === 'all' || $type === 'models') {
                $this->translateModelContent($locales, $rateLimit);
            }

            // Show progress
            $this->showProgress();

            // If running continuously, sleep between full cycles
            if (!$runOnce) {
                $this->info('Cycle complete. Sleeping 60s before next cycle...');
                sleep(60);
            }

        } while (!$runOnce);

        $this->newLine();
        $this->info('✅ Translation worker finished');
        $this->showFinalStats();

        return Command::SUCCESS;
    }

    /**
     * Get target locales
     */
    protected function getLocales(): array
    {
        if ($locales = $this->option('locales')) {
            return array_filter(array_map('trim', explode(',', $locales)));
        }

        return config('locales.priority', [
            'es', 'fr', 'de', 'pt', 'it', 'nl', 'pl', 'ru',
            'ja', 'ko', 'zh', 'ar', 'tr', 'pt-BR', 'es-MX',
        ]);
    }

    /**
     * Translate missing tag translations
     */
    protected function translateMissingTags(array $locales, int $rateLimit): void
    {
        $tags = Tag::all();
        if ($tags->isEmpty()) return;

        $batchSize = (int) $this->option('batch');

        foreach ($locales as $locale) {
            // Find tags without translation for this locale
            $existingTagIds = TagTranslation::where('locale', $locale)->pluck('tag_id')->toArray();
            $missingTags = $tags->whereNotIn('id', $existingTagIds);

            if ($missingTags->isEmpty()) continue;

            $this->line("  Translating {$missingTags->count()} tags to {$locale}...");

            foreach ($missingTags->chunk($batchSize) as $batch) {
                $this->enforceRateLimit($rateLimit);

                if ($this->option('dry-run')) {
                    $this->line("    [DRY RUN] Would translate: " . $batch->pluck('name')->implode(', '));
                    continue;
                }

                try {
                    $translations = $this->translateTagBatch($batch->toArray(), $locale);
                    foreach ($translations as $trans) {
                        $this->saveTagTranslation($trans, $locale);
                        $this->stats['tags']++;
                    }
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    $this->error("    Error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Translate missing country translations
     */
    protected function translateMissingCountries(array $locales, int $rateLimit): void
    {
        $countries = Country::all();
        if ($countries->isEmpty()) return;

        $batchSize = (int) $this->option('batch');

        foreach ($locales as $locale) {
            $existingIds = CountryTranslation::where('locale', $locale)->pluck('country_id')->toArray();
            $missing = $countries->whereNotIn('id', $existingIds);

            if ($missing->isEmpty()) continue;

            $this->line("  Translating {$missing->count()} countries to {$locale}...");

            foreach ($missing->chunk($batchSize) as $batch) {
                $this->enforceRateLimit($rateLimit);

                if ($this->option('dry-run')) {
                    $this->line("    [DRY RUN] Would translate: " . $batch->pluck('name')->implode(', '));
                    continue;
                }

                try {
                    $translations = $this->translateCountryBatch($batch->toArray(), $locale);
                    foreach ($translations as $trans) {
                        $this->saveCountryTranslation($trans, $locale);
                        $this->stats['countries']++;
                    }
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    $this->error("    Error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Translate missing homepage section translations
     */
    protected function translateMissingHomepageSections(array $locales, int $rateLimit): void
    {
        $sections = HomepageSection::active()->get();
        if ($sections->isEmpty()) return;

        foreach ($locales as $locale) {
            $existingIds = HomepageSectionTranslation::where('locale', $locale)
                ->pluck('homepage_section_id')
                ->toArray();
            $missing = $sections->whereNotIn('id', $existingIds);

            if ($missing->isEmpty()) continue;

            $this->line("  Translating {$missing->count()} homepage sections to {$locale}...");

            foreach ($missing as $section) {
                $this->enforceRateLimit($rateLimit);

                if ($this->option('dry-run')) {
                    $this->line("    [DRY RUN] Would translate: {$section->title}");
                    continue;
                }

                try {
                    $langName = $this->getLanguageName($locale);

                    $prompt = <<<PROMPT
Translate this section title for an adult live cam site to {$langName}.

Section title: "{$section->title}"

Respond with ONLY JSON:
{"title": "translated title"}

Keep it concise and natural. This is a category heading like "Latina Sex Cams" or "MILF Sex Cams".
PROMPT;

                    $response = $this->callApi($prompt);
                    $data = $this->parseJson($response);

                    if (!empty($data['title'])) {
                        HomepageSectionTranslation::updateOrCreate(
                            ['homepage_section_id' => $section->id, 'locale' => $locale],
                            ['title' => $data['title']]
                        );
                        $this->stats['homepage_sections']++;
                    }
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    $this->error("    Error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Translate model content (descriptions, FAQs, tip menus)
     */
    protected function translateModelContent(array $locales, int $rateLimit): void
    {
        // Get models with descriptions that need translation
        $descriptions = ModelDescription::whereNotNull('short_description')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        foreach ($descriptions as $desc) {
            foreach ($locales as $locale) {
                // Check if translation exists
                $existingTrans = $desc->translations()->where('locale', $locale)->first();
                if ($existingTrans) continue;

                $this->enforceRateLimit($rateLimit);

                if ($this->option('dry-run')) {
                    $this->line("  [DRY RUN] Would translate description for {$desc->model_id} to {$locale}");
                    continue;
                }

                try {
                    $translated = $this->translateDescription($desc, $locale);
                    if ($translated) {
                        $this->stats['descriptions']++;
                    }
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                    if ($this->option('verbose')) {
                        $this->error("    Error translating description: " . $e->getMessage());
                    }
                }
            }
        }

        // Translate FAQs
        $this->translateModelFaqs($locales, $rateLimit);

        // Translate tip menus
        $this->translateTipMenus($locales, $rateLimit);
    }

    /**
     * Translate model FAQs
     */
    protected function translateModelFaqs(array $locales, int $rateLimit): void
    {
        $faqs = ModelFaq::whereNotNull('question')
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get();

        foreach ($faqs as $faq) {
            foreach ($locales as $locale) {
                $existingTrans = $faq->translations()->where('locale', $locale)->first();
                if ($existingTrans) continue;

                $this->enforceRateLimit($rateLimit);

                if ($this->option('dry-run')) {
                    $this->line("  [DRY RUN] Would translate FAQ for {$faq->model_id} to {$locale}");
                    continue;
                }

                try {
                    $translated = $this->translateFaq($faq, $locale);
                    if ($translated) {
                        $this->stats['faqs']++;
                    }
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                }
            }
        }
    }

    /**
     * Translate tip menu items
     */
    protected function translateTipMenus(array $locales, int $rateLimit): void
    {
        $tipMenus = ModelTipMenuItem::whereNotNull('action_name')
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->limit(500)
            ->get();

        $batchSize = (int) $this->option('batch');

        foreach ($locales as $locale) {
            // Get tip menu items without translation for this locale
            $itemsNeedingTranslation = $tipMenus->filter(function ($item) use ($locale) {
                return !$item->translations()->where('locale', $locale)->exists();
            });

            if ($itemsNeedingTranslation->isEmpty()) continue;

            $this->line("  Translating {$itemsNeedingTranslation->count()} tip menu items to {$locale}...");

            foreach ($itemsNeedingTranslation->chunk($batchSize) as $batch) {
                $this->enforceRateLimit($rateLimit);

                if ($this->option('dry-run')) {
                    $this->line("    [DRY RUN] Would translate tip items");
                    continue;
                }

                try {
                    $this->translateTipMenuBatch($batch, $locale);
                    $this->stats['tip_menus'] += $batch->count();
                } catch (\Exception $e) {
                    $this->stats['errors']++;
                }
            }
        }
    }

    /**
     * Enforce rate limit - sleep if necessary
     */
    protected function enforceRateLimit(int $rateLimit): void
    {
        $this->apiCallsThisMinute++;
        
        $elapsed = microtime(true) - $this->minuteStart;
        
        // Reset counter if a minute has passed
        if ($elapsed >= 60) {
            $this->apiCallsThisMinute = 1;
            $this->minuteStart = microtime(true);
            return;
        }

        // If at rate limit, sleep until the minute is up
        if ($this->apiCallsThisMinute >= $rateLimit) {
            $sleepTime = ceil(60 - $elapsed);
            $this->line("    Rate limit reached, sleeping {$sleepTime}s...");
            sleep((int) $sleepTime + 1);
            $this->apiCallsThisMinute = 1;
            $this->minuteStart = microtime(true);
        }
    }

    /**
     * Translate a batch of tags
     */
    protected function translateTagBatch(array $tags, string $locale): array
    {
        $langName = $this->getLanguageName($locale);
        $tagList = collect($tags)->map(fn($t) => "- {$t['name']} (slug: {$t['slug']})")->implode("\n");

        $prompt = <<<PROMPT
Translate these adult cam site category tags to {$langName}.

Tags:
{$tagList}

Respond with ONLY a JSON array:
[{"original_slug": "slug", "name": "Translated Name", "slug": "url-friendly-slug"}]

Rules:
- original_slug must match input exactly
- slug: lowercase, hyphens only, no accents (ñ→n, ü→u)
- Each slug must be unique
PROMPT;

        $response = $this->callApi($prompt);
        $translations = $this->parseJsonArray($response);

        return collect($tags)->map(function ($tag) use ($translations) {
            $found = collect($translations)->first(fn($t) => ($t['original_slug'] ?? '') === $tag['slug']);
            return $found ? [
                'id' => $tag['id'],
                'name' => $found['name'] ?? $tag['name'],
                'slug' => $found['slug'] ?? $tag['slug'],
            ] : null;
        })->filter()->values()->toArray();
    }

    /**
     * Translate a batch of countries
     */
    protected function translateCountryBatch(array $countries, string $locale): array
    {
        $langName = $this->getLanguageName($locale);
        $countryList = collect($countries)->map(fn($c) => "- {$c['name']} ({$c['code']})")->implode("\n");

        $prompt = <<<PROMPT
Translate these country names to {$langName}.

Countries:
{$countryList}

Respond with ONLY a JSON array:
[{"code": "US", "name": "Translated Name", "slug": "url-friendly-slug"}]

Rules:
- code must match input exactly
- Use official translations
- slug: lowercase, hyphens, no accents
PROMPT;

        $response = $this->callApi($prompt);
        $translations = $this->parseJsonArray($response);

        return collect($countries)->map(function ($country) use ($translations) {
            $found = collect($translations)->first(fn($t) => ($t['code'] ?? '') === $country['code']);
            return $found ? [
                'id' => $country['id'],
                'name' => $found['name'] ?? $country['name'],
                'slug' => $found['slug'] ?? Str::slug($country['name']),
            ] : null;
        })->filter()->values()->toArray();
    }

    /**
     * Translate a model description
     */
    protected function translateDescription(ModelDescription $desc, string $locale): bool
    {
        $langName = $this->getLanguageName($locale);

        $prompt = <<<PROMPT
Translate this cam model description to {$langName}:

Short: {$desc->short_description}

Long: {$desc->long_description}

Respond with ONLY JSON:
{"short_description": "...", "long_description": "..."}

Keep the tone warm and inviting. Maintain SEO quality.
PROMPT;

        $response = $this->callApi($prompt);
        $data = $this->parseJson($response);

        if (empty($data['short_description'])) return false;

        $desc->translations()->updateOrCreate(
            ['locale' => $locale],
            [
                'short_description' => $data['short_description'],
                'long_description' => $data['long_description'] ?? '',
            ]
        );

        return true;
    }

    /**
     * Translate a FAQ
     */
    protected function translateFaq(ModelFaq $faq, string $locale): bool
    {
        $langName = $this->getLanguageName($locale);

        $prompt = <<<PROMPT
Translate this FAQ to {$langName}:

Q: {$faq->question}
A: {$faq->answer}

Respond with ONLY JSON:
{"question": "...", "answer": "..."}
PROMPT;

        $response = $this->callApi($prompt);
        $data = $this->parseJson($response);

        if (empty($data['question'])) return false;

        $faq->translations()->updateOrCreate(
            ['locale' => $locale],
            [
                'question' => $data['question'],
                'answer' => $data['answer'] ?? '',
            ]
        );

        return true;
    }

    /**
     * Translate a batch of tip menu items
     */
    protected function translateTipMenuBatch($items, string $locale): void
    {
        $langName = $this->getLanguageName($locale);
        $itemList = $items->map(fn($i) => "- [{$i->id}] {$i->action_name}")->implode("\n");

        $prompt = <<<PROMPT
Translate these tip menu actions to {$langName}:

{$itemList}

Respond with ONLY JSON array:
[{"id": 123, "action_name": "Translated action"}]

Keep adult context but not explicit.
PROMPT;

        $response = $this->callApi($prompt);
        $translations = $this->parseJsonArray($response);

        foreach ($items as $item) {
            $found = collect($translations)->first(fn($t) => ($t['id'] ?? 0) == $item->id);
            if ($found && !empty($found['action_name'])) {
                \App\Models\ModelTipMenuTranslation::updateOrCreate(
                    [
                        'model_tip_menu_item_id' => $item->id,
                        'locale' => $locale,
                    ],
                    ['action_name' => $found['action_name']]
                );
            }
        }
    }

    /**
     * Save tag translation with unique slug
     */
    protected function saveTagTranslation(array $trans, string $locale): void
    {
        $baseSlug = Str::slug($trans['slug']);
        $slug = $baseSlug;
        $counter = 1;

        while (TagTranslation::where('locale', $locale)
                ->where('slug', $slug)
                ->where('tag_id', '!=', $trans['id'])
                ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        TagTranslation::updateOrCreate(
            ['tag_id' => $trans['id'], 'locale' => $locale],
            ['name' => $trans['name'], 'slug' => $slug]
        );
    }

    /**
     * Save country translation with unique slug
     */
    protected function saveCountryTranslation(array $trans, string $locale): void
    {
        $baseSlug = Str::slug($trans['slug']);
        $slug = $baseSlug;
        $counter = 1;

        while (CountryTranslation::where('locale', $locale)
                ->where('slug', $slug)
                ->where('country_id', '!=', $trans['id'])
                ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        CountryTranslation::updateOrCreate(
            ['country_id' => $trans['id'], 'locale' => $locale],
            ['name' => $trans['name'], 'slug' => $slug]
        );
    }

    /**
     * Call Anthropic API
     */
    protected function callApi(string $prompt): string
    {
        $response = Http::timeout(60)->withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 2048,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]);

        if ($response->failed()) {
            throw new \Exception($response->json('error.message', $response->body()));
        }

        return $response->json('content.0.text', '');
    }

    /**
     * Parse JSON object from response
     */
    protected function parseJson(string $response): array
    {
        if (preg_match('/\{[\s\S]*\}/u', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if (is_array($data)) return $data;
        }
        return [];
    }

    /**
     * Parse JSON array from response
     */
    protected function parseJsonArray(string $response): array
    {
        $data = json_decode($response, true);
        if (is_array($data)) return $data;

        if (preg_match('/\[[\s\S]*\]/u', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if (is_array($data)) return $data;
        }
        return [];
    }

    /**
     * Get language name from locale
     */
    protected function getLanguageName(string $locale): string
    {
        $locales = config('locales.supported', []);
        return $locales[$locale]['name'] ?? ucfirst($locale);
    }

    /**
     * Show progress
     */
    protected function showProgress(): void
    {
        $total = array_sum($this->stats);
        $this->newLine();
        $this->info("📊 Progress: {$total} items translated");
        $this->line("   Tags: {$this->stats['tags']}, Countries: {$this->stats['countries']}, Sections: {$this->stats['homepage_sections']}");
        $this->line("   Descriptions: {$this->stats['descriptions']}, FAQs: {$this->stats['faqs']}");
        $this->line("   Tip Menus: {$this->stats['tip_menus']}, Errors: {$this->stats['errors']}");
    }

    /**
     * Show final stats
     */
    protected function showFinalStats(): void
    {
        $this->newLine();
        $this->table(
            ['Type', 'Count'],
            [
                ['Tags', $this->stats['tags']],
                ['Countries', $this->stats['countries']],
                ['Homepage Sections', $this->stats['homepage_sections']],
                ['Descriptions', $this->stats['descriptions']],
                ['FAQs', $this->stats['faqs']],
                ['Tip Menus', $this->stats['tip_menus']],
                ['Errors', $this->stats['errors']],
                ['Total', array_sum($this->stats) - $this->stats['errors']],
            ]
        );
    }
}
