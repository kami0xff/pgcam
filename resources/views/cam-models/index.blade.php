@extends('layouts.pornguru')

@section('title', __('Live Cam Models') . ($models->currentPage() > 1 ? ' - ' . __('Page') . ' ' . $models->currentPage() : ''))

@section('meta_description'){{ __('Watch :count live cam models streaming now. Free live sex cams from top adult platforms.', ['count' => number_format($onlineCount)]) }}@endsection

@section('canonical'){{ $models->currentPage() > 1 ? $models->url($models->currentPage()) : localized_route('home') }}@endsection

@section('og_title', __('Live Cam Models') . ' - PornGuru.cam')

{{-- SEO Pagination Links (for search engine crawlers) --}}
@push('seo-pagination')
    @php
        $homeHreflangUrls = ['en' => route('home'), 'x-default' => route('home')];
        foreach (config('locales.priority', []) as $loc) {
            if ($loc !== 'en') $homeHreflangUrls[$loc] = url("/{$loc}");
        }
    @endphp
    <x-seo.hreflang :urls="$homeHreflangUrls" />
    @if($models->currentPage() > 1)
        <link rel="prev" href="{{ $models->previousPageUrl() }}">
    @endif
    @if($models->hasMorePages())
        <link rel="next" href="{{ $models->nextPageUrl() }}">
    @endif
@endpush

