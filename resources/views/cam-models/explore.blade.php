<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="RATING" content="RTA-5042-1996-1400-1577-RTA" />

    <title>{{ $pageTitle }} | PornGuru</title>
    <meta name="description" content="{{ __('Explore live cam streams in a full-screen feed.') }} {{ $categoryLabels[$category] ?? $categoryLabels[null] }}. {{ __('Swipe through models, watch previews, and join live shows.') }}">

    <link rel="canonical" href="{{ $models->currentPage() > 1 ? $models->url($models->currentPage()) : localized_route('explore', $category ? ['category' => $category] : []) }}">

    @if($models->currentPage() === 1 && !empty($hreflangUrls))
    @foreach($hreflangUrls as $lang => $href)
    <link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}" />
    @endforeach
    @endif

    @if($models->currentPage() > 1)
    <link rel="prev" href="{{ $models->previousPageUrl() }}">
    @endif
    @if($models->hasMorePages())
    <link rel="next" href="{{ $models->nextPageUrl() }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PornGuru.cam">
    <meta property="og:title" content="{{ $pageTitle }} | PornGuru">
    <meta property="og:url" content="{{ localized_route('explore', $category ? ['category' => $category] : []) }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

    <link rel="icon" type="image/png" href="{{ asset('favicon-nobg.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --safe-top: env(safe-area-inset-top, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --accent: #e53e3e;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #000;
            color: #fff;
            overflow: hidden;
            height: 100dvh;
            -webkit-overflow-scrolling: touch;
        }

        /* ── Feed container ── */
        .explore-feed {
            height: 100dvh;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            scroll-behavior: auto;
        }
        .explore-feed::-webkit-scrollbar { display: none; }

        /* ── Each slide ── */
        .explore-slide {
            height: 100dvh;
            scroll-snap-align: start;
            scroll-snap-stop: always;
            position: relative;
            overflow: hidden;
            background: #111;
        }

        .explore-slide-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }
        .explore-slide-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: blur(20px) brightness(0.3);
            transform: scale(1.1);
        }

        .explore-slide video,
        .explore-slide .explore-poster {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 1;
        }
        .explore-slide .explore-poster {
            transition: opacity 0.3s;
        }
        .explore-slide.stream-active .explore-poster { opacity: 0; pointer-events: none; }

        /* ── Spinner ── */
        .explore-loading {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .explore-slide.stream-loading .explore-loading { opacity: 1; }
        .explore-slide.stream-active .explore-loading { opacity: 0; }

        .explore-spinner {
            width: 40px; height: 40px;
            border: 3px solid rgba(255,255,255,0.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Gradient overlays ── */
        .explore-slide::before {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 55%;
            background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 100%);
            z-index: 3;
            pointer-events: none;
        }

        /* ── Top bar ── */
        .explore-top {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: calc(var(--safe-top) + 10px) 16px 10px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%);
            pointer-events: auto;
        }
        .explore-back {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #fff;
            text-decoration: none;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .explore-back svg { width: 20px; height: 20px; }
        .explore-logo { font-size: 1rem; font-weight: 800; letter-spacing: -0.02em; }
        .explore-logo span { color: var(--accent); }
        .explore-counter {
            font-size: 0.6875rem;
            color: rgba(255,255,255,0.5);
            font-variant-numeric: tabular-nums;
        }

        /* ── Category tabs ── */
        .explore-categories {
            position: fixed;
            top: calc(var(--safe-top) + 44px);
            left: 0; right: 0;
            z-index: 50;
            display: flex;
            gap: 6px;
            padding: 6px 16px;
            overflow-x: auto;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .explore-categories::-webkit-scrollbar { display: none; }

        .explore-cat {
            flex-shrink: 0;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            color: rgba(255,255,255,0.7);
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.2s;
            white-space: nowrap;
        }
        .explore-cat.active {
            color: #fff;
            background: var(--accent);
            border-color: var(--accent);
        }

        /* ── Bottom overlay (info + CTA) ── */
        .explore-overlay {
            position: absolute;
            inset: 0;
            z-index: 5;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 0 16px calc(var(--safe-bottom) + 16px);
        }

        .explore-info { pointer-events: auto; padding-right: 60px; }

        .explore-model-name {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 4px;
            text-shadow: 0 1px 4px rgba(0,0,0,0.8);
        }
        .explore-model-name a { color: inherit; text-decoration: none; }

        .explore-model-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 0.8125rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 6px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
        }
        .explore-model-meta span { display: flex; align-items: center; gap: 3px; }

        .explore-stream-title {
            font-size: 0.8125rem;
            color: rgba(255,255,255,0.65);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
        }

        /* Tags row */
        .explore-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 10px;
        }
        .explore-tag {
            font-size: 0.6875rem;
            padding: 2px 8px;
            border-radius: 10px;
            background: rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }

        /* CTA */
        .explore-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 0.9375rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.15s;
            width: fit-content;
        }
        .explore-cta:active { transform: scale(0.95); }
        .explore-cta svg { width: 18px; height: 18px; }

        /* ── Detail sheet (expandable) ── */
        .explore-detail-sheet {
            margin-top: 10px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: auto;
        }
        .explore-detail-sheet.open {
            max-height: 300px;
            overflow-y: auto;
        }
        .explore-detail-content {
            padding: 12px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 12px;
            font-size: 0.8125rem;
            line-height: 1.5;
            color: rgba(255,255,255,0.85);
        }
        .explore-detail-content h3 {
            font-size: 0.875rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #fff;
        }
        .explore-detail-content p { margin-bottom: 8px; }
        .explore-detail-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }
        .explore-detail-stat {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
        }
        .explore-detail-stat strong {
            display: block;
            font-size: 0.875rem;
            color: #fff;
        }

        .explore-detail-toggle {
            display: flex;
            align-items: center;
            gap: 4px;
            background: none;
            border: none;
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 0;
            pointer-events: auto;
        }
        .explore-detail-toggle svg {
            width: 14px; height: 14px;
            transition: transform 0.3s;
        }
        .explore-detail-toggle.open svg { transform: rotate(180deg); }

        /* ── Side action buttons ── */
        .explore-actions {
            position: absolute;
            right: 10px;
            bottom: calc(var(--safe-bottom) + 80px);
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            pointer-events: auto;
        }
        .explore-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            filter: drop-shadow(0 1px 3px rgba(0,0,0,0.8));
        }
        .explore-action-btn svg { width: 26px; height: 26px; }
        .explore-action-label {
            font-size: 0.625rem;
            font-weight: 500;
            color: rgba(255,255,255,0.7);
        }

        .explore-live-badge {
            background: var(--accent);
            color: #fff;
            font-size: 0.625rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 3px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .explore-viewers { display: flex; align-items: center; gap: 4px; font-size: 0.75rem; }
        .explore-viewers svg { width: 14px; height: 14px; }
        .explore-hd { background: rgba(255,255,255,0.15); padding: 1px 6px; border-radius: 3px; font-weight: 600; font-size: 0.6875rem; }

        .explore-platform {
            position: absolute;
            top: calc(var(--safe-top) + 80px);
            left: 16px;
            z-index: 10;
            font-size: 0.625rem;
            font-weight: 600;
            background: rgba(0,0,0,0.4);
            padding: 3px 8px;
            border-radius: 4px;
            backdrop-filter: blur(8px);
            text-transform: capitalize;
        }

        /* ── Scroll hint ── */
        .explore-scroll-hint {
            position: fixed;
            bottom: calc(var(--safe-bottom) + 6px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            color: rgba(255,255,255,0.4);
            font-size: 0.625rem;
            animation: bounce-hint 2s ease-in-out infinite;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .explore-scroll-hint svg { width: 16px; height: 16px; }
        @keyframes bounce-hint {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-4px); }
        }
        .explore-feed.scrolled .explore-scroll-hint { opacity: 0; }

        /* ── SEO pagination (hidden visually, crawlable) ── */
        .explore-seo-pagination {
            position: absolute;
            width: 1px; height: 1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            white-space: nowrap;
        }

        /* ── Desktop tweaks ── */
        @media (min-width: 768px) {
            .explore-slide video,
            .explore-slide .explore-poster {
                max-width: 500px;
                margin: 0 auto;
                left: 50%;
                transform: translateX(-50%);
            }
            .explore-model-name { font-size: 1.5rem; }
            .explore-cta { padding: 14px 32px; font-size: 1rem; }
        }
    </style>

    {{-- Schema.org for SEO --}}
    <script type="application/ld+json">
    @php
        echo json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $pageTitle,
            'description' => __('Explore live cam streams in a full-screen feed.'),
            'url' => localized_route('explore', $category ? ['category' => $category] : []),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'PornGuru',
                'url' => url('/'),
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $models->total(),
                'itemListElement' => $models->map(fn ($m, $i) => [
                    '@type' => 'ListItem',
                    'position' => ($models->currentPage() - 1) * $models->perPage() + $i + 1,
                    'url' => $m->url,
                    'name' => $m->username,
                ])->values(),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    @endphp
    </script>
</head>
<body>
    {{-- Fixed top bar --}}
    <div class="explore-top">
        <a href="{{ localized_route('home') }}" class="explore-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="explore-logo">Porn<span>Guru</span></div>
        <div class="explore-counter" id="explore-counter">1 / {{ $models->count() }}</div>
    </div>

    {{-- Category tabs --}}
    <nav class="explore-categories" aria-label="{{ __('Categories') }}">
        <a href="{{ $categoryUrls['all'] }}" class="explore-cat {{ $category === null ? 'active' : '' }}">{{ __('All') }}</a>
        <a href="{{ $categoryUrls['girls'] }}" class="explore-cat {{ $category === 'girls' ? 'active' : '' }}">{{ __('Girls') }}</a>
        <a href="{{ $categoryUrls['couples'] }}" class="explore-cat {{ $category === 'couples' ? 'active' : '' }}">{{ __('Couples') }}</a>
        <a href="{{ $categoryUrls['men'] }}" class="explore-cat {{ $category === 'men' ? 'active' : '' }}">{{ __('Men') }}</a>
        <a href="{{ $categoryUrls['trans'] }}" class="explore-cat {{ $category === 'trans' ? 'active' : '' }}">{{ __('Trans') }}</a>
    </nav>

    {{-- Main scroll feed --}}
    <div class="explore-feed" id="explore-feed">
        @foreach($models as $i => $model)
        @php
            $modelTags = is_array($model->tags) ? array_slice($model->tags, 0, 6) : [];
        @endphp
        <article class="explore-slide"
             data-model-id="{{ $model->id }}"
             data-stream-url="{{ $model->best_stream_url }}"
             data-image-url="{{ $model->best_image_url }}"
             data-index="{{ $i }}">

            <div class="explore-slide-bg">
                <img src="{{ $model->best_image_url }}" alt="" loading="{{ $i < 2 ? 'eager' : 'lazy' }}" width="640" height="480">
            </div>

            <img class="explore-poster"
                 src="{{ $model->best_image_url }}"
                 alt="{{ $model->username }} {{ $model->is_online ? __('live cam') : __('cam model') }}"
                 loading="{{ $i < 2 ? 'eager' : 'lazy' }}"
                 width="640" height="480">

            <video muted playsinline preload="none"></video>

            <div class="explore-loading"><div class="explore-spinner"></div></div>

            <div class="explore-platform">{{ ucfirst($model->source_platform) }}</div>

            <div class="explore-overlay">
                <div class="explore-info">
                    <div class="explore-model-name">
                        <a href="{{ $model->url }}">{{ $model->username }}</a>
                    </div>
                    <div class="explore-model-meta">
                        @if($model->viewers_count)
                        <span class="explore-viewers">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            {{ number_format($model->viewers_count) }}
                        </span>
                        @endif
                        @if($model->age)<span>{{ $model->age }}yo</span>@endif
                        @if($model->country)<span>{{ country_flag($model->country) }} {{ $model->country }}</span>@endif
                        @if($model->is_hd)<span class="explore-hd">HD</span>@endif
                    </div>

                    @if($model->stream_title)
                    <div class="explore-stream-title">{{ $model->stream_title }}</div>
                    @endif

                    @if(!empty($modelTags))
                    <div class="explore-tags">
                        @foreach($modelTags as $tag)
                        <span class="explore-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif

                    <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="explore-cta">
                        <span class="explore-live-badge">LIVE</span>
                        {{ __('Watch on') }} {{ ucfirst($model->source_platform) }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>

                    {{-- Detail sheet toggle --}}
                    <button class="explore-detail-toggle" data-target="detail-{{ $model->id }}" aria-expanded="false">
                        <span>{{ __('More info') }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </button>

                    {{-- Expandable detail sheet (content visible to crawlers in HTML) --}}
                    <div class="explore-detail-sheet" id="detail-{{ $model->id }}">
                        <div class="explore-detail-content">
                            <div class="explore-detail-stats">
                                @if($model->age)
                                <div class="explore-detail-stat">
                                    <strong>{{ $model->age }}</strong>
                                    {{ __('Age') }}
                                </div>
                                @endif
                                @if($model->country)
                                <div class="explore-detail-stat">
                                    <strong>{{ country_flag($model->country) }} {{ $model->country }}</strong>
                                    {{ __('Country') }}
                                </div>
                                @endif
                                @if($model->viewers_count)
                                <div class="explore-detail-stat">
                                    <strong>{{ number_format($model->viewers_count) }}</strong>
                                    {{ __('Viewers') }}
                                </div>
                                @endif
                                @if($model->languages && count($model->languages))
                                <div class="explore-detail-stat">
                                    <strong>{{ implode(', ', array_slice($model->languages, 0, 3)) }}</strong>
                                    {{ __('Languages') }}
                                </div>
                                @endif
                            </div>
                            @if($model->description)
                            <p>{{ \Illuminate\Support\Str::limit($model->description, 250) }}</p>
                            @endif
                            <a href="{{ $model->url }}" style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.8125rem;">
                                {{ __('View full profile') }} →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="explore-actions">
                <a href="{{ $model->url }}" class="explore-action-btn" aria-label="{{ __('Profile') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="explore-action-label">{{ __('Profile') }}</span>
                </a>
                <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="explore-action-btn" aria-label="{{ __('Chat') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span class="explore-action-label">{{ __('Chat') }}</span>
                </a>
            </div>
        </article>
        @endforeach
    </div>

    {{-- Scroll hint --}}
    <div class="explore-scroll-hint" id="scroll-hint">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        <span>{{ __('Swipe up') }}</span>
    </div>

    {{-- SEO-only pagination links (hidden but crawlable) --}}
    <nav class="explore-seo-pagination" aria-label="Pagination">
        <h2>{{ $pageTitle }}</h2>
        @if($models->currentPage() > 1)
        <a href="{{ $models->previousPageUrl() }}">{{ __('Previous page') }}</a>
        @endif
        @for($p = 1; $p <= min($models->lastPage(), 10); $p++)
        <a href="{{ $models->url($p) }}" {{ $p === $models->currentPage() ? 'aria-current=page' : '' }}>{{ __('Page') }} {{ $p }}</a>
        @endfor
        @if($models->hasMorePages())
        <a href="{{ $models->nextPageUrl() }}">{{ __('Next page') }}</a>
        @endif
    </nav>

<script>
(function() {
    const feed = document.getElementById('explore-feed');
    const counterEl = document.getElementById('explore-counter');
    const slides = [];
    const hlsInstances = new Map();
    let currentIndex = 0;
    let isLoadingMore = false;
    let hasMore = {{ $models->hasMorePages() ? 'true' : 'false' }};
    let totalLoaded = {{ $models->count() }};
    let apiOffset = totalLoaded;
    const seenIds = new Set(@json($models->pluck('id')));
    const PRELOAD_AHEAD = 3;
    const PRELOAD_BEHIND = 2;
    const MAX_ACTIVE_HLS = 8;
    const API_URL = '{{ route("api.explore") }}';
    const CATEGORY = @json($category);

    function initSlides() {
        document.querySelectorAll('.explore-slide').forEach(s => slides.push(s));
    }

    /* ── Scroll snap detection ── */
    function onSnap() {
        const scrollTop = feed.scrollTop;
        const slideH = feed.clientHeight;
        const newIndex = Math.round(scrollTop / slideH);

        if (newIndex !== currentIndex) {
            currentIndex = Math.min(newIndex, slides.length - 1);
            updateCounter();
            manageStreams();
        }

        if (currentIndex >= slides.length - PRELOAD_AHEAD - 1) {
            loadMore();
        }

        if (currentIndex > 0) feed.classList.add('scrolled');
    }

    function updateCounter() {
        if (counterEl) counterEl.textContent = (currentIndex + 1) + ' / ' + slides.length;
    }

    /* ── Stream lifecycle management ── */
    function manageStreams() {
        for (let i = 0; i < slides.length; i++) {
            const dist = Math.abs(i - currentIndex);
            const slide = slides[i];
            const url = slide.dataset.streamUrl;

            if (i === currentIndex) {
                startStream(slide, url);
            } else if (dist <= PRELOAD_AHEAD && i > currentIndex || dist <= PRELOAD_BEHIND && i < currentIndex) {
                preloadStream(slide, url);
            } else if (dist > PRELOAD_AHEAD + 1) {
                destroyStream(slide);
            }
        }

        pauseNonCurrent();

        if (hlsInstances.size > MAX_ACTIVE_HLS) {
            pruneDistantStreams();
        }
    }

    function startStream(slide, url) {
        if (!url) return;
        const video = slide.querySelector('video');
        if (!video) return;

        const existing = hlsInstances.get(slide);
        if (existing && existing.state === 'playing') return;

        slide.classList.add('stream-loading');

        if (existing && existing.hls) {
            video.play().then(() => {
                slide.classList.remove('stream-loading');
                slide.classList.add('stream-active');
                existing.state = 'playing';
            }).catch(() => { slide.classList.remove('stream-loading'); });
            return;
        }

        if (typeof Hls !== 'undefined' && Hls.isSupported()) {
            const hls = new Hls({
                maxBufferLength: 15,
                maxMaxBufferLength: 30,
                startLevel: -1,
                capLevelToPlayerSize: true,
                manifestLoadingTimeOut: 12000,
                manifestLoadingMaxRetry: 2,
                fragLoadingTimeOut: 15000,
            });

            hls.loadSource(url);
            hls.attachMedia(video);

            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                if (slides[currentIndex] === slide) {
                    video.play().then(() => {
                        slide.classList.remove('stream-loading');
                        slide.classList.add('stream-active');
                        hlsInstances.set(slide, { hls, state: 'playing' });
                    }).catch(() => { slide.classList.remove('stream-loading'); });
                } else {
                    hlsInstances.set(slide, { hls, state: 'preloaded' });
                    slide.classList.remove('stream-loading');
                }
            });

            hls.on(Hls.Events.ERROR, (_, data) => {
                if (data.fatal) {
                    slide.classList.remove('stream-loading');
                    destroyStream(slide);
                }
            });

            hlsInstances.set(slide, { hls, state: 'loading' });

        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = url;
            video.addEventListener('loadedmetadata', () => {
                if (slides[currentIndex] === slide) {
                    video.play().then(() => {
                        slide.classList.remove('stream-loading');
                        slide.classList.add('stream-active');
                    }).catch(() => {});
                }
            }, { once: true });
            hlsInstances.set(slide, { hls: null, state: 'playing' });
        }
    }

    function preloadStream(slide, url) {
        if (!url || hlsInstances.has(slide)) return;
        const video = slide.querySelector('video');
        if (!video) return;

        if (typeof Hls !== 'undefined' && Hls.isSupported()) {
            const hls = new Hls({
                maxBufferLength: 5,
                maxMaxBufferLength: 10,
                startLevel: -1,
                capLevelToPlayerSize: true,
                autoStartLoad: true,
            });
            hls.loadSource(url);
            hls.attachMedia(video);
            hlsInstances.set(slide, { hls, state: 'preloaded' });
        }
    }

    function destroyStream(slide) {
        const entry = hlsInstances.get(slide);
        if (!entry) return;
        const video = slide.querySelector('video');
        if (entry.hls) entry.hls.destroy();
        if (video) { video.pause(); video.removeAttribute('src'); video.load(); }
        slide.classList.remove('stream-active', 'stream-loading');
        hlsInstances.delete(slide);
    }

    function pauseNonCurrent() {
        hlsInstances.forEach((entry, slide) => {
            if (slide !== slides[currentIndex]) {
                const video = slide.querySelector('video');
                if (video && !video.paused) {
                    video.pause();
                    entry.state = 'preloaded';
                }
            }
        });
    }

    function pruneDistantStreams() {
        [...hlsInstances.entries()]
            .map(([slide, data]) => ({ slide, data, dist: Math.abs(slides.indexOf(slide) - currentIndex) }))
            .sort((a, b) => b.dist - a.dist)
            .slice(MAX_ACTIVE_HLS)
            .forEach(({ slide }) => destroyStream(slide));
    }

    /* ── Infinite loading ── */
    async function loadMore() {
        if (isLoadingMore || !hasMore) return;
        isLoadingMore = true;

        try {
            const params = new URLSearchParams({ offset: apiOffset, limit: 6 });
            if (CATEGORY) params.set('category', CATEGORY);
            seenIds.forEach(id => params.append('exclude[]', id));

            const resp = await fetch(API_URL + '?' + params.toString(), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();

            if (!data.models || data.models.length === 0) {
                hasMore = false;
                return;
            }

            hasMore = data.hasMore;

            data.models.forEach(m => {
                seenIds.add(m.id);
                apiOffset++;
                const slide = createSlide(m, slides.length);
                feed.appendChild(slide);
                slides.push(slide);
            });

            updateCounter();
        } catch (e) {
            console.error('Failed to load more:', e);
        } finally {
            isLoadingMore = false;
        }
    }

    function createSlide(m, index) {
        const div = document.createElement('article');
        div.className = 'explore-slide';
        div.dataset.modelId = m.id;
        div.dataset.streamUrl = m.stream_url || '';
        div.dataset.imageUrl = m.image_url;
        div.dataset.index = index;

        const viewers = m.viewers_count ? `<span class="explore-viewers"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>${Number(m.viewers_count).toLocaleString()}</span>` : '';
        const age = m.age ? `<span>${m.age}yo</span>` : '';
        const country = m.country ? `<span>${m.flag || ''} ${esc(m.country)}</span>` : '';
        const hd = m.is_hd ? '<span class="explore-hd">HD</span>' : '';
        const title = m.stream_title ? `<div class="explore-stream-title">${esc(m.stream_title)}</div>` : '';
        const tags = (m.tags || []).map(t => `<span class="explore-tag">${esc(t)}</span>`).join('');
        const tagsHtml = tags ? `<div class="explore-tags">${tags}</div>` : '';

        const desc = m.description ? `<p>${esc(m.description)}</p>` : '';
        const langs = (m.languages || []).slice(0, 3).join(', ');
        const detailId = 'detail-dyn-' + m.id;

        div.innerHTML = `
            <div class="explore-slide-bg"><img src="${m.image_url}" alt="" loading="lazy" width="640" height="480"></div>
            <img class="explore-poster" src="${m.image_url}" alt="${esc(m.username)} live cam" loading="lazy" width="640" height="480">
            <video muted playsinline preload="none"></video>
            <div class="explore-loading"><div class="explore-spinner"></div></div>
            <div class="explore-platform">${esc(capitalize(m.platform || ''))}</div>
            <div class="explore-overlay">
                <div class="explore-info">
                    <div class="explore-model-name"><a href="${m.url}">${esc(m.username)}</a></div>
                    <div class="explore-model-meta">${viewers}${age}${country}${hd}</div>
                    ${title}
                    ${tagsHtml}
                    <a href="${m.affiliate_url}" target="_blank" rel="nofollow noopener" class="explore-cta">
                        <span class="explore-live-badge">LIVE</span>
                        Watch on ${esc(capitalize(m.platform || ''))}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <button class="explore-detail-toggle" data-target="${detailId}">
                        <span>More info</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="explore-detail-sheet" id="${detailId}">
                        <div class="explore-detail-content">
                            <div class="explore-detail-stats">
                                ${m.age ? `<div class="explore-detail-stat"><strong>${m.age}</strong>Age</div>` : ''}
                                ${m.country ? `<div class="explore-detail-stat"><strong>${m.flag || ''} ${esc(m.country)}</strong>Country</div>` : ''}
                                ${m.viewers_count ? `<div class="explore-detail-stat"><strong>${Number(m.viewers_count).toLocaleString()}</strong>Viewers</div>` : ''}
                                ${langs ? `<div class="explore-detail-stat"><strong>${esc(langs)}</strong>Languages</div>` : ''}
                            </div>
                            ${desc}
                            <a href="${m.url}" style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.8125rem;">
                                View full profile →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="explore-actions">
                <a href="${m.url}" class="explore-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="explore-action-label">Profile</span>
                </a>
                <a href="${m.affiliate_url}" target="_blank" rel="nofollow noopener" class="explore-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span class="explore-action-label">Chat</span>
                </a>
            </div>
        `;
        return div;
    }

    /* ── Detail sheet toggle ── */
    document.addEventListener('click', e => {
        const btn = e.target.closest('.explore-detail-toggle');
        if (!btn) return;
        e.preventDefault();
        const targetId = btn.dataset.target;
        const sheet = document.getElementById(targetId);
        if (!sheet) return;

        const isOpen = sheet.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        btn.setAttribute('aria-expanded', isOpen);
    });

    /* ── Utilities ── */
    const _escDiv = document.createElement('div');
    function esc(str) { _escDiv.textContent = str || ''; return _escDiv.innerHTML; }
    function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

    /* ── Scroll listener with debounce ── */
    let scrollTimer;
    feed.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(onSnap, 60);
    }, { passive: true });

    /* ── Init ── */
    initSlides();
    if (slides.length > 0) manageStreams();
})();
</script>
</body>
</html>
