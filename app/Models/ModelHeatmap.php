<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelHeatmap extends Model
{
    protected $fillable = [
        'model_id',
        'day_of_week',
        'hour_of_day',
        'times_online',
        'times_checked',
        'online_percentage',
        'avg_viewers',
        'last_seen_at',
    ];

    protected $casts = [
        'online_percentage' => 'decimal:2',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Day names
     */
    public const DAYS = [
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
     * Get day name
     */
    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Get formatted hour (e.g., "14:00")
     */
    public function getFormattedHourAttribute(): string
    {
        return sprintf('%02d:00', $this->hour_of_day);
    }

    /**
     * Get heat level (0-4) for CSS class
     */
    public function getHeatLevelAttribute(): int
    {
        $pct = $this->online_percentage;
        
        if ($pct >= 80) return 4; // Very hot
        if ($pct >= 60) return 3; // Hot
        if ($pct >= 40) return 2; // Warm
        if ($pct >= 20) return 1; // Cool
        return 0; // Cold
    }

    /**
     * Get full heatmap grid for a model (7x24 array)
     */
    public static function getHeatmapGrid(string $modelId): array
    {
        $data = static::forModel($modelId)->get()->keyBy(function ($item) {
            return $item->day_of_week . '_' . $item->hour_of_day;
        });

        $grid = [];
        
        // Build 7x24 grid (rows = hours, cols = days)
        for ($hour = 0; $hour < 24; $hour++) {
            $row = [];
            for ($day = 0; $day < 7; $day++) {
                $key = $day . '_' . $hour;
                $slot = $data[$key] ?? null;
                
                $row[] = [
                    'day' => $day,
                    'hour' => $hour,
                    'percentage' => $slot?->online_percentage ?? 0,
                    'heat_level' => $slot?->heat_level ?? 0,
                    'times_online' => $slot?->times_online ?? 0,
                    'times_checked' => $slot?->times_checked ?? 0,
                    'avg_viewers' => $slot?->avg_viewers,
                ];
            }
            $grid[] = $row;
        }

        return $grid;
    }

    /**
     * Get heatmap as JSON for frontend
     */
    public static function getHeatmapJson(string $modelId): string
    {
        return json_encode(static::getHeatmapGrid($modelId));
    }

    /**
     * Get best times (slots with highest online percentage)
     */
    public static function getBestTimes(string $modelId, int $limit = 5): Collection
    {
        return static::forModel($modelId)
            ->where('online_percentage', '>', 0)
            ->orderByDesc('online_percentage')
            ->limit($limit)
            ->get();
    }

    /**
     * Get summary stats for a model
     */
    public static function getSummary(string $modelId): array
    {
        $heatmap = static::forModel($modelId)->get();
        
        if ($heatmap->isEmpty()) {
            return [
                'has_data' => false,
                'total_slots' => 0,
                'active_slots' => 0,
                'avg_online_percentage' => 0,
                'best_day' => null,
                'best_hour' => null,
            ];
        }

        $activeSlots = $heatmap->where('online_percentage', '>', 0);
        $bestSlot = $heatmap->sortByDesc('online_percentage')->first();
        
        // Find best day (day with most active hours)
        $dayStats = $heatmap->groupBy('day_of_week')->map(function ($slots) {
            return $slots->where('online_percentage', '>', 50)->count();
        });
        $bestDay = $dayStats->sortDesc()->keys()->first();

        return [
            'has_data' => true,
            'total_slots' => $heatmap->count(),
            'active_slots' => $activeSlots->count(),
            'avg_online_percentage' => round($activeSlots->avg('online_percentage'), 1),
            'best_day' => $bestDay !== null ? self::DAYS[$bestDay] : null,
            'best_hour' => $bestSlot ? sprintf('%02d:00', $bestSlot->hour_of_day) : null,
            'peak_percentage' => $bestSlot?->online_percentage ?? 0,
        ];
    }

    /**
     * Schema.org day mapping (ISO 8601 day names)
     */
    public const SCHEMA_DAYS = [
        0 => 'https://schema.org/Sunday',
        1 => 'https://schema.org/Monday',
        2 => 'https://schema.org/Tuesday',
        3 => 'https://schema.org/Wednesday',
        4 => 'https://schema.org/Thursday',
        5 => 'https://schema.org/Friday',
        6 => 'https://schema.org/Saturday',
    ];

    /**
     * Get the next occurrence of a day/hour
     */
    public static function getNextOccurrence(int $dayOfWeek, int $hour): string
    {
        $now = now();
        $dayName = self::DAYS[$dayOfWeek];
        
        // Get next occurrence of this day
        $target = $now->copy();
        
        // If today is the target day and the hour hasn't passed, use today
        if ($now->dayOfWeek === $dayOfWeek && $now->hour < $hour) {
            $target->setTime($hour, 0, 0);
        } else {
            // Find next occurrence of this day
            $daysUntil = ($dayOfWeek - $now->dayOfWeek + 7) % 7;
            if ($daysUntil === 0) {
                $daysUntil = 7; // Next week same day
            }
            $target->addDays($daysUntil)->setTime($hour, 0, 0);
        }
        
        return $target->toIso8601String();
    }

    /**
     * Get typical schedule blocks (consecutive hours grouped)
     * Returns array of schedule blocks with start time, end time, and days
     */
    public static function getScheduleBlocks(string $modelId, int $minPercentage = 40): array
    {
        $heatmap = static::forModel($modelId)
            ->where('online_percentage', '>=', $minPercentage)
            ->orderBy('day_of_week')
            ->orderBy('hour_of_day')
            ->get();

        if ($heatmap->isEmpty()) {
            return [];
        }

        // Group consecutive hours by day
        $blocks = [];
        $currentBlock = null;

        foreach ($heatmap as $slot) {
            if ($currentBlock === null) {
                // Start new block
                $currentBlock = [
                    'day' => $slot->day_of_week,
                    'day_name' => $slot->day_name,
                    'start_hour' => $slot->hour_of_day,
                    'end_hour' => $slot->hour_of_day + 1,
                    'avg_percentage' => $slot->online_percentage,
                    'slots' => 1,
                ];
            } elseif (
                $slot->day_of_week === $currentBlock['day'] &&
                $slot->hour_of_day === $currentBlock['end_hour']
            ) {
                // Extend current block
                $currentBlock['end_hour'] = $slot->hour_of_day + 1;
                $currentBlock['avg_percentage'] = 
                    (($currentBlock['avg_percentage'] * $currentBlock['slots']) + $slot->online_percentage) 
                    / ($currentBlock['slots'] + 1);
                $currentBlock['slots']++;
            } else {
                // Save current block and start new one
                $blocks[] = $currentBlock;
                $currentBlock = [
                    'day' => $slot->day_of_week,
                    'day_name' => $slot->day_name,
                    'start_hour' => $slot->hour_of_day,
                    'end_hour' => $slot->hour_of_day + 1,
                    'avg_percentage' => $slot->online_percentage,
                    'slots' => 1,
                ];
            }
        }

        // Don't forget the last block
        if ($currentBlock !== null) {
            $blocks[] = $currentBlock;
        }

        // Sort by average percentage (most reliable times first)
        usort($blocks, fn($a, $b) => $b['avg_percentage'] <=> $a['avg_percentage']);

        return $blocks;
    }

    /**
     * Get Schema.org BroadcastService with Schedule for SEO
     * Uses proper recurring schedule format
     */
    public static function getBroadcastServiceSchema(
        string $modelId,
        string $modelName,
        string $profileUrl,
        ?string $imageUrl = null
    ): ?array {
        $blocks = static::getScheduleBlocks($modelId, 50);
        
        if (empty($blocks)) {
            // Fall back to best times if no consistent blocks
            $bestTimes = static::getBestTimes($modelId, 5);
            if ($bestTimes->isEmpty()) {
                return null;
            }
            
            $blocks = $bestTimes->map(fn($slot) => [
                'day' => $slot->day_of_week,
                'day_name' => $slot->day_name,
                'start_hour' => $slot->hour_of_day,
                'end_hour' => $slot->hour_of_day + 1,
                'avg_percentage' => $slot->online_percentage,
            ])->toArray();
        }

        // Build schedule specifications
        $scheduleSpecs = [];
        foreach (array_slice($blocks, 0, 7) as $block) {
            $scheduleSpecs[] = [
                '@type' => 'Schedule',
                'byDay' => self::SCHEMA_DAYS[$block['day']],
                'startTime' => sprintf('%02d:00:00', $block['start_hour']),
                'endTime' => sprintf('%02d:00:00', $block['end_hour']),
                'scheduleTimezone' => config('app.timezone', 'UTC'),
                'repeatFrequency' => 'P1W', // Weekly
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BroadcastService',
            '@id' => $profileUrl . '#broadcast-service',
            'name' => "{$modelName} Live Cam Show",
            'description' => "Watch {$modelName} live on webcam. Typical broadcast schedule included.",
            'url' => $profileUrl,
            'broadcastDisplayName' => $modelName,
            'broadcaster' => [
                '@type' => 'Person',
                '@id' => $profileUrl . '#performer',
                'name' => $modelName,
                'url' => $profileUrl,
            ],
            'hasBroadcastChannel' => [
                '@type' => 'BroadcastChannel',
                'broadcastChannelId' => $modelId,
                'broadcastServiceTier' => 'Free',
                'genre' => 'Adult Entertainment',
            ],
        ];

        if ($imageUrl) {
            $schema['broadcaster']['image'] = $imageUrl;
        }

        // Add schedule if we have data
        if (!empty($scheduleSpecs)) {
            $schema['broadcastSchedule'] = $scheduleSpecs;
        }

        return $schema;
    }

    /**
     * Get Schema.org BroadcastEvent for upcoming broadcasts
     * Creates events for the next 7 days based on typical schedule
     */
    public static function getUpcomingBroadcastEventsSchema(
        string $modelId,
        string $modelName,
        string $profileUrl,
        ?string $imageUrl = null
    ): ?array {
        $blocks = static::getScheduleBlocks($modelId, 50);
        
        if (empty($blocks)) {
            return null;
        }

        $events = [];
        $now = now();
        
        // Generate events for the next 7 days
        foreach ($blocks as $block) {
            $startDate = static::getNextOccurrence($block['day'], $block['start_hour']);
            $startDateTime = \Carbon\Carbon::parse($startDate);
            
            // Only include events within next 7 days
            if ($startDateTime->diffInDays($now) > 7) {
                continue;
            }

            $endDateTime = $startDateTime->copy()->setTime($block['end_hour'], 0, 0);
            
            $event = [
                '@type' => 'BroadcastEvent',
                'name' => "{$modelName} Live Stream",
                'description' => "{$modelName} typically broadcasts on {$block['day_name']}s around this time ({$block['avg_percentage']}% likelihood)",
                'startDate' => $startDateTime->toIso8601String(),
                'endDate' => $endDateTime->toIso8601String(),
                'isLiveBroadcast' => true,
                'videoFormat' => 'HD',
                'isAccessibleForFree' => true,
                'broadcastOfEvent' => [
                    '@type' => 'Event',
                    'name' => "{$modelName} Webcam Show",
                    'performer' => [
                        '@type' => 'Person',
                        'name' => $modelName,
                        'url' => $profileUrl,
                    ],
                    'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode',
                    'eventStatus' => 'https://schema.org/EventScheduled',
                    'location' => [
                        '@type' => 'VirtualLocation',
                        'url' => $profileUrl,
                    ],
                ],
            ];

            if ($imageUrl) {
                $event['broadcastOfEvent']['image'] = $imageUrl;
            }

            $events[] = $event;
        }

        if (empty($events)) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $events,
        ];
    }

    /**
     * Get combined JSON-LD schema with both service and events
     */
    public static function getFullBroadcastSchema(
        string $modelId,
        string $modelName,
        string $profileUrl,
        ?string $imageUrl = null
    ): ?array {
        $service = static::getBroadcastServiceSchema($modelId, $modelName, $profileUrl, $imageUrl);
        $events = static::getUpcomingBroadcastEventsSchema($modelId, $modelName, $profileUrl, $imageUrl);

        if (!$service && !$events) {
            return null;
        }

        $graph = [];
        
        if ($service) {
            // Remove @context from service, we'll use @graph
            unset($service['@context']);
            $graph[] = $service;
        }

        if ($events && isset($events['@graph'])) {
            $graph = array_merge($graph, $events['@graph']);
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    /**
     * Get JSON-LD script tag for embedding in HTML
     */
    public static function getJsonLdScript(
        string $modelId,
        string $modelName,
        string $profileUrl,
        ?string $imageUrl = null
    ): string {
        $schema = static::getFullBroadcastSchema($modelId, $modelName, $profileUrl, $imageUrl);
        
        if (!$schema) {
            return '';
        }

        return '<script type="application/ld+json">' . 
               json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . 
               '</script>';
    }
}
