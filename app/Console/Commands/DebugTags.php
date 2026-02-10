<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use Illuminate\Console\Command;

class DebugTags extends Command
{
    protected $signature = 'debug:tags {--limit=5 : Number of models to show}';
    protected $description = 'Debug: Show how tags are stored in the cam database';

    public function handle(): int
    {
        $this->info('Checking tag format in CamModel database...');

        try {

            $list = [];
            // Get a few models with tags
            $models = CamModel::on('cam')
                ->whereNotNull('tags')
                ->whereJsonLength('tags', '>', '0')s
                ->limit((int) $this->option('limit'))
                ->get(['username', 'gender', 'tags']);

        
            //dunno why cannot connect to cam 
            foreach ($models as $model) {
                dd($model);
            }

            //i think the first tag in the list is always the niche 

            if ($models->isEmpty()) {
                $this->warn('No models with tags found');
                return Command::SUCCESS;
            }

            foreach ($models as $model) {
                $this->newLine();
                $this->info("Model: {$model->username} (gender: {$model->gender})");
                $this->line("Tags: " . json_encode($model->tags, JSON_PRETTY_PRINT));
            }

            // Show unique tag formats
            $this->newLine();
            $this->info('Sample tags from database:');

            $sampleTags = CamModel::on('cam')
                ->whereNotNull('tags')
                ->whereRaw("tags::text != '[]'")
                ->limit(100)
                ->pluck('tags')
                ->flatten()
                ->unique()
                ->take(50)
                ->values();

            foreach ($sampleTags as $tag) {
                $this->line("  - {$tag}");
            }

            // Check for specific tags
            $this->newLine();
            $this->info('Checking for "young" tag variations...');

            $youngCount = CamModel::on('cam')
                ->whereRaw("tags::text ILIKE ?", ['%young%'])
                ->count();
            $this->line("Models with 'young' in tags: {$youngCount}");

            $girlsYoungCount = CamModel::on('cam')
                ->whereRaw("tags::text ILIKE ?", ['%girls/young%'])
                ->count();
            $this->line("Models with 'girls/young' in tags: {$girlsYoungCount}");

            // Check gender distribution
            $this->newLine();
            $this->info('Gender distribution:');
            $genders = CamModel::on('cam')
                ->select('gender')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('gender')
                ->orderByDesc('count')
                ->get();

            foreach ($genders as $g) {
                $this->line("  {$g->gender}: {$g->count}");
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
