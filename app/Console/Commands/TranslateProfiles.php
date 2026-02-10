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
use Illuminate\Support\Facades\Http;

class TranslateProfiles extends Command
{
    protected $signature = 'translate:profiles
                            {--locale=fr : Target locale to translate to}
                            {--limit=50 : Maximum profiles to translate}
                            {--model= : Specific model ID to translate}
                            {--type=all : Type to translate (all, descriptions, faqs, tipmenus)}
                            {--batch=5 : Batch size for API calls}
                            {--delay=1000 : Delay between batches in ms}
                            {--force : Overwrite existing translations}';

    protected $description = 'Translate all model profile content to a specific locale';

    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle(): int
    {
        $locale = $this->option('locale');
        $limit = (int) $this->option('limit');
        $type = $this->option('type');
        $specificModel = $this->option('model');
        $batchSize = (int) $this->option('batch');
        $delay = (int) $this->option('delay');
        $force = $this->option('force');

        if ($locale === 'en') {
            $this->error('Cannot translate to English - English is the source language.');
            return 1;
        }

        $langName = $this->translationService->getLanguageName($locale);
        $this->info("🌐 Translating profile content to {$langName} ({$locale})");
        $this->newLine();

        $stats = [
            'descriptions' => ['translated' => 0, 'skipped' => 0, 'failed' => 0],
            'faqs' => ['translated' => 0, 'skipped' => 0, 'failed' => 0],
            'tipmenus' => ['translated' => 0, 'skipped' => 0, 'failed' => 0],
        ];

        // Translate descriptions
        if (in_array($type, ['all', 'descriptions'])) {
            $this->info('📝 Translating descriptions...');
            $stats['descriptions'] = $this->translateDescriptions($locale, $limit, $specificModel, $batchSize, $delay, $force);
        }

        // Translate FAQs
        if (in_array($type, ['all', 'faqs'])) {
            $this->info('❓ Translating FAQs...');
            $stats['faqs'] = $this->translateFaqs($locale, $limit, $specificModel, $batchSize, $delay, $force);
        }

        // Translate tip menus
        if (in_array($type, ['all', 'tipmenus'])) {
            $this->info('💰 Translating tip menus...');
            $stats['tipmenus'] = $this->translateTipMenus($locale, $limit, $specificModel, $batchSize, $delay, $force);
        }

        $this->newLine();
        $this->info('✅ Translation complete!');
        $this->table(
            ['Type', 'Translated', 'Skipped', 'Failed'],
            [
                ['Descriptions', $stats['descriptions']['translated'], $stats['descriptions']['skipped'], $stats['descriptions']['failed']],
                ['FAQs', $stats['faqs']['translated'], $stats['faqs']['skipped'], $stats['faqs']['failed']],
                ['Tip Menus', $stats['tipmenus']['translated'], $stats['tipmenus']['skipped'], $stats['tipmenus']['failed']],
            ]
        );

        return 0;
    }

    /**
     * Translate model descriptions
     */
    protected function translateDescriptions(string $locale, int $limit, ?string $specificModel, int $batchSize, int $delay, bool $force): array
    {
        $stats = ['translated' => 0, 'skipped' => 0, 'failed' => 0];

        $query = ModelDescription::query();
        
        if ($specificModel) {
            $query->where('model_id', $specificModel);
        }
        
        if (!$force) {
            $query->withoutTranslation($locale);
        }

        $descriptions = $query->limit($limit)->get();

        if ($descriptions->isEmpty()) {
            $this->line('   No descriptions to translate.');
            return $stats;
        }

        $bar = $this->output->createProgressBar($descriptions->count());
        $bar->start();

        foreach ($descriptions->chunk($batchSize) as $batch) {
            foreach ($batch as $description) {
                // Check if translation exists
                if (!$force && $description->hasTranslation($locale)) {
                    $stats['skipped']++;
                    $bar->advance();
                    continue;
                }

                try {
                    $translated = $this->translateText([
                        'short_description' => $description->short_description,
                        'long_description' => $description->long_description,
                        'specialties' => $description->specialties,
                    ], $locale);

                    ModelDescriptionTranslation::updateOrCreate(
                        ['model_description_id' => $description->id, 'locale' => $locale],
                        [
                            'short_description' => $translated['short_description'] ?? '',
                            'long_description' => $translated['long_description'] ?? '',
                            'specialties' => $translated['specialties'] ?? '',
                        ]
                    );

                    $stats['translated']++;
                } catch (\Exception $e) {
                    $stats['failed']++;
                    if ($this->option('verbose')) {
                        $this->newLine();
                        $this->error("   Error: " . $e->getMessage());
                    }
                }

                $bar->advance();
            }

            // Delay between batches
            usleep($delay * 1000);
        }

        $bar->finish();
        $this->newLine();

        return $stats;
    }

