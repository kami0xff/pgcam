@extends('layouts.pornguru')

@section('title'){{ $category ? $categoryLabels[$category] . ' ' : '' }}Cam Roulette: Free Random Live Sex Chat | PornGuru @endsection
@section('meta_description')Free cam roulette - get matched with random live {{ $category ? strtolower($categoryLabels[$category] ?? 'cam models') : 'cam models' }} instantly! 🔥 Chatroulette-style random video chat. Spin to discover new models streaming now on PornGuru.cam ❤️ #camroulette #randomchat #livecams @endsection
@section('canonical'){{ localized_route('roulette', $category ? ['category' => $category] : []) }}@endsection

@push('seo-pagination')
@if(!empty($hreflangUrls))
@foreach($hreflangUrls as $lang => $href)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}" />
@endforeach
@endif
@endpush

@push('head')
<script type="application/ld+json">
@php
echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => ($category ? ($categoryLabels[$category] ?? '') . ' ' : '') . 'Cam Roulette: Free Random Live Sex Chat',
    'description' => 'Free cam roulette - chatroulette-style random video chat with live cam models. Spin to get matched instantly.',
    'url' => localized_route('roulette', $category ? ['category' => $category] : []),
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => 'PornGuru.cam',
        'url' => url('/'),
    ],
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => url('/roulette') . '?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
</script>
<style>
    .page-wrapper:has(.rlt) { overflow: hidden; height: 100svh; }
    .site-footer { display: none; }

    .rlt {
        display: flex;
        flex-direction: column;
        height: calc(100svh - 110px);
        background: var(--bg-primary);
        overflow: hidden;
    }

    .rlt-cats {
        display: flex;
        gap: 6px;
        padding: 10px 16px;
        overflow-x: auto;
        scrollbar-width: none;
        border-bottom: 1px solid var(--border);
        background: var(--bg-secondary);
    }
    .rlt-cats::-webkit-scrollbar { display: none; }
    .rlt-cat {
        padding: 6px 16px;
        border-radius: var(--radius-full);
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        text-decoration: none;
        color: var(--text-secondary);
        background: var(--bg-card);
        border: 1px solid var(--border);
        transition: all .15s;
    }
    .rlt-cat:hover { background: var(--bg-card-hover); color: var(--text-primary); }
    .rlt-cat.active {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    .rlt-main {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        min-height: 0;
        gap: 16px;
    }

    .rlt-screen {
        position: relative;
        width: 100%;
        max-width: 800px;
        height: 100%;
        border-radius: var(--radius-2xl);
        overflow: hidden;
        background: #111;
        box-shadow: 0 0 80px rgba(0,0,0,.7), 0 0 0 1px rgba(255,255,255,.05);
    }

    .rlt-video-wrap {
        width: 100%;
        height: 100%;
        position: relative;
        background: #000;
    }
    .rlt-video-wrap video,
    .rlt-video-wrap .rlt-poster {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0; left: 0;
    }
    .rlt-poster { z-index: 1; }
    .rlt-video-wrap video { z-index: 2; }
    .rlt-video-wrap.stream-active .rlt-poster { opacity: 0; transition: opacity .5s; }

    .rlt-video-wrap::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.85) 0%, rgba(0,0,0,.2) 25%, transparent 50%);
        z-index: 4;
        pointer-events: none;
    }

    .rlt-spinner {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        z-index: 5; pointer-events: none;
    }
    .rlt-spinner-ring {
        width: 48px; height: 48px;
        border: 3px solid rgba(255,255,255,.15);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: rlt-spin .8s linear infinite;
    }
    @keyframes rlt-spin { to { transform: rotate(360deg); } }
    .rlt-video-wrap.stream-active .rlt-spinner { display: none; }

    .rlt-info {
        position: absolute;
        bottom: 90px;
        left: 20px;
        right: 80px;
        z-index: 10;
        color: #fff;
    }
    .rlt-info-name {
        font-size: 22px;
        font-weight: 800;
        text-shadow: 0 2px 6px rgba(0,0,0,.6);
        margin-bottom: 4px;
    }
    .rlt-info-name a { color: #fff; text-decoration: none; }
    .rlt-info-meta {
        font-size: 14px;
        color: rgba(255,255,255,.8);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        text-shadow: 0 1px 3px rgba(0,0,0,.5);
    }
    .rlt-info-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 8px;
    }
    .rlt-info-tag {
        font-size: 11px;
        background: rgba(255,255,255,.12);
        padding: 2px 8px;
        border-radius: var(--radius-sm);
        backdrop-filter: blur(4px);
    }

    .rlt-actions {
        position: absolute;
        right: 16px;
        bottom: 100px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        z-index: 10;
    }
    .rlt-action-btn {
        width: 48px; height: 48px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: transform .15s, background .15s;
    }
    .rlt-action-btn:active { transform: scale(.9); }
    .rlt-action-btn:hover { background: rgba(255,255,255,.2); }
    .rlt-action-btn svg { width: 22px; height: 22px; }
    .rlt-action-label {
        font-size: 10px;
        font-weight: 600;
        color: rgba(255,255,255,.7);
        margin-top: 2px;
        text-align: center;
        text-shadow: 0 1px 2px rgba(0,0,0,.8);
    }

    .rlt-bottom-bar {
        position: absolute;
        bottom: 0;
        left: 0; right: 0;
        z-index: 10;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .rlt-next-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 14px 24px;
        background: var(--gradient-accent);
        color: #fff;
        border: none;
        border-radius: var(--radius-xl);
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 4px 20px var(--accent-glow);
        transition: transform .15s, opacity .15s;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .rlt-next-btn:hover { transform: scale(1.02); }
    .rlt-next-btn:active { transform: scale(.97); }
    .rlt-next-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
    .rlt-next-btn svg {
        width: 22px; height: 22px;
        transition: transform .3s;
    }
    .rlt-next-btn.spinning svg {
        animation: rlt-dice-spin .4s ease;
    }
    @keyframes rlt-dice-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .rlt-join-btn {
        padding: 14px 20px;
        background: rgba(255,255,255,.1);
        backdrop-filter: blur(8px);
        color: #fff;
        border: 1px solid rgba(255,255,255,.15);
        border-radius: var(--radius-xl);
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s;
    }
    .rlt-join-btn:hover { background: rgba(255,255,255,.2); }

    .rlt-empty {
        text-align: center;
        color: var(--text-muted);
        padding: 40px;
    }
    .rlt-empty h2 { font-size: 20px; margin-bottom: 8px; color: var(--text-primary); }

    .rlt-seo {
        position: absolute;
        overflow: hidden;
        width: 1px; height: 1px;
        padding: 0; margin: -1px;
        clip: rect(0,0,0,0);
        border: 0;
    }

    @media (max-width: 767px) {
        .rlt { height: calc(100svh - 50px); }
        .rlt-main { padding: 8px; }
        .rlt-screen { border-radius: var(--radius-lg); }
        .rlt-info { bottom: 80px; left: 14px; right: 64px; }
        .rlt-info-name { font-size: 18px; }
        .rlt-info-meta { font-size: 13px; }
        .rlt-actions { right: 10px; bottom: 88px; }
        .rlt-action-btn { width: 42px; height: 42px; }
        .rlt-bottom-bar { padding: 12px 14px; }
        .rlt-next-btn { padding: 12px 18px; font-size: 14px; }
        .rlt-join-btn { padding: 12px 14px; font-size: 13px; }
    }
</style>
@endpush

@section('content')
<div class="rlt" id="rlt">
    <nav class="rlt-cats">
        @foreach($categoryUrls as $key => $catUrl)
            <a href="{{ $catUrl }}"
               class="rlt-cat {{ ($key === 'all' && !$category) || $key === $category ? 'active' : '' }}"
               data-category="{{ $key === 'all' ? '' : $key }}">
                {{ $categoryLabels[$key === 'all' ? null : $key] ?? __('roulette.all_cams') }}
            </a>
        @endforeach
    </nav>

    <div class="rlt-main">
        <div class="rlt-screen" id="rlt-screen">
            @if($model)
                <div class="rlt-video-wrap" id="rlt-video-wrap"
                     data-stream-url="{{ $model->best_stream_url }}"
                     data-model-id="{{ $model->id }}">
                    <img class="rlt-poster" id="rlt-poster"
                         src="{{ $model->best_image_url }}"
                         alt="{{ $model->username }} live cam"
                         width="640" height="480">
                    <video id="rlt-video" muted playsinline></video>
                    <div class="rlt-spinner" id="rlt-spinner">
                        <div class="rlt-spinner-ring"></div>
                    </div>

                    <div class="rlt-info" id="rlt-info">
                        <div class="rlt-info-name">
                            <a href="{{ $model->url }}" id="rlt-profile-link">{{ $model->username }}</a>
                        </div>
                        <div class="rlt-info-meta" id="rlt-meta">
                            @if($model->age)<span>{{ $model->age }}</span>@endif
                            @if($model->country)<span>{{ country_flag($model->country) }} {{ $model->country }}</span>@endif
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="vertical-align:-2px">
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{ number_format($model->viewers_count ?? 0) }}
                            </span>
                            @if($model->is_hd)<span style="color:var(--accent);font-weight:700">HD</span>@endif
                        </div>
                        @if(!empty($model->tags))
                        <div class="rlt-info-tags" id="rlt-tags">
                            @foreach(array_slice($model->tags, 0, 5) as $tag)
                                <span class="rlt-info-tag">#{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="rlt-actions">
                        <div>
                            <button class="rlt-action-btn" id="rlt-mute-btn" aria-label="Toggle sound">
                                <svg id="rlt-sound-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>
                                <svg id="rlt-sound-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 010 14.14"/><path d="M15.54 8.46a5 5 0 010 7.07"/></svg>
                            </button>
                            <div class="rlt-action-label">{{ __('explore.sound') }}</div>
                        </div>
                        <div>
                            <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="rlt-action-btn" id="rlt-chat-btn" title="{{ __('common.chat') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            </a>
                            <div class="rlt-action-label">{{ __('common.chat') }}</div>
                        </div>
                        <div>
                            <a href="{{ $model->url }}" class="rlt-action-btn" id="rlt-view-btn" title="Profile">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </a>
                            <div class="rlt-action-label">Profile</div>
                        </div>
                    </div>

                    <div class="rlt-bottom-bar">
                        <button class="rlt-next-btn" id="rlt-next-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                            {{ __('roulette.next') }}
                        </button>
                        <a href="{{ $model->affiliate_url }}" target="_blank" rel="nofollow noopener" class="rlt-join-btn" id="rlt-join-btn" data-affiliate="{{ $model->source_platform }}">
                            {{ __('roulette.join_chat') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="rlt-empty">
                    <h2>{{ __('roulette.no_models') }}</h2>
                    <p>{{ __('roulette.try_another_category') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="rlt-seo">
    <h1>{{ $category ? $categoryLabels[$category] . ' ' : '' }}Cam Roulette - Free Random Live Sex Chat</h1>
    <h2>Chatroulette for Live Cams - Random Video Chat</h2>
    <p>PornGuru.cam Cam Roulette lets you get matched with random live cam models instantly. Like Chatroulette but for adult live cams. Spin to meet new {{ $category ? strtolower($categoryLabels[$category] ?? 'cam models') : 'cam girls, couples, men and trans models' }} streaming right now. Free random cam chat with no signup required. Cam roulette, random cam, chatroulette alternative, omegle cams, random video chat.</p>
    @if($model)
    <p>Now matched with <a href="{{ $model->url }}">{{ $model->username }}</a> — watch live and chat free.</p>
    @endif
</div>

<script>
(function() {
    const API_URL = '{{ route("api.roulette") }}';
    const CATEGORY = @json($category ?? '');
    let currentModelId = @json($model?->id);
    let hls = null;
    let isMuted = localStorage.getItem('streamMuted') !== 'false';

    const video = document.getElementById('rlt-video');
    const poster = document.getElementById('rlt-poster');
    const videoWrap = document.getElementById('rlt-video-wrap');
    const spinner = document.getElementById('rlt-spinner');
    const nextBtn = document.getElementById('rlt-next-btn');
    const info = document.getElementById('rlt-info');

    function updateMuteUI() {
        const on = document.getElementById('rlt-sound-on');
        const off = document.getElementById('rlt-sound-off');
        if (on) on.style.display = isMuted ? 'none' : 'block';
        if (off) off.style.display = isMuted ? 'block' : 'none';
    }

    document.getElementById('rlt-mute-btn')?.addEventListener('click', () => {
        isMuted = !isMuted;
        localStorage.setItem('streamMuted', isMuted);
        if (video) video.muted = isMuted;
        updateMuteUI();
    });

    updateMuteUI();

    function startStream(url) {
        if (!url || !video) return;

        videoWrap?.classList.remove('stream-active');
        if (spinner) spinner.style.display = '';

        if (hls) { hls.destroy(); hls = null; }
        video.pause();
        video.removeAttribute('src');

        if (typeof Hls !== 'undefined' && Hls.isSupported()) {
            hls = new Hls({
                maxBufferLength: 20,
                maxMaxBufferLength: 40,
                startLevel: -1,
                capLevelToPlayerSize: true,
            });
            hls.loadSource(url);
            hls.attachMedia(video);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                video.muted = isMuted;
                video.play().then(() => {
                    videoWrap?.classList.add('stream-active');
                }).catch(() => {
                    video.muted = true;
                    video.play().then(() => videoWrap?.classList.add('stream-active')).catch(() => {});
                });
            });
            hls.on(Hls.Events.ERROR, (_, data) => {
                if (data.fatal && spinner) {
                    spinner.innerHTML = '<span style="color:rgba(255,255,255,.5);font-size:14px">Stream unavailable</span>';
                }
            });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = url;
            video.addEventListener('loadedmetadata', () => {
                video.muted = isMuted;
                video.play().then(() => videoWrap?.classList.add('stream-active')).catch(() => {
                    video.muted = true;
                    video.play().then(() => videoWrap?.classList.add('stream-active')).catch(() => {});
                });
            }, { once: true });
        }

        setTimeout(() => {
            if (!videoWrap?.classList.contains('stream-active')) {
                videoWrap?.classList.add('stream-active');
            }
        }, 8000);
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    async function loadNext() {
        nextBtn.disabled = true;
        nextBtn.classList.add('spinning');

        try {
            const params = new URLSearchParams();
            if (CATEGORY) params.set('category', CATEGORY);
            if (currentModelId) params.set('exclude', currentModelId);

            const resp = await fetch(API_URL + '?' + params, { headers: { Accept: 'application/json' } });
            const data = await resp.json();

            if (!data.model) {
                nextBtn.disabled = false;
                nextBtn.classList.remove('spinning');
                return;
            }

            const m = data.model;
            currentModelId = m.id;

            if (poster) poster.src = m.image_url;
            videoWrap?.classList.remove('stream-active');
            if (videoWrap) videoWrap.dataset.modelId = m.id;

            const profileLink = document.getElementById('rlt-profile-link');
            if (profileLink) { profileLink.href = m.url; profileLink.textContent = m.username; }

            const metaEl = document.getElementById('rlt-meta');
            if (metaEl) {
                let metaHtml = '';
                if (m.age) metaHtml += `<span>${m.age}</span>`;
                if (m.country) metaHtml += `<span>${m.flag || ''} ${esc(m.country)}</span>`;
                metaHtml += `<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="vertical-align:-2px"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> ${Number(m.viewers_count || 0).toLocaleString()}</span>`;
                if (m.is_hd) metaHtml += `<span style="color:var(--accent);font-weight:700">HD</span>`;
                metaEl.innerHTML = metaHtml;
            }

            const tagsEl = document.getElementById('rlt-tags');
            if (tagsEl) {
                tagsEl.innerHTML = (m.tags || []).slice(0, 5).map(t => `<span class="rlt-info-tag">#${esc(t)}</span>`).join('');
            }

            const chatBtn = document.getElementById('rlt-chat-btn');
            if (chatBtn) chatBtn.href = m.affiliate_url;

            const viewBtn = document.getElementById('rlt-view-btn');
            if (viewBtn) viewBtn.href = m.url;

            const joinBtn = document.getElementById('rlt-join-btn');
            if (joinBtn) joinBtn.href = m.affiliate_url;

            startStream(m.stream_url);
        } catch (e) {
            console.error('Roulette error:', e);
        } finally {
            setTimeout(() => {
                nextBtn.disabled = false;
                nextBtn.classList.remove('spinning');
            }, 500);
        }
    }

    nextBtn?.addEventListener('click', loadNext);

    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === ' ' || e.key === 'ArrowRight') {
            e.preventDefault();
            loadNext();
        }
        if (e.key === 'm' || e.key === 'M') {
            document.getElementById('rlt-mute-btn')?.click();
        }
    });

    const initialStreamUrl = videoWrap?.dataset.streamUrl;
    if (initialStreamUrl) startStream(initialStreamUrl);
})();
</script>
@endsection
