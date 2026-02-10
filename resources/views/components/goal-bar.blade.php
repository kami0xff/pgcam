@props([
    'goalMessage' => null,
    'tokensNeeded' => null,
    'tokensEarned' => 0,
    'progress' => null,
])

@php
    // Calculate progress if not provided
    if ($progress === null && $tokensNeeded && $tokensNeeded > 0) {
        $progress = min(100, round(($tokensEarned / $tokensNeeded) * 100, 1));
    }
    $progress = $progress ?? 0;
    
    // Parse goal message to extract tokens if embedded (e.g., "11737 jt oil my boobs")
    $displayMessage = $goalMessage;
    $displayTokens = $tokensNeeded;
    
    // Try to extract token amount from message if not provided separately
    if (!$displayTokens && $goalMessage && preg_match('/^(\d+)\s*/', $goalMessage, $matches)) {
        $displayTokens = (int) $matches[1];
        $displayMessage = trim(preg_replace('/^\d+\s*/', '', $goalMessage));
    }
@endphp

@if($goalMessage)
<div class="goal-bar-container">
    <div class="goal-bar">
        {{-- Progress Fill --}}
        <div class="goal-bar-fill" style="width: {{ $progress }}%;"></div>
        
        {{-- Content --}}
        <div class="goal-bar-content">
            {{-- Left: Icon + Text --}}
            <div class="goal-bar-left">
                <div class="goal-bar-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="12" cy="12" r="6"/>
                        <circle cx="12" cy="12" r="2"/>
                    </svg>
                </div>
                <div class="goal-bar-text">
                    <span class="goal-bar-label">{{ __('Goal') }}:</span>
                    @if($displayTokens)
                        <span class="goal-bar-tokens">{{ number_format($displayTokens) }}</span>
                    @endif
                    <span class="goal-bar-message">{{ $displayMessage }}</span>
                </div>
            </div>
            
            {{-- Right: Percentage --}}
            <div class="goal-bar-percentage">
                {{ $progress }}%
            </div>
        </div>
    </div>
</div>

<style>
.goal-bar-container {
    width: 100%;
    padding: 0;
}

.goal-bar {
    position: relative;
    width: 100%;
    height: 48px;
    background: #2C2C2C;
    border-radius: 24px;
    overflow: hidden;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

@media (min-width: 640px) {
    .goal-bar {
        height: 56px;
        border-radius: 28px;
    }
}

.goal-bar-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, #22c55e 0%, #a3e635 100%);
    transition: width 0.5s ease;
}

.goal-bar-content {
    position: relative;
    z-index: 1;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 0.5rem;
}

@media (min-width: 640px) {
    .goal-bar-content {
        padding: 0 0.75rem;
    }
}

.goal-bar-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
    flex: 1;
}

@media (min-width: 640px) {
    .goal-bar-left {
        gap: 0.75rem;
    }
}

.goal-bar-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 50%;
    background: rgba(34, 197, 94, 0.3);
    backdrop-filter: blur(4px);
}

@media (min-width: 640px) {
    .goal-bar-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
    }
}

.goal-bar-icon svg {
    width: 18px;
    height: 18px;
    color: white;
}

@media (min-width: 640px) {
    .goal-bar-icon svg {
        width: 22px;
        height: 22px;
    }
}

.goal-bar-text {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: white;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

@media (min-width: 640px) {
    .goal-bar-text {
        font-size: 1rem;
        gap: 0.5rem;
    }
}

.goal-bar-label {
    opacity: 0.9;
}

.goal-bar-tokens {
    color: #a3e635;
    font-weight: 700;
}

.goal-bar-message {
    opacity: 0.9;
    overflow: hidden;
    text-overflow: ellipsis;
}

.goal-bar-percentage {
    font-weight: 700;
    font-size: 1rem;
    color: white;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    white-space: nowrap;
}

@media (min-width: 640px) {
    .goal-bar-percentage {
        font-size: 1.25rem;
        padding-right: 1rem;
    }
}
</style>
@endif
