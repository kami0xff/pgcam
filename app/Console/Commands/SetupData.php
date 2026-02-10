<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupData extends Command
{
    protected $signature = 'setup:data
                            {--fresh : Clear and resync all data}
                            {--skip-translations : Skip translation generation}
                            {--skip-descriptions : Skip AI description generation}';

    protected $description = 'Master command to setup/sync all data (tags, countries, goals, counts)';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║              PornGuruCam Data Setup                          ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');

        $steps = [
            ['Syncing tags from enum', 'tags:sync'],
            ['Syncing countries from cam database', 'countries:sync'],
            ['Syncing model goals', 'sync:model-goals --limit=5000'],
            ['Recording online models for heatmap', 'heatmap:record'],
            ['Updating tag counts', 'tags:update-counts'],
            ['Updating country counts', 'tags:update-counts --countries'],
        ];

        if (!$this->option('skip-translations')) {
            $steps[] = ['Seeding English translations', 'translate:all --seed-tags --seed-countries'];
        }

        $total = count($steps);
        $current = 0;

        foreach ($steps as [$description, $command]) {
            $current++;
            $this->info("[$current/$total] $description...");
            
            try {
                $this->call($command);
                $this->info("    ✓ Done\n");
            } catch (\Exception $e) {
                $this->warn("    ⚠ Warning: " . $e->getMessage() . "\n");
            }
        }

        // Show summary
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                      Summary                                 ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        
        $this->table(
            ['Table', 'Count'],
            [
                ['tags', number_format(\App\Models\Tag::count())],
                ['tag_translations', number_format(\App\Models\TagTranslation::count())],
                ['countries', number_format(\App\Models\Country::count())],
                ['model_goals', number_format(\App\Models\ModelGoal::count())],
                ['model_heatmaps', number_format(\App\Models\ModelHeatmap::count())],
                ['model_online_snapshots', number_format(\App\Models\ModelOnlineSnapshot::count())],
            ]
        );

        $this->info('');
        $this->info('Next steps:');
        $this->info('  1. Set up the cron job: * * * * * cd /var/www/porngurucam && php artisan schedule:run >> /dev/null 2>&1');
        $this->info('  2. Generate AI descriptions: php artisan seo:generate-model-descriptions --limit=100');
        $this->info('  3. Translate to other languages: php artisan translate:profiles --locale=fr');
        $this->info('');

        return Command::SUCCESS;
    }
}
