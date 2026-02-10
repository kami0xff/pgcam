<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelGoal extends Model
{
    protected $fillable = [
        'model_id',
        'goal_message',
        'tokens_needed',
        'tokens_earned',
        'was_completed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'was_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Scope by model
     */
    public function scopeForModel($query, string $modelId)
    {
        return $query->where('model_id', $modelId);
    }

    /**
     * Scope to completed goals
     */
    public function scopeCompleted($query)
    {
        return $query->where('was_completed', true);
    }

    /**
     * Get recent goals for a model
     */
    public static function getRecentForModel(string $modelId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::forModel($modelId)
            ->orderByDesc('started_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Record a new goal or mark previous as completed
     */
    public static function recordGoal(string $modelId, string $goalMessage, ?int $tokensNeeded = null, ?int $tokensEarned = null): self
    {
        // Mark previous active goal as completed
        $previousGoal = static::forModel($modelId)
            ->where('was_completed', false)
            ->latest('started_at')
            ->first();

        if ($previousGoal && $previousGoal->goal_message !== $goalMessage) {
            $previousGoal->update([
                'was_completed' => true,
                'completed_at' => now(),
            ]);
        }

        // Create or update current goal
        return static::updateOrCreate(
            [
                'model_id' => $modelId,
                'goal_message' => $goalMessage,
                'was_completed' => false,
            ],
            [
                'tokens_needed' => $tokensNeeded,
                'tokens_earned' => $tokensEarned,
                'started_at' => now(),
            ]
        );
    }

    /**
     * Extract keywords from goal message for SEO
     */
    public function getKeywordsAttribute(): array
    {
        // Remove common words and extract meaningful terms
        $stopWords = ['the', 'a', 'an', 'for', 'to', 'of', 'and', 'in', 'at', 'is', 'my', 'me', 'i'];
        $words = str_word_count(strtolower($this->goal_message), 1);
        
        return array_values(array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        }));
    }
}
