<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelSchedule extends Model
{
    protected $fillable = [
        'model_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_usually_online',
        'confidence_score',
        'sample_count',
    ];

    protected $casts = [
        'is_usually_online' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Day names for display
     */
    public const DAY_NAMES = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /**
     * Scope by model
     */
    public function scopeForModel($query, string $modelId)
    {
        return $query->where('model_id', $modelId);
    }

    /**
     * Get the day name
     */
    public function getDayNameAttribute(): string
    {
        return self::DAY_NAMES[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Get full schedule for a model (all 7 days)
     */
    public static function getFullSchedule(string $modelId): array
    {
        $schedules = static::forModel($modelId)->get()->keyBy('day_of_week');
        
        $fullSchedule = [];
        for ($day = 0; $day < 7; $day++) {
            $fullSchedule[$day] = $schedules[$day] ?? null;
        }
        
        return $fullSchedule;
    }

    /**
     * Update schedule from online status observation
     */
    public static function recordOnlineStatus(string $modelId, bool $isOnline): void
    {
        $dayOfWeek = (int) now()->format('w');
        $hour = (int) now()->format('H');
        
        $schedule = static::firstOrCreate(
            ['model_id' => $modelId, 'day_of_week' => $dayOfWeek],
            ['is_usually_online' => false, 'confidence_score' => 0, 'sample_count' => 0]
        );

        $schedule->sample_count++;
        
        if ($isOnline) {
            // Increase confidence when online
            $schedule->is_usually_online = true;
            $schedule->confidence_score = min(100, $schedule->confidence_score + 5);
            
            // Update time range
            $currentTime = now()->format('H:i:s');
            if (!$schedule->start_time || $currentTime < $schedule->start_time) {
                $schedule->start_time = $currentTime;
            }
            if (!$schedule->end_time || $currentTime > $schedule->end_time) {
                $schedule->end_time = $currentTime;
            }
        } else {
            // Decrease confidence when offline
            $schedule->confidence_score = max(0, $schedule->confidence_score - 1);
            if ($schedule->confidence_score < 20) {
                $schedule->is_usually_online = false;
            }
        }
        
        $schedule->save();
    }

    /**
     * Get heatmap data for visualization
     */
    public static function getHeatmapData(string $modelId): array
    {
        $schedules = static::forModel($modelId)->get();
        
        $heatmap = [];
        foreach ($schedules as $schedule) {
            $heatmap[$schedule->day_of_week] = [
                'day' => $schedule->day_name,
                'online' => $schedule->is_usually_online,
                'confidence' => $schedule->confidence_score,
                'start' => $schedule->start_time?->format('H:i'),
                'end' => $schedule->end_time?->format('H:i'),
            ];
        }
        
        return $heatmap;
    }
}
