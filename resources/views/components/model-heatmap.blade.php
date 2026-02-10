@props([
    'modelId',
    'modelName' => null,
    'profileUrl' => null,
    'imageUrl' => null,
    'compact' => false,
    'includeSchema' => true,
])

@php
    use App\Models\ModelHeatmap;
    
    $grid = ModelHeatmap::getHeatmapGrid($modelId);
    $summary = ModelHeatmap::getSummary($modelId);
    $bestTimes = ModelHeatmap::getBestTimes($modelId, 5);
    $scheduleBlocks = ModelHeatmap::getScheduleBlocks($modelId, 40);
    
    $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $daysFull = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    // Generate JSON-LD if we have the required data
    $jsonLd = null;
    if ($includeSchema && $modelName && $profileUrl && $summary['has_data']) {
        $jsonLd = ModelHeatmap::getJsonLdScript($modelId, $modelName, $profileUrl, $imageUrl);
    }
@endphp

{{-- JSON-LD Structured Data for Broadcast Schedule --}}
@if($jsonLd)
{!! $jsonLd !!}
@endif

@if($summary['has_data'])
<div class="heatmap-card">
    {{-- Header --}}
    <div class="heatmap-card-header">
        <div>
            <h3 class="heatmap-card-title">
                <svg class="heatmap-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                {{ __('Online Schedule') }}
            </h3>
            @if($summary['best_day'])
                <p class="heatmap-card-subtitle">
                    {{ __('Most active:') }} {{ __($summary['best_day']) }}
                    @if($summary['best_hour']) {{ __('around') }} {{ $summary['best_hour'] }} @endif
                </p>
            @endif
        </div>
    </div>

    {{-- Grid --}}
    <div class="heatmap-card-body">
        <div class="heatmap-grid-wrapper">
            {{-- Day headers --}}
            <div class="heatmap-day-row">
                <div class="heatmap-hour-cell"></div>
                @foreach($days as $index => $day)
                    <div class="heatmap-day-cell" title="{{ $daysFull[$index] }}">{{ $day }}</div>
                @endforeach
            </div>
            
            {{-- Hour rows --}}
            @foreach($grid as $hour => $row)
                @if(!$compact || $hour % 2 === 0)
                <div class="heatmap-grid-row">
                    <div class="heatmap-hour-cell">{{ sprintf('%02d', $hour) }}</div>
                    @foreach($row as $slot)
                        <div 
                            class="heatmap-slot heat-level-{{ $slot['heat_level'] }}"
                            title="{{ $daysFull[$slot['day']] }} {{ sprintf('%02d:00', $slot['hour']) }} - {{ round($slot['percentage']) }}% {{ __('online') }}"
                        ></div>
                    @endforeach
                </div>
                @endif
            @endforeach
        </div>
        
        {{-- Legend --}}
        <div class="heatmap-legend-row">
            <span class="heatmap-legend-text">{{ __('Less') }}</span>
            <div class="heatmap-legend-slot heat-level-0"></div>
            <div class="heatmap-legend-slot heat-level-1"></div>
            <div class="heatmap-legend-slot heat-level-2"></div>
            <div class="heatmap-legend-slot heat-level-3"></div>
            <div class="heatmap-legend-slot heat-level-4"></div>
            <span class="heatmap-legend-text">{{ __('More') }}</span>
        </div>
    </div>

    {{-- Schedule blocks --}}
    @if(!$compact && !empty($scheduleBlocks))
    <div class="heatmap-card-footer">
        <h4 class="heatmap-schedule-title">{{ __('Typical Schedule') }}</h4>
        <div class="heatmap-schedule-list">
            @foreach(array_slice($scheduleBlocks, 0, 4) as $block)
                <div class="heatmap-schedule-item">
                    <span class="schedule-item-day">{{ __($block['day_name']) }}</span>
                    <span class="schedule-item-time">{{ sprintf('%02d:00', $block['start_hour']) }} - {{ sprintf('%02d:00', $block['end_hour']) }}</span>
                    <span class="schedule-item-pct">{{ round($block['avg_percentage']) }}%</span>
                </div>
            @endforeach
        </div>
    </div>
    @elseif(!$compact && $bestTimes->isNotEmpty())
    <div class="heatmap-card-footer">
        <h4 class="heatmap-schedule-title">{{ __('Best Times') }}</h4>
        <div class="heatmap-best-list">
            @foreach($bestTimes->take(4) as $time)
                <div class="heatmap-best-item">
                    <span class="best-item-day">{{ __($time->day_name) }}</span>
                    <span class="best-item-time">{{ $time->formatted_hour }}</span>
                    <span class="best-item-pct">{{ round($time->online_percentage) }}%</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@else
<div class="heatmap-card heatmap-card--empty">
    <div class="heatmap-empty-content">
        <svg class="heatmap-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <p class="heatmap-empty-text">{{ __('Online Schedule') }}</p>
        <p class="heatmap-empty-subtext">{{ __('Schedule data coming soon') }}</p>
    </div>
