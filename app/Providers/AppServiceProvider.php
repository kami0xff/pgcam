<?php

namespace App\Providers;

use App\Models\CamModel;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicit route model binding for CamModel (on external 'cam' connection).
        // Implicit binding fails in localized route groups ({locale}/model/{model}).
        Route::bind('model', function (string $value) {
            return CamModel::where('username', $value)->firstOrFail();
        });

        // Log scheduled command invocations to storage/logs/schedule.log
        $this->registerScheduleLogging();
    }

    /**
     * Register lightweight logging for scheduled tasks.
     * Logs command name, parameters, timing, and exit code.
     */
    protected function registerScheduleLogging(): void
    {
        $logger = Log::channel('schedule');

        Event::listen(ScheduledTaskStarting::class, function (ScheduledTaskStarting $event) use ($logger) {
            $summary = $this->describeScheduledTask($event->task);
            $logger->info("STARTED  {$summary}");
        });

        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) use ($logger) {
            $summary = $this->describeScheduledTask($event->task);
            $runtime = round($event->runtime, 2);
            $exit = $event->task->exitCode ?? 0;
            $status = $exit === 0 ? 'OK' : "EXIT:{$exit}";
            $logger->info("FINISHED {$summary}  [{$status}, {$runtime}s]");
        });

        Event::listen(ScheduledTaskSkipped::class, function (ScheduledTaskSkipped $event) use ($logger) {
            $summary = $this->describeScheduledTask($event->task);
            $logger->warning("SKIPPED  {$summary}  (overlapping or condition failed)");
        });
    }

    /**
     * Extract a human-readable summary from a scheduled task event.
     */
    protected function describeScheduledTask(\Illuminate\Console\Scheduling\Event $task): string
    {
        $command = $task->command ?? $task->description ?? 'unknown';

        // Strip the PHP binary and artisan prefix to get just the command + args
        // e.g., "'/usr/bin/php' 'artisan' sync:model-goals --limit=5000 --online"
        if (preg_match("/artisan['\"]?\s+(.+)$/", $command, $matches)) {
            return trim($matches[1]);
        }

        // Fallback: strip quotes and return as-is
        return str_replace(["'", '"'], '', $command);
    }
}