    /**
     * Translate model FAQs
     */
    protected function translateFaqs(string $locale, int $limit, ?string $specificModel, int $batchSize, int $delay, bool $force): array
    {
        $stats = ['translated' => 0, 'skipped' => 0, 'failed' => 0];

        $query = ModelFaq::query();
        
        if ($specificModel) {
            $query->where('model_id', $specificModel);
        }
        
        if (!$force) {
            $query->withoutTranslation($locale);
        }

        $faqs = $query->limit($limit)->get();

        if ($faqs->isEmpty()) {
            $this->line('   No FAQs to translate.');
            return $stats;
        }

        $bar = $this->output->createProgressBar($faqs->count());
        $bar->start();

        foreach ($faqs->chunk($batchSize) as $batch) {
            foreach ($batch as $faq) {
                try {
                    $translated = $this->translateText([
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

                    $stats['translated']++;
                } catch (\Exception $e) {
                    $stats['failed']++;
                }

                $bar->advance();
            }

            usleep($delay * 1000);
        }

        $bar->finish();
        $this->newLine();

        return $stats;
    }

    /**
     * Translate tip menu items
     */
    protected function translateTipMenus(string $locale, int $limit, ?string $specificModel, int $batchSize, int $delay, bool $force): array
    {
        $stats = ['translated' => 0, 'skipped' => 0, 'failed' => 0];

        $query = ModelTipMenuItem::query();
        
        if ($specificModel) {
            $query->where('model_id', $specificModel);
        }
        
        if (!$force) {
            $query->whereDoesntHave('translations', function ($q) use ($locale) {
                $q->where('locale', $locale);
            });
        }

        $items = $query->limit($limit)->get();

        if ($items->isEmpty()) {
            $this->line('   No tip menu items to translate.');
            return $stats;
        }

        // Batch translate unique action names
        $uniqueActions = $items->pluck('action_name')->unique()->values()->toArray();
        
        $this->line("   Translating " . count($uniqueActions) . " unique action names...");
        
        try {
            $translations = $this->batchTranslateActions($uniqueActions, $locale);
            
            // Store translations
            foreach ($items as $item) {
                $translatedName = $translations[$item->action_name] ?? $item->action_name;
                
                ModelTipMenuTranslation::updateOrCreate(
                    ['model_tip_menu_item_id' => $item->id, 'locale' => $locale],
                    ['action_name' => $translatedName]
                );
                
                $stats['translated']++;
            }
        } catch (\Exception $e) {
            $stats['failed'] = $items->count();
            if ($this->option('verbose')) {
                $this->error("   Error: " . $e->getMessage());
            }
        }

        return $stats;
    }

    /**
     * Translate a single text array
     */
    protected function translateText(array $texts, string $locale): array
    {
        $langName = $this->translationService->getLanguageName($locale);
        
        // Filter out empty values
        $textsToTranslate = array_filter($texts, fn($t) => !empty($t));
        
        if (empty($textsToTranslate)) {
            return $texts;
        }

        $prompt = "Translate the following texts to {$langName}. Keep the same keys and return valid JSON.\n\n";
        $prompt .= json_encode($textsToTranslate, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $prompt .= "\n\nReturn only the JSON object with translated values.";

        $response = $this->callAnthropicApi($prompt);
        
        // Parse response
        if (preg_match('/\{[\s\S]*\}/u', $response, $matches)) {
            $translated = json_decode($matches[0], true);
            if (is_array($translated)) {
                return array_merge($texts, $translated);
            }
        }

        return $texts;
    }

    /**
     * Batch translate action names
     */
    protected function batchTranslateActions(array $actions, string $locale): array
    {
        if (empty($actions)) {
            return [];
        }

        $langName = $this->translationService->getLanguageName($locale);

        $prompt = "Translate these tip menu action names to {$langName}. These are adult cam site tip menu items.\n\n";
        $prompt .= "Actions: " . json_encode($actions, JSON_UNESCAPED_UNICODE) . "\n\n";
        $prompt .= "Return a JSON object where keys are the English names and values are the translations.\n";
        $prompt .= "Example: {\"Flash\": \"Flash\", \"Dance\": \"Danse\"}\n";
        $prompt .= "Only return the JSON object.";

        $response = $this->callAnthropicApi($prompt);
        
        if (preg_match('/\{[\s\S]*\}/u', $response, $matches)) {
            $translated = json_decode($matches[0], true);
            if (is_array($translated)) {
                return $translated;
            }
        }

        // Return original names if translation fails
        return array_combine($actions, $actions);
    }

    /**
     * Call Anthropic API directly
     */
    protected function callAnthropicApi(string $prompt): string
    {
        $apiKey = config('services.anthropic.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('Anthropic API key not configured');
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('API error: ' . $response->body());
        }

        return $response->json('content.0.text', '');
    }
}