@section('content')
    <div class="container page-section">
        {{-- Page Header --}}
        <div class="page-title-bar">
            <div class="page-title-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 23a7.5 7.5 0 01-5.138-12.963C8.204 8.774 11.5 6.5 11 1.5c6 4 9 8 3 14 1 0 2.5 0 5-2.47.27.773.5 1.604.5 2.47A7.5 7.5 0 0112 23z"/>
                </svg>
            </div>
            <h1 class="page-title-text">{{ __('LIVE CAMS') }}</h1>
        </div>
        
        {{-- Stats --}}
        <div class="page-stats">
            <span class="page-stats-total">{{ number_format($totalCount) }} {{ __('models total') }}</span>
            <span class="page-stats-separator">•</span>
            <span class="page-stats-online">{{ number_format($onlineCount) }} {{ __('online now') }}</span>
        </div>

        {{-- Top SEO Content (if configured) --}}
        <x-seo.content-block pageKey="home" position="top" class="seo-text-top" />

        {{-- Filters --}}
        <x-pornguru.filter-panel 
            :action="localized_route('home')" 
            :filters="$filters" 
            :platforms="$platforms" 
            :genders="$genders" 
        />

        {{-- Your Favorites Section (if logged in and has online favorites) --}}
        @auth
            @if($onlineFavorites->isNotEmpty())
                <section class="favorites-section" data-section="favorites">
                    <div class="favorites-header">
                        <div class="favorites-title">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                            <h2>{{ __('Your Favorites') }}</h2>
                            <span class="favorites-count">{{ $onlineFavorites->count() }} {{ __('online') }}</span>
                        </div>
                        <a href="{{ route('dashboard') }}" class="favorites-link">{{ __('View all') }}</a>
                    </div>
                    <div class="favorites-grid">
                        @foreach($onlineFavorites as $model)
                            <x-pornguru.model-card :model="$model" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endauth

        {{-- SEO Featured Sections (server-rendered for SEO) --}}
        @if(!empty($seoSections))
            @foreach($seoSections as $section)
                <section class="seo-section" data-section="{{ $section['slug'] ?? Str::slug($section['title']) }}">
                    <h2 class="seo-section-title">{{ $section['title'] }}</h2>
                    <div class="seo-section-grid">
                        @foreach($section['models'] as $model)
                            <x-pornguru.model-card :model="$model" />
                        @endforeach
                    </div>
                    <a href="{{ localized_route('home', ['tags' => $section['slug'], 'online' => 1]) }}" class="seo-section-more">
                        {{ __('View all :category', ['category' => $section['title']]) }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </section>
            @endforeach
        @endif

        {{-- Results Count with Preview Toggle --}}
        <div class="results-bar">
            <x-pornguru.results-count :paginator="$models" />
            <div class="preview-controls">
                <button class="preview-toggle" id="preview-toggle" onclick="toggleAllPreviews()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="preview-off">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    <svg viewBox="0 0 24 24" fill="currentColor" class="preview-on" style="display: none;">
                        <rect x="6" y="4" width="4" height="16"/>
                        <rect x="14" y="4" width="4" height="16"/>
                    </svg>
                    <span id="preview-label">{{ __('Play All') }}</span>
                </button>
            </div>
        </div>

        {{-- Models Grid or Empty State --}}
        @if($models->isEmpty())
            <x-pornguru.empty-state 
                :title="__('No models found')" 
                :text="__('Try adjusting your filters.')" 
            />
        @else
            <div class="models-grid" id="models-grid" data-section="main-grid">
                @foreach($models as $model)
                    <x-pornguru.model-card :model="$model" />
                @endforeach
            </div>

            {{-- Infinite Scroll Loader --}}
            @if($models->hasMorePages())
                <div class="infinite-scroll-loader" id="infinite-loader">
                    <div class="loader-spinner"></div>
                    <span>{{ __('Loading more models...') }}</span>
                </div>
            @endif

            {{-- Infinite Scroll Trigger --}}
            <div id="infinite-scroll-trigger"></div>

            {{-- SEO Pagination (hidden from users, visible to search engines) --}}
            <nav class="seo-pagination" aria-label="Pagination">
                @if($models->currentPage() > 1)
                    <a href="{{ $models->previousPageUrl() }}" rel="prev">{{ __('Previous Page') }}</a>
                @endif
                
                <span>{{ __('Page') }} {{ $models->currentPage() }} {{ __('of') }} {{ $models->lastPage() }}</span>
                
                @if($models->hasMorePages())
                    <a href="{{ $models->nextPageUrl() }}" rel="next">{{ __('Next Page') }}</a>
                @endif
            </nav>
        @endif

        {{-- Bottom SEO Content --}}
        <x-seo.content-block pageKey="home" position="bottom" class="seo-text-bottom" />
    </div>

    {{-- HLS.js for stream previews --}}
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    
    {{-- Pass data to JavaScript --}}
    <script>
        // Favorite toggle
        async function toggleFavorite(modelUsername, button) {
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
    </script>
    <script>
        window.infiniteScrollConfig = {
            apiUrl: '{{ route('api.models.load') }}',
            currentPage: {{ $models->currentPage() }},
            hasMore: {{ $models->hasMorePages() ? 'true' : 'false' }},
            filters: @json($filters)
        };

        // Stream preview management
        let allPreviewsPlaying = false;
        let autoplayEnabled = false;
        let hoverTimeout = null;
        const activeStreams = new Map();
        const failedStreams = new Set();

        function isSlowConnection() {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            if (!connection) return false;
            if (connection.saveData) return true;
            const slowTypes = ['slow-2g', '2g', '3g'];
            if (slowTypes.includes(connection.effectiveType)) return true;
            if (connection.downlink && connection.downlink < 1.5) return true;
            return false;
        }

        function initAutoplay() {
            const savedPref = localStorage.getItem('autoplayPreviews');
            const checkbox = document.getElementById('autoplay-checkbox');

            if (savedPref !== null) {
                if (savedPref === 'true') {
                    if (checkbox) checkbox.checked = true;
                    toggleAutoplay(true, false);
                }
                return;
            }

            if (isSlowConnection()) return;

            if (checkbox) checkbox.checked = true;
            toggleAutoplay(true, true);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initAutoplay();
            window.addEventListener('load', () => {
                if (allPreviewsPlaying) {
                    setTimeout(playAllVisibleStreams, 500);
                }
            });
        });

        function updateToggleUI(playing) {
            const toggle = document.getElementById('preview-toggle');
            const label = document.getElementById('preview-label');
            if (!toggle || !label) return;
            const offIcon = toggle.querySelector('.preview-off');
            const onIcon = toggle.querySelector('.preview-on');
            if (playing) {
                label.textContent = @json(__('Stop All'));
                if (offIcon) offIcon.style.display = 'none';
                if (onIcon) onIcon.style.display = 'block';
                toggle.classList.add('active');
            } else {
                label.textContent = @json(__('Play All'));
                if (offIcon) offIcon.style.display = 'block';
                if (onIcon) onIcon.style.display = 'none';
                toggle.classList.remove('active');
            }
        }

        function toggleAutoplay(enabled, save = true) {
            autoplayEnabled = enabled;
            if (save) localStorage.setItem('autoplayPreviews', enabled);

            if (enabled) {
                allPreviewsPlaying = true;
                updateToggleUI(true);
                playAllVisibleStreams();
            } else {
                allPreviewsPlaying = false;
                updateToggleUI(false);
                stopAllStreams();
            }
        }

        function toggleAllPreviews() {
            allPreviewsPlaying = !allPreviewsPlaying;
            autoplayEnabled = allPreviewsPlaying;

            const checkbox = document.getElementById('autoplay-checkbox');
            if (checkbox) checkbox.checked = allPreviewsPlaying;
            localStorage.setItem('autoplayPreviews', allPreviewsPlaying);
            updateToggleUI(allPreviewsPlaying);

            if (allPreviewsPlaying) {
                failedStreams.clear();
                document.querySelectorAll('.stream-failed').forEach(el => {
                    el.classList.remove('stream-failed');
                    el.dataset.retryCount = '0';
                });
                playAllVisibleStreams();
            } else {
                stopAllStreams();
            }
        }

        function playAllVisibleStreams() {
            const cards = document.querySelectorAll('.model-card[data-stream-url]');
            let delay = 0;
            cards.forEach(card => {
                const streamUrl = card.dataset.streamUrl;
                if (streamUrl && isElementInViewport(card) && !activeStreams.has(card)) {
                    if (delay === 0) {
                        startStream(card, streamUrl);
                    } else {
                        setTimeout(() => {
                            if (allPreviewsPlaying && isElementInViewport(card)) {
                                startStream(card, streamUrl);
                            }
                        }, delay);
                    }
                    delay += 300;
                }
            });
        }

        function stopAllStreams() {
            activeStreams.forEach((data, card) => {
                stopStream(card);
            });
        }

        function startStream(card, streamUrl) {
            if (!streamUrl || activeStreams.has(card) || failedStreams.has(streamUrl)) return;

            const video = card.querySelector('.model-card-video');
            if (!video) return;

            video.addEventListener('playing', () => {
                card.classList.add('stream-playing');
            }, { once: true });

            const loadTimeout = setTimeout(() => {
                if (!card.classList.contains('stream-playing')) {
                    handleStreamError(card, streamUrl);
                }
            }, 15000);

            if (Hls.isSupported()) {
                const hls = new Hls({
                    maxBufferLength: 10,
                    maxMaxBufferLength: 20,
                    startLevel: 0,
                    capLevelToPlayerSize: true,
                    manifestLoadingTimeOut: 12000,
                    manifestLoadingMaxRetry: 2,
                    levelLoadingTimeOut: 12000,
                    fragLoadingTimeOut: 15000
                });

                hls.loadSource(streamUrl);
                hls.attachMedia(video);

                hls.on(Hls.Events.MANIFEST_PARSED, () => {
                    clearTimeout(loadTimeout);
                    card.dataset.retryCount = '0';
                    video.play().catch(() => handleStreamError(card, streamUrl));
                });

                hls.on(Hls.Events.ERROR, (event, data) => {
                    if (data.fatal) {
                        clearTimeout(loadTimeout);
                        handleStreamError(card, streamUrl);
                    }
                });

                activeStreams.set(card, { hls, timeout: loadTimeout });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = streamUrl;
                video.addEventListener('error', () => {
                    clearTimeout(loadTimeout);
                    handleStreamError(card, streamUrl);
                }, { once: true });
                video.play().catch(() => {
                    clearTimeout(loadTimeout);
                    handleStreamError(card, streamUrl);
                });
                activeStreams.set(card, { hls: null, timeout: loadTimeout });
            }
        }

        function handleStreamError(card, streamUrl) {
            stopStream(card);
            const retryCount = parseInt(card.dataset.retryCount || '0', 10);
            if (retryCount < 1) {
                card.dataset.retryCount = String(retryCount + 1);
                setTimeout(() => {
                    if (allPreviewsPlaying && isElementInViewport(card)) {
                        startStream(card, streamUrl);
                    }
                }, 2000);
            } else {
                failedStreams.add(streamUrl);
                card.classList.add('stream-failed');
            }
        }

        function stopStream(card) {
            const streamData = activeStreams.get(card);
            if (streamData) {
                if (streamData.hls) streamData.hls.destroy();
                if (streamData.timeout) clearTimeout(streamData.timeout);
            }
            const video = card.querySelector('.model-card-video');
            if (video) {
                video.pause();
                video.src = '';
                video.load();
            }
            card.classList.remove('stream-playing');
            activeStreams.delete(card);
        }

        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top < (window.innerHeight || document.documentElement.clientHeight) &&
                rect.bottom > 0 &&
                rect.left < (window.innerWidth || document.documentElement.clientWidth) &&
                rect.right > 0
            );
        }

        document.addEventListener('mouseenter', (e) => {
            if (!(e.target instanceof Element)) return;
            const card = e.target.closest('.model-card');
            if (!card || allPreviewsPlaying) return;
            if (activeStreams.has(card)) return;
            const streamUrl = card.dataset.streamUrl;
            if (!streamUrl) return;
            hoverTimeout = setTimeout(() => startStream(card, streamUrl), 500);
        }, true);

        document.addEventListener('mouseleave', (e) => {
            if (!(e.target instanceof Element)) return;
            const card = e.target.closest('.model-card');
            if (!card || allPreviewsPlaying) return;
            if (hoverTimeout) { clearTimeout(hoverTimeout); hoverTimeout = null; }
            if (!autoplayEnabled) stopStream(card);
        }, true);

        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (!allPreviewsPlaying) return;
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                activeStreams.forEach((data, card) => {
                    if (!isElementInViewport(card)) stopStream(card);
                });
                playAllVisibleStreams();
            }, 200);
        });
    </script>
@endsection
