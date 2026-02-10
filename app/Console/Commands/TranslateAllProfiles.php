<?php

namespace App\Console\Commands;

use App\Models\ModelDescription;
use App\Models\ModelDescriptionTranslation;
use App\Models\ModelFaq;
use App\Models\ModelFaqTranslation;
use App\Models\ModelTipMenuItem;
use App\Models\ModelTipMenuTranslation;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TranslateAllProfiles extends Command
{
    protected $signature = 'translate:all-profiles
                            {--limit=10 : Number of profiles to translate}
                            {--locales= : Comma-separated locales (default: priority locales)}
                            {--skip-locales= : Comma-separated locales to skip}
                            {--types=all : Content types to translate (all, descriptions, faqs, tipmenus)}
                            {--batch-size=10 : Number of items per API batch}
                            {--delay=500 : Delay between API calls in ms}
                            {--force : Overwrite existing translations}
                            {--dry-run : Show what would be translated without doing it}';

    protected $description = 'Translate X profiles to ALL supported locales in one command';

    protected array $stats = [];
    protected int $apiCalls = 0;

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $types = $this->option('types');
        $batchSize = (int) $this->option('batch-size');
        $delay = (int) $this->option('delay');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $locales = $this->getTargetLocales();

        $this->showBanner($limit, count($locales));

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No translations will be saved');
            $this->newLine();
        }

        // Initialize stats
        foreach ($locales as $locale) {
            $this->stats[$locale] = [
                'descriptions' => 0,
                'faqs' => 0,
                'tipmenus' => 0,
            ];
        }

        // Get profiles to translate (models with descriptions)
        $modelIds = $this->getModelsToTranslate($limit);
        
        if ($modelIds->isEmpty()) {
            $this->warn('No profiles found to translate. Run seo:generate-model-descriptions first.');
            return 1;
        }

        $this->info("Found {$modelIds->count()} profiles to translate to " . count($locales) . " locales");
        $this->newLine();

        // Process each content type
        if (in_array($types, ['all', 'descriptions'])) {
            $this->translateDescriptions($modelIds, $locales, $batchSize, $delay, $force, $dryRun);
        }

        if (in_array($types, ['all', 'faqs'])) {
            $this->translateFaqs($modelIds, $locales, $batchSize, $delay, $force, $dryRun);
        }

        if (in_array($types, ['all', 'tipmenus'])) {
            $this->translateTipMenus($modelIds, $locales, $batchSize, $delay, $force, $dryRun);
        }

        $this->showSummary();

        return 0;
    }

    protected function showBanner(int $limit, int $localeCount): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║        Profile Translation - All Languages                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->info("📊 Target: {$limit} profiles × {$localeCount} languages");
        $this->newLine();
    }

    protected function getTargetLocales(): array
    {
        $skipLocales = [];
        if ($this->option('skip-locales')) {
            $skipLocales = array_map('trim', explode(',', $this->option('skip-locales')));
        }

        if ($this->option('locales')) {
            $locales = array_map('trim', explode(',', $this->option('locales')));
        } else {
            $locales = config('locales.priority', [
                'en', 'es', 'fr', 'de', 'pt', 'it', 'nl', 'pl', 'ru',
                'ja', 'ko', 'zh', 'ar', 'tr', 'pt-BR', 'es-MX',
            ]);
        }

        // Remove English (source) and skipped locales
        return array_filter($locales, fn($l) => $l !== 'en' && !in_array($l, $skipLocales));
    }

    protected function getModelsToTranslate(int $limit): \Illuminate\Support\Collection
    {
        // Get model IDs that have descriptions (content to translate)
        return ModelDescription::select('model_id')
            ->limit($limit)
            ->pluck('model_id');
    }

    protected function translateDescriptions($modelIds, array $locales, int $batchSize, int $delay, bool $force, bool $dryRun): void
    {
        $this->section('Translating Descriptions');

        $descriptions = ModelDescription::whereIn('model_id', $modelIds)->get();
        
        if ($descriptions->isEmpty()) {
            $this->line('   No descriptions found.');
            return;
        }

        $total = $descriptions->count() * count($locales);
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        foreach ($locales as $locale) {
            $bar->setMessage("Translating to {$locale}...");
            
            foreach ($descriptions->chunk($batchSize) as $batch) {
                foreach ($batch as $description) {
                    // Skip if translation exists and not forcing
                    if (!$force && $this->hasDescriptionTranslation($description->id, $locale)) {
                        $bar->advance();
                        continue;
                    }

                    if (!$dryRun) {
                        try {
                            $this->translateSingleDescription($description, $locale);
                            $this->stats[$locale]['descriptions']++;
                        } catch (\Exception $e) {
                            if ($this->option('verbose')) {
                                $this->newLine();
                                $this->error("Error: " . $e->getMessage());
                            }
                        }
                    } else {
                        $this->stats[$locale]['descriptions']++;
                    }

                    $bar->advance();
                }

                // Rate limiting
                usleep($delay * 1000);
            }
        }

        $bar->finish();
        $this->newLine(2);
    }

    protected function translateFaqs($modelIds, array $locales, int $batchSize, int $delay, bool $force, bool $dryRun): void
    {
        $this->section('Translating FAQs');

        $faqs = ModelFaq::whereIn('model_id', $modelIds)->get();
        
        if ($faqs->isEmpty()) {
            $this->line('   No FAQs found.');
            return;
        }

        $total = $faqs->count() * count($locales);
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        foreach ($locales as $locale) {
            $bar->setMessage("Translating FAQs to {$locale}...");
            
            foreach ($faqs->chunk($batchSize) as $batch) {
                foreach ($batch as $faq) {
                    if (!$force && $this->hasFaqTranslation($faq->id, $locale)) {
                        $bar->advance();
                        continue;
                    }

                    if (!$dryRun) {
                        try {
                            $this->translateSingleFaq($faq, $locale);
                            $this->stats[$locale]['faqs']++;
                        } catch (\Exception $e) {
                            if ($this->option('verbose')) {
                                $this->newLine();
                                $this->error("Error: " . $e->getMessage());
                            }
                        }
                    } else {
                        $this->stats[$locale]['faqs']++;
                    }

                    $bar->advance();
                }

                usleep($delay * 1000);
            }
        }

        $bar->finish();
        $this->newLine(2);
    }

    protected function translateTipMenus($modelIds, array $locales, int $batchSize, int $delay, bool $force, bool $dryRun): void
    {
        $this->section('Translating Tip Menus');

        $items = ModelTipMenuItem::whereIn('model_id', $modelIds)->get();
        
        if ($items->isEmpty()) {
            $this->line('   No tip menu items found.');
            return;
        }

        // Get unique action names to translate
        $uniqueActions = $items->pluck('action_name')->unique()->values()->toArray();
        $this->line("   Found " . count($uniqueActions) . " unique action names");

        foreach ($locales as $locale) {
            $this->line("   Translating tip menus to {$locale}...");
            
            if (!$dryRun) {
                try {
                    $translations = $this->batchTranslateActions($uniqueActions, $locale);
                    
                    // Save translations for each item
                    foreach ($items as $item) {
                        if (!$force && $this->hasTipMenuTranslation($item->id, $locale)) {
                            continue;
                        }
                        
                        $translatedName = $translations[$item->action_name] ?? $item->action_name;
                        
                        ModelTipMenuTranslation::updateOrCreate(
                            ['model_tip_menu_item_id' => $item->id, 'locale' => $locale],
                            ['action_name' => $translatedName]
                        );
                        
                        $this->stats[$locale]['tipmenus']++;
                    }
                } catch (\Exception $e) {
                    if ($this->option('verbose')) {
                        $this->error("Error: " . $e->getMessage());
                    }
                }
            } else {
                $this->stats[$locale]['tipmenus'] = $items->count();
            }

            usleep($delay * 1000);
        }

        $this->newLine();
    }

    protected function section(string $title): void
    {
        $this->newLine();
        $this->info("┌─────────────────────────────────────────────────────────────┐");
        $this->info("│ {$title}");
        $this->info("└─────────────────────────────────────────────────────────────┘");
    }

    protected function hasDescriptionTranslation(int $descriptionId, string $locale): bool
    {
        return ModelDescriptionTranslation::where('model_description_id', $descriptionId)
            ->where('locale', $locale)
            ->exists();
    }

    protected function hasFaqTranslation(int $faqId, string $locale): bool
    {
        return ModelFaqTranslation::where('model_faq_id', $faqId)
            ->where('locale', $locale)
            ->exists();
    }

    protected function hasTipMenuTranslation(int $itemId, string $locale): bool
    {
        return ModelTipMenuTranslation::where('model_tip_menu_item_id', $itemId)
            ->where('locale', $locale)
            ->exists();
    }

    protected function translateSingleDescription(ModelDescription $description, string $locale): void
    {
        $langName = $this->getLanguageName($locale);
        
        $textsToTranslate = array_filter([
            'short_description' => $description->short_description,
            'long_description' => $description->long_description,
            'specialties' => $description->specialties,
        ], fn($t) => !empty($t));

        if (empty($textsToTranslate)) {
            return;
        }

        $translated = $this->translateTexts($textsToTranslate, $locale);

        ModelDescriptionTranslation::updateOrCreate(
            ['model_description_id' => $description->id, 'locale' => $locale],
            [
                'short_description' => $translated['short_description'] ?? '',
                'long_description' => $translated['long_description'] ?? '',
                'specialties' => $translated['specialties'] ?? '',
            ]
        );
    }

    protected function translateSingleFaq(ModelFaq $faq, string $locale): void
    {
        $translated = $this->translateTexts([
            'question' => $faq->question,
            'answer' => $faq->answer,
        ], $locale);

        ModelFaqTranslation::updateOrCreate(
            ['model_faq_id' => $faq->id, 'locale' => $locale],
            [
                'question' => $translated['question'] ?? $faq->question,
                'answer' => $translated['answer'] ?? $faq->answer,
            ]
        );
    }

    protected function translateTexts(array $texts, string $locale): array
    {
        $langName = $this->getLanguageName($locale);
        
        $prompt = "Translate the following JSON to {$langName}. Keep the same keys and return valid JSON only.\n\n";
        $prompt .= json_encode($texts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $prompt .= "\n\nReturn only the JSON object with translated values.";

        $response = $this->callAnthropicApi($prompt);
        
        if (preg_match('/\{[\s\S]*\}/u', $response, $matches)) {
            $translated = json_decode($matches[0], true);
            if (is_array($translated)) {
                return array_merge($texts, $translated);
            }
        }

        return $texts;
    }

    protected function batchTranslateActions(array $actions, string $locale): array
    {
        if (empty($actions)) {
            return [];
        }

        $langName = $this->getLanguageName($locale);

        $prompt = "Translate these tip menu action names to {$langName}. These are adult cam site tip menu items.\n\n";
        $prompt .= "Actions: " . json_encode($actions, JSON_UNESCAPED_UNICODE) . "\n\n";
        $prompt .= "Return a JSON object where keys are the English names and values are the translations.\n";
        $prompt .= "Only return the JSON object, no other text.";

        $response = $this->callAnthropicApi($prompt);
        
        if (preg_match('/\{[\s\S]*\}/u', $response, $matches)) {
            $translated = json_decode($matches[0], true);
            if (is_array($translated)) {
                return $translated;
            }
        }

        return array_combine($actions, $actions);
    }

    protected function callAnthropicApi(string $prompt): string
    {
        $this->apiCalls++;
        
        $apiKey = config('services.anthropic.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('Anthropic API key not configured (ANTHROPIC_API_KEY)');
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('API error: ' . $response->body());
        }

        return $response->json('content.0.text', '');
    }

    protected function getLanguageName(string $locale): string
    {
        $locales = config('locales.supported', []);
        return $locales[$locale]['name'] ?? $locale;
    }

    protected function showSummary(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    Translation Summary                       ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');

        $tableData = [];
        $totalDescriptions = 0;
        $totalFaqs = 0;
        $totalTipMenus = 0;

        foreach ($this->stats as $locale => $counts) {
            $tableData[] = [
                $locale,
                $counts['descriptions'],
                $counts['faqs'],
                $counts['tipmenus'],
                $counts['descriptions'] + $counts['faqs'] + $counts['tipmenus'],
            ];
            $totalDescriptions += $counts['descriptions'];
            $totalFaqs += $counts['faqs'];
            $totalTipMenus += $counts['tipmenus'];
        }

        // Add totals row
        $tableData[] = ['──────', '────', '────', '────', '────'];
        $tableData[] = ['TOTAL', $totalDescriptions, $totalFaqs, $totalTipMenus, $totalDescriptions + $totalFaqs + $totalTipMenus];

        $this->table(
            ['Locale', 'Descriptions', 'FAQs', 'Tip Menus', 'Total'],
            $tableData
        );

        $this->newLine();
        $this->info("📡 API Calls Made: {$this->apiCalls}");
        $this->info("✅ Translation complete!");
        $this->newLine();
    }
}
