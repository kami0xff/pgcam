<?php

namespace App\Console\Commands;

use App\Models\ModelHeatmap;
use App\Models\ModelOnlineSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateHeatmaps extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * Run this daily via cron (e.g., at 4 AM):
     * 0 4 * * * cd /var/www/porngurucam && php artisan heatmap:aggregate >> /dev/null 2>&1
     */
    protected $signature = 'heatmap:aggregate
                            {--model= : Aggregate for a specific model only}
                            {--weeks=4 : Number of weeks of data to consider}
                            {--min-snapshots=4 : Minimum snapshots required for a valid percentage}
                            {--fresh : Delete existing heatmap data and recalculate}
                            {--dry-run : Show what would be calculated without saving}';

    protected $description = 'Aggregate online snapshots into heatmap percentages';

    public function handle(): int
    {
        $weeks = (int) $this->option('weeks');
        $minSnapshots = (int) $this->option('min-snapshots');
        $specificModel = $this->option('model');

        $this->info("Aggregating heatmap data from the last {$weeks} weeks");
        $this->info("Minimum snapshots required: {$minSnapshots}");

        if ($this->option('fresh')) {
            if ($specificModel) {
                ModelHeatmap::where('model_id', $specificModel)->delete();
                $this->warn("Cleared existing heatmap for model: {$specificModel}");
            } else {
                ModelHeatmap::truncate();
                $this->warn("Cleared all existing heatmap data");
            }
        }

        if ($this->option('dry-run')) {
            return $this->dryRun($weeks, $minSnapshots, $specificModel);
        }

        if ($specificModel) {
            return $this->aggregateSingleModel($specificModel, $weeks, $minSnapshots);
        }

        return $this->aggregateBulk($weeks, $minSnapshots);
    }

    /**
     * Aggregate all models using a single bulk SQL upsert.
     * Replaces the old per-model loop (188K individual queries -> 1 query).
     */
    protected function aggregateBulk(int $weeks, int $minSnapshots): int
    {
        $cutoff = now()->subDays($weeks * 7)->toDateTimeString();
        $timesChecked = $weeks;
        $minPercentage = 5;

        $this->info('Running bulk aggregation via single SQL upsert...');

        $affected = DB::statement("
            INSERT INTO model_heatmaps (model_id, day_of_week, hour_of_day, times_online, times_checked, online_percentage, avg_viewers, last_seen_at, created_at, updated_at)
            SELECT
                model_id,
                day_of_week,
                hour_of_day,
                cnt AS times_online,
                ? AS times_checked,
                LEAST(100, ROUND((cnt::numeric / ? * 100), 2)) AS online_percentage,
                ROUND(avg_v) AS avg_viewers,
                max_seen AS last_seen_at,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM (
                SELECT
                    model_id,
                    day_of_week,
                    hour_of_day,
                    COUNT(*) AS cnt,
                    AVG(viewers_count) AS avg_v,
                    MAX(snapshot_at) AS max_seen
                FROM model_online_snapshots
                WHERE snapshot_at >= ?
                GROUP BY model_id, day_of_week, hour_of_day
                HAVING COUNT(*) >= 1
                   AND LEAST(100, (COUNT(*)::numeric / ? * 100)) >= ?
            ) agg
            ON CONFLICT (model_id, day_of_week, hour_of_day) DO UPDATE SET
                times_online = EXCLUDED.times_online,
                times_checked = EXCLUDED.times_checked,
                online_percentage = EXCLUDED.online_percentage,
                avg_viewers = EXCLUDED.avg_viewers,
                last_seen_at = EXCLUDED.last_seen_at,
                updated_at = NOW()
        ", [$timesChecked, $timesChecked, $cutoff, $timesChecked, $minPercentage]);

        $this->info('Bulk upsert complete.');

        // Remove stale heatmap entries for models no longer in recent snapshots
        $staleDeleted = ModelHeatmap::where('updated_at', '<', now()->subDays($weeks * 7 + 1))->delete();
        if ($staleDeleted > 0) {
            $this->info("Removed {$staleDeleted} stale heatmap entries.");
        }

        $totalRows = ModelHeatmap::count();
        $this->info("Total heatmap rows: " . number_format($totalRows));

        return self::SUCCESS;
    }

    /**
     * Aggregate a single model (used with --model= flag).
     */
    protected function aggregateSingleModel(string $modelId, int $weeks, int $minSnapshots): int
    {
        $stats = ModelOnlineSnapshot::where('model_id', $modelId)
            ->withinDays($weeks * 7)
            ->select([
                'day_of_week',
                'hour_of_day',
                DB::raw('COUNT(*) as times_online'),
                DB::raw('AVG(viewers_count) as avg_viewers'),
                DB::raw('MAX(snapshot_at) as last_seen_at'),
            ])
            ->groupBy('day_of_week', 'hour_of_day')
            ->get();

        $timesChecked = $weeks;
        $upserted = 0;

        foreach ($stats as $stat) {
            if ($stat->times_online < 1) continue;

            $percentage = min(100, ($stat->times_online / $timesChecked) * 100);
            if ($percentage < 5 && $stat->times_online < $minSnapshots) continue;

            ModelHeatmap::updateOrCreate(
                ['model_id' => $modelId, 'day_of_week' => $stat->day_of_week, 'hour_of_day' => $stat->hour_of_day],
                [
                    'times_online' => $stat->times_online,
                    'times_checked' => $timesChecked,
                    'online_percentage' => round($percentage, 2),
                    'avg_viewers' => $stat->avg_viewers ? round($stat->avg_viewers) : null,
                    'last_seen_at' => $stat->last_seen_at,
                ]
            );
            $upserted++;
        }

        ModelHeatmap::flushCache($modelId);
        $this->info("Processed model {$modelId}: {$upserted} slots updated.");

        return self::SUCCESS;
    }

    protected function dryRun(int $weeks, int $minSnapshots, ?string $specificModel): int
    {
        $query = ModelOnlineSnapshot::withinDays($weeks * 7)->select('model_id')->distinct();
        if ($specificModel) $query->where('model_id', $specificModel);

        $sampleModel = $query->value('model_id');
        if (!$sampleModel) {
            $this->warn('No snapshot data found.');
            return self::SUCCESS;
        }

        $stats = ModelOnlineSnapshot::where('model_id', $sampleModel)
            ->withinDays($weeks * 7)
            ->select([
                'day_of_week', 'hour_of_day',
                DB::raw('COUNT(*) as times_online'),
                DB::raw('AVG(viewers_count) as avg_viewers'),
                DB::raw('MAX(snapshot_at) as last_seen_at'),
            ])
            ->groupBy('day_of_week', 'hour_of_day')
            ->get();

        $this->info("Sample data for model: {$sampleModel}");
        $this->table(
            ['Day', 'Hour', 'Times Online', 'Avg Viewers', 'Last Seen'],
            $stats->take(10)->map(fn($s) => [
                ModelHeatmap::DAYS[$s->day_of_week],
                sprintf('%02d:00', $s->hour_of_day),
                $s->times_online,
                round($s->avg_viewers ?? 0),
                $s->last_seen_at,
            ])
        );

        return self::SUCCESS;
    }
}
