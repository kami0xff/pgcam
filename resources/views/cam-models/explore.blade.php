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
            background: var(--bg-primary);
            overflow: hidden;
        }

        /* ── Video column ── */
        .xpl-center {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 16px;
            min-width: 0;
        }
        .xpl-phone {
            width: 100%;
            max-width: 480px;
            height: 100%;
            border-radius: var(--radius-2xl);
            overflow: hidden;
            position: relative;
            background: #111;
            box-shadow: 0 0 80px rgba(0,0,0,.7), 0 0 0 1px rgba(255,255,255,.05);
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
            border: 3px solid rgba(255,255,255,.15);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: xpl-spin .8s linear infinite;
        }
        @keyframes xpl-spin { to { transform: rotate(360deg); } }
        .xpl-slide.stream-active .xpl-spinner-wrap { display: none; }

        .xpl-slide::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,.8) 0%, rgba(0,0,0,.25) 22%, transparent 50%);
            z-index: 4;
            pointer-events: none;
        }

        /* ── Bottom info ── */
        .xpl-info {
            position: absolute; bottom: 16px; left: 16px; right: 68px;
            z-index: 10; color: var(--text-primary);
        }
        .xpl-name {
            font-size: 18px; font-weight: 700;
            margin-bottom: 4px;
            text-shadow: 0 1px 4px rgba(0,0,0,.6);
        }
        .xpl-name a { color: var(--text-primary); text-decoration: none; }
        .xpl-title {
            font-size: 13px; opacity: .85;
            line-height: 1.4;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden;
            text-shadow: 0 1px 3px rgba(0,0,0,.5);
            margin-bottom: 8px;
        }
        .xpl-goal-inline {
            position: relative;
            width: 100%; height: 36px;
            background: #2C2C2C;
            border-radius: 18px;
            overflow: hidden;
            display: flex; align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,.3);
            margin-bottom: 6px;
        }
        .xpl-goal-inline-fill {
            position: absolute; top: 0; left: 0;
            height: 100%;
            background: linear-gradient(90deg, #22c55e 0%, #a3e635 100%);
            transition: width .5s ease;
        }
        .xpl-goal-inline-content {
            position: relative; z-index: 1;
            width: 100%; display: flex;
            align-items: center; justify-content: space-between;
            padding: 0 10px;
        }
        .xpl-goal-inline-left {
            display: flex; align-items: center; gap: 6px;
            min-width: 0; flex: 1;
        }
        .xpl-goal-inline-icon {
            width: 24px; height: 24px; min-width: 24px;
            border-radius: 50%;
            background: rgba(34,197,94,.3);
            display: flex; align-items: center; justify-content: center;
        }
        .xpl-goal-inline-icon svg { width: 14px; height: 14px; color: #fff; }
        .xpl-goal-inline-text {
            font-size: 11px; font-weight: 600; color: #fff;
            overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
        }
        .xpl-goal-inline-tokens { color: #fbbf24; font-weight: 700; }
        .xpl-goal-inline-pct {
            font-size: 12px; font-weight: 700; color: #fff;
            padding-left: 8px; white-space: nowrap;
        }
        .xpl-tags-row {
            display: flex; flex-wrap: wrap; gap: 4px;
            margin-top: 6px;
        }
        .xpl-tag {
            font-size: 11px;
            background: rgba(255,255,255,.12);
            padding: 2px 8px;
            border-radius: var(--radius-sm);
            backdrop-filter: blur(4px);
        }

        /* ── Right actions ── */
        .xpl-actions {
            position: absolute; right: 10px; bottom: 20px;
            display: flex; flex-direction: column;
            align-items: center; gap: 20px;
            z-index: 10;
        }
        .xpl-avatar {
            width: 48px; height: 48px;
            border-radius: 50%;
            border: 2.5px solid var(--accent);
            overflow: hidden;
            display: block;
            box-shadow: 0 0 10px var(--accent-glow);
            transition: box-shadow .3s;
        }
        .xpl-avatar:hover { box-shadow: 0 0 18px var(--accent-glow); }
        .xpl-avatar img {
            width: 100%; height: 100%;
            object-fit: cover; display: block;
        }
        .xpl-btn {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,.1);
            backdrop-filter: blur(8px);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-primary); border: none; cursor: pointer;
            transition: transform .15s, background .15s;
            text-decoration: none;
        }
        .xpl-btn:active { transform: scale(.9); }
        .xpl-btn:hover { background: rgba(255,255,255,.2); }
        .xpl-btn svg { width: 22px; height: 22px; }
        .xpl-btn.is-fav { color: #ef4444; }
        .xpl-btn.is-fav svg { fill: #ef4444; }
        .xpl-btn-label {
            font-size: 10px; font-weight: 600;
            margin-top: 2px; text-align: center;
            text-shadow: 0 1px 2px rgba(0,0,0,.8);
            color: var(--text-primary);
        }

        /* ══════════════════════════════════════════════
           SIDEPANEL
           ══════════════════════════════════════════════ */
        .xpl-panel {
            width: 420px;
            background: var(--bg-secondary);
            border-left: 1px solid var(--border);
            display: flex; flex-direction: column;
            overflow: hidden;
        }
        .xpl-panel-scroll {
            flex: 1; overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.08) transparent;
        }

        .xpl-ph {
            padding: 20px;
            display: flex; align-items: center; gap: 14px;
            border-bottom: 1px solid var(--border);
        }
        .xpl-ph-avatar {
            width: 56px; height: 56px; min-width: 56px;
            border-radius: 50%; overflow: hidden;
            border: 2px solid var(--accent);
        }
        .xpl-ph-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .xpl-ph-info { min-width: 0; flex: 1; }
        .xpl-ph-name {
            font-size: 17px; font-weight: 700; color: var(--text-primary);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .xpl-ph-meta {
            font-size: 13px; color: var(--text-secondary);
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
            margin-top: 3px;
        }
        .xpl-ph-platform {
            font-size: 11px; font-weight: 600;
            background: rgba(255,255,255,.06);
            padding: 2px 8px; border-radius: var(--radius-sm);
            color: var(--text-secondary);
        }
        .xpl-ph-live {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 12px; font-weight: 700; color: var(--accent);
        }
        .xpl-ph-live-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--accent);
            animation: xpl-dot-pulse 1.5s ease-in-out infinite;
        }
        @keyframes xpl-dot-pulse {
            0%, 100% { opacity: 1; } 50% { opacity: .3; }
        }

        /* Panel action buttons */
        .xpl-pactions {
            display: flex; gap: 8px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }
        .xpl-paction {
            flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 10px 12px;
            border-radius: var(--radius-md);
            font-size: 13px; font-weight: 600;
            text-decoration: none;
            transition: opacity .15s;
            border: none; cursor: pointer;
        }
        .xpl-paction svg { width: 16px; height: 16px; flex-shrink: 0; }
        .xpl-paction-tip {
            background: var(--gradient-accent);
            color: #fff;
        }
        .xpl-paction-tip:hover { opacity: .9; }
        .xpl-paction-profile {
            background: rgba(255,255,255,.06);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }
        .xpl-paction-profile:hover { background: rgba(255,255,255,.1); }

        /* Stats grid */
        .xpl-stats {
            display: grid; grid-template-columns: 1fr 1fr 1fr;
            gap: 1px; background: var(--border);
            border-bottom: 1px solid var(--border);
        }
        .xpl-stat {
            padding: 14px 12px;
            background: var(--bg-secondary);
            text-align: center;
        }
        .xpl-stat-val {
            font-size: 16px; font-weight: 700;
            color: var(--text-primary);
        }
        .xpl-stat-label {
            font-size: 11px; text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-muted); font-weight: 500;
            margin-top: 2px;
        }

        /* Stream title */
        .xpl-pstream {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: flex-start; gap: 8px;
            color: var(--text-secondary);
            font-size: 13px; line-height: 1.5;
        }
        .xpl-pstream svg { flex-shrink: 0; margin-top: 2px; color: var(--text-muted); }

        /* Panel section */
        .xpl-psection {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }
        .xpl-psection-title {
            font-size: 12px; font-weight: 600;
            color: var(--text-muted); margin-bottom: 10px;
            text-transform: uppercase; letter-spacing: .5px;
        }

        /* Tip menu */
        .xpl-tip-list { display: flex; flex-direction: column; gap: 0; }
        .xpl-tip-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,.03);
        }
        .xpl-tip-item:last-child { border-bottom: none; }
        .xpl-tip-emoji {
            font-size: 18px; width: 28px; text-align: center;
            flex-shrink: 0;
        }
        .xpl-tip-name {
            flex: 1; font-size: 14px; color: var(--text-secondary);
            min-width: 0; overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap;
        }
        .xpl-tip-price {
            font-size: 13px; font-weight: 700; color: #fbbf24;
            white-space: nowrap;
        }

        /* Tags */
        .xpl-ptags-list { display: flex; flex-wrap: wrap; gap: 6px; }
        .xpl-ptag {
            font-size: 13px; color: var(--text-secondary);
            background: var(--bg-card);
            padding: 5px 12px; border-radius: var(--radius-full);
            border: 1px solid var(--border);
            transition: all .15s;
            text-decoration: none;
        }
        .xpl-ptag:hover {
            background: var(--bg-card-hover);
            color: var(--text-primary);
            border-color: var(--border-light);
        }

        /* Description */
        .xpl-pdesc-text {
            font-size: 14px; color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Languages */
        .xpl-plangs { display: flex; flex-wrap: wrap; gap: 6px; }
        .xpl-plang {
            font-size: 12px; color: var(--text-secondary);
            background: var(--bg-card);
            padding: 4px 10px; border-radius: var(--radius-sm);
            border: 1px solid var(--border);
        }

        /* Panel CTA */
        .xpl-pcta {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            background: var(--bg-secondary);
        }
        .xpl-pcta-main {
            display: block; width: 100%;
            padding: 14px;
            background: var(--gradient-accent);
            color: #fff; text-align: center;
            border-radius: var(--radius-lg); font-weight: 700;
            font-size: 15px; text-decoration: none;
            transition: opacity .15s;
            box-shadow: 0 4px 16px var(--accent-glow);
        }
        .xpl-pcta-main:hover { opacity: .9; }
        .xpl-pcta-secondary {
            display: block; width: 100%;
            padding: 12px; margin-top: 10px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text-secondary); text-align: center;
            border-radius: var(--radius-lg); font-weight: 600;
            font-size: 14px; text-decoration: none;
            transition: all .15s;
        }
        .xpl-pcta-secondary:hover { background: var(--bg-card-hover); color: var(--text-primary); }

        /* ── Mobile ── */
        @media (max-width: 991px) {
            .xpl { flex-direction: column; height: calc(100dvh - 50px); }
            .xpl-center { padding: 0; }
            .xpl-phone {
                max-width: 100%; max-height: 100%;
                border-radius: 0; box-shadow: none;
            }
            .xpl-panel {
                position: fixed; bottom: 0; left: 0; right: 0;
                width: 100%; height: 70vh;
                border-radius: var(--radius-2xl) var(--radius-2xl) 0 0;
                border-left: none;
                border-top: 1px solid var(--border);
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
                background: rgba(255,255,255,.2);
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
                    $goalTokens = $model->goal_needed;
                    $goalMsg = $model->goal_message;
                    if (!$goalTokens && $goalMsg && preg_match('/^(\d+)\s*/', $goalMsg, $gm)) {
                        $goalTokens = (int) $gm[1];
                        $goalMsg = trim(preg_replace('/^\d+\s*/', '', $goalMsg));
                    }
                    $tipItems = \App\Models\ModelTipMenuItem::getForModel($model->username)->take(6);
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
                        'languages' => $model->languages ?? [],
                        'description' => $model->description,
                        'url' => $model->url,
                        'affiliate_url' => $model->affiliate_url,
                        'platform' => ucfirst($model->source_platform),
                        'image_url' => $model->best_image_url,
                        'rating' => $model->rating,
                        'is_hd' => $model->is_hd,
                        'goal_message' => $goalMsg,
                        'goal_tokens' => $goalTokens,
                        'goal_progress' => $goalPct,
                        'stream_title' => $model->stream_title,
                        'tags' => $mTags,
                        'tip_menu' => $tipItems->map(fn($item) => [
                            'emoji' => $item->emoji,
                            'name' => $item->translated_name,
                            'price' => $item->token_price,
                        ])->values(),
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) @endphp
                    </script>

                    <img class="xpl-poster"
                         src="{{ $model->best_image_url }}"
                         alt="{{ $model->username }} {{ $model->is_online ? __('live cam') : __('cam model') }}"
                         loading="{{ $i < 2 ? 'eager' : 'lazy' }}"
                         width="640" height="480">
                    <video muted playsinline preload="none"></video>
                    <div class="xpl-spinner-wrap"><div class="xpl-spinner"></div></div>

                    <div class="xpl-info">
                        <div class="xpl-name"><a href="{{ $model->url }}">{{ $model->username }}</a></div>
                        @if($model->stream_title)
                        <div class="xpl-title">{{ $model->stream_title }}</div>
                        @endif
                        @if($model->goal_message)
                        <div class="xpl-goal-inline">
                            <div class="xpl-goal-inline-fill" style="width:{{ $goalPct }}%"></div>
                            <div class="xpl-goal-inline-content">
                                <div class="xpl-goal-inline-left">
                                    <div class="xpl-goal-inline-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                    </div>
                                    <span class="xpl-goal-inline-text">
                                        @if($goalTokens)<span class="xpl-goal-inline-tokens">{{ number_format($goalTokens) }}</span>@endif
                                        {{ $goalMsg }}
                                    </span>
                                </div>
                                <span class="xpl-goal-inline-pct">{{ $goalPct }}%</span>
                            </div>
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

                    <div class="xpl-actions">
                        <div>
                            <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="xpl-avatar" title="{{ $model->username }}">
                                <img src="{{ $model->best_image_url }}" alt="{{ $model->username }}" width="48" height="48">
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
                    </div>

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

    <div class="xpl-overlay" id="xpl-overlay"></div>
    <aside class="xpl-panel" id="xpl-panel">
        <div class="xpl-panel-handle"><span></span></div>
        <div class="xpl-panel-scroll" id="xpl-panel-body">
            <div style="text-align:center; color:var(--text-muted); padding:60px 20px;">{{ __('Select a model to see details') }}</div>
        </div>
        <div class="xpl-pcta" id="xpl-panel-cta" style="display:none"></div>
    </aside>
</div>

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
    const panelCta = document.getElementById('xpl-panel-cta');
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
                if (favorites.has(id)) favorites.delete(id); else favorites.add(id);
                saveFavs(); updateFavButtons();
            });
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

            // Header
            html += `<div class="xpl-ph">
                <div class="xpl-ph-avatar"><img src="${d.image_url}" alt="${esc(d.username)}"></div>
                <div class="xpl-ph-info">
                    <div class="xpl-ph-name">${esc(d.username)}</div>
                    <div class="xpl-ph-meta">
                        <span class="xpl-ph-live"><span class="xpl-ph-live-dot"></span> LIVE</span>
                        <span class="xpl-ph-platform">${esc(d.platform)}</span>
                        ${d.is_hd ? '<span class="xpl-ph-platform" style="color:var(--accent)">HD</span>' : ''}
                    </div>
                </div>
            </div>`;

            // Actions: Tip + Profile only
            html += `<div class="xpl-pactions">
                <a href="${d.affiliate_url}" target="_blank" rel="nofollow noopener" class="xpl-paction xpl-paction-tip">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/></svg>
                    Send Tip
                </a>
                <a href="${d.url}" class="xpl-paction xpl-paction-profile">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile
                </a>
            </div>`;

            // Stats
            let statCount = 0;
            let statsHtml = '';
            if (d.age) { statsHtml += `<div class="xpl-stat"><div class="xpl-stat-val">${esc(d.age)}</div><div class="xpl-stat-label">Age</div></div>`; statCount++; }
            if (d.country) { statsHtml += `<div class="xpl-stat"><div class="xpl-stat-val">${d.flag || ''} ${esc(d.country)}</div><div class="xpl-stat-label">Country</div></div>`; statCount++; }
            if (d.viewers) { statsHtml += `<div class="xpl-stat"><div class="xpl-stat-val">${esc(d.viewers)}</div><div class="xpl-stat-label">Viewers</div></div>`; statCount++; }
            if (d.rating) { statsHtml += `<div class="xpl-stat"><div class="xpl-stat-val">⭐ ${Number(d.rating).toFixed(1)}</div><div class="xpl-stat-label">Rating</div></div>`; statCount++; }
            if (statCount) html += `<div class="xpl-stats" style="grid-template-columns:repeat(${Math.min(statCount,4)},1fr)">${statsHtml}</div>`;

            // Stream title
            if (d.stream_title) {
                html += `<div class="xpl-pstream">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    <span>${esc(d.stream_title)}</span>
                </div>`;
            }

            // Description
            if (d.description) {
                html += `<div class="xpl-psection">
                    <div class="xpl-psection-title">About ${esc(d.username)}</div>
                    <div class="xpl-pdesc-text">${esc(d.description)}</div>
                </div>`;
            }

            // Tip menu
            if (d.tip_menu && d.tip_menu.length) {
                html += `<div class="xpl-psection">
                    <div class="xpl-psection-title">🎁 Tip Menu</div>
                    <div class="xpl-tip-list">
                        ${d.tip_menu.map(t => `<div class="xpl-tip-item">
                            <span class="xpl-tip-emoji">${t.emoji || '🎁'}</span>
                            <span class="xpl-tip-name">${esc(t.name)}</span>
                            <span class="xpl-tip-price">${Number(t.price).toLocaleString()} tk</span>
                        </div>`).join('')}
                    </div>
                </div>`;
            }

            // Tags
            if (d.tags && d.tags.length) {
                html += `<div class="xpl-psection">
                    <div class="xpl-psection-title">Tags</div>
                    <div class="xpl-ptags-list">${d.tags.map(t => `<span class="xpl-ptag">#${esc(t)}</span>`).join('')}</div>
                </div>`;
            }

            // Languages
            if (d.languages && d.languages.length) {
                html += `<div class="xpl-psection">
                    <div class="xpl-psection-title">Languages</div>
                    <div class="xpl-plangs">${d.languages.map(l => `<span class="xpl-plang">${esc(l)}</span>`).join('')}</div>
                </div>`;
            }

            panelBody.innerHTML = html;

            // Bottom CTA
            panelCta.style.display = '';
            panelCta.innerHTML = `
                <a href="${d.affiliate_url}" target="_blank" rel="nofollow noopener" class="xpl-pcta-main">Watch ${esc(d.username)} Live</a>
                <a href="${d.url}" class="xpl-pcta-secondary">View Full Profile</a>
            `;
        } catch (e) { console.error('Panel error', e); }
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
            if (i === currentIndex) startStream(slide, url);
            else if ((dist <= PRELOAD_AHEAD && i > currentIndex) || (dist <= PRELOAD_BEHIND && i < currentIndex)) preloadStream(slide, url);
            else if (dist > PRELOAD_AHEAD + 1) destroyStream(slide);
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
            video.play().then(() => { slide.classList.add('stream-active'); entry.state = 'playing'; }).catch(() => {});
            return;
        }
        if (typeof Hls !== 'undefined' && Hls.isSupported()) {
            const hls = new Hls({ maxBufferLength: 15, maxMaxBufferLength: 30, startLevel: -1, capLevelToPlayerSize: true });
            hls.loadSource(url);
            hls.attachMedia(video);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                if (slides[currentIndex] === slide) {
                    video.play().then(() => { slide.classList.add('stream-active'); hlsInstances.set(slide, { hls, state: 'playing' }); }).catch(() => {});
                } else {
                    hlsInstances.set(slide, { hls, state: 'preloaded' });
                }
            });
            hls.on(Hls.Events.ERROR, (_, data) => { if (data.fatal) destroyStream(slide); });
            hlsInstances.set(slide, { hls, state: 'loading' });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = url;
            video.addEventListener('loadedmetadata', () => {
                if (slides[currentIndex] === slide) video.play().then(() => slide.classList.add('stream-active')).catch(() => {});
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

        let goalMsg = m.goal_message || '';
        let goalTokens = m.goal_needed || 0;
        if (!goalTokens && goalMsg) {
            const gm = goalMsg.match(/^(\d+)\s*/);
            if (gm) { goalTokens = parseInt(gm[1]); goalMsg = goalMsg.replace(/^\d+\s*/, ''); }
        }

        const jsonData = {
            username: m.username, age: m.age || '', country: m.country || '',
            flag: m.flag || '', viewers: m.viewers_count ? Number(m.viewers_count).toLocaleString() : '',
            languages: m.languages || [],
            description: m.description || '', url: m.url, affiliate_url: m.affiliate_url,
            platform: m.platform ? m.platform.charAt(0).toUpperCase() + m.platform.slice(1) : '',
            image_url: m.image_url, rating: m.rating, is_hd: m.is_hd,
            goal_message: goalMsg, goal_tokens: goalTokens,
            goal_progress: m.goal_progress || 0,
            stream_title: m.stream_title || '',
            tags: m.tags || [],
            tip_menu: m.tip_menu || [],
        };

        const tags = (m.tags || []).slice(0, 3).map(t => `<span class="xpl-tag">#${esc(t)}</span>`).join('');
        const goalPct = m.goal_progress || 0;
        let goalHtml = '';
        if (m.goal_message) {
            goalHtml = `<div class="xpl-goal-inline">
                <div class="xpl-goal-inline-fill" style="width:${goalPct}%"></div>
                <div class="xpl-goal-inline-content">
                    <div class="xpl-goal-inline-left">
                        <div class="xpl-goal-inline-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg></div>
                        <span class="xpl-goal-inline-text">${goalTokens ? `<span class="xpl-goal-inline-tokens">${Number(goalTokens).toLocaleString()}</span> ` : ''}${esc(goalMsg)}</span>
                    </div>
                    <span class="xpl-goal-inline-pct">${goalPct}%</span>
                </div>
            </div>`;
        }

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
                <div>
                    <a href="${m.affiliate_url}" target="_blank" rel="nofollow noopener" class="xpl-avatar">
                        <img src="${m.image_url}" alt="${esc(m.username)}" width="48" height="48">
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
            </div>
        `;
        return div;
    }

    const _esc = document.createElement('div');
    function esc(s) { _esc.textContent = s || ''; return _esc.innerHTML; }

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
