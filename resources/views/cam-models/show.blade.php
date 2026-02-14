@extends('layouts.pornguru')

@section('title', $model->username . ' - Live Cam')

@section('meta_description'){{ $metaDescription }}@endsection

@push('seo-pagination')
<x-seo.schema :schemas="$seoSchemas" />
@endpush

@push('head')
{{-- Broadcast Schedule JSON-LD for SEO --}}
<x-broadcast-schedule-schema 
    :model-id="$model->username"
    :model-name="$model->username"
    :profile-url="route('cam-models.show', $model)"
    :image-url="$model->preview_url"
/>
@endpush

@section('content')
<div class="container page-section">
    
    {{-- Model Navigation --}}
    <div class="model-nav">
        @if($prevModel)
            <a href="{{ route('cam-models.show', $prevModel) }}" class="model-nav-btn" id="prev-model" title="Previous model (←)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="model-nav-label">{{ $prevModel->username }}</span>
            </a>
        @else
            <div class="model-nav-btn model-nav-disabled"></div>
        @endif
        
        <a href="{{ route('home', ['online' => 1]) }}" class="model-nav-home" title="Back to models">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9,22 9,12 15,12 15,22"/>
            </svg>
        </a>
        
        @if($nextModel)
            <a href="{{ route('cam-models.show', $nextModel) }}" class="model-nav-btn" id="next-model" title="Next model (→)">
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
                            <span>Loading stream...</span>
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
                        <button class="stream-control-btn" id="mute-btn" onclick="toggleMute()">
                            <svg id="icon-muted" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>
                            </svg>
                            <svg id="icon-unmuted" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                                <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
                            </svg>
                        </button>
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
                            <a href="{{ $model->profile_url }}" target="_blank" rel="nofollow" class="model-stream-play-btn">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                Watch Live Stream
                            </a>
                        </div>
                        <div class="model-stream-live-badge">LIVE</div>
                    </div>
                @else
                    {{-- Offline - show preview --}}
                    <div class="model-stream-preview">
                        <img src="{{ $model->best_image_url }}" alt="{{ $model->username }}">
                        <div class="model-stream-preview-overlay model-stream-preview-offline">
                            <div class="model-stream-offline-badge">OFFLINE</div>
                            <h2>{{ $model->username }} is Offline</h2>
                            <p>Check back later or browse other live models</p>
                            <a href="{{ $model->profile_url }}" target="_blank" rel="nofollow" class="btn btn-primary">
                                View Profile
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Model Info Bar --}}
            <div class="model-info-bar">
                <div class="model-info-bar-left">
                    <div class="model-info-avatar">
                        <img src="{{ $model->avatar_url }}" alt="{{ $model->username }}">
                        @if($model->is_online)
                            <span class="model-info-online-dot"></span>
                        @endif
                    </div>
                    <div class="model-info-details">
                        <h1 class="model-info-name">{{ $model->username }}</h1>
                        <div class="model-info-meta">
                            @if($model->age)
                                <span>{{ $model->age }} years</span>
                            @endif
                            @if($model->country)
                                <span>{{ $model->country }}</span>
                            @endif
                            @if($model->is_hd)
                                <span class="model-hd-badge">HD</span>
                            @endif
                        </div>
                    </div>
                </div>
                @php
                    $isFavorited = auth()->check() && auth()->user()->hasFavorited($model);
                @endphp
                <div class="model-info-bar-right">
                    <button class="model-btn-favorite {{ $isFavorited ? 'is-favorited' : '' }}" 
                            id="favorite-btn"
                            onclick="toggleFavoriteModel({{ $model->id }})"
                            title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}">
                        <svg viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>
                    <div class="model-info-stat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>{{ number_format($model->viewers_count ?? 0) }}</span>
                    </div>
                    @if($model->rating)
                        <div class="model-info-stat">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>{{ number_format($model->rating, 1) }}</span>
                        </div>
                    @endif
                    <a href="{{ $model->profile_url }}" target="_blank" rel="nofollow" class="model-btn-private">
                        <span>Private Show</span>
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </a>
                    <a href="{{ $model->profile_url }}" target="_blank" rel="nofollow" class="model-btn-tip">
                        <span>Tip</span>
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            {{-- Goal Bar --}}
            @if($model->goal_message)
                <div class="model-goal-bar-wrapper">
                    <x-goal-bar 
                        :goalMessage="$model->goal_message"
                        :tokensNeeded="$model->goal_needed"
                        :tokensEarned="$model->goal_earned"
                        :progress="$model->goal_progress"
                    />
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="model-sidebar-narrow">
            <div class="model-chat" id="chat-panel">
                <div class="model-chat-header">
                    <span class="model-chat-title">Live Chat</span>
                </div>
                <div class="model-chat-messages" id="chat-messages">
                    <div class="model-chat-welcome">Welcome to {{ $model->username }}'s room!</div>
                </div>
                <div class="model-chat-input">
                    <input type="text" placeholder="Type a message..." onfocus="showChatOverlay()">
                </div>
                <div class="model-chat-overlay" id="chat-overlay" style="display: none;">
                    <div class="model-chat-overlay-card">
                        <h3 class="model-chat-overlay-title">Join the Chat</h3>
                        <p class="model-chat-overlay-text">Create a free account to chat!</p>
                        <a href="{{ $model->profile_url }}" target="_blank" rel="nofollow" class="btn btn-primary" style="width: 100%;">
                            Sign Up Free
                        </a>
                        <button onclick="hideChatOverlay()" class="model-chat-overlay-close">Maybe later</button>
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

    {{-- Tags Section --}}
    @if(!empty($model->tags) && count($model->tags) > 0)
        <section class="model-section">
            <h3 class="model-section-title">Tags</h3>
            <div class="model-tags">
                @foreach($model->tags as $tag)
                    <x-tag-link :tag="$tag" />
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
                :affiliateUrl="$model->profile_url" 
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
                :profile-url="route('cam-models.show', $model)"
                :image-url="$model->preview_url"
                :include-schema="false"
            />
        </section>
    </div>

    {{-- Suggested Models --}}
    @if(isset($similarModels) && $similarModels->count() > 0)
        <section class="model-section">
            <div class="model-section-header">
                <h3 class="model-section-title">Suggested Models</h3>
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
                    <a href="{{ route('cam-models.show', $similar) }}" class="suggested-model-card" data-preview="{{ $similar->snapshot_url ?? $similar->preview_url }}">
                        <div class="suggested-model-image">
                            <img src="{{ $similar->best_image_url }}" alt="{{ $similar->username }}" class="suggested-model-thumb" loading="lazy">
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
    async function toggleFavoriteModel(modelId) {
        try {
            const response = await fetch(`/api/favorite/${modelId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.requiresAuth) {
                window.location.href = '{{ route("login") }}';
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

    function scrollSimilar(direction) {
        const container = document.getElementById('similar-scroll');
        const scrollAmount = 300;
        container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }

    function toggleMute() {
        const video = document.getElementById('stream-player');
        const iconMuted = document.getElementById('icon-muted');
        const iconUnmuted = document.getElementById('icon-unmuted');
        
        if (video) {
            video.muted = !video.muted;
            if (iconMuted && iconUnmuted) {
                iconMuted.style.display = video.muted ? 'block' : 'none';
                iconUnmuted.style.display = video.muted ? 'none' : 'block';
            }
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
</script>
@endsection
