<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\ModelDescription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Generate AI descriptions for cam models (English only).
 * 
 * This command generates English descriptions only.
 * For translations, use: php artisan translate:profiles --locale=fr
 */
class GenerateModelDescriptions extends Command
{
    protected $signature = 'seo:generate-model-descriptions
                            {--limit=50 : Number of models to process}
                            {--force : Overwrite existing descriptions}
                            {--model= : Specific model username}
                            {--online : Only process online models}
                            {--batch=10 : Batch size for API calls}
                            {--delay=500 : Delay between API calls in ms}';

    protected $description = 'Generate AI descriptions for cam models (English only)';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $force = $this->option('force');
        $specificModel = $this->option('model');
        $onlineOnly = $this->option('online');
        $delay = (int) $this->option('delay');

        $this->info('📝 Generating English descriptions for cam models...');
        $this->newLine();

        // Get models that need descriptions
        $query = CamModel::on('cam');

        if ($onlineOnly) {
            $query->where('is_online', true);
        }

        if ($specificModel) {
            $query->where('username', $specificModel);
        }

        // Exclude models that already have descriptions
        if (!$force) {
            $existingIds = ModelDescription::pluck('model_id')->toArray();
            if (!empty($existingIds)) {
                $query->whereNotIn('username', $existingIds);
            }
        }

        $query->orderByDesc('viewers_count');
        $models = $query->limit($limit)->get();

        if ($models->isEmpty()) {
            $this->info('No models found that need descriptions.');
            return 0;
        }

        $this->info("Processing {$models->count()} models...");
        
        $bar = $this->output->createProgressBar($models->count());
        $bar->start();

        $stats = ['generated' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($models as $model) {
            // Check if description already exists
            if (!$force && ModelDescription::where('model_id', $model->username)->exists()) {
                $stats['skipped']++;
                $bar->advance();
                continue;
            }

            try {
                $description = $this->generateDescription($model);

                if (!empty($description['short_description'])) {
                    ModelDescription::updateOrCreate(
                        ['model_id' => $model->username],
                        [
                            'short_description' => $description['short_description'],
                            'long_description' => $description['long_description'],
                            'personality_traits' => json_encode($description['traits'] ?? []),
                            'specialties' => $description['specialties'] ?? null,
                            'source' => 'ai',
                            'is_approved' => false,
                        ]
                    );
                    $stats['generated']++;
                } else {
                    $stats['failed']++;
                }
            } catch (\Exception $e) {
                $stats['failed']++;
                if ($this->option('verbose')) {
                    $this->newLine();
                    $this->error("Error for {$model->username}: " . $e->getMessage());
                }
            }

            $bar->advance();
            usleep($delay * 1000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Generation complete!');
        $this->table(
            ['Generated', 'Skipped', 'Failed'],
            [[$stats['generated'], $stats['skipped'], $stats['failed']]]
        );

        $this->newLine();
        $this->line('💡 To translate descriptions to other languages, run:');
        $this->line('   php artisan translate:profiles --locale=fr');

        return 0;
    }

    /**
     * Generate description for a model
     */
    protected function generateDescription(CamModel $model): array
    {
        // Get model tags from stripchat_profiles if available
        $tags = $this->getModelTags($model);
        $country = $model->country ?? 'unknown';

        $prompt = <<<PROMPT
Generate a description for an adult cam model:

Username: {$model->username}
Country: {$country}
Tags/Categories: {$tags}

Create a compelling, SEO-friendly description that:
1. Highlights what makes this model special
2. Mentions her style and what viewers can expect
3. Is appropriate for adult content but not explicit
4. Sounds natural and engaging

Respond in this exact JSON format:
{
    "short_description": "1-2 sentences, catchy intro (50-100 chars)",
    "long_description": "2-3 paragraphs describing the model (200-400 words)",
    "traits": ["trait1", "trait2", "trait3"],
    "specialties": "What she's known for or excels at"
}

Keep the tone warm, inviting, and professional.
PROMPT;

        $response = $this->callAnthropicApi($prompt);
        return $this->parseResponse($response);
    }

    /**
     * Get tags for a model from stripchat_profiles
     */
    protected function getModelTags(CamModel $model): string
    {
        $profile = \DB::connection('cam')
            ->table('stripchat_profiles')
            ->where('username', $model->username)
            ->first(['tags']);

        if ($profile && $profile->tags) {
            $tags = json_decode($profile->tags, true);
            if (is_array($tags)) {
                // Extract unique base tags (remove niche prefixes)
                $baseTags = [];
                foreach ($tags as $tag) {
                    $parts = explode('/', $tag);
                    $baseTags[] = end($parts);
                }
                return implode(', ', array_unique(array_slice($baseTags, 0, 15)));
            }
        }

        return 'unknown';
    }

    /**
     * Call Anthropic API
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
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('API error: ' . $response->body());
        }

        return $response->json('content.0.text', '');
    }

    /**
     * Parse API response
     */
    protected function parseResponse(string $response): array
    {
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $response);

        if (preg_match('/\{[\s\S]*\}/u', $cleaned, $matches)) {
            $data = json_decode($matches[0], true);
            if (is_array($data)) {
                return [
                    'short_description' => $data['short_description'] ?? '',
                    'long_description' => $data['long_description'] ?? '',
                    'traits' => $data['traits'] ?? [],
                    'specialties' => $data['specialties'] ?? '',
                ];
            }
        }

        return [
            'short_description' => '',
            'long_description' => '',
            'traits' => [],
            'specialties' => '',
        ];
    }
}
