<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelOnlineSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'model_id',
        'snapshot_at',
        'day_of_week',
        'hour_of_day',
        'is_online',
        'viewers_count',
        'stream_status',
    ];

    protected $casts = [
        'snapshot_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    /**
     * Scope by model
     */
    public function scopeForModel($query, string $modelId)
    {
        return $query->where('model_id', $modelId);
    }

    /**
     * Scope to snapshots within date range
     */
    public function scopeWithinDays($query, int $days)
    {
        return $query->where('snapshot_at', '>=', now()->subDays($days));
    }

    /**
     * Prune old snapshots (keep last N weeks)
     */
    public static function pruneOlderThan(int $weeks = 8): int
    {
        return static::where('snapshot_at', '<', now()->subWeeks($weeks))->delete();
    }
}
