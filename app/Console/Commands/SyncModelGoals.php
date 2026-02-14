<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\ModelGoal;
use Illuminate\Console\Command;

class SyncModelGoals extends Command
{
    protected $signature = 'sync:model-goals
                            {--limit=1000 : Maximum models to process}
                            {--online : Only process online models}';

    protected $description = 'Sync model goals from cam database to track goal history';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $onlineOnly = $this->option('online');

        $this->info('🎯 Syncing model goals...');

        $query = CamModel::on('cam')
            ->whereNotNull('goal_message')
            ->where('goal_message', '!=', '');

        if ($onlineOnly) {
            $query->where('is_online', true);
        }

        $models = $query->limit($limit)->get([
            'username',
            'goal_message',
            'goal_needed',
            'goal_earned',
            'is_online'
        ]);

        if ($models->isEmpty()) {
            $this->info('No models with goals found.');
            return 0;
        }

        $bar = $this->output->createProgressBar($models->count());
        $bar->start();

        $stats = [
            'new' => 0,
            'updated' => 0,
            'completed' => 0,
        ];

        foreach ($models as $model) {
            try {
                $result = $this->recordGoal($model);
                $stats[$result]++;
            } catch (\Exception $e) {
                if ($this->option('verbose')) {
                    $this->newLine();
                    $this->error("Error for {$model->username}: " . $e->getMessage());
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Sync complete!');
        $this->table(
            ['New Goals', 'Updated', 'Completed'],
            [[$stats['new'], $stats['updated'], $stats['completed']]]
        );

        return 0;
    }

    /**
     * Record a goal for a model
     */
    protected function recordGoal(CamModel $model): string
    {
        $modelId = $model->username;
        $goalMessage = trim($model->goal_message);
        $tokensNeeded = (int) $model->goal_needed;
        $tokensEarned = (int) $model->goal_earned;

        // Get the most recent goal for this model
        $currentGoal = ModelGoal::forModel($modelId)
            ->where('was_completed', false)
            ->latest('started_at')
            ->first();

        // If no current goal, create a new one
        if (!$currentGoal) {
            $isAlreadyCompleted = $tokensNeeded > 0 && $tokensEarned >= $tokensNeeded;
            ModelGoal::create([
                'model_id' => $modelId,
                'goal_message' => $goalMessage,
                'tokens_needed' => $tokensNeeded,
                'tokens_earned' => $tokensEarned,
                'was_completed' => $isAlreadyCompleted,
                'started_at' => now(),
                'completed_at' => $isAlreadyCompleted ? now() : null,
            ]);
            return $isAlreadyCompleted ? 'completed' : 'new';
        }

        // Check if goal changed (different message = new goal, old one completed)
        $goalChanged = $this->hasGoalChanged($currentGoal, $goalMessage, $tokensNeeded);
        
        if ($goalChanged) {
            // Mark old goal as completed with final tokens earned
            $currentGoal->update([
                'was_completed' => true,
                'completed_at' => now(),
                // Keep the last known earned tokens before the change
            ]);

            // Create new goal
            ModelGoal::create([
                'model_id' => $modelId,
                'goal_message' => $goalMessage,
                'tokens_needed' => $tokensNeeded,
                'tokens_earned' => $tokensEarned,
                'was_completed' => false,
                'started_at' => now(),
            ]);

            return 'completed';
        }

        // Check if goal was fully funded (100% reached)
        if ($tokensNeeded > 0 && $tokensEarned >= $tokensNeeded) {
            // Goal is 100% funded - mark as completed
            $currentGoal->update([
                'tokens_earned' => $tokensEarned,
                'was_completed' => true,
                'completed_at' => now(),
            ]);
            return 'completed';
        }

        // Update existing goal with new token counts
        $currentGoal->update([
            'tokens_needed' => $tokensNeeded,
            'tokens_earned' => $tokensEarned,
        ]);

        return 'updated';
    }

    /**
     * Check if the goal has meaningfully changed
     */
    protected function hasGoalChanged(ModelGoal $currentGoal, string $newMessage, int $newTokensNeeded): bool
    {
        // Goal message changed completely = new goal
        if ($currentGoal->goal_message !== $newMessage) {
            return true;
        }

        // Token goal changed significantly (more than 10% difference or reset to lower)
        // This catches when a model resets their goal
        if ($currentGoal->tokens_needed > 0 && $newTokensNeeded > 0) {
            // If new goal has much fewer tokens needed, it's likely a reset
            if ($newTokensNeeded < $currentGoal->tokens_earned * 0.5) {
                return true;
            }
        }

        return false;
    }
}
