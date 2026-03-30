<?php

namespace App\Console\Commands;

use App\Models\ModelGoal;
use Illuminate\Console\Command;

class PruneModelGoals extends Command
{
    protected $signature = 'goals:prune
                            {--weeks=6 : Delete completed goals older than this many weeks}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Prune old completed goals to prevent unbounded table growth';

    public function handle(): int
    {
        $weeks = (int) $this->option('weeks');
        $cutoff = now()->subWeeks($weeks);

        $query = ModelGoal::where('was_completed', true)
            ->where('completed_at', '<', $cutoff);

        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Would prune {$count} completed goals older than {$weeks} weeks.");
            return self::SUCCESS;
        }

        if ($count === 0) {
            $this->info('No old goals to prune.');
            return self::SUCCESS;
        }

        $totalDeleted = 0;
        do {
            $deleted = ModelGoal::where('was_completed', true)
                ->where('completed_at', '<', $cutoff)
                ->limit(10000)
                ->delete();
            $totalDeleted += $deleted;
        } while ($deleted > 0);

        $this->info("Pruned {$totalDeleted} completed goals older than {$weeks} weeks.");
        $this->info('Remaining goals: ' . number_format(ModelGoal::count()));

        return self::SUCCESS;
    }
}
