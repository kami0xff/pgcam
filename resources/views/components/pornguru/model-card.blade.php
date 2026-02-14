@props(['model'])

@php
    $flagSource = $model->country ?: ($model->languages[0] ?? null);
    $flag = $flagSource ? country_flag($flagSource) : null;
    $isFavorited = auth()->check() && auth()->user()->hasFavorited($model);
@endphp

<div class="model-card-wrapper">
    <a href="{{ route('cam-models.show', $model) }}" 
       class="model-card" 
       data-stream-url="{{ $model->best_stream_url }}"
       data-model-id="{{ $model->id }}">
        <div class="model-card-image">
            {{-- Static Image --}}
            <img src="{{ $model->best_image_url }}" 
                 alt="{{ $model->username }}" 
                 class="model-card-thumb"
                 loading="lazy">
            
            {{-- Video Preview (hidden by default) --}}
            @if($model->is_online && $model->best_stream_url)
                <video class="model-card-video" muted playsinline></video>
            @endif
            
            <div class="model-card-overlay"></div>
            
            {{-- Online Indicator (subtle green dot) --}}
            @if($model->is_online)
                <div class="model-card-live-dot" title="Live now"></div>
            @endif
            
            {{-- New Badge (if model is new) --}}
            @if($model->is_new ?? false)
                <div class="model-card-new-badge">NEW</div>
            @endif
            
            {{-- Rating --}}
            @if($model->rating)
                <div class="model-card-rating">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    {{ number_format((float)$model->rating, 1) }}
                </div>
            @endif

            {{-- Flag (bottom right) --}}
            @if($flag)
                <span class="model-card-flag" title="{{ $model->country }}">{{ $flag }}</span>
            @endif
            
            {{-- Model Info --}}
            <div class="model-card-info">
                <h3 class="model-card-name">{{ $model->username }}</h3>
                <div class="model-card-meta">
                    @if($model->age)
                        <span>{{ $model->age }}yo</span>
                    @endif
                    @if($model->viewers_count)
                        <span>{{ number_format($model->viewers_count) }} viewers</span>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Goal Bar --}}
        @if($model->goal_message && $model->goal_progress !== null)
            <div class="model-card-goal">
                <div class="model-card-goal-header">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <span class="model-card-goal-tokens">{{ number_format($model->goal_needed ?? 0) }} tk</span>
                    <span class="model-card-goal-text">{{ Str::limit($model->goal_message, 30) }}</span>
                    <span class="model-card-goal-percent">{{ round($model->goal_progress) }}%</span>
                </div>
                <div class="model-card-goal-bar">
                    <div class="model-card-goal-fill" style="width: {{ min($model->goal_progress, 100) }}%"></div>
                </div>
            </div>
        @endif
    </a>
    
    {{-- Favorite Button --}}
    <button class="model-card-favorite {{ $isFavorited ? 'is-favorited' : '' }}" 
            onclick="toggleFavorite({{ $model->id }}, this); event.preventDefault();"
            title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}">
        <svg viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
    </button>
</div>