</div>
@endif

<style>
/* Heatmap Card - PornGuru Blue Theme */
.heatmap-card {
    background: var(--bg-card, #0a0a0a);
    border: 1px solid var(--border, #27272a);
    border-radius: 12px;
    overflow: hidden;
}

.heatmap-card-header {
    background: linear-gradient(135deg, rgba(0, 191, 255, 0.15) 0%, rgba(0, 191, 255, 0.05) 100%);
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border, #27272a);
}

.heatmap-card-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary, #fff);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.heatmap-icon {
    width: 16px;
    height: 16px;
    color: var(--accent, #00BFFF);
}

.heatmap-card-subtitle {
    font-size: 0.7rem;
    color: var(--text-muted, #71717a);
    margin: 0.2rem 0 0 0;
}

.heatmap-card-body {
    padding: 0.75rem 1rem;
}

/* Grid */
.heatmap-grid-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.heatmap-day-row,
.heatmap-grid-row {
    display: flex;
    gap: 1px;
}

.heatmap-day-cell {
    flex: 1;
    text-align: center;
    font-size: 0.6rem;
    font-weight: 500;
    color: var(--text-muted, #71717a);
    text-transform: uppercase;
    padding-bottom: 3px;
}

.heatmap-hour-cell {
    width: 20px;
    min-width: 20px;
    font-size: 0.55rem;
    color: var(--text-muted, #71717a);
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 3px;
    height: 12px;
}

.heatmap-slot {
    flex: 1;
    height: 12px;
    border-radius: 2px;
    cursor: pointer;
    transition: transform 0.1s ease, box-shadow 0.1s ease;
}

.heatmap-slot:hover {
    transform: scale(1.15);
    box-shadow: 0 0 6px rgba(0, 191, 255, 0.4);
    z-index: 1;
}

/* Heat levels - PornGuru Blue (#00BFFF) */
.heat-level-0 { background: rgba(255,255,255,0.04); }
.heat-level-1 { background: rgba(0, 191, 255, 0.2); }
.heat-level-2 { background: rgba(0, 191, 255, 0.4); }
.heat-level-3 { background: rgba(0, 191, 255, 0.7); }
.heat-level-4 { background: rgba(0, 191, 255, 1); }

/* Legend */
.heatmap-legend-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 2px;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--border, #27272a);
}

.heatmap-legend-text {
    font-size: 0.55rem;
    color: var(--text-muted, #71717a);
    padding: 0 3px;
}

.heatmap-legend-slot {
    width: 8px;
    height: 8px;
    border-radius: 2px;
}

/* Footer / Schedule */
.heatmap-card-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--border, #27272a);
    background: rgba(0,0,0,0.2);
}

.heatmap-schedule-title {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--text-secondary, #a1a1aa);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 0.5rem 0;
}

.heatmap-schedule-list,
.heatmap-best-list {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.heatmap-schedule-item,
.heatmap-best-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.5rem;
    background: rgba(255,255,255,0.03);
    border-radius: 5px;
    border-left: 2px solid var(--accent, #00BFFF);
}

.schedule-item-day,
.best-item-day {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-primary, #fff);
    min-width: 70px;
}

.schedule-item-time,
.best-item-time {
    font-size: 0.7rem;
    color: var(--text-secondary, #a1a1aa);
    font-family: monospace;
    flex: 1;
}

.schedule-item-pct,
.best-item-pct {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--accent, #00BFFF);
    background: rgba(0, 191, 255, 0.1);
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

/* Empty State */
.heatmap-card--empty {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.heatmap-empty-content {
    text-align: center;
    padding: 2rem;
}

.heatmap-empty-icon {
    width: 40px;
    height: 40px;
    color: var(--text-faint, #52525b);
    margin-bottom: 0.75rem;
}

.heatmap-empty-text {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-secondary, #a1a1aa);
    margin: 0 0 0.25rem 0;
}

.heatmap-empty-subtext {
    font-size: 0.75rem;
    color: var(--text-muted, #71717a);
    margin: 0;
}

/* Responsive */
@media (max-width: 640px) {
    .heatmap-card-header,
    .heatmap-card-body,
    .heatmap-card-footer {
        padding: 0.5rem 0.75rem;
    }
    
    .heatmap-card-title {
        font-size: 0.8rem;
    }
    
    .heatmap-day-cell {
        font-size: 0.5rem;
    }
    
    .heatmap-hour-cell {
        width: 16px;
        min-width: 16px;
        font-size: 0.5rem;
        height: 10px;
    }
    
    .heatmap-slot {
        min-width: 0;
        height: 10px;
    }
    
    .schedule-item-day,
    .best-item-day {
        min-width: 50px;
        font-size: 0.65rem;
    }
    
    .schedule-item-time,
    .best-item-time {
        font-size: 0.6rem;
    }
    
    .schedule-item-pct,
    .best-item-pct {
        font-size: 0.55rem;
        padding: 0.1rem 0.25rem;
    }
}
</style>
