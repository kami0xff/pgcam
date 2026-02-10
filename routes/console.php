<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| These tasks run automatically via the Laravel scheduler.
| Add this cron entry to your server:
| * * * * * cd /var/www/porngurucam && php artisan schedule:run >> /dev/null 2>&1
|
| Inside Docker:
| * * * * * docker compose -f /path/docker-compose.dev.yml exec -T app php artisan schedule:run
|
*/

// ============================================================================
// EVERY 15 MINUTES - Goal tracking (catch fast goal changes)
// ============================================================================

// Sync model goals frequently to catch goal completions
Schedule::command('sync:model-goals', ['--limit' => 5000, '--online' => true])
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/model-goals.log'));

// ============================================================================
// HOURLY TASKS
// ============================================================================

// Record which models are online every hour (for heatmap data)
Schedule::command('heatmap:record')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/heatmap-record.log'));

// Full goal sync hourly (including offline models)
Schedule::command('sync:model-goals', ['--limit' => 10000])
    ->hourly()
    ->withoutOverlapping(5 * 60) // 5 minute expiration
    ->appendOutputTo(storage_path('logs/model-goals-full.log'));

// ============================================================================
// EVERY 6 HOURS - Data counts refresh
// ============================================================================

// Update tag and country model counts
Schedule::command('tags:update-counts', ['--countries' => true])
    ->everySixHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/update-counts.log'));

// ============================================================================
// DAILY TASKS
// ============================================================================

// Aggregate heatmap data daily at 4 AM
Schedule::command('heatmap:aggregate', ['--weeks' => 4])
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/heatmap-aggregate.log'));

// Sync countries daily at 5 AM (in case new countries appear)
Schedule::command('countries:sync')
    ->dailyAt('05:00')
    ->appendOutputTo(storage_path('logs/countries-sync.log'));

// Generate AI descriptions for top models daily at 6 AM
Schedule::command('seo:generate-model-descriptions', ['--limit' => 20, '--online' => true])
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/descriptions.log'));

// ============================================================================
// WEEKLY TASKS
// ============================================================================

// Prune old heatmap snapshots weekly (Sunday at 3 AM)
Schedule::command('heatmap:record', ['--prune' => true])
    ->weeklyOn(0, '03:00')
    ->appendOutputTo(storage_path('logs/heatmap-prune.log'));

// Regenerate sitemaps weekly (Sunday at 6 AM)
Schedule::command('sitemap:generate', ['--warm-cache' => true])
    ->weeklyOn(0, '06:00')
    ->appendOutputTo(storage_path('logs/sitemap-generate.log'));

// Sync tags from enum weekly (Sunday at 7 AM)
Schedule::command('tags:sync', ['--seed' => true])
    ->weeklyOn(0, '07:00')
    ->appendOutputTo(storage_path('logs/tags-sync.log'));
