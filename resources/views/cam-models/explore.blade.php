<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="RATING" content="RTA-5042-1996-1400-1577-RTA" />
    <meta name="robots" content="noindex">
    <title>{{ __('Explore Live Cams') }} | PornGuru</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-nobg.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --safe-top: env(safe-area-inset-top, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #000;
            color: #fff;
            overflow: hidden;
            height: 100dvh;
            -webkit-overflow-scrolling: touch;
        }

        .explore-feed {
            height: 100dvh;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .explore-feed::-webkit-scrollbar { display: none; }

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
            object-fit: contain;
            transition: opacity 0.3s;
        }
        .explore-slide.stream-active .explore-poster { opacity: 0; pointer-events: none; }

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

        /* Overlays */
        .explore-overlay {
            position: absolute;
            inset: 0;
            z-index: 5;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: calc(var(--safe-top) + 12px) 16px calc(var(--safe-bottom) + 20px);
        }

        /* Top bar */
        .explore-top {
            position: absolute;
            top: 0;
            left: 0; right: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: calc(var(--safe-top) + 12px) 16px 12px;
            pointer-events: auto;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, transparent 100%);
        }
        .explore-back {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #fff;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .explore-back svg { width: 20px; height: 20px; }
        .explore-logo {
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .explore-logo span { color: #e53e3e; }
        .explore-counter {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            font-variant-numeric: tabular-nums;
        }

        /* Bottom info */
        .explore-info {
            pointer-events: auto;
            padding-right: 64px;
        }
        .explore-model-name {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 4px;
            text-shadow: 0 1px 4px rgba(0,0,0,0.8);
        }
        .explore-model-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 0.8125rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 8px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
        }
        .explore-model-meta span { display: flex; align-items: center; gap: 3px; }
        .explore-stream-title {
            font-size: 0.8125rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
        }

        /* CTA button */
        .explore-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #e53e3e;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 0.9375rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.15s, background 0.15s;
            width: fit-content;
        }
        .explore-cta:active { transform: scale(0.95); }
        .explore-cta svg { width: 18px; height: 18px; }

        /* Side actions */
        .explore-actions {
            position: absolute;
            right: 12px;
            bottom: calc(var(--safe-bottom) + 80px);
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            pointer-events: auto;
        }
        .explore-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            filter: drop-shadow(0 1px 3px rgba(0,0,0,0.8));
        }
        .explore-action-btn svg { width: 28px; height: 28px; }
        .explore-action-label {
            font-size: 0.6875rem;
            font-weight: 500;
            color: rgba(255,255,255,0.8);
        }

        .explore-live-badge {
            background: #e53e3e;
            color: #fff;
            font-size: 0.6875rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .explore-viewers {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
        }
        .explore-viewers svg { width: 14px; height: 14px; }

        /* Gradient overlays */
        .explore-slide::before {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
            z-index: 3;
            pointer-events: none;
        }

        /* Scroll hint */
        .explore-scroll-hint {
            position: absolute;
            bottom: calc(var(--safe-bottom) + 6px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            color: rgba(255,255,255,0.5);
            font-size: 0.6875rem;
            animation: bounce-hint 2s ease-in-out infinite;
            pointer-events: none;
        }
        .explore-scroll-hint svg { width: 16px; height: 16px; }
        @keyframes bounce-hint {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-4px); }
        }
        .explore-feed.scrolled .explore-scroll-hint { display: none; }

        /* HD badge */
        .explore-hd { background: rgba(255,255,255,0.15); padding: 1px 6px; border-radius: 3px; font-weight: 600; font-size: 0.6875rem; }

        /* Platform badge */
        .explore-platform {
            position: absolute;
            top: calc(var(--safe-top) + 56px);
            left: 16px;
            z-index: 10;
            font-size: 0.6875rem;
            font-weight: 600;
            background: rgba(0,0,0,0.4);
            padding: 3px 8px;
            border-radius: 4px;
            backdrop-filter: blur(8px);
            text-transform: capitalize;
        }

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
</head>
<body>
    <div class="explore-feed" id="explore-feed">
        @foreach($models as $i => $model)
        <div class="explore-slide"
             data-model-id="{{ $model->id }}"
             data-stream-url="{{ $model->best_stream_url }}"
             data-image-url="{{ $model->best_image_url }}"
             data-index="{{ $i }}">

            <div class="explore-slide-bg">
                <img src="{{ $model->best_image_url }}" alt="" loading="{{ $i < 2 ? 'eager' : 'lazy' }}">
            </div>

            <img class="explore-poster"
                 src="{{ $model->best_image_url }}"
                 alt="{{ $model->username }}"
                 loading="{{ $i < 2 ? 'eager' : 'lazy' }}">

            <video muted playsinline preload="none"></video>

            <div class="explore-loading"><div class="explore-spinner"></div></div>

            <div class="explore-platform">{{ $model->source_platform }}</div>

            <div class="explore-overlay">
                <div class="explore-info">
                    <div class="explore-model-name">{{ $model->username }}</div>
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
                    <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="explore-cta">
                        <span class="explore-live-badge">LIVE</span>
                        {{ __('Watch on') }} {{ ucfirst($model->source_platform) }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </div>
            </div>

            <div class="explore-actions">
                <a href="{{ $model->url }}" class="explore-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="explore-action-label">{{ __('Profile') }}</span>
                </a>
                <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="explore-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span class="explore-action-label">{{ __('Chat') }}</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="explore-top">
        <a href="{{ localized_route('home') }}" class="explore-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="explore-logo">Porn<span>Guru</span></div>
        <div class="explore-counter" id="explore-counter">1 / {{ count($models) }}</div>
    </div>

    <div class="explore-scroll-hint" id="scroll-hint">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        <span>{{ __('Swipe up') }}</span>
    </div>

<script>
(function() {
    const feed = document.getElementById('explore-feed');
    const counterEl = document.getElementById('explore-counter');
    const slides = [];
    const hlsInstances = new Map();
    let currentIndex = 0;
    let isLoadingMore = false;
    let hasMore = true;
    let totalLoaded = {{ count($models) }};
    const seenIds = new Set(@json($models->pluck('id')));
    const PRELOAD_AHEAD = 3;
    const PRELOAD_BEHIND = 2;
    const MAX_ACTIVE_HLS = 8;
    const API_URL = '{{ route("api.explore") }}';

    function initSlides() {
        document.querySelectorAll('.explore-slide').forEach(s => slides.push(s));
    }

    function onSnap() {
        const scrollTop = feed.scrollTop;
        const slideH = feed.clientHeight;
        const newIndex = Math.round(scrollTop / slideH);

        if (newIndex === currentIndex) return;
        currentIndex = newIndex;

        updateCounter();
        manageStreams();

        if (currentIndex >= slides.length - PRELOAD_AHEAD) {
            loadMore();
        }

        if (currentIndex > 0) feed.classList.add('scrolled');
    }

    function updateCounter() {
        if (counterEl) counterEl.textContent = (currentIndex + 1) + ' / ' + slides.length;
    }

    function manageStreams() {
        for (let i = 0; i < slides.length; i++) {
            const dist = Math.abs(i - currentIndex);
            const slide = slides[i];
            const url = slide.dataset.streamUrl;

            if (i === currentIndex) {
                startStream(slide, url);
            } else if (dist <= PRELOAD_AHEAD || dist <= PRELOAD_BEHIND) {
                preloadStream(slide, url);
            } else {
                destroyStream(slide);
            }
        }

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
            }).catch(() => {});
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
                fragLoadingTimeOut: 15000
            });

            hls.loadSource(url);
            hls.attachMedia(video);

            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                if (slides[currentIndex] === slide) {
                    video.play().then(() => {
                        slide.classList.remove('stream-loading');
                        slide.classList.add('stream-active');
                        hlsInstances.set(slide, { hls, state: 'playing' });
                    }).catch(() => {
                        slide.classList.remove('stream-loading');
                    });
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

    function pruneDistantStreams() {
        const entries = [...hlsInstances.entries()];
        entries
            .map(([slide, data]) => ({ slide, data, dist: Math.abs(slides.indexOf(slide) - currentIndex) }))
            .sort((a, b) => b.dist - a.dist)
            .slice(MAX_ACTIVE_HLS)
            .forEach(({ slide }) => destroyStream(slide));
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

    async function loadMore() {
        if (isLoadingMore || !hasMore) return;
        isLoadingMore = true;

        try {
            const params = new URLSearchParams({
                offset: totalLoaded,
                limit: 6,
            });
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
                const slide = createSlide(m, slides.length);
                feed.appendChild(slide);
                slides.push(slide);
                totalLoaded++;
            });

            updateCounter();

        } catch (e) {
            console.error('Failed to load more:', e);
        } finally {
            isLoadingMore = false;
        }
    }

    function createSlide(m, index) {
        const div = document.createElement('div');
        div.className = 'explore-slide';
        div.dataset.modelId = m.id;
        div.dataset.streamUrl = m.stream_url || '';
        div.dataset.imageUrl = m.image_url;
        div.dataset.index = index;

        const viewers = m.viewers_count ? `<span class="explore-viewers"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>${Number(m.viewers_count).toLocaleString()}</span>` : '';
        const age = m.age ? `<span>${m.age}yo</span>` : '';
        const country = m.country ? `<span>${m.flag || ''} ${m.country}</span>` : '';
        const hd = m.is_hd ? '<span class="explore-hd">HD</span>' : '';
        const title = m.stream_title ? `<div class="explore-stream-title">${escapeHtml(m.stream_title)}</div>` : '';

        div.innerHTML = `
            <div class="explore-slide-bg"><img src="${m.image_url}" alt="" loading="lazy"></div>
            <img class="explore-poster" src="${m.image_url}" alt="${escapeHtml(m.username)}" loading="lazy">
            <video muted playsinline preload="none"></video>
            <div class="explore-loading"><div class="explore-spinner"></div></div>
            <div class="explore-platform">${m.platform || ''}</div>
            <div class="explore-overlay">
                <div class="explore-info">
                    <div class="explore-model-name">${escapeHtml(m.username)}</div>
                    <div class="explore-model-meta">${viewers}${age}${country}${hd}</div>
                    ${title}
                    <a href="${m.affiliate_url}" target="_blank" rel="nofollow noopener" class="explore-cta">
                        <span class="explore-live-badge">LIVE</span>
                        Watch on ${capitalize(m.platform || '')}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
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

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

    let scrollTimer;
    feed.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        pauseNonCurrent();
        scrollTimer = setTimeout(onSnap, 80);
    }, { passive: true });

    initSlides();
    manageStreams();
})();
</script>
</body>
</html>
