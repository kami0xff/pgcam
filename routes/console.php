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
| Cron entry for production (on the HOST, not inside the container):
|
| * * * * * docker compose --env-file /var/www/porngurucam/.env.production \
|   -f /var/www/porngurucam/docker-compose.prod.yml \
|   exec -T app php artisan schedule:run >> /dev/null 2>&1
|
*/

// ============================================================================
// EVERY 15 MINUTES - Goal tracking
// ============================================================================

// Sync goals for online models (catch fast goal completions)
Schedule::command('sync:model-goals', ['--limit' => 5000, '--online' => true])
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/model-goals.log'));

// ============================================================================
// HOURLY TASKS
// ============================================================================

// Record which models are online (heatmap data)
Schedule::command('heatmap:record')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/heatmap-record.log'));

// Full goal sync including offline models
Schedule::command('sync:model-goals', ['--limit' => 10000])
    ->hourly()
    ->withoutOverlapping(5 * 60)
    ->appendOutputTo(storage_path('logs/model-goals-full.log'));

// ============================================================================
// EVERY 2 HOURS - AI content generation (rate-limited, one at a time)
// ============================================================================

// Generate AI descriptions at even hours (00:00, 02:00, 04:00, ...)
// 5 models per run, prioritize online high-viewership models
// ~60 descriptions/day, one API call at a time to respect Anthropic rate limits
Schedule::command('seo:generate-model-descriptions', [
    '--limit' => 5,
    '--online' => true,
    '--batch' => 1,
    '--delay' => 2000,
])
    ->cron('0 */2 * * *')
    ->withoutOverlapping(30 * 60)
    ->appendOutputTo(storage_path('logs/descriptions.log'));

// Translate profiles at odd hours (01:00, 03:00, 05:00, ...)
// 3 models per run to priority locales, staggered from descriptions
Schedule::command('translate:all-profiles', [
    '--limit' => 3,
    '--types' => 'descriptions',
    '--batch-size' => 1,
    '--delay' => 2000,
])
    ->cron('0 1-23/2 * * *')
    ->withoutOverlapping(30 * 60)
    ->appendOutputTo(storage_path('logs/translate-profiles.log'));

// ============================================================================
// EVERY 6 HOURS - Data counts refresh
// ============================================================================

Schedule::command('tags:update-counts', ['--countries' => true])
    ->everySixHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/update-counts.log'));

// ============================================================================
// DAILY TASKS
// ============================================================================

// Aggregate heatmap data (4 AM)
Schedule::command('heatmap:aggregate', ['--weeks' => 4])
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/heatmap-aggregate.log'));

// Sync countries (5 AM)
Schedule::command('countries:sync')
    ->dailyAt('05:00')
    ->appendOutputTo(storage_path('logs/countries-sync.log'));

// Generate FAQs for top models (7 AM, small batch)
Schedule::command('seo:generate-faqs', [
    '--limit' => 5,
])
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/faqs.log'));

// Generate SEO page content for tags & countries (7:30 AM)
Schedule::command('seo:generate-page-content', [
    '--tags' => true,
    '--countries' => true,
    '--limit' => 20,
])
    ->dailyAt('07:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/page-seo-content.log'));

// Translate tags & countries if new ones appeared (8 AM)
Schedule::command('translate:all', [
    '--priority' => true,
    '--skip-existing' => true,
    '--batch' => 5,
    '--delay' => 3,
])
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/translate-tags.log'));

// ============================================================================
// WEEKLY TASKS (Sunday)
// ============================================================================

// Prune old heatmap snapshots (Sunday 3 AM)
Schedule::command('heatmap:record', ['--prune' => true])
    ->weeklyOn(0, '03:00')
    ->appendOutputTo(storage_path('logs/heatmap-prune.log'));

// Regenerate sitemaps (Sunday 6 AM)
Schedule::command('sitemap:generate', ['--warm-cache' => true])
    ->weeklyOn(0, '06:00')
    ->appendOutputTo(storage_path('logs/sitemap-generate.log'));

// Sync tags from enum (Sunday 7 AM)
Schedule::command('tags:sync', ['--seed' => true])
    ->weeklyOn(0, '07:00')
    ->appendOutputTo(storage_path('logs/tags-sync.log'));

// Translate tip actions for new locales (Sunday 9 AM)
Schedule::command('seo:translate-tip-actions', [
    '--locale' => 'all',
    '--delay' => 3,
])
    ->weeklyOn(0, '09:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/translate-tip-actions.log'));
