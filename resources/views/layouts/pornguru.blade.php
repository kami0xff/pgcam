<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(in_array(app()->getLocale(), config('locales.rtl', [])))dir="rtl"@endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PornGuru - The Ultimate Adult Guide')</title>
    <meta name="description" content="@yield('meta_description', 'Watch free live cam shows from the hottest models. Browse thousands of live sex cams on PornGuru.cam')">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Open Graph -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="PornGuru.cam">
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', 'PornGuru - The Ultimate Adult Guide'))">
    <meta property="og:description" content="@yield('og_description', View::yieldContent('meta_description', 'Watch free live cam shows from the hottest models.'))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    <meta property="og:image:width" content="@yield('og_image_width', '800')">
    <meta property="og:image:height" content="@yield('og_image_height', '600')">
    @endif
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
    <meta name="twitter:title" content="@yield('og_title', View::yieldContent('title', 'PornGuru - The Ultimate Adult Guide'))">
    <meta name="twitter:description" content="@yield('og_description', View::yieldContent('meta_description', 'Watch free live cam shows from the hottest models.'))">
    @hasSection('og_image')
    <meta name="twitter:image" content="@yield('og_image')">
    @endif

    <!-- SEO Pagination (for search engines to crawl all pages) -->
    @stack('seo-pagination')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- HLS.js -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <!-- Additional Head Content (JSON-LD, etc.) -->
    @stack('head')

    <!-- Google Analytics (GA4) -->
    @if(config('services.google.analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google.analytics_id') }}');

        document.addEventListener('DOMContentLoaded', function() {
            // 1. Track model card clicks (homepage, tag pages, favorites)
            document.addEventListener('click', function(e) {
                var card = e.target.closest('.model-card');
                if (card) {
                    gtag('event', 'select_content', {
                        content_type: 'model',
                        item_id: card.dataset.modelName || card.dataset.modelId || '',
                        model_name: card.dataset.modelName || '',
                        model_status: card.dataset.modelStatus || '',
                        page_section: card.closest('[data-section]') ? card.closest('[data-section]').dataset.section : 'unknown'
                    });
                }
            });

            // 2. Track outbound affiliate clicks (whitelabel links)
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a[data-affiliate], a[href*="stripchat"], a[href*="xlovecam"], a[href*="stripguru"]');
                if (link) {
                    gtag('event', 'click', {
                        event_category: 'affiliate_outbound',
                        link_url: link.href,
                        link_domain: link.hostname,
                        model_name: link.dataset.modelName || link.closest('[data-model-name]')?.dataset.modelName || '',
                        affiliate_platform: link.dataset.affiliate || '',
                        link_text: link.textContent.trim().substring(0, 50),
                        transport_type: 'beacon'
                    });
                }
            });

            // 3. Track favorite toggles
            document.addEventListener('click', function(e) {
                var favBtn = e.target.closest('.model-card-favorite');
                if (favBtn) {
                    var wasFavorited = favBtn.classList.contains('is-favorited');
                    var card = favBtn.closest('.model-card-wrapper');
                    var modelCard = card ? card.querySelector('.model-card') : null;
                    gtag('event', wasFavorited ? 'remove_from_wishlist' : 'add_to_wishlist', {
                        content_type: 'model',
                        item_id: modelCard ? (modelCard.dataset.modelName || modelCard.dataset.modelId) : '',
                        model_name: modelCard ? (modelCard.dataset.modelName || '') : ''
                    });
                }
            });
        });
    </script>
    @endif

    {{-- Fire sign_up event after successful registration --}}
    @if(session('ga_event'))
    <script>
        if (typeof gtag === 'function') {
            gtag('event', {!! json_encode(session('ga_event.name')) !!}, {!! json_encode(session('ga_event.params', [])) !!});
        }
    </script>
    @endif
</head>
<body>
    <div class="page-wrapper">
        <x-pornguru.header />

        <main class="main-content">
            @yield('content')
        </main>

        <x-pornguru.footer />
    </div>

    <script>
        // Close language selector on outside click
        document.addEventListener('click', function(e) {
            const sel = document.getElementById('lang-selector');
            if (sel && !sel.contains(e.target)) {
                sel.classList.remove('open');
            }
        });
    </script>
</body>
</html>
