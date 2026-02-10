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

        // Get unique models with snapshots
        $query = ModelOnlineSnapshot::withinDays($weeks * 7)
            ->select('model_id')
            ->distinct();

        if ($specificModel) {
            $query->where('model_id', $specificModel);
        }

        $models = $query->pluck('model_id');
        $this->info("Processing {$models->count()} models");

        if ($this->option('dry-run')) {
            $this->warn('Dry run mode - showing sample data');
            $this->processModel($models->first(), $weeks, $minSnapshots, true);
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($models->count());
        $bar->start();

        $processed = 0;
        foreach ($models as $modelId) {
            $this->processModel($modelId, $weeks, $minSnapshots, false);
            $bar->advance();
            $processed++;
        }

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$processed} models");

        return self::SUCCESS;
    }

    /**
     * Process a single model's heatmap data
     */
    protected function processModel(string $modelId, int $weeks, int $minSnapshots, bool $dryRun): void
    {
        // Get aggregated stats per day/hour slot
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

        if ($dryRun && $stats->isNotEmpty()) {
            $this->newLine();
            $this->info("Sample data for model: {$modelId}");
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
            return;
        }

        // Calculate times_checked (how many times we could have checked this slot)
        // = number of weeks * 1 (one check per hour per week)
        $timesChecked = $weeks;

        foreach ($stats as $stat) {
            // Only save if we have enough data
            if ($stat->times_online < 1) {
                continue;
            }

            $percentage = min(100, ($stat->times_online / $timesChecked) * 100);

            // Skip very low percentages (likely noise)
            if ($percentage < 5 && $stat->times_online < $minSnapshots) {
                continue;
            }

            ModelHeatmap::updateOrCreate(
                [
                    'model_id' => $modelId,
                    'day_of_week' => $stat->day_of_week,
                    'hour_of_day' => $stat->hour_of_day,
                ],
                [
                    'times_online' => $stat->times_online,
                    'times_checked' => $timesChecked,
                    'online_percentage' => round($percentage, 2),
                    'avg_viewers' => $stat->avg_viewers ? round($stat->avg_viewers) : null,
                    'last_seen_at' => $stat->last_seen_at,
                ]
            );
        }
    }
}
