<?php

namespace App\Console\Commands;

use App\Models\TipActionType;
use App\Models\TipActionTranslation;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class TranslateTipActions extends Command
{
    protected $signature = 'seo:translate-tip-actions
                            {--locale= : Target locale (or "all" for all locales)}
                            {--force : Overwrite existing translations}
                            {--delay=2 : Delay in seconds between API calls (rate limiting)}';

    protected $description = 'Translate tip action types to other languages (batched to avoid rate limits)';

    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle(): int
    {
        $targetLocale = $this->option('locale');
        $force = $this->option('force');
        $delay = (int) $this->option('delay');

        $locales = [];
        if ($targetLocale === 'all' || !$targetLocale) {
            $locales = array_keys(config('locales.supported', []));
            // Remove English
            $locales = array_filter($locales, fn($l) => $l !== 'en');
        } else {
            $locales = [$targetLocale];
        }

        $actions = TipActionType::all();

        $this->info("🌐 Translating " . $actions->count() . " tip actions to " . count($locales) . " languages");
        $this->info("   Using batched requests (1 API call per language) with {$delay}s delay");
        $this->newLine();

        $bar = $this->output->createProgressBar(count($locales));
        $bar->start();

        $translated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($locales as $locale) {
            // Check if we need to translate this locale
            $existingCount = TipActionTranslation::where('locale', $locale)->count();
            
            if ($existingCount >= $actions->count() && !$force) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                // Batch translate ALL actions in one API call
                $translations = $this->translateAllActions($actions, $locale);

                // Save all translations
                foreach ($actions as $action) {
                    $slug = $action->slug;
                    if (isset($translations[$slug])) {
                        TipActionTranslation::updateOrCreate(
                            [
                                'tip_action_type_id' => $action->id,
                                'locale' => $locale,
                            ],
                            [
                                'name' => $translations[$slug]['name'] ?? $action->name,
                                'description' => $translations[$slug]['description'] ?? $action->description,
                            ]
                        );
                    }
                }
                $translated++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("  ✗ Failed {$locale}: " . $e->getMessage());
            }

            $bar->advance();
            
            // Rate limiting - wait between API calls
            sleep($delay);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Translation complete!");
        $this->table(
            ['Languages Translated', 'Skipped (existing)', 'Failed'],
            [[$translated, $skipped, $failed]]
        );

        return Command::SUCCESS;
    }

    /**
     * Translate ALL actions in a single API call to avoid rate limits
     */
    protected function translateAllActions($actions, string $locale): array
    {
        $langName = $this->translationService->getLanguageName($locale);

        // Build list of actions to translate
        $actionList = $actions->map(fn($a) => [
            'slug' => $a->slug,
            'name' => $a->name,
            'description' => $a->description,
        ])->toArray();

        $actionsJson = json_encode($actionList, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
Translate these adult cam site tip menu actions to {$langName}.

Actions to translate:
{$actionsJson}

Context: These are tip menu actions on a live cam site (e.g., "Flash" = quick body reveal, "Striptease" = slow undressing). Keep translations natural and short.

Respond with a JSON object where keys are the slugs and values contain translated name and description:
{
  "flash": {"name": "translated name", "description": "translated description"},
  "boobs-flash": {"name": "...", "description": "..."},
  ... (all actions)
}

Only output the JSON, no other text.
PROMPT;

        $response = $this->callAnthropicDirect($prompt);
        
        return $this->parseBatchResponse($response);
    }

    protected function callAnthropicDirect(string $prompt): string
    {
        $apiKey = config('services.anthropic.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('Anthropic API key not configured');
        }

        $response = \Illuminate\Support\Facades\Http::timeout(60)->withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 2048, // Larger for batch response
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('API error: ' . $response->body());
        }

        return $response->json('content.0.text', '');
    }

    protected function parseBatchResponse(string $response): array
    {
        // Clean control characters
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $response);
        
        // Extract JSON object
        if (preg_match('/\{[\s\S]*\}/u', $cleaned, $matches)) {
            $cleaned = $matches[0];
        }

        $data = json_decode($cleaned, true);

        if ($data === null) {
            throw new \Exception('Failed to parse batch response: ' . json_last_error_msg());
        }

        return $data;
    }
}
