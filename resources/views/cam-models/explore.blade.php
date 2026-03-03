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
@endpush

@section('content')
<div class="explore-page">
    <div class="explore-phone-frame">
        {{-- Category tabs --}}
        <nav class="explore-categories" aria-label="{{ __('Categories') }}">
            <a href="{{ $categoryUrls['all'] }}" class="explore-cat {{ $category === null ? 'active' : '' }}">{{ __('All') }}</a>
            <a href="{{ $categoryUrls['girls'] }}" class="explore-cat {{ $category === 'girls' ? 'active' : '' }}">{{ __('Girls') }}</a>
            <a href="{{ $categoryUrls['couples'] }}" class="explore-cat {{ $category === 'couples' ? 'active' : '' }}">{{ __('Couples') }}</a>
            <a href="{{ $categoryUrls['men'] }}" class="explore-cat {{ $category === 'men' ? 'active' : '' }}">{{ __('Men') }}</a>
            <a href="{{ $categoryUrls['trans'] }}" class="explore-cat {{ $category === 'trans' ? 'active' : '' }}">{{ __('Trans') }}</a>
        </nav>

        {{-- Counter --}}
        <div class="explore-counter" id="explore-counter">1 / {{ $models->count() }}</div>

        {{-- Scroll feed --}}
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

                        <button class="explore-detail-toggle" data-target="detail-{{ $model->id }}" aria-expanded="false">
                            <span>{{ __('More info') }}</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </button>

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
                                <a href="{{ $model->url }}" class="explore-detail-link">
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
    </div>
</div>

{{-- SEO-only pagination --}}
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
                manifestLoadingTimeOut: 12000, manifestLoadingMaxRetry: 2,
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
            updateCounter();
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
                    ${title}${tagsHtml}
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
                            <a href="${m.url}" class="explore-detail-link">View full profile →</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="explore-actions">
                <a href="${m.url}" class="explore-action-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span class="explore-action-label">Profile</span></a>
                <a href="${m.affiliate_url}" target="_blank" rel="nofollow noopener" class="explore-action-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg><span class="explore-action-label">Chat</span></a>
            </div>
        `;
        return div;
    }

    document.addEventListener('click', e => {
        const btn = e.target.closest('.explore-detail-toggle');
        if (!btn) return;
        e.preventDefault();
        const sheet = document.getElementById(btn.dataset.target);
        if (!sheet) return;
        const isOpen = sheet.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        btn.setAttribute('aria-expanded', isOpen);
    });

    const _escDiv = document.createElement('div');
    function esc(str) { _escDiv.textContent = str || ''; return _escDiv.innerHTML; }
    function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

    let scrollTimer;
    feed.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(onSnap, 60);
    }, { passive: true });

    initSlides();
    if (slides.length > 0) manageStreams();
})();
</script>
@endsection
