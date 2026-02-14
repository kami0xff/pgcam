@extends('layouts.pornguru')

@section('title', $model->username . ' - Live Cam')

@section('meta_description'){{ $metaDescription }}@endsection

@section('canonical'){{ $model->url }}@endsection

@section('og_type', 'profile')
@section('og_title', $model->username . ' - ' . ($model->is_online ? 'Live Now' : 'Cam Model') . ' | PornGuru.cam')
@section('og_description'){{ $metaDescription }}@endsection
@section('og_image', $model->best_image_url)

@push('seo-pagination')
<x-seo.schema :schemas="$seoSchemas" />
<x-seo.hreflang :urls="$hreflangUrls" />
@endpush

@push('head')
{{-- Broadcast Schedule JSON-LD for SEO --}}
<x-broadcast-schedule-schema 
    :model-id="$model->username"
    :model-name="$model->username"
    :profile-url="$model->url"
    :image-url="$model->preview_url"
/>
@endpush

@section('content')
<div class="container page-section">

    {{-- Mobile swipe tutorial (shown once) --}}
    <div class="swipe-tutorial" id="swipe-tutorial">
        <div class="swipe-tutorial-content">
            <div class="swipe-tutorial-hand">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7.5 12.5L4.5 9.5M4.5 9.5L7.5 6.5M4.5 9.5H15M16.5 12.5l3-3m0 0l-3-3m3 3H9"/></svg>
            </div>
            <span class="swipe-tutorial-text">{{ __('Swipe to navigate models') }}</span>
        </div>
    </div>

    {{-- Model Navigation --}}
    <div class="model-nav">
        @if($prevModel)
            <a href="{{ $prevModel->url }}" class="model-nav-btn" id="prev-model" title="Previous model (←)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="model-nav-label">{{ $prevModel->username }}</span>
            </a>
        @else
            <div class="model-nav-btn model-nav-disabled"></div>
        @endif
        
        <a href="{{ localized_route('home', ['online' => 1]) }}" class="model-nav-home" title="Back to models">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9,22 9,12 15,12 15,22"/>
            </svg>
        </a>
        
        @if($nextModel)
            <a href="{{ $nextModel->url }}" class="model-nav-btn" id="next-model" title="Next model (→)">
                <span class="model-nav-label">{{ $nextModel->username }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <div class="model-nav-btn model-nav-disabled"></div>
        @endif
    </div>

    <div class="model-detail-wide">
        {{-- Main Stream Area --}}
        <div class="model-stream-wide">
            <div class="model-stream-player-wide {{ $model->stream_width && $model->stream_height && $model->stream_height > $model->stream_width ? 'portrait-stream' : '' }}" style="aspect-ratio: {{ $model->stream_aspect_ratio }};">
                @if($model->is_online && $model->best_stream_url)
                    {{-- Loading Skeleton --}}
                    <div class="stream-loading-skeleton" id="stream-skeleton">
                        <img src="{{ $model->best_image_url }}" alt="{{ $model->username }}" class="stream-skeleton-bg">
                        <div class="stream-skeleton-overlay">
                            <div class="stream-skeleton-spinner"></div>
                            <span>{{ __('Loading stream...') }}</span>
                        </div>
                    </div>
                    {{-- HLS Video Player --}}
                    <video 
                        id="stream-player"
                        class="model-stream-video"
                        playsinline>
                    </video>
                    <div class="model-stream-live-badge">LIVE</div>
                    
                    {{-- Custom Controls --}}
                    <div class="stream-controls">
                        <div class="stream-volume-group">
                            <button class="stream-control-btn" id="mute-btn" onclick="toggleMute()">
                                <svg id="icon-muted" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>
                                </svg>
                                <svg id="icon-unmuted" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                                    <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
                                </svg>
                            </button>
                            <div class="stream-volume-slider" id="volume-slider-wrap">
                                <input type="range" id="volume-slider" class="volume-slider" min="0" max="1" step="0.05" value="1" oninput="setVolume(this.value)">
                            </div>
                        </div>
                        <button class="stream-control-btn stream-control-fullscreen" id="fullscreen-btn" onclick="toggleFullscreen()">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                            </svg>
                        </button>
                    </div>
                @elseif($model->is_online)
                    {{-- Online but no embed - show preview with link --}}
                    <div class="model-stream-preview">
                        <img src="{{ $model->best_image_url }}" alt="{{ $model->username }}">
                        <div class="model-stream-preview-overlay">
                            <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow" class="model-stream-play-btn" data-affiliate="{{ $model->source_platform }}">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                {{ __('Watch Live Stream') }}
                            </a>
                        </div>
                        <div class="model-stream-live-badge">LIVE</div>
                    </div>
                @else
                    {{-- Offline - show preview --}}
                    <div class="model-stream-preview">
                        <img src="{{ $model->best_image_url }}" alt="{{ $model->username }}">
                        <div class="model-stream-preview-overlay model-stream-preview-offline">
                            <div class="model-stream-offline-badge">{{ __('OFFLINE') }}</div>
                            <h2>{{ $model->username }} {{ __('is Offline') }}</h2>
                            <p>{{ __('Check back later or browse other live models') }}</p>
                            <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow" class="btn btn-primary" data-affiliate="{{ $model->source_platform }}">
                                {{ __('Visit Profile') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Keyboard shortcuts hint (desktop only) --}}
            <div class="shortcuts-hint" id="shortcuts-hint">
                <div class="shortcuts-hint-items">
                    <span class="shortcut-item"><kbd>←</kbd><kbd>→</kbd> {{ __('navigate') }}</span>
                    <span class="shortcut-item"><kbd>M</kbd> {{ __('mute') }}</span>
                    <span class="shortcut-item"><kbd>F</kbd> {{ __('fullscreen') }}</span>
                </div>
            </div>

            {{-- Model Info Bar --}}
            @php
                $isFavorited = auth()->check() && auth()->user()->hasFavorited($model);
            @endphp
            <div class="model-info-bar">
                {{-- Top row: avatar + details + stats + favorite --}}
                <div class="model-info-bar-top">
                    <div class="model-info-avatar">
                        <img src="{{ $model->avatar_url }}" alt="{{ $model->username }}">
                        @if($model->is_online)
                            <span class="model-info-online-dot"></span>
                        @endif
                    </div>
                    <div class="model-info-details">
                        <div class="model-info-name-row">
                            <h1 class="model-info-name">{{ $model->username }}</h1>
                            @if($model->is_hd)
                                <span class="model-hd-badge">HD</span>
                            @endif
                        </div>
                        <div class="model-info-meta">
                            @if($model->age)
                                <span>{{ $model->age }} {{ __('years') }}</span>
                            @endif
                            @if($model->country)
                                <span>{{ $model->country }}</span>
                            @endif
                            <span class="model-info-stat-inline">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span id="viewers-count-inline">{{ number_format($model->viewers_count ?? 0) }}</span>
                            </span>
                            @if($model->rating)
                                <span class="model-info-stat-inline model-info-stat-rating">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    {{ number_format($model->rating, 1) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <button class="model-btn-favorite {{ $isFavorited ? 'is-favorited' : '' }}" 
                            id="favorite-btn"
                            onclick="toggleFavoriteModel('{{ $model->username }}')"
                            title="{{ $isFavorited ? __('Remove from favorites') : __('Add to favorites') }}">
                        <svg viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>
                </div>

                {{-- Bottom row: action buttons --}}
                <div class="model-info-bar-actions">
                    <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow" class="model-btn-private" data-affiliate="{{ $model->source_platform }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <span>{{ __('Private') }}</span>
                    </a>
                    <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow" class="model-btn-tip" data-affiliate="{{ $model->source_platform }}">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                        </svg>
                        <span>{{ __('Tip') }}</span>
                    </a>
                    <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow" class="model-btn-chat" data-affiliate="{{ $model->source_platform }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                        </svg>
                        <span>{{ __('Chat') }}</span>
                    </a>
                    <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow" class="model-btn-profile" data-affiliate="{{ $model->source_platform }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        <span>{{ __('Profile') }}</span>
                    </a>
                </div>
            </div>
            
            {{-- Goal Bar (auto-refreshes every 30s) --}}
            <div class="model-goal-bar-wrapper" id="goal-bar-wrapper">
                @if($model->goal_message)
                    <x-goal-bar 
                        :goalMessage="$model->goal_message"
                        :tokensNeeded="$model->goal_needed"
                        :tokensEarned="$model->goal_earned"
                        :progress="$model->goal_progress"
                    />
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="model-sidebar-narrow">
            <div class="model-chat" id="chat-panel">
                <div class="model-chat-header">
                    <span class="model-chat-title">{{ __('Live Chat') }}</span>
                </div>
                <div class="model-chat-messages" id="chat-messages">
                    <div class="model-chat-welcome">{{ __('Welcome to') }} {{ $model->username }}{{ __("'s room!") }}</div>
                </div>
                <div class="model-chat-input">
                    <input type="text" placeholder="{{ __('Type a message...') }}" onfocus="showChatOverlay()">
                </div>
                <div class="model-chat-overlay" id="chat-overlay" style="display: none;">
                    <div class="model-chat-overlay-card">
                        <h3 class="model-chat-overlay-title">{{ __('Join the Chat') }}</h3>
                        <p class="model-chat-overlay-text">{{ __('Create a free account to chat!') }}</p>
                        <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow" class="btn btn-primary" data-affiliate="{{ $model->source_platform }}" style="width: 100%;">
                            {{ __('Sign Up Free') }}
                        </a>
                        <button onclick="hideChatOverlay()" class="model-chat-overlay-close">{{ __('Maybe later') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Description (directly below stream) --}}
    <section class="model-section model-about-section">
        <h3 class="model-section-title">{{ __('About') }} {{ $model->username }}</h3>
        @if($modelDescription && !empty($modelDescription['long_description']))
            <div class="model-description-full">
                @if(!empty($modelDescription['short_description']))
                    <p class="model-description-intro">{{ $modelDescription['short_description'] }}</p>
                @endif
                <div class="model-description-text">
                    {!! nl2br(e($modelDescription['long_description'])) !!}
                </div>
                @if(!empty($modelDescription['personality_traits']) && count($modelDescription['personality_traits']) > 0)
                    <div class="model-personality-traits">
                        @foreach($modelDescription['personality_traits'] as $trait)
                            <span class="personality-trait">{{ $trait }}</span>
                        @endforeach
                    </div>
                @endif
                @if(!empty($modelDescription['specialties']))
                    <p class="model-specialties">
                        <strong>{{ __('Known for:') }}</strong> {{ $modelDescription['specialties'] }}
                    </p>
                @endif
            </div>
        @else
            <p class="model-description">
                {{ $model->description ?? "Watch {$model->username} live on PornGuruCam. Join the free chat and interact with this amazing model." }}
            </p>
        @endif
    </section>

    {{-- FAQs Section --}}
    @if($modelFaqs->isNotEmpty())
        <section class="model-section model-faqs-section">
            <h3 class="model-section-title">{{ __('Frequently Asked Questions') }}</h3>
            <div class="model-faqs">
                @foreach($modelFaqs as $faq)
                    <details class="model-faq-item">
                        <summary class="model-faq-question">{{ $faq->localized_question }}</summary>
                        <div class="model-faq-answer">
                            {!! nl2br(e($faq->localized_answer)) !!}
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Tip Menu & Schedule Side by Side --}}
    <div class="model-info-grid">
        {{-- Tip Menu Panel + Completed Goals --}}
        <section class="model-info-grid-item model-info-grid-item-stacked">
            <x-tip-menu 
                :modelId="$model->id" 
                :modelName="$model->username"
                :affiliateUrl="$model->affiliate_url" 
            />
            
            {{-- Past Completed Goals --}}
            @php
                $completedGoals = \App\Models\ModelGoal::forModel($model->username)->completed()->orderByDesc('completed_at')->limit(6)->get();
            @endphp
            @if($completedGoals->isNotEmpty())
                <div class="model-completed-goals">
                    <div class="completed-goals-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ __('Completed Goals') }}</span>
                    </div>
                    <div class="completed-goals-list">
                        @foreach($completedGoals as $goal)
                            <div class="completed-goal-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span class="completed-goal-text">{{ Str::limit($goal->goal_message, 50) }}</span>
                                <span class="completed-goal-time">{{ $goal->completed_at->diffForHumans(null, true) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- Online Schedule Heatmap --}}
        <section class="model-info-grid-item">
            <x-model-heatmap 
                :model-id="$model->username"
                :model-name="$model->username"
                :profile-url="$model->url"
                :image-url="$model->preview_url"
                :include-schema="false"
            />
        </section>
    </div>

    {{-- Suggested Models --}}
    @if(isset($similarModels) && $similarModels->count() > 0)
        <section class="model-section">
            <div class="model-section-header">
                <h3 class="model-section-title">{{ __('Suggested Models') }}</h3>
                <div class="similar-nav">
                    <button class="similar-nav-btn" id="similar-prev" onclick="scrollSimilar(-1)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button class="similar-nav-btn" id="similar-next" onclick="scrollSimilar(1)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="similar-models-scroll" id="similar-scroll">
                @foreach($similarModels as $similar)
                    <a href="{{ $similar->url }}" 
                       class="suggested-model-card" 
                       data-stream-url="{{ $similar->best_stream_url }}"
                       data-preview="{{ $similar->snapshot_url ?? $similar->preview_url }}">
                        <div class="suggested-model-image">
                            <img src="{{ $similar->best_image_url }}" alt="{{ $similar->username }}" class="suggested-model-thumb" loading="lazy">
                            
                            @if($similar->is_online && $similar->best_stream_url)
                                <video class="model-card-video" muted playsinline></video>
                            @endif

                            @if($similar->snapshot_url || $similar->preview_url)
                                <img src="{{ $similar->snapshot_url ?? $similar->preview_url }}" alt="{{ $similar->username }}" class="suggested-model-preview" loading="lazy">
                            @endif
                            @php
                                $flagSource = $similar->country ?: ($similar->languages[0] ?? null);
                            @endphp
                            @if($flagSource)
                                <span class="suggested-model-flag" title="{{ $similar->country }}">{{ country_flag($flagSource) }}</span>
                            @endif
                        </div>
                        <div class="suggested-model-info">
                            <span class="suggested-model-name">{{ $similar->username }}</span>
                            @if($similar->viewers_count)
                                <span class="suggested-model-viewers">{{ number_format($similar->viewers_count) }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Tags Section --}}
    @if(!empty($model->tags) && count($model->tags) > 0)
        <section class="model-section">
            <h3 class="model-section-title">{{ __('Tags') }}</h3>
            <div class="model-tags">
                @foreach($model->tags as $tag)
                    <x-tag-link :tag="$tag" />
                @endforeach
            </div>
        </section>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    function showChatOverlay() {
        document.getElementById('chat-overlay').style.display = 'flex';
    }
    
    function hideChatOverlay() {
        document.getElementById('chat-overlay').style.display = 'none';
    }

    // Favorite toggle
    async function toggleFavoriteModel(modelUsername) {
        try {
            const response = await fetch(`/api/favorite/${modelUsername}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.requiresAuth) {
                showFavoritePopup();
                return;
            }

            if (data.success) {
                const button = document.getElementById('favorite-btn');
                const svg = button.querySelector('svg');
                if (data.isFavorited) {
                    button.classList.add('is-favorited');
                    svg.setAttribute('fill', 'currentColor');
                } else {
                    button.classList.remove('is-favorited');
                    svg.setAttribute('fill', 'none');
                }
            }
        } catch (error) {
            console.error('Error toggling favorite:', error);
        }
    }

    function showFavoritePopup() {
        // Remove existing popup if any
        const existing = document.getElementById('favorite-popup');
        if (existing) existing.remove();

        const popup = document.createElement('div');
        popup.id = 'favorite-popup';
        popup.innerHTML = `
            <div class="favorite-popup-overlay" onclick="closeFavoritePopup()">
                <div class="favorite-popup" onclick="event.stopPropagation()">
                    <button class="favorite-popup-close" onclick="closeFavoritePopup()">&times;</button>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:48px;height:48px;color:var(--accent);margin-bottom:1rem">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <h3>{{ __('Save Your Favorites') }}</h3>
                    <p>{{ __('Create a free account to build your personal favorites list and get notified when your favorite models go live.') }}</p>
                    <div class="favorite-popup-actions">
                        <a href="{{ route('register') }}" class="favorite-popup-btn favorite-popup-btn-primary">{{ __('Sign Up Free') }}</a>
                        <a href="{{ route('login') }}" class="favorite-popup-btn favorite-popup-btn-secondary">{{ __('Log In') }}</a>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(popup);
    }

    function closeFavoritePopup() {
        const popup = document.getElementById('favorite-popup');
        if (popup) popup.remove();
    }

    function scrollSimilar(direction) {
        const container = document.getElementById('similar-scroll');
        const scrollAmount = 300;
        container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }

    let savedVolume = 1;

    function toggleMute() {
        const video = document.getElementById('stream-player');
        if (!video) return;

        video.muted = !video.muted;
        updateVolumeUI();

        // Sync slider when unmuting
        const slider = document.getElementById('volume-slider');
        if (slider && !video.muted && video.volume === 0) {
            video.volume = savedVolume || 0.5;
            slider.value = video.volume;
        }
    }

    function setVolume(val) {
        const video = document.getElementById('stream-player');
        if (!video) return;

        val = parseFloat(val);
        video.volume = val;
        savedVolume = val;

        if (val === 0) {
            video.muted = true;
        } else if (video.muted) {
            video.muted = false;
        }

        updateVolumeUI();
    }

    function updateVolumeUI() {
        const video = document.getElementById('stream-player');
        const iconMuted = document.getElementById('icon-muted');
        const iconUnmuted = document.getElementById('icon-unmuted');
        const slider = document.getElementById('volume-slider');

        if (!video) return;

        const isSilent = video.muted || video.volume === 0;
        if (iconMuted && iconUnmuted) {
            iconMuted.style.display = isSilent ? 'block' : 'none';
            iconUnmuted.style.display = isSilent ? 'none' : 'block';
        }
        if (slider) {
            slider.value = isSilent ? 0 : video.volume;
        }
    }

    function toggleFullscreen() {
        const player = document.querySelector('.model-stream-player-wide');
        if (!player) return;
        
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            player.requestFullscreen().catch(() => {});
        }
    }

    // Initialize HLS player
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.getElementById('stream-player');
        const skeleton = document.getElementById('stream-skeleton');
        const streamUrl = @json($model->best_stream_url);

        if (!video || !streamUrl) return;

        function onStreamReady() {
            video.classList.add('loaded');
            if (skeleton) {
                setTimeout(() => skeleton.style.display = 'none', 300);
            }
        }

        function onStreamError() {
            console.error('Stream failed to load');
            if (skeleton) {
                skeleton.querySelector('.stream-skeleton-overlay span').textContent = 'Stream unavailable';
                skeleton.querySelector('.stream-skeleton-spinner').style.display = 'none';
            }
        }

        function tryPlayUnmuted() {
            // Try to play unmuted first
            video.muted = false;
            updateMuteIcon();
            
            return video.play().catch(() => {
                // Browser blocked unmuted autoplay, fall back to muted
                video.muted = true;
                updateMuteIcon();
                return video.play().catch(() => {});
            });
        }

        function updateMuteIcon() {
            const iconMuted = document.getElementById('icon-muted');
            const iconUnmuted = document.getElementById('icon-unmuted');
            if (iconMuted && iconUnmuted) {
                iconMuted.style.display = video.muted ? 'block' : 'none';
                iconUnmuted.style.display = video.muted ? 'none' : 'block';
            }
        }

        if (Hls.isSupported()) {
            const hls = new Hls({
                maxBufferLength: 30,
                maxMaxBufferLength: 60,
                startLevel: -1, // Auto quality
                capLevelToPlayerSize: true
            });
            
            hls.loadSource(streamUrl);
            hls.attachMedia(video);
            
            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                tryPlayUnmuted();
                onStreamReady();
            });

            hls.on(Hls.Events.ERROR, function(event, data) {
                if (data.fatal) {
                    onStreamError();
                }
            });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Native HLS support (Safari)
            video.src = streamUrl;
            video.addEventListener('loadedmetadata', function() {
                tryPlayUnmuted();
                onStreamReady();
            });
            video.addEventListener('error', onStreamError);
        } else {
            onStreamError();
        }

        // Fallback timeout
        setTimeout(() => {
            if (!video.classList.contains('loaded')) {
                onStreamReady();
            }
        }, 10000);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Don't trigger if typing in an input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        switch(e.key) {
            case 'ArrowLeft':
                const prevLink = document.getElementById('prev-model');
                if (prevLink) {
                    e.preventDefault();
                    prevLink.click();
                }
                break;
            case 'ArrowRight':
                const nextLink = document.getElementById('next-model');
                if (nextLink) {
                    e.preventDefault();
                    nextLink.click();
                }
                break;
            case 'm':
            case 'M':
                toggleMute();
                break;
            case 'f':
            case 'F':
                toggleFullscreen();
                break;
            case 'Escape':
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
                break;
        }
    });

    // ============================================
    // Mobile swipe tutorial (show once)
    // ============================================
    (function() {
        const tutorial = document.getElementById('swipe-tutorial');
        if (!tutorial) return;
        // Only show on touch devices, and only once
        if ('ontouchstart' in window && !localStorage.getItem('swipe_tutorial_seen')) {
            tutorial.classList.add('visible');
            localStorage.setItem('swipe_tutorial_seen', '1');
            // Remove after animation ends
            setTimeout(() => tutorial.remove(), 5500);
        }
    })();

    // ============================================
    // Mobile swipe navigation (prev/next model)
    // ============================================
    (function() {
        const swipeArea = document.querySelector('.model-detail-wide');
        if (!swipeArea) return;

        const prevLink = document.getElementById('prev-model');
        const nextLink = document.getElementById('next-model');
        const prevName = @json($prevModel?->username ?? '');
        const nextName = @json($nextModel?->username ?? '');
        const prevAvatar = @json($prevModel?->avatar_url ?? '');
        const nextAvatar = @json($nextModel?->avatar_url ?? '');

        let touchStartX = 0;
        let touchStartY = 0;
        let touchDeltaX = 0;
        let swiping = false;
        let indicator = null;

        function createIndicator(direction, name, avatar) {
            removeIndicator();
            indicator = document.createElement('div');
            indicator.className = 'swipe-indicator ' + direction;
            const arrow = direction === 'left'
                ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>'
                : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>';
            const avatarImg = avatar ? `<img class="swipe-avatar" src="${avatar}" alt="">` : '';
            if (direction === 'left') {
                indicator.innerHTML = arrow + avatarImg + `<span>${name}</span>`;
            } else {
                indicator.innerHTML = `<span>${name}</span>` + avatarImg + arrow;
            }
            document.body.appendChild(indicator);
            requestAnimationFrame(() => indicator.classList.add('visible'));
        }

        function removeIndicator() {
            if (indicator) {
                indicator.classList.remove('visible');
                const el = indicator;
                setTimeout(() => el.remove(), 200);
                indicator = null;
            }
        }

        swipeArea.addEventListener('touchstart', function(e) {
            // Don't interfere with controls or links
            if (e.target.closest('.stream-controls, a, button, input')) return;
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            touchDeltaX = 0;
            swiping = false;
        }, { passive: true });

        swipeArea.addEventListener('touchmove', function(e) {
            if (!touchStartX) return;

            const deltaX = e.touches[0].clientX - touchStartX;
            const deltaY = e.touches[0].clientY - touchStartY;

            // Must be mostly horizontal
            if (!swiping && Math.abs(deltaX) > 20 && Math.abs(deltaX) > Math.abs(deltaY) * 1.5) {
                swiping = true;
            }

            if (!swiping) return;

            touchDeltaX = deltaX;

            // Show indicator based on direction
            if (deltaX > 50 && prevLink && prevName) {
                if (!indicator || !indicator.classList.contains('left')) {
                    createIndicator('left', prevName, prevAvatar);
                }
            } else if (deltaX < -50 && nextLink && nextName) {
                if (!indicator || !indicator.classList.contains('right')) {
                    createIndicator('right', nextName, nextAvatar);
                }
            } else {
                removeIndicator();
            }
        }, { passive: true });

        swipeArea.addEventListener('touchend', function() {
            const threshold = 80;

            if (swiping) {
                if (touchDeltaX > threshold && prevLink) {
                    prevLink.click();
                } else if (touchDeltaX < -threshold && nextLink) {
                    nextLink.click();
                }
            }

            removeIndicator();
            touchStartX = 0;
            touchStartY = 0;
            touchDeltaX = 0;
            swiping = false;
        }, { passive: true });
    })();

    // ============================================
    // Goal bar auto-refresh (every 30 seconds)
    // ============================================
    (function() {
        const goalApiUrl = @json(route('api.model.goal', $model));
        const goalWrapper = document.getElementById('goal-bar-wrapper');
        if (!goalWrapper) return;

        let lastGoalMessage = @json($model->goal_message ?? '');
        let lastProgress = @json($model->goal_progress ?? 0);

        function refreshGoal() {
            fetch(goalApiUrl, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    // Update viewers count in the page if present
                    const viewersEl = document.getElementById('viewers-count-inline');
                    if (viewersEl && data.viewers_count !== undefined) {
                        viewersEl.textContent = Number(data.viewers_count).toLocaleString();
                    }

                    // Update online status dot
                    const statusDot = document.querySelector('.model-status-dot');
                    if (statusDot) {
                        statusDot.className = 'model-status-dot ' + (data.is_online ? 'online' : '');
                    }

                    // Goal bar update
                    if (!data.goal_message) {
                        // No active goal — clear it
                        goalWrapper.innerHTML = '';
                        lastGoalMessage = '';
                        lastProgress = 0;
                        return;
                    }

                    const progress = data.goal_progress ?? (data.goal_needed > 0 ? Math.min(100, Math.round((data.goal_earned / data.goal_needed) * 1000) / 10) : 0);

                    // If goal changed or didn't exist before, rebuild
                    if (data.goal_message !== lastGoalMessage || !goalWrapper.querySelector('.goal-bar-container')) {
                        goalWrapper.innerHTML = buildGoalBarHTML(data.goal_message, data.goal_needed, data.goal_earned, progress);
                        lastGoalMessage = data.goal_message;
                        lastProgress = progress;
                        return;
                    }

                    // Same goal — just animate the progress
                    if (progress !== lastProgress) {
                        const fill = goalWrapper.querySelector('.goal-bar-fill');
                        const pctEl = goalWrapper.querySelector('.goal-bar-percentage');
                        if (fill) fill.style.width = progress + '%';
                        if (pctEl) pctEl.textContent = progress + '%';
                        lastProgress = progress;
                    }
                })
                .catch(() => { /* silent fail */ });
        }

        function buildGoalBarHTML(message, needed, earned, progress) {
            // Parse token from message if needed
            let displayMessage = message;
            let displayTokens = needed;
            if (!displayTokens && message) {
                const m = message.match(/^(\d+)\s*/);
                if (m) {
                    displayTokens = parseInt(m[1]);
                    displayMessage = message.replace(/^\d+\s*/, '');
                }
            }
            const tokensFormatted = displayTokens ? Number(displayTokens).toLocaleString() : '';
            const tokenSpan = tokensFormatted ? `<span class="goal-bar-tokens">${tokensFormatted}</span>` : '';

            return `<div class="goal-bar-container">
                <div class="goal-bar">
                    <div class="goal-bar-fill" style="width: ${progress}%;"></div>
                    <div class="goal-bar-content">
                        <div class="goal-bar-left">
                            <div class="goal-bar-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>
                                </svg>
                            </div>
                            <div class="goal-bar-text">
                                <span class="goal-bar-label">{{ __('Goal') }}:</span>
                                ${tokenSpan}
                                <span class="goal-bar-message">${displayMessage}</span>
                            </div>
                        </div>
                        <div class="goal-bar-percentage">${progress}%</div>
                    </div>
                </div>
            </div>`;
        }

        // Poll every 30 seconds
        setInterval(refreshGoal, 30000);
    })();
</script>

{{-- Stripchat Interstitial for offline models --}}
@if(!$model->is_online && $model->source_platform === 'stripchat')
<script>
!function(){
    const e = {
        url: @json($model->affiliate_url),
        decryptUrl: false,
        contentUrl: @json($model->affiliate_url),
        decryptContentUrl: false,
        contentType: "iframe",
        width: "85%",
        height: "91%",
        timeout: 15000,
        delayClose: 2500,
        clickStart: false,
        closeIntent: true,
        borderColor: "#000",
        closeButtonColor: "#000",
        closeCrossColor: "#fff",
        shadow: true,
        shadowColor: "#000",
        shadowOpacity: ".5",
        shadeColor: "#111",
        shadeOpacity: ".5",
        border: "1px",
        borderRadius: "8px",
        leadOut: true,
        animation: "fade",
        direction: "up",
        verticalPosition: "center",
        horizontalPosition: "center",
        expireDays: 1
    };
    // Load the popin script and initialize
    const s = document.createElement('script');
    s.src = 'https://creative.mavrtracktor.com/js/popin.js';
    s.onload = function() {
        if (window.popinMin) {
            window.popinMin(e);
        }
    };
    document.head.appendChild(s);
}();
</script>
@endif
@endsection
