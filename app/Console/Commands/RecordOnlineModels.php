<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\ModelOnlineSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RecordOnlineModels extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * Run this hourly via cron:
     * 0 * * * * cd /var/www/porngurucam && php artisan heatmap:record >> /dev/null 2>&1
     */
    protected $signature = 'heatmap:record
                            {--prune : Prune old snapshots (older than 8 weeks)}
                            {--dry-run : Show what would be recorded without saving}';

    protected $description = 'Record which models are currently online for heatmap generation';

    public function handle(): int
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
        $hourOfDay = $now->hour; // 0-23

        $this->info("Recording online models for {$now->format('l')} at {$now->format('H:i')}");
        $this->info("Day of week: {$dayOfWeek}, Hour: {$hourOfDay}");

        // Get all online models from the cam database
        $onlineModels = CamModel::online()
            ->select(['username', 'is_online', 'stream_status', 'viewers_count'])
            ->get();

        $this->info("Found {$onlineModels->count()} online models");

        if ($this->option('dry-run')) {
            $this->warn('Dry run mode - no data will be saved');
            $this->table(
                ['Username', 'Status', 'Viewers'],
                $onlineModels->take(20)->map(fn($m) => [$m->username, $m->stream_status, $m->viewers_count])
            );
            return self::SUCCESS;
        }

        $inserted = 0;
        $skipped = 0;

        // Batch insert for performance
        $snapshots = [];
        foreach ($onlineModels as $model) {
            // Skip if we already have a snapshot for this model this hour
            $exists = ModelOnlineSnapshot::where('model_id', $model->username)
                ->where('day_of_week', $dayOfWeek)
                ->where('hour_of_day', $hourOfDay)
                ->where('snapshot_at', '>=', $now->copy()->startOfHour())
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $snapshots[] = [
                'model_id' => $model->username,
                'snapshot_at' => $now,
                'day_of_week' => $dayOfWeek,
                'hour_of_day' => $hourOfDay,
                'is_online' => true,
                'viewers_count' => $model->viewers_count,
                'stream_status' => $model->stream_status,
            ];

            $inserted++;

            // Insert in batches of 1000
            if (count($snapshots) >= 1000) {
                ModelOnlineSnapshot::insert($snapshots);
                $snapshots = [];
            }
        }

        // Insert remaining
        if (!empty($snapshots)) {
            ModelOnlineSnapshot::insert($snapshots);
        }

        $this->info("Recorded {$inserted} snapshots, skipped {$skipped} duplicates");

        // Optionally prune old data
        if ($this->option('prune')) {
            $pruned = ModelOnlineSnapshot::pruneOlderThan(8);
            $this->info("Pruned {$pruned} old snapshots (> 8 weeks)");
        }

        return self::SUCCESS;
    }
}
