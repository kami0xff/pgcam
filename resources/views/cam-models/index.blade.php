@extends('layouts.pornguru')

@section('title'){{ __('seo.home_title', ['count' => number_format($onlineCount)]) }}{{ $models->currentPage() > 1 ? ' - ' . __('pagination.page') . ' ' . $models->currentPage() : '' }}@endsection

@section('meta_description'){{ __('seo.home_desc', ['count' => number_format($onlineCount)]) }}@endsection

@section('canonical'){{ $models->currentPage() > 1 ? $models->url($models->currentPage()) : localized_route('home') }}@endsection

@section('og_title'){{ __('seo.home_og_title', ['count' => number_format($onlineCount)]) }}@endsection
@section('og_image', asset('img/og-home.png'))
@section('og_image_width', '1200')
@section('og_image_height', '630')

{{-- SEO Pagination Links (for search engine crawlers) --}}
@push('seo-pagination')
    @php
        $homeHreflangUrls = ['en' => route('home'), 'x-default' => route('home')];
        foreach (config('locales.priority', []) as $loc) {
            if ($loc !== 'en') $homeHreflangUrls[$loc] = url("/{$loc}");
        }
    @endphp
    @if($models->currentPage() === 1)
    <x-seo.hreflang :urls="$homeHreflangUrls" />
    @if(!empty($seoSchemas))
    <x-seo.schema :schemas="$seoSchemas" />
    @endif
    @endif
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
            <h1 class="page-title-text">{{ __('models.live_cams_heading') }}</h1>
        </div>
        
        {{-- Stats --}}
        <div class="page-stats">
            <span class="page-stats-total">{{ number_format($totalCount) }} {{ __('models.models_total') }}</span>
            <span class="page-stats-separator">•</span>
            <span class="page-stats-online">{{ number_format($onlineCount) }} {{ __('common.online_now') }}</span>
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
                            <h2>{{ __('models.your_favorites') }}</h2>
                            <span class="favorites-count">{{ $onlineFavorites->count() }} {{ __('common.online') }}</span>
                        </div>
                        <a href="{{ route('dashboard') }}" class="favorites-link">{{ __('common.view_all') }}</a>
                    </div>
                    <div class="favorites-grid">
                        @foreach($onlineFavorites as $model)
                            <x-pornguru.model-card :model="$model" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endauth

        {{-- Models from your country --}}
        @if($countryModels->isNotEmpty() && $visitorCountry)
            <section class="country-section" data-section="country">
                <div class="country-section-header">
                    <h2 class="country-section-title">
                        @if($visitorCountry['flag'])<span class="country-section-flag">{{ $visitorCountry['flag'] }}</span>@endif
                        {{ __('models.models_from_country', ['country' => $visitorCountry['name']]) }}
                    </h2>
                    <a href="{{ localized_route('countries.show', $visitorCountry['slug']) }}" class="country-section-link">
                        {{ __('common.view_all') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <div class="seo-section-grid">
                    @foreach($countryModels as $model)
                        <x-pornguru.model-card :model="$model" />
                    @endforeach
                </div>
            </section>
        @endif

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
                        {{ __('models.view_all_category', ['category' => $section['title']]) }}
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
                    <span id="preview-label">{{ __('common.play_all') }}</span>
                </button>
            </div>
        </div>

        {{-- Models Grid or Empty State --}}
        @if($models->isEmpty())
            <x-pornguru.empty-state 
                :title="__('common.no_models_found')" 
                :text="__('common.try_adjusting_filters')" 
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
                    <span>{{ __('common.loading_more_models') }}</span>
                </div>
            @endif

            {{-- Infinite Scroll Trigger --}}
            <div id="infinite-scroll-trigger"></div>

            {{-- SEO Pagination (hidden from users, visible to search engines) --}}
            <nav class="seo-pagination" aria-label="Pagination">
                @if($models->currentPage() > 1)
                    <a href="{{ $models->previousPageUrl() }}" rel="prev">{{ __('pagination.previous_page') }}</a>
                @endif
                
                <span>{{ __('pagination.page') }} {{ $models->currentPage() }} {{ __('pagination.of') }} {{ ceil($totalCount / $models->perPage()) }}</span>
                
                @if($models->hasMorePages())
                    <a href="{{ $models->nextPageUrl() }}" rel="next">{{ __('pagination.next_page') }}</a>
                @endif
            </nav>
        @endif

        {{-- Bottom SEO Content --}}
        <x-seo.content-block pageKey="home" position="bottom" class="seo-text-bottom" />
    </div>

    {{-- Favorite toggle --}}
    <script>
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
                        <h3>{{ __('common.save_your_favorites') }}</h3>
                        <p>{{ __('common.save_favorites_description') }}</p>
                        <div class="favorite-popup-actions">
                            <a href="{{ route('register') }}" class="favorite-popup-btn favorite-popup-btn-primary">{{ __('common.sign_up_free') }}</a>
                            <a href="{{ route('login') }}" class="favorite-popup-btn favorite-popup-btn-secondary">{{ __('common.log_in') }}</a>
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

    {{-- Infinite scroll config --}}
    <script>
        window.infiniteScrollConfig = {
            apiUrl: '{{ route('api.models.load') }}',
            currentPage: {{ $models->currentPage() }},
            hasMore: {{ $models->hasMorePages() ? 'true' : 'false' }},
            filters: @json($filters)
        };
    </script>

    {{-- Stream Preview Manager (shared across all listing pages) --}}
    <script src="{{ asset('js/stream-previews.js') }}"></script>
@endsection
