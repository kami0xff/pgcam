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
        .page-wrapper:has(.xpl) { overflow: hidden; height: 100dvh; }
        .site-header, .niche-bar { position: relative; z-index: 100; }
        .site-footer { display: none; }

        .xpl {
            display: flex;
            height: calc(100dvh - 110px);
            background: #000;
            overflow: hidden;
        }
        .xpl-center {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px;
        }
        .xpl-phone {
            width: 100%;
            max-width: 400px;
            height: 100%;
            max-height: 800px;
            border-radius: 24px;
            overflow: hidden;
            position: relative;
            background: #111;
            box-shadow: 0 0 60px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.06);
        }
        .xpl-feed {
            height: 100%;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .xpl-feed::-webkit-scrollbar { display: none; }

        /* ── Slide ── */
        .xpl-slide {
            height: 100%;
            width: 100%;
            position: relative;
            scroll-snap-align: start;
            overflow: hidden;
            background: #000;
        }
        .xpl-slide video,
        .xpl-slide .xpl-poster {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0; left: 0;
        }
        .xpl-slide .xpl-poster { z-index: 1; }
        .xpl-slide video { z-index: 2; }
        .xpl-slide.stream-active .xpl-poster { opacity: 0; }

        .xpl-spinner-wrap {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 3; pointer-events: none;
        }
        .xpl-spinner {
            width: 36px; height: 36px;
            border: 3px solid rgba(255,255,255,.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: xpl-spin .8s linear infinite;
        }
        @keyframes xpl-spin { to { transform: rotate(360deg); } }
        .xpl-slide.stream-active .xpl-spinner-wrap { display: none; }

        /* ── Gradient overlay ── */
        .xpl-slide::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,.85) 0%, rgba(0,0,0,.3) 25%, transparent 55%);
            z-index: 4;
            pointer-events: none;
        }

        /* ── Bottom info ── */
        .xpl-info {
            position: absolute; bottom: 16px; left: 16px; right: 70px;
            z-index: 10; color: #fff;
        }
        .xpl-name {
            font-size: 18px; font-weight: 700;
            margin-bottom: 4px;
            text-shadow: 0 1px 4px rgba(0,0,0,.6);
        }
        .xpl-name a { color: #fff; text-decoration: none; }
        .xpl-title {
            font-size: 13px; opacity: .85;
            line-height: 1.4;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden;
            text-shadow: 0 1px 3px rgba(0,0,0,.5);
            margin-bottom: 8px;
        }
        .xpl-goal {
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(6px);
            border-radius: 20px;
            padding: 6px 12px;
            font-size: 12px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
            max-width: 280px;
        }
        .xpl-goal-bar {
            flex: 1; height: 4px;
            background: rgba(255,255,255,.2);
            border-radius: 2px; overflow: hidden;
        }
        .xpl-goal-fill {
            height: 100%;
            background: linear-gradient(90deg, #22c55e, #a3e635);
            border-radius: 2px;
            transition: width .4s ease;
        }
        .xpl-goal-pct { color: #a3e635; font-weight: 700; }
        .xpl-tags-row {
            display: flex; flex-wrap: wrap; gap: 4px;
            margin-top: 6px;
        }
        .xpl-tag {
            font-size: 11px;
            background: rgba(255,255,255,.15);
            padding: 2px 8px;
            border-radius: 4px;
            backdrop-filter: blur(4px);
        }

        /* ── Right actions (TikTok style) ── */
        .xpl-actions {
            position: absolute; right: 10px; bottom: 20px;
            display: flex; flex-direction: column;
            align-items: center; gap: 18px;
            z-index: 10;
        }
        .xpl-avatar {
            width: 50px; height: 50px;
            border-radius: 50%;
            border: 3px solid #ef4444;
            overflow: hidden;
            position: relative;
            display: block;
            animation: xpl-live-ring 2s ease-in-out infinite;
            box-shadow: 0 0 12px rgba(239,68,68,.5);
        }
        @keyframes xpl-live-ring {
            0%, 100% { box-shadow: 0 0 8px rgba(239,68,68,.4); }
            50% { box-shadow: 0 0 18px rgba(239,68,68,.7); }
        }
        .xpl-avatar img {
            width: 100%; height: 100%;
            object-fit: cover; display: block;
        }
        .xpl-avatar-live {
            position: absolute; bottom: -4px; left: 50%;
            transform: translateX(-50%);
            background: #ef4444;
            color: #fff;
            font-size: 8px; font-weight: 800;
            letter-spacing: .5px;
            padding: 1px 5px;
            border-radius: 3px;
            line-height: 1.3;
            text-transform: uppercase;
        }
        .xpl-btn {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,.1);
            backdrop-filter: blur(8px);
            display: flex; align-items: center; justify-content: center;
            color: #fff; border: none; cursor: pointer;
            transition: transform .15s, background .15s;
            text-decoration: none;
        }
        .xpl-btn:active { transform: scale(.9); }
        .xpl-btn:hover { background: rgba(255,255,255,.2); }
        .xpl-btn svg { width: 22px; height: 22px; }
        .xpl-btn.is-fav { color: #ef4444; }
        .xpl-btn.is-fav svg { fill: #ef4444; }
        .xpl-btn-label {
            font-size: 11px; font-weight: 600;
            margin-top: 2px; text-align: center;
            text-shadow: 0 1px 2px rgba(0,0,0,.8);
            color: #fff;
        }

        /* ── Sidepanel (desktop: always shown, mobile: bottom sheet) ── */
        .xpl-panel {
            width: 360px;
            background: #0a0a0a;
            border-left: 1px solid rgba(255,255,255,.06);
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        .xpl-panel-scroll {
            flex: 1; overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.1) transparent;
        }

        /* Panel: Model header */
        .xpl-ph {
            padding: 20px;
            display: flex; align-items: center; gap: 14px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .xpl-ph-avatar {
            width: 56px; height: 56px;
            border-radius: 50%; overflow: hidden;
            border: 2px solid #ef4444; flex-shrink: 0;
        }
        .xpl-ph-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .xpl-ph-info { min-width: 0; }
        .xpl-ph-name {
            font-size: 16px; font-weight: 700; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .xpl-ph-meta {
            font-size: 13px; color: #a1a1aa;
            display: flex; align-items: center; gap: 8px;
            margin-top: 2px;
        }
        .xpl-ph-platform {
            font-size: 11px; font-weight: 600;
            background: rgba(255,255,255,.08);
            padding: 2px 8px; border-radius: 4px;
            color: #d4d4d8;
        }
        .xpl-ph-live {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 12px; font-weight: 700; color: #ef4444;
        }
        .xpl-ph-live-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #ef4444;
            animation: xpl-dot-pulse 1.5s ease-in-out infinite;
        }
        @keyframes xpl-dot-pulse {
            0%, 100% { opacity: 1; } 50% { opacity: .3; }
        }

        /* Panel: Stats grid */
        .xpl-stats {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 1px; background: rgba(255,255,255,.04);
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .xpl-stat {
            padding: 14px 16px;
            background: #0a0a0a;
        }
        .xpl-stat-label {
            font-size: 11px; text-transform: uppercase;
            letter-spacing: .5px;
            color: #71717a; font-weight: 600;
        }
        .xpl-stat-val {
            font-size: 15px; font-weight: 700;
            color: #fff; margin-top: 2px;
        }

        /* Panel: Goal */
        .xpl-pgoal {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .xpl-pgoal-label {
            font-size: 12px; font-weight: 600;
            color: #a1a1aa; margin-bottom: 8px;
            display: flex; align-items: center; gap: 6px;
        }
        .xpl-pgoal-msg {
            font-size: 14px; color: #d4d4d8;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        .xpl-pgoal-track {
            height: 8px; background: #27272a;
            border-radius: 4px; overflow: hidden;
        }
        .xpl-pgoal-fill {
            height: 100%;
            background: linear-gradient(90deg, #22c55e, #a3e635);
            border-radius: 4px;
            transition: width .4s;
        }
        .xpl-pgoal-pct {
            font-size: 13px; font-weight: 700;
            color: #a3e635; margin-top: 6px;
            text-align: right;
        }

        /* Panel: Tags */
        .xpl-ptags {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .xpl-ptags-label {
            font-size: 12px; font-weight: 600;
            color: #a1a1aa; margin-bottom: 10px;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .xpl-ptags-list {
            display: flex; flex-wrap: wrap; gap: 6px;
        }
        .xpl-ptag {
            font-size: 13px; color: #d4d4d8;
            background: #1a1a1a;
            padding: 5px 12px; border-radius: 16px;
            border: 1px solid rgba(255,255,255,.06);
            transition: background .15s;
        }
        .xpl-ptag:hover {
            background: #27272a;
            color: #fff;
        }

        /* Panel: Description */
        .xpl-pdesc {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .xpl-pdesc-label {
            font-size: 12px; font-weight: 600;
            color: #a1a1aa; margin-bottom: 8px;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .xpl-pdesc-text {
            font-size: 14px; color: #d4d4d8;
            line-height: 1.6;
        }

        /* Panel: CTA buttons */
        .xpl-pcta {
            padding: 16px 20px;
        }
        .xpl-pcta-main {
            display: block; width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #fff; text-align: center;
            border-radius: 12px; font-weight: 700;
            font-size: 15px; text-decoration: none;
            transition: opacity .15s;
            box-shadow: 0 4px 16px rgba(239,68,68,.3);
        }
        .xpl-pcta-main:hover { opacity: .9; }
        .xpl-pcta-secondary {
            display: block; width: 100%;
            padding: 12px; margin-top: 10px;
            background: #1a1a1a;
            border: 1px solid rgba(255,255,255,.08);
            color: #d4d4d8; text-align: center;
            border-radius: 12px; font-weight: 600;
            font-size: 14px; text-decoration: none;
            transition: background .15s;
        }
        .xpl-pcta-secondary:hover { background: #27272a; color: #fff; }

        /* ── Mobile overrides ── */
        @media (max-width: 991px) {
            .xpl { flex-direction: column; height: calc(100dvh - 50px); }
            .xpl-center { padding: 0; }
            .xpl-phone {
                max-width: 100%; max-height: 100%;
                border-radius: 0;
                box-shadow: none;
            }
            .xpl-panel {
                position: fixed; bottom: 0; left: 0; right: 0;
                width: 100%; height: 65vh;
                border-radius: 16px 16px 0 0;
                border-left: none;
                border-top: 1px solid rgba(255,255,255,.1);
                transform: translateY(100%);
                transition: transform .35s cubic-bezier(.4,0,.2,1);
                z-index: 60;
            }
            .xpl-panel.open { transform: translateY(0); }
            .xpl-panel-handle {
                display: flex; justify-content: center;
                padding: 10px 0 4px;
            }
            .xpl-panel-handle span {
                width: 36px; height: 4px;
                background: rgba(255,255,255,.25);
                border-radius: 2px;
            }
            .xpl-overlay {
                position: fixed; inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 55;
                opacity: 0; pointer-events: none;
                transition: opacity .3s;
            }
            .xpl-overlay.active { opacity: 1; pointer-events: auto; }
        }
        @media (min-width: 992px) {
            .xpl-panel-handle { display: none; }
            .xpl-overlay { display: none; }
        }
    </style>
@endpush

@section('content')
<div class="xpl" id="xpl">
    <div class="xpl-center">
        <div class="xpl-phone">
            <div class="xpl-feed" id="xpl-feed">
                @foreach($models as $i => $model)
                @php
                    $mTags = is_array($model->tags) ? array_slice($model->tags, 0, 5) : [];
                    $goalPct = $model->goal_progress ?? 0;
                @endphp
                <article class="xpl-slide"
                    data-model-id="{{ $model->id }}"
                    data-stream-url="{{ $model->best_stream_url }}"
                    data-index="{{ $i }}">

                    <script type="application/json" class="slide-data">
                    @php echo json_encode([
                        'username' => $model->username,
                        'age' => $model->age,
                        'country' => $model->country,
                        'flag' => country_flag($model->country),
                        'viewers' => number_format($model->viewers_count),
                        'languages' => implode(', ', array_slice($model->languages ?? [], 0, 3)),
                        'description' => $model->description,
                        'url' => $model->url,
                        'affiliate_url' => $model->affiliate_url,
                        'platform' => ucfirst($model->source_platform),
                        'image_url' => $model->best_image_url,
                        'rating' => $model->rating,
                        'is_hd' => $model->is_hd,
                        'goal_message' => $model->goal_message,
                        'goal_progress' => $goalPct,
                        'tags' => $mTags,
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) @endphp
                    </script>

                    <img class="xpl-poster"
                         src="{{ $model->best_image_url }}"
                         alt="{{ $model->username }} {{ $model->is_online ? __('live cam') : __('cam model') }}"
                         loading="{{ $i < 2 ? 'eager' : 'lazy' }}"
                         width="640" height="480">
                    <video muted playsinline preload="none"></video>
                    <div class="xpl-spinner-wrap"><div class="xpl-spinner"></div></div>

                    {{-- Bottom info --}}
                    <div class="xpl-info">
                        <div class="xpl-name"><a href="{{ $model->url }}">{{ $model->username }}</a></div>
                        @if($model->stream_title)
                        <div class="xpl-title">{{ $model->stream_title }}</div>
                        @endif
                        @if($model->goal_message)
                        <div class="xpl-goal">
                            <span>🎯</span>
                            <div class="xpl-goal-bar"><div class="xpl-goal-fill" style="width:{{ $goalPct }}%"></div></div>
                            <span class="xpl-goal-pct">{{ $goalPct }}%</span>
                        </div>
                        @endif
                        @if(!empty($mTags))
                        <div class="xpl-tags-row">
                            @foreach(array_slice($mTags, 0, 3) as $tag)
                            <span class="xpl-tag">#{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Right actions --}}
                    <div class="xpl-actions">
                        <div style="position:relative">
                            <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="xpl-avatar" title="{{ __('Watch') }} {{ $model->username }} {{ __('live') }}">
                                <img src="{{ $model->best_image_url }}" alt="{{ $model->username }}" width="50" height="50">
                                <span class="xpl-avatar-live">LIVE</span>
                            </a>
                        </div>

                        <div>
                            <button class="xpl-btn xpl-fav-btn" data-model-id="{{ $model->id }}" aria-label="{{ __('Favorite') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                            </button>
                            <div class="xpl-btn-label">{{ __('Fav') }}</div>
                        </div>

                        <div>
                            <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="xpl-btn" title="{{ __('Chat') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            </a>
                            <div class="xpl-btn-label">{{ __('Chat') }}</div>
                        </div>

                        <div>
                            <button class="xpl-btn xpl-detail-btn" aria-label="{{ __('Details') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                            </button>
                            <div class="xpl-btn-label">{{ __('More') }}</div>
                        </div>
                    </div>

                    {{-- Crawlable model details --}}
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
    <div class="xpl-overlay" id="xpl-overlay"></div>
    <aside class="xpl-panel" id="xpl-panel">
        <div class="xpl-panel-handle"><span></span></div>
        <div class="xpl-panel-scroll" id="xpl-panel-body">
            <div style="text-align:center; color:#555; padding:40px 20px;">{{ __('Select a model to see details') }}</div>
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
    const feed = document.getElementById('xpl-feed');
    const panel = document.getElementById('xpl-panel');
    const panelBody = document.getElementById('xpl-panel-body');
    const overlay = document.getElementById('xpl-overlay');
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

    const favorites = new Set(JSON.parse(localStorage.getItem('xpl_favs') || '[]'));
    function saveFavs() { localStorage.setItem('xpl_favs', JSON.stringify([...favorites])); }

    function initSlides() {
        slides.length = 0;
        document.querySelectorAll('.xpl-slide').forEach(s => {
            slides.push(s);
            bindSlideEvents(s);
        });
        updateFavButtons();
    }

    function bindSlideEvents(slide) {
        const favBtn = slide.querySelector('.xpl-fav-btn');
        if (favBtn) {
            favBtn.addEventListener('click', () => {
                const id = slide.dataset.modelId;
                if (favorites.has(id)) { favorites.delete(id); } else { favorites.add(id); }
                saveFavs();
                updateFavButtons();
            });
        }
        const detailBtn = slide.querySelector('.xpl-detail-btn');
        if (detailBtn) {
            detailBtn.addEventListener('click', () => togglePanel(true));
        }
    }

    function updateFavButtons() {
        document.querySelectorAll('.xpl-fav-btn').forEach(btn => {
            const id = btn.closest('.xpl-slide')?.dataset.modelId;
            btn.classList.toggle('is-fav', favorites.has(id));
        });
    }

    function togglePanel(show) {
        if (show === undefined) show = !panel.classList.contains('open');
        panel.classList.toggle('open', show);
        overlay.classList.toggle('active', show);
    }
    overlay.addEventListener('click', () => togglePanel(false));

    function populatePanel(index) {
        const slide = slides[index];
        if (!slide) return;
        const script = slide.querySelector('.slide-data');
        if (!script) return;
        try {
            const d = JSON.parse(script.textContent);
            let html = '';

            html += `<div class="xpl-ph">
                <div class="xpl-ph-avatar"><img src="${d.image_url}" alt="${esc(d.username)}"></div>
                <div class="xpl-ph-info">
                    <div class="xpl-ph-name">${esc(d.username)}</div>
                    <div class="xpl-ph-meta">
                        <span class="xpl-ph-live"><span class="xpl-ph-live-dot"></span> LIVE</span>
                        <span class="xpl-ph-platform">${esc(d.platform)}</span>
                    </div>
                </div>
            </div>`;

            html += `<div class="xpl-stats">`;
            if (d.age) html += `<div class="xpl-stat"><div class="xpl-stat-label">Age</div><div class="xpl-stat-val">${esc(d.age)}</div></div>`;
            if (d.country) html += `<div class="xpl-stat"><div class="xpl-stat-label">Country</div><div class="xpl-stat-val">${d.flag ? d.flag + ' ' : ''}${esc(d.country)}</div></div>`;
            if (d.viewers) html += `<div class="xpl-stat"><div class="xpl-stat-label">Viewers</div><div class="xpl-stat-val">${esc(d.viewers)}</div></div>`;
            if (d.rating) html += `<div class="xpl-stat"><div class="xpl-stat-label">Rating</div><div class="xpl-stat-val">⭐ ${Number(d.rating).toFixed(1)}</div></div>`;
            if (d.languages) html += `<div class="xpl-stat"><div class="xpl-stat-label">Languages</div><div class="xpl-stat-val">${esc(d.languages)}</div></div>`;
            if (d.is_hd) html += `<div class="xpl-stat"><div class="xpl-stat-label">Quality</div><div class="xpl-stat-val">HD</div></div>`;
            html += `</div>`;

            if (d.goal_message) {
                const pct = d.goal_progress || 0;
                html += `<div class="xpl-pgoal">
                    <div class="xpl-pgoal-label">🎯 Current Goal</div>
                    <div class="xpl-pgoal-msg">${esc(d.goal_message)}</div>
                    <div class="xpl-pgoal-track"><div class="xpl-pgoal-fill" style="width:${pct}%"></div></div>
                    <div class="xpl-pgoal-pct">${pct}%</div>
                </div>`;
            }

            if (d.tags && d.tags.length) {
                html += `<div class="xpl-ptags">
                    <div class="xpl-ptags-label">Tags</div>
                    <div class="xpl-ptags-list">${d.tags.map(t => `<span class="xpl-ptag">#${esc(t)}</span>`).join('')}</div>
                </div>`;
            }

            if (d.description) {
                html += `<div class="xpl-pdesc">
                    <div class="xpl-pdesc-label">About</div>
                    <div class="xpl-pdesc-text">${esc(d.description)}</div>
                </div>`;
            }

            html += `<div class="xpl-pcta">
                <a href="${d.affiliate_url}" target="_blank" rel="nofollow noopener" class="xpl-pcta-main">Join ${esc(d.username)}'s Live Chat</a>
                <a href="${d.url}" class="xpl-pcta-secondary">View Full Profile</a>
            </div>`;

            panelBody.innerHTML = html;
        } catch (e) { console.error('Panel data error', e); }
    }

    function onSnap() {
        const scrollTop = feed.scrollTop;
        const slideH = feed.clientHeight;
        const newIndex = Math.round(scrollTop / slideH);
        if (newIndex !== currentIndex) {
            currentIndex = Math.min(newIndex, slides.length - 1);
            populatePanel(currentIndex);
            manageStreams();
        }
        if (currentIndex >= slides.length - PRELOAD_AHEAD - 1) loadMore();
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
        if (hlsInstances.size > MAX_ACTIVE_HLS) pruneDistant();
    }

    function startStream(slide, url) {
        if (!url) return;
        const video = slide.querySelector('video');
        if (!video) return;
        const entry = hlsInstances.get(slide);
        if (entry && entry.state === 'playing') return;
        if (entry && entry.hls) {
            video.play().then(() => {
                slide.classList.add('stream-active');
                entry.state = 'playing';
            }).catch(() => {});
            return;
        }
        if (typeof Hls !== 'undefined' && Hls.isSupported()) {
            const hls = new Hls({ maxBufferLength: 15, maxMaxBufferLength: 30, startLevel: -1, capLevelToPlayerSize: true });
            hls.loadSource(url);
            hls.attachMedia(video);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                if (slides[currentIndex] === slide) {
                    video.play().then(() => {
                        slide.classList.add('stream-active');
                        hlsInstances.set(slide, { hls, state: 'playing' });
                    }).catch(() => {});
                } else {
                    hlsInstances.set(slide, { hls, state: 'preloaded' });
                }
            });
            hls.on(Hls.Events.ERROR, (_, data) => { if (data.fatal) destroyStream(slide); });
            hlsInstances.set(slide, { hls, state: 'loading' });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = url;
            video.addEventListener('loadedmetadata', () => {
                if (slides[currentIndex] === slide) {
                    video.play().then(() => slide.classList.add('stream-active')).catch(() => {});
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
        slide.classList.remove('stream-active');
        hlsInstances.delete(slide);
    }

    function pauseNonCurrent() {
        hlsInstances.forEach((entry, slide) => {
            if (slide !== slides[currentIndex]) {
                const v = slide.querySelector('video');
                if (v && !v.paused) { v.pause(); entry.state = 'preloaded'; }
            }
        });
    }

    function pruneDistant() {
        [...hlsInstances.entries()]
            .map(([sl, d]) => ({ sl, d, dist: Math.abs(slides.indexOf(sl) - currentIndex) }))
            .sort((a, b) => b.dist - a.dist)
            .slice(MAX_ACTIVE_HLS)
            .forEach(({ sl }) => destroyStream(sl));
    }

    async function loadMore() {
        if (isLoadingMore || !hasMore) return;
        isLoadingMore = true;
        try {
            const params = new URLSearchParams({ offset: apiOffset, limit: 6 });
            if (CATEGORY) params.set('category', CATEGORY);
            seenIds.forEach(id => params.append('exclude[]', id));
            const resp = await fetch(API_URL + '?' + params, { headers: { Accept: 'application/json' } });
            const data = await resp.json();
            if (!data.models || !data.models.length) { hasMore = false; return; }
            hasMore = data.hasMore;
            data.models.forEach(m => {
                seenIds.add(m.id);
                apiOffset++;
                const slide = createSlide(m, slides.length);
                feed.appendChild(slide);
                slides.push(slide);
                bindSlideEvents(slide);
            });
            updateFavButtons();
        } catch (e) { console.error('Load error:', e); }
        finally { isLoadingMore = false; }
    }

    function createSlide(m, index) {
        const div = document.createElement('article');
        div.className = 'xpl-slide';
        div.dataset.modelId = m.id;
        div.dataset.streamUrl = m.stream_url || '';
        div.dataset.index = index;

        const jsonData = {
            username: m.username, age: m.age || '', country: m.country || '',
            flag: m.flag || '', viewers: m.viewers_count ? Number(m.viewers_count).toLocaleString() : '',
            languages: (m.languages || []).slice(0, 3).join(', '),
            description: m.description || '', url: m.url, affiliate_url: m.affiliate_url,
            platform: m.platform ? m.platform.charAt(0).toUpperCase() + m.platform.slice(1) : '',
            image_url: m.image_url, rating: m.rating, is_hd: m.is_hd,
            goal_message: m.goal_message || '', goal_progress: m.goal_progress || 0,
            tags: m.tags || [],
        };

        const tags = (m.tags || []).slice(0, 3).map(t => `<span class="xpl-tag">#${esc(t)}</span>`).join('');
        const goalPct = m.goal_progress || 0;
        const goalHtml = m.goal_message ? `<div class="xpl-goal"><span>🎯</span><div class="xpl-goal-bar"><div class="xpl-goal-fill" style="width:${goalPct}%"></div></div><span class="xpl-goal-pct">${goalPct}%</span></div>` : '';

        div.innerHTML = `
            <script type="application/json" class="slide-data">${JSON.stringify(jsonData)}<\/script>
            <img class="xpl-poster" src="${m.image_url}" alt="${esc(m.username)}" loading="lazy" width="640" height="480">
            <video muted playsinline preload="none"></video>
            <div class="xpl-spinner-wrap"><div class="xpl-spinner"></div></div>
            <div class="xpl-info">
                <div class="xpl-name"><a href="${m.url}">${esc(m.username)}</a></div>
                ${m.stream_title ? `<div class="xpl-title">${esc(m.stream_title)}</div>` : ''}
                ${goalHtml}
                ${tags ? `<div class="xpl-tags-row">${tags}</div>` : ''}
            </div>
            <div class="xpl-actions">
                <div style="position:relative">
                    <a href="${m.affiliate_url}" target="_blank" rel="nofollow noopener" class="xpl-avatar">
                        <img src="${m.image_url}" alt="${esc(m.username)}" width="50" height="50">
                        <span class="xpl-avatar-live">LIVE</span>
                    </a>
                </div>
                <div>
                    <button class="xpl-btn xpl-fav-btn" data-model-id="${m.id}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                    </button>
                    <div class="xpl-btn-label">Fav</div>
                </div>
                <div>
                    <a href="${m.affiliate_url}" target="_blank" rel="nofollow noopener" class="xpl-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    </a>
                    <div class="xpl-btn-label">Chat</div>
                </div>
                <div>
                    <button class="xpl-btn xpl-detail-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="xpl-btn-label">More</div>
                </div>
            </div>
        `;
        return div;
    }

    const _esc = document.createElement('div');
    function esc(s) { _esc.textContent = s || ''; return _esc.innerHTML; }

    // Touch: swipe left opens panel on mobile
    let tx0 = 0;
    feed.addEventListener('touchstart', e => { tx0 = e.changedTouches[0].screenX; }, { passive: true });
    feed.addEventListener('touchend', e => {
        if (tx0 - e.changedTouches[0].screenX > 70 && window.innerWidth < 992) togglePanel(true);
    }, { passive: true });

    let scrollTimer;
    feed.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(onSnap, 60);
    }, { passive: true });

    initSlides();
    if (slides.length > 0) { populatePanel(0); manageStreams(); }
})();
</script>
@endsection
