<?php

namespace App\Console\Commands;

use App\Enums\StripchatTag;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Tag;
use App\Models\TagTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TranslateTagsAndCountries extends Command
{
    protected $signature = 'translate:all 
                            {--type=all : What to translate: tags, countries, or all}
                            {--locale= : Single locale to translate (e.g., es, fr, de)}
                            {--group= : Locale group from config (e.g., europe_west, asia)}
                            {--priority : Only translate priority locales}
                            {--seed-tags : Seed tags from StripchatTag enum first}
                            {--batch=20 : Number of items per API batch}
                            {--delay=2 : Seconds between API calls}
                            {--skip-existing : Skip locales that already have translations}';

    protected $description = 'Translate tags and countries to multiple languages using AI';

    protected string $apiKey;
    protected string $model = 'claude-3-haiku-20240307';

    public function handle(): int
    {
        $this->apiKey = config('services.anthropic.api_key', '');

        if (empty($this->apiKey)) {
            $this->error('ANTHROPIC_API_KEY not set in .env file');
            return Command::FAILURE;
        }

        // Optionally seed tags first
        if ($this->option('seed-tags')) {
            $this->seedTags();
        }

        // Determine which locales to translate
        $locales = $this->getTargetLocales();
        
        if (empty($locales)) {
            $this->error('No locales to translate');
            return Command::FAILURE;
        }

        $this->info('Will translate to: ' . implode(', ', $locales));

        $type = $this->option('type');

        if ($type === 'tags' || $type === 'all') {
            $this->translateTags($locales);
        }

        if ($type === 'countries' || $type === 'all') {
            $this->translateCountries($locales);
        }

        $this->info('Translation complete!');
        return Command::SUCCESS;
    }

    /**
     * Seed tags from StripchatTag enum
     */
    protected function seedTags(): void
    {
        $this->info('Seeding tags from StripchatTag enum...');
        
        $featuredSlugs = [
            'teens', 'young', 'milfs', 'mature', 'asian', 'ebony', 'latin', 'white',
            'big-tits', 'small-tits', 'big-ass', 'anal', 'squirt', 'deepthroat',
            'lovense', 'interactive-toy', 'new', 'hd',
        ];

        $count = 0;
        $order = 0;

        foreach (StripchatTag::grouped() as $category => $tags) {
            foreach ($tags as $tag) {
                Tag::updateOrCreate(
                    ['slug' => $tag->value],
                    [
                        'name' => $tag->label(),
                        'category' => $category,
                        'sort_order' => $order++,
                        'is_featured' => in_array($tag->value, $featuredSlugs),
                    ]
                );
                $count++;
            }
        }

        // Create English translations for all tags
        foreach (Tag::all() as $tag) {
            TagTranslation::updateOrCreate(
                ['tag_id' => $tag->id, 'locale' => 'en'],
                [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ]
            );
        }

        $this->info("Seeded {$count} tags with English translations");
    }

    /**
     * Get target locales based on options
     */
    protected function getTargetLocales(): array
    {
        // Single locale
        if ($locale = $this->option('locale')) {
            return [$locale];
        }

        // Locale group
        if ($group = $this->option('group')) {
            return config("locales.groups.{$group}", []);
        }

        // Priority locales
        if ($this->option('priority')) {
            return config('locales.priority', ['en', 'es', 'fr', 'de', 'pt']);
        }

        // All locales except English
        return array_filter(
            array_keys(config('locales.supported', [])),
            fn($l) => $l !== 'en'
        );
    }

    /**
     * Translate all tags
     */
    protected function translateTags(array $locales): void
    {
        $tags = Tag::all();
        
        if ($tags->isEmpty()) {
            $this->warn('No tags found. Run with --seed-tags first.');
            return;
        }

        $this->info("Translating {$tags->count()} tags...");
        $batchSize = (int) $this->option('batch');
        $delay = (int) $this->option('delay');
        $skipExisting = $this->option('skip-existing');

        $bar = $this->output->createProgressBar(count($locales));
        $bar->start();

        foreach ($locales as $locale) {
            // Check if we should skip
            if ($skipExisting) {
                $existingCount = TagTranslation::where('locale', $locale)->count();
                if ($existingCount >= $tags->count()) {
                    $bar->advance();
                    continue;
                }
            }

            $this->newLine();
            $this->info("Translating tags to {$locale}...");

            // Get tags that need translation for this locale
            $existingTagIds = TagTranslation::where('locale', $locale)->pluck('tag_id')->toArray();
            $tagsToTranslate = $tags->whereNotIn('id', $existingTagIds);
            
            if ($tagsToTranslate->isEmpty()) {
                $this->line("  All tags already translated to {$locale}");
                $bar->advance();
                continue;
            }

            // Batch tags for efficient API calls
            $tagBatches = $tagsToTranslate->chunk($batchSize);
            
            foreach ($tagBatches as $batch) {
                $tagData = $batch->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                ])->toArray();

                try {
                    $translations = $this->batchTranslateTags($tagData, $locale);
                    
                    foreach ($translations as $translation) {
                        $this->saveTagTranslation($translation, $locale);
                    }
                } catch (\Exception $e) {
                    $this->error("Error translating batch: " . $e->getMessage());
                }

                sleep($delay);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Save tag translation with unique slug handling
     */
    protected function saveTagTranslation(array $translation, string $locale): void
    {
        $baseSlug = Str::slug($translation['slug']);
        $slug = $baseSlug;
        $counter = 1;
        
        // Check for slug collision - must be unique per locale
        while (TagTranslation::where('locale', $locale)
                ->where('slug', $slug)
                ->where('tag_id', '!=', $translation['id'])
                ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        
        TagTranslation::updateOrCreate(
            ['tag_id' => $translation['id'], 'locale' => $locale],
            [
                'name' => $translation['name'],
                'slug' => $slug,
            ]
        );
    }

    /**
     * Translate all countries
     */
    protected function translateCountries(array $locales): void
    {
        $countries = Country::all();
        
        if ($countries->isEmpty()) {
            $this->warn('No countries found. Run countries:sync first.');
            return;
        }

        $this->info("Translating {$countries->count()} countries...");
        $batchSize = (int) $this->option('batch');
        $delay = (int) $this->option('delay');
        $skipExisting = $this->option('skip-existing');

        $bar = $this->output->createProgressBar(count($locales));
        $bar->start();

        foreach ($locales as $locale) {
            // Check if we should skip
            if ($skipExisting) {
                $existingCount = CountryTranslation::where('locale', $locale)->count();
                if ($existingCount >= $countries->count()) {
                    $bar->advance();
                    continue;
                }
            }

            $this->newLine();
            $this->info("Translating countries to {$locale}...");

            // Get countries that need translation
            $existingCountryIds = CountryTranslation::where('locale', $locale)->pluck('country_id')->toArray();
            $countriesToTranslate = $countries->whereNotIn('id', $existingCountryIds);
            
            if ($countriesToTranslate->isEmpty()) {
                $this->line("  All countries already translated to {$locale}");
                $bar->advance();
                continue;
            }

            // Batch countries
            $countryBatches = $countriesToTranslate->chunk($batchSize);
            
            foreach ($countryBatches as $batch) {
                $countryData = $batch->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'code' => $c->code,
                ])->toArray();

                try {
                    $translations = $this->batchTranslateCountries($countryData, $locale);
                    
                    foreach ($translations as $translation) {
                        $this->saveCountryTranslation($translation, $locale);
                    }
                } catch (\Exception $e) {
                    $this->error("Error translating batch: " . $e->getMessage());
                }

                sleep($delay);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Save country translation with unique slug handling
     */
    protected function saveCountryTranslation(array $translation, string $locale): void
    {
        $baseSlug = Str::slug($translation['slug']);
        $slug = $baseSlug;
        $counter = 1;
        
        // Check for slug collision
        while (CountryTranslation::where('locale', $locale)
                ->where('slug', $slug)
                ->where('country_id', '!=', $translation['id'])
                ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        
        CountryTranslation::updateOrCreate(
            ['country_id' => $translation['id'], 'locale' => $locale],
            [
                'name' => $translation['name'],
                'slug' => $slug,
            ]
        );
    }

    /**
     * Batch translate tags using Anthropic API
     */
    protected function batchTranslateTags(array $tags, string $locale): array
    {
        $langName = $this->getLanguageName($locale);
        $tagList = collect($tags)->map(fn($t) => "- {$t['name']} (slug: {$t['slug']})")->implode("\n");

        $prompt = <<<PROMPT
Translate these adult cam site category tags to {$langName}.

Tags to translate:
{$tagList}

Respond with ONLY a JSON array, no other text:
[
    {"original_slug": "tag-slug", "name": "Translated Name", "slug": "translated-slug"},
    ...
]

Rules:
- "original_slug" must match exactly the input slug
- "name" is the translated display name
- "slug" is URL-friendly: lowercase, hyphens, no accents/special chars (convert ñ→n, ü→u, etc.)
- Maintain the same order as input
- Keep adult terms appropriate but not explicit
- IMPORTANT: Each slug must be unique - if two tags would have the same translation, add a number suffix (e.g., "tag-1", "tag-2")
PROMPT;

        $response = $this->callAnthropic($prompt);
        
        // Parse response
        $translations = json_decode($response, true);
        
        if (!is_array($translations)) {
            // Try to extract JSON array from response
            if (preg_match('/\[[\s\S]*\]/u', $response, $matches)) {
                $translations = json_decode($matches[0], true);
            }
        }

        if (!is_array($translations)) {
            $this->warn("Failed to parse translation response for {$locale}");
            return [];
        }

        // Map back to tag IDs
        $result = [];
        foreach ($tags as $tag) {
            $found = collect($translations)->first(fn($t) => 
                ($t['original_slug'] ?? '') === $tag['slug']
            );
            
            if ($found) {
                $result[] = [
                    'id' => $tag['id'],
                    'name' => $found['name'] ?? $tag['name'],
                    'slug' => $found['slug'] ?? $tag['slug'],
                ];
            }
        }

        return $result;
    }

    /**
     * Batch translate countries using Anthropic API
     */
    protected function batchTranslateCountries(array $countries, string $locale): array
    {
        $langName = $this->getLanguageName($locale);
        $countryList = collect($countries)->map(fn($c) => "- {$c['name']} ({$c['code']})")->implode("\n");

        $prompt = <<<PROMPT
Translate these country names to {$langName}.

Countries:
{$countryList}

Respond with ONLY a JSON array, no other text:
[
    {"code": "US", "name": "Translated Country Name", "slug": "translated-slug"},
    ...
]

Rules:
- "code" must match exactly the input country code
- "name" is the country name in {$langName}
- "slug" is URL-friendly: lowercase, hyphens, no accents/special chars
- Use official country name translations where possible
PROMPT;

        $response = $this->callAnthropic($prompt);
        
        $translations = json_decode($response, true);
        
        if (!is_array($translations)) {
            if (preg_match('/\[[\s\S]*\]/u', $response, $matches)) {
                $translations = json_decode($matches[0], true);
            }
        }

        if (!is_array($translations)) {
            $this->warn("Failed to parse country translation response for {$locale}");
            return [];
        }

        // Map back to country IDs
        $result = [];
        foreach ($countries as $country) {
            $found = collect($translations)->first(fn($t) => 
                ($t['code'] ?? '') === $country['code']
            );
            
            if ($found) {
                $result[] = [
                    'id' => $country['id'],
                    'name' => $found['name'] ?? $country['name'],
                    'slug' => $found['slug'] ?? Str::slug($country['name']),
                ];
            }
        }

        return $result;
    }

    /**
     * Call Anthropic API
     */
    protected function callAnthropic(string $prompt): string
    {
        $response = Http::timeout(60)->withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message', $response->body());
            throw new \Exception("Anthropic API error: {$error}");
        }

        return $response->json('content.0.text', '');
    }

    /**
     * Get language name from locale
     */
    protected function getLanguageName(string $locale): string
    {
        $locales = config('locales.supported', []);
        return $locales[$locale]['name'] ?? ucfirst($locale);
    }
}
