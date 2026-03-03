@extends('layouts.pornguru')

@section('title'){{ $pageTitle }} | PornGuru @endsection
@section('meta_description'){{ __('Explore live cam streams in a full-screen feed.') }} {{ $categoryLabels[$category] ?? $categoryLabels[null] }}. {{ __('Swipe through models, watch previews, and join live shows.') }}@endsection
@section('canonical'){{ $models->currentPage() > 1 ? $models->url($models->currentPage()) : localized_route('explore', $category ? ['category' => $category] : []) }}@endsection

@push('seo-pagination')
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
@endpush

@push('head')
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
    <style>
        /* Explore Layout Overrides — scoped to page, not global body */
        .page-wrapper:has(.explore-layout) { overflow: hidden; height: 100dvh; }
        
        .explore-layout {
            display: flex;
            height: calc(100vh - 60px); /* Adjust based on header height, usually ~60px */
            height: 100dvh; /* Use dynamic viewport height if possible */
            width: 100%;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        .explore-main {
            flex: 1;
            position: relative;
            height: 100%;
            display: flex;
            justify-content: center;
            background: #000;
        }

        .explore-feed-container {
            width: 100%;
            max-width: 500px; /* TikTok-style width on desktop */
            height: 100%;
            position: relative;
        }
        
        @media (max-width: 768px) {
            .explore-feed-container {
                max-width: 100%;
            }
        }

        .explore-feed {
            height: 100%;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .explore-feed::-webkit-scrollbar { display: none; }

        /* Floating Categories */
        .explore-categories-float {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            z-index: 20;
            display: flex;
            justify-content: center;
            gap: 15px;
            pointer-events: none; /* Let clicks pass through */
        }
        .explore-categories-float a {
            pointer-events: auto;
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
            text-decoration: none;
            font-size: 16px;
            padding: 5px 10px;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .explore-categories-float a.active {
            color: white;
            border-bottom-color: white;
        }

        /* Slide Layout */
        .explore-slide {
            height: 100%;
            width: 100%;
            position: relative;
            scroll-snap-align: start;
            overflow: hidden;
            background: #000;
        }

        .explore-slide video, 
        .explore-slide img.explore-poster,
        .explore-slide-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        /* Overlay & Info */
        .explore-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            top: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 20%, rgba(0,0,0,0) 50%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
            pointer-events: none;
            z-index: 10;
        }

        .explore-info-container {
            pointer-events: auto;
            max-width: 80%;
            padding-bottom: 20px;
            color: white;
            text-align: left;
        }

        .explore-model-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        .explore-model-name a { color: white; text-decoration: none; }
        .explore-model-name a:hover { text-decoration: underline; }

        .explore-stream-title {
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 1.4;
            opacity: 0.9;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .explore-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 10px;
        }
        .explore-tag {
            font-size: 12px;
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 4px;
            backdrop-filter: blur(4px);
        }

        /* Right Actions */
        .explore-right-actions {
            position: absolute;
            right: 10px;
            bottom: 100px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
            pointer-events: auto;
            z-index: 20;
        }

        .action-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
            cursor: pointer;
            transition: transform 0.2s, background 0.2s;
            text-decoration: none;
            position: relative;
        }
        .action-btn:hover { background: rgba(0,0,0,0.6); }
        .action-btn:active { transform: scale(0.95); }
        .action-btn svg { width: 24px; height: 24px; }
        
        .action-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid white;
            overflow: hidden;
            padding: 0;
        }
        .action-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .action-plus {
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            background: #ef4444;
            color: white;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .action-label {
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.8);
            text-align: center;
        }

        /* Sidepanel */
        .explore-sidepanel {
            width: 350px;
            background: #0a0a0a;
            border-left: 1px solid #27272a;
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidepanel-header {
            padding: 15px 20px;
            border-bottom: 1px solid #27272a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f0f0f;
        }
        .sidepanel-header h3 { margin: 0; font-size: 16px; font-weight: 600; color: white; }
        
        .sidepanel-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .sidepanel-close-btn {
            background: none;
            border: none;
            color: #a1a1aa;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        .sidepanel-close-btn:hover { color: white; }

        /* Mobile Sidepanel Behavior */
        @media (max-width: 991px) {
            .explore-sidepanel {
                position: fixed;
                top: 0;
                right: 0;
                bottom: 0;
                width: 85%;
                max-width: 350px;
                transform: translateX(100%);
                box-shadow: -5px 0 30px rgba(0,0,0,0.8);
            }
            .explore-sidepanel.open {
                transform: translateX(0);
            }
            .sidepanel-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.6);
                z-index: 40;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s;
            }
            .sidepanel-overlay.active {
                opacity: 1;
                pointer-events: auto;
            }
        }
        @media (min-width: 992px) {
            .sidepanel-close-btn { display: none; }
            .sidepanel-overlay { display: none; }
        }

        /* Detail Stats in Sidepanel */
        .detail-stat-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #27272a;
            font-size: 14px;
        }
        .detail-stat-label { color: #a1a1aa; }
        .detail-stat-value { color: white; font-weight: 600; text-align: right; }
        .detail-desc { margin-top: 20px; color: #d4d4d8; font-size: 14px; line-height: 1.6; }
        .detail-cta {
            display: block;
            width: 100%;
            padding: 12px;
            background: #ef4444; /* Primary color */
            color: white;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            text-decoration: none;
        }
        .detail-cta:hover { background: #dc2626; }

        .explore-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 5;
        }
        .explore-spinner {
            width: 40px; height: 40px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .explore-slide.stream-active .explore-loading { display: none; }
    </style>
@endpush

@section('content')
<div class="explore-layout" id="explore-layout">
    <div class="explore-main">
        <div class="explore-feed-container">
            {{-- Floating Categories --}}
            <nav class="explore-categories-float" aria-label="{{ __('Categories') }}">
                <a href="{{ $categoryUrls['all'] }}" class="{{ $category === null ? 'active' : '' }}">{{ __('All') }}</a>
                <a href="{{ $categoryUrls['girls'] }}" class="{{ $category === 'girls' ? 'active' : '' }}">{{ __('Girls') }}</a>
                <a href="{{ $categoryUrls['couples'] }}" class="{{ $category === 'couples' ? 'active' : '' }}">{{ __('Couples') }}</a>
                <a href="{{ $categoryUrls['men'] }}" class="{{ $category === 'men' ? 'active' : '' }}">{{ __('Men') }}</a>
                <a href="{{ $categoryUrls['trans'] }}" class="{{ $category === 'trans' ? 'active' : '' }}">{{ __('Trans') }}</a>
            </nav>

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
                    
                    <script type="application/json" class="slide-data">
                    @php echo json_encode([
                        'username' => $model->username,
                        'age' => $model->age,
                        'country' => $model->country,
                        'flag' => country_flag($model->country),
                        'viewers' => number_format($model->viewers_count),
                        'languages' => implode(', ', array_slice($model->languages ?? [], 0, 3)),
                        'description' => \Illuminate\Support\Str::limit($model->description, 300),
                        'url' => $model->url,
                        'affiliate_url' => $model->affiliate_url,
                        'platform' => ucfirst($model->source_platform),
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) @endphp
                    </script>

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

                    {{-- Right Actions --}}
                    <div class="explore-right-actions">
                        <a href="{{ $model->url }}" class="action-btn action-avatar">
                            <img src="{{ $model->best_image_url }}" alt="{{ $model->username }}">
                            <div class="action-plus">+</div>
                        </a>
                        
                        <button class="action-btn" onclick="toggleSidepanel(true)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="action-label">Info</span>
                        </button>

                        <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            <span class="action-label">Chat</span>
                        </a>
                        
                        <div class="action-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                            <span class="action-label">Share</span>
                        </div>
                    </div>

                    {{-- Bottom Info --}}
                    <div class="explore-overlay">
                        <div class="explore-info-container">
                            <div class="explore-model-name">
                                <a href="{{ $model->url }}">{{ $model->username }}</a>
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
                        </div>
                    </div>

                    {{-- Crawlable model details (hidden visually, readable by search engines) --}}
                    <div class="explore-seo-pagination">
                        <p>{{ $model->username }}@if($model->age), {{ $model->age }} {{ __('years old') }}@endif @if($model->country) {{ __('from') }} {{ $model->country }}@endif.</p>
                        @if($model->description)<p>{{ \Illuminate\Support\Str::limit($model->description, 200) }}</p>@endif
                        <a href="{{ $model->url }}">{{ __('View') }} {{ $model->username }} {{ __('profile') }}</a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sidepanel --}}
    <div class="sidepanel-overlay" id="sidepanel-overlay" onclick="toggleSidepanel(false)"></div>
    <aside class="explore-sidepanel" id="explore-sidepanel">
        <div class="sidepanel-header">
            <h3>Model Details</h3>
            <button class="sidepanel-close-btn" onclick="toggleSidepanel(false)">&times;</button>
        </div>
        <div class="sidepanel-body" id="sidepanel-content">
            {{-- Populated by JS --}}
            <div style="text-align:center; color: #666; margin-top: 50px;">Loading...</div>
        </div>
    </aside>
</div>

{{-- SEO Pagination (visually hidden but crawlable) --}}
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
    const sidepanel = document.getElementById('explore-sidepanel');
    const sidepanelContent = document.getElementById('sidepanel-content');
    const overlay = document.getElementById('sidepanel-overlay');
    const slides = [];
    const hlsInstances = new Map();
    let currentIndex = 0;
    let isLoadingMore = false;
    let hasMore = {{ $models->hasMorePages() ? 'true' : 'false' }};
    let apiOffset = {{ $models->count() }};
    const seenIds = new Set(@json($models->pluck('id')));
    const PRELOAD_AHEAD = 3;
    const PRELOAD_BEHIND = 2;
    const MAX_ACTIVE_HLS = 8;
    const API_URL = '{{ route("api.explore") }}';
    const CATEGORY = @json($category);

    // Global toggle function
    window.toggleSidepanel = function(show) {
        if (show === undefined) show = !sidepanel.classList.contains('open');
        sidepanel.classList.toggle('open', show);
        overlay.classList.toggle('active', show);
    };

    function initSlides() {
        slides.length = 0;
        document.querySelectorAll('.explore-slide').forEach(s => slides.push(s));
    }

    function updateSidepanelContent(index) {
        const slide = slides[index];
        if (!slide) return;
        
        const script = slide.querySelector('.slide-data');
        if (!script) return;

        try {
            const data = JSON.parse(script.textContent);
            const html = `
                <div class="detail-stat-row">
                    <span class="detail-stat-label">Name</span>
                    <span class="detail-stat-value">${data.username}</span>
                </div>
                ${data.age ? `
                <div class="detail-stat-row">
                    <span class="detail-stat-label">Age</span>
                    <span class="detail-stat-value">${data.age}</span>
                </div>` : ''}
                ${data.country ? `
                <div class="detail-stat-row">
                    <span class="detail-stat-label">Country</span>
                    <span class="detail-stat-value">${data.flag} ${data.country}</span>
                </div>` : ''}
                ${data.viewers ? `
                <div class="detail-stat-row">
                    <span class="detail-stat-label">Viewers</span>
                    <span class="detail-stat-value">${data.viewers}</span>
                </div>` : ''}
                ${data.languages ? `
                <div class="detail-stat-row">
                    <span class="detail-stat-label">Languages</span>
                    <span class="detail-stat-value">${data.languages}</span>
                </div>` : ''}
                
                <div class="detail-desc">
                    ${data.description || 'No description available.'}
                </div>

                <a href="${data.affiliate_url}" target="_blank" rel="nofollow noopener" class="detail-cta">
                    Chat on ${data.platform}
                </a>
                <a href="${data.url}" class="detail-cta" style="background: #27272a; margin-top: 10px;">
                    View Full Profile
                </a>
            `;
            sidepanelContent.innerHTML = html;
        } catch (e) {
            console.error('Error parsing slide data', e);
        }
    }

    function onSnap() {
        const scrollTop = feed.scrollTop;
        const slideH = feed.clientHeight;
        const newIndex = Math.round(scrollTop / slideH);

        if (newIndex !== currentIndex) {
            currentIndex = Math.min(newIndex, slides.length - 1);
            updateSidepanelContent(currentIndex);
            manageStreams();
        }

        if (currentIndex >= slides.length - PRELOAD_AHEAD - 1) {
            loadMore();
        }
    }

    function manageStreams() {
        for (let i = 0; i < slides.length; i++) {
            const dist = Math.abs(i - currentIndex);
            const slide = slides[i];
            const url = slide.dataset.streamUrl;

            if (i === currentIndex) {
                startStream(slide, url);
            } else if ((dist <= PRELOAD_AHEAD && i > currentIndex) || (dist <= PRELOAD_BEHIND && i < currentIndex)) {
                preloadStream(slide, url);
            } else if (dist > PRELOAD_AHEAD + 1) {
                destroyStream(slide);
            }
        }
        pauseNonCurrent();
        if (hlsInstances.size > MAX_ACTIVE_HLS) pruneDistantStreams();
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
                maxBufferLength: 15, maxMaxBufferLength: 30,
                startLevel: -1, capLevelToPlayerSize: true,
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
                if (data.fatal) { slide.classList.remove('stream-loading'); destroyStream(slide); }
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
            const hls = new Hls({ maxBufferLength: 5, maxMaxBufferLength: 10, startLevel: -1, capLevelToPlayerSize: true, autoStartLoad: true });
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
                if (video && !video.paused) { video.pause(); entry.state = 'preloaded'; }
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

    async function loadMore() {
        if (isLoadingMore || !hasMore) return;
        isLoadingMore = true;
        try {
            const params = new URLSearchParams({ offset: apiOffset, limit: 6 });
            if (CATEGORY) params.set('category', CATEGORY);
            seenIds.forEach(id => params.append('exclude[]', id));
            const resp = await fetch(API_URL + '?' + params.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await resp.json();
            if (!data.models || data.models.length === 0) { hasMore = false; return; }
            hasMore = data.hasMore;
            data.models.forEach(m => {
                seenIds.add(m.id);
                apiOffset++;
                const slide = createSlide(m, slides.length);
                feed.appendChild(slide);
                slides.push(slide);
            });
        } catch (e) { console.error('Failed to load more:', e); }
        finally { isLoadingMore = false; }
    }

    function createSlide(m, index) {
        const div = document.createElement('article');
        div.className = 'explore-slide';
        div.dataset.modelId = m.id;
        div.dataset.streamUrl = m.stream_url || '';
        div.dataset.imageUrl = m.image_url;
        div.dataset.index = index;

        // JSON Data for Sidepanel
        const jsonData = {
            username: m.username,
            age: m.age || '',
            country: m.country || '',
            flag: m.flag || '',
            viewers: m.viewers_count ? Number(m.viewers_count).toLocaleString() : '',
            languages: (m.languages || []).slice(0, 3).join(', '),
            description: m.description || '',
            url: m.url,
            affiliate_url: m.affiliate_url,
            platform: m.platform ? m.platform.charAt(0).toUpperCase() + m.platform.slice(1) : ''
        };
        
        const tags = (m.tags || []).map(t => `<span class="explore-tag">${esc(t)}</span>`).join('');
        const tagsHtml = tags ? `<div class="explore-tags">${tags}</div>` : '';

        div.innerHTML = `
            <script type="application/json" class="slide-data">${JSON.stringify(jsonData)}<\/script>
            <div class="explore-slide-bg"><img src="${m.image_url}" alt="" loading="lazy"></div>
            <img class="explore-poster" src="${m.image_url}" alt="${esc(m.username)}" loading="lazy">
            <video muted playsinline preload="none"></video>
            <div class="explore-loading"><div class="explore-spinner"></div></div>
            
            <div class="explore-right-actions">
                <a href="${m.url}" class="action-btn action-avatar">
                    <img src="${m.image_url}" alt="${esc(m.username)}">
                    <div class="action-plus">+</div>
                </a>
                <button class="action-btn" onclick="toggleSidepanel(true)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="action-label">Info</span>
                </button>
                <a href="${m.affiliate_url}" target="_blank" rel="nofollow noopener" class="action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span class="action-label">Chat</span>
                </a>
                <div class="action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                    <span class="action-label">Share</span>
                </div>
            </div>

            <div class="explore-overlay">
                <div class="explore-info-container">
                    <div class="explore-model-name"><a href="${m.url}">${esc(m.username)}</a></div>
                    ${m.stream_title ? `<div class="explore-stream-title">${esc(m.stream_title)}</div>` : ''}
                    ${tagsHtml}
                </div>
            </div>
        `;
        return div;
    }

    const _escDiv = document.createElement('div');
    function esc(str) { _escDiv.textContent = str || ''; return _escDiv.innerHTML; }

    // Swipe Handling
    let touchStartX = 0;
    let touchEndX = 0;
    
    feed.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    }, {passive: true});
    
    feed.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, {passive: true});

    function handleSwipe() {
        // Swipe Left (Drag right to left) -> Open Sidepanel
        if (touchStartX - touchEndX > 70) {
            if (window.innerWidth < 992) toggleSidepanel(true);
        }
        // Swipe Right -> Close Sidepanel (handled by overlay click usually, but can add here)
    }

    let scrollTimer;
    feed.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(onSnap, 60);
    }, { passive: true });

    initSlides();
    if (slides.length > 0) {
        updateSidepanelContent(0);
        manageStreams();
    }
})();
</script>
@endsection
