<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\ModelFaq;
use App\Models\ModelSeoContent;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class GenerateModelFaqs extends Command
{
    protected $signature = 'seo:generate-faqs 
                            {--limit=10 : Number of models to process}
                            {--model= : Generate FAQs for a specific model (username)}
                            {--locale=en : Locale for FAQs}
                            {--force : Regenerate existing FAQs}';

    protected $description = 'Generate SEO FAQs and descriptions for cam models using AI';

    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $locale = $this->option('locale');
        $force = $this->option('force');

        // Target a specific model if --model is provided
        $modelUsername = $this->option('model');

        if ($modelUsername) {
            $model = CamModel::where('username', $modelUsername)->first();
            if (!$model) {
                $this->error("Model '{$modelUsername}' not found.");
                return 1;
            }
            $models = collect([$model]);
        } else {
            // Get models without FAQs (prioritize popular ones)
            // CamModel is on a separate DB connection, so we can't use whereDoesntHave.
            // Instead, fetch IDs that already have FAQs and exclude them.
            $query = CamModel::orderBy('viewers_count', 'desc');

            if (!$force) {
                $existingModelIds = ModelFaq::where('locale', $locale)
                    ->distinct()
                    ->pluck('model_id')
                    ->toArray();

                if (!empty($existingModelIds)) {
                    $query->whereNotIn('id', $existingModelIds);
                }
            }

            $models = $query->limit($limit)->get();
        }

        if ($models->isEmpty()) {
            $this->info('No models need FAQs generated.');
            return 0;
        }

        $this->info("Generating FAQs for {$models->count()} models...");
        $bar = $this->output->createProgressBar($models->count());
        $bar->start();

        foreach ($models as $model) {
            try {
                // Generate FAQs
                $faqs = $this->translationService->generateModelFAQs([
                    'username' => $model->username,
                    'platform' => $model->source_platform,
                    'tags' => $model->tags ?? [],
                    'country' => $model->country,
                    'age' => $model->age,
                ], $locale);

                // Delete old FAQs if forcing
                if ($force) {
                    ModelFaq::where('model_id', $model->id)
                        ->where('locale', $locale)
                        ->delete();
                }

                // Save FAQs
                foreach ($faqs as $index => $faq) {
                    ModelFaq::create([
                        'model_id' => $model->id,
                        'locale' => $locale,
                        'question' => $faq['question'],
                        'answer' => $faq['answer'],
                        'sort_order' => $index,
                    ]);
                }

                // Generate SEO description
                $seoContent = $this->translationService->generateModelDescription([
                    'username' => $model->username,
                    'platform' => $model->source_platform,
                    'tags' => $model->tags ?? [],
                    'country' => $model->country,
                    'age' => $model->age,
                ], $locale);

                if (!empty($seoContent)) {
                    ModelSeoContent::updateOrCreate(
                        ['cam_model_id' => $model->id, 'locale' => $locale],
                        [
                            'meta_title' => $seoContent['meta_title'] ?? null,
                            'meta_description' => $seoContent['meta_description'] ?? null,
                            'long_description' => $seoContent['long_description'] ?? null,
                        ]
                    );
                }

            } catch (\Exception $e) {
                $this->warn("Failed for {$model->username}: " . $e->getMessage());
            }

            $bar->advance();
            usleep(200000); // Rate limiting
        }

        $bar->finish();
        $this->newLine();
        $this->info('FAQ generation complete!');

        return 0;
    }
}
