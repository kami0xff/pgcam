<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Master orchestration command for data collection tasks.
 * 
 * This command handles:
 * - Data syncing (tags, countries, goals, heatmap)
 * - AI description generation
 * - SEO tasks (sitemaps)
 * 
 * For translations, use the separate background worker:
 *   php artisan translate:worker --rate=10
 */
class RunAllTasks extends Command
{
    protected $signature = 'run:all
                            {--data : Run data collection tasks (goals, heatmap, counts)}
                            {--descriptions : Generate AI descriptions for models}
                            {--seo : Run SEO tasks (sitemaps)}
                            {--full : Run everything (data + descriptions + seo)}
                            {--desc-limit=50 : Number of descriptions to generate}
                            {--fresh : Clear caches and regenerate everything}';

    protected $description = 'Master orchestration command for data gathering and SEO tasks';

    public function handle(): int
    {
        $this->showBanner();

        // Check database connectivity
        if (!$this->checkDatabaseConnectivity()) {
            $this->error('Database connectivity check failed. Make sure you are running inside Docker.');
            $this->line('Run: docker compose -f docker-compose.dev.yml exec app php artisan run:all --full');
            return Command::FAILURE;
        }

        $runData = $this->option('data') || $this->option('full');
        $runDescriptions = $this->option('descriptions') || $this->option('full');
        $runSeo = $this->option('seo') || $this->option('full');
        $fresh = $this->option('fresh');

        // If no specific options, run data by default
        if (!$runData && !$runDescriptions && !$runSeo) {
            $runData = true;
        }

        $startTime = microtime(true);

        // Clear caches if fresh
        if ($fresh) {
            $this->section('Clearing Caches');
            $this->runTask('Clearing application cache', 'cache:clear');
            $this->runTask('Clearing view cache', 'view:clear');
        }

        // ============================================================================
        // DATA COLLECTION TASKS
        // ============================================================================
        if ($runData) {
            $this->section('Data Collection');
            
            $this->runTask('Syncing tags from enum', 'tags:sync', ['--seed' => true]);
            $this->runTask('Syncing countries from database', 'countries:sync');
            $this->runTask('Recording online models for heatmap', 'heatmap:record');
            $this->runTask('Syncing model goals', 'sync:model-goals', ['--limit' => 10000]);
            $this->runTask('Aggregating heatmap data', 'heatmap:aggregate', ['--weeks' => 4]);
            $this->runTask('Updating tag counts', 'tags:update-counts');
            $this->runTask('Updating country counts', 'tags:update-counts', ['--countries' => true]);
        }

        // ============================================================================
        // AI DESCRIPTION GENERATION
        // ============================================================================
        if ($runDescriptions) {
            $this->section('AI Description Generation');
            
            $limit = (int) $this->option('desc-limit');
            $this->runTask(
                "Generating descriptions for {$limit} models",
                'seo:generate-model-descriptions',
                ['--limit' => $limit, '--batch' => 10]
            );
        }

        // ============================================================================
        // SEO TASKS
        // ============================================================================
        if ($runSeo) {
            $this->section('SEO Tasks');
            
            $this->runTask('Generating sitemaps', 'sitemap:generate', ['--warm-cache' => true]);
        }

        // ============================================================================
        // SUMMARY
        // ============================================================================
        $this->showSummary($startTime);

        $this->newLine();
        $this->info('💡 For translations, run the background worker in a separate terminal:');
        $this->line('   docker compose -f docker-compose.dev.yml exec app php artisan translate:worker --rate=10');

        return Command::SUCCESS;
    }

    protected function checkDatabaseConnectivity(): bool
    {
        try {
            // Check main pgsql connection
            DB::connection('pgsql')->getPdo();
            $this->line('  <fg=green>✓</> PostgreSQL connection OK');
            
            // Check cam connection
            DB::connection('cam')->getPdo();
            $this->line('  <fg=green>✓</> Cam database connection OK');
            
            return true;
        } catch (\Exception $e) {
            $this->error('  ✗ Database connection failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function showBanner(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║          PornGuruCam Master Task Runner                      ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    protected function section(string $title): void
    {
        $this->newLine();
        $this->info("┌─────────────────────────────────────────────────────────────┐");
        $this->info("│ {$title}");
        $this->info("└─────────────────────────────────────────────────────────────┘");
    }

    protected function runTask(string $description, string $command, array $arguments = []): void
    {
        $this->line("  → {$description}...");
        
        try {
            $exitCode = $this->call($command, $arguments);
            
            if ($exitCode === 0) {
                $this->line("    <fg=green>✓</> Done");
            } else {
                $this->line("    <fg=yellow>⚠</> Completed with exit code {$exitCode}");
            }
        } catch (\Exception $e) {
            $this->line("    <fg=red>✗</> Error: " . $e->getMessage());
        }
    }

    protected function showSummary(float $startTime): void
    {
        $elapsed = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                      Summary                                 ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');

        try {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Tags', number_format(\App\Models\Tag::count())],
                    ['Tag Translations', number_format(\App\Models\TagTranslation::count())],
                    ['Countries', number_format(\App\Models\Country::count())],
                    ['Model Goals', number_format(\App\Models\ModelGoal::count())],
                    ['Completed Goals', number_format(\App\Models\ModelGoal::completed()->count())],
                    ['Model Heatmaps', number_format(\App\Models\ModelHeatmap::count())],
                    ['Online Snapshots', number_format(\App\Models\ModelOnlineSnapshot::count())],
                    ['Model Descriptions', number_format(\App\Models\ModelDescription::count())],
                    ['Elapsed Time', "{$elapsed}s"],
                ]
            );
        } catch (\Exception $e) {
            $this->warn("Could not display summary: " . $e->getMessage());
        }

        $this->newLine();
        $this->info("✅ All tasks completed in {$elapsed} seconds");
    }
}
