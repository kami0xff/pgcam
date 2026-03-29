<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(in_array(app()->getLocale(), config('locales.rtl', [])))dir="rtl"@endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('seo.default_title'))</title>
    <meta name="description" content="@yield('meta_description', __('seo.default_desc'))">
    @hasSection('meta_robots')<meta name="robots" content="@yield('meta_robots')">
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon-nobg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- RTA Label (Restricted to Adults - parental filtering) -->
    <meta name="RATING" content="RTA-5042-1996-1400-1577-RTA" />

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Open Graph -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="PornGuru.cam">
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', __('seo.default_title')))">
    <meta property="og:description" content="@yield('og_description', View::yieldContent('meta_description', __('seo.default_desc')))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    <meta property="og:image:width" content="@yield('og_image_width', '800')">
    <meta property="og:image:height" content="@yield('og_image_height', '600')">
    @endif
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
    <meta name="twitter:title" content="@yield('og_title', View::yieldContent('title', __('seo.default_title')))">
    <meta name="twitter:description" content="@yield('og_description', View::yieldContent('meta_description', __('seo.default_desc')))">
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

    @if(config('services.google.gtm_id'))
    <!-- Google Tag Manager -->
    <script>
        window.dataLayer = window.dataLayer || [];
        @if(session('ga_event'))
        dataLayer.push({
            'event': {!! json_encode(session('ga_event.name')) !!},
            @foreach(session('ga_event.params', []) as $key => $val)
            {{ json_encode($key) }}: {!! json_encode($val) !!},
            @endforeach
        });
        @endif
    </script>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ config('services.google.gtm_id') }}');</script>
    <!-- End Google Tag Manager -->
    @elseif(config('services.google.analytics_id'))
    <!-- Fallback: GA4 via gtag.js (when GTM is not configured) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google.analytics_id') }}');
    </script>
    @endif

    <!-- DataLayer event tracking (works with both GTM and gtag.js) -->
    @if(config('services.google.gtm_id') || config('services.google.analytics_id'))
    <script>
        window.dataLayer = window.dataLayer || [];
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                var card = e.target.closest('.model-card');
                if (card) {
                    dataLayer.push({
                        'event': 'select_content',
                        'content_type': 'model',
                        'item_id': card.dataset.modelName || card.dataset.modelId || '',
                        'model_name': card.dataset.modelName || '',
                        'model_status': card.dataset.modelStatus || '',
                        'page_section': card.closest('[data-section]') ? card.closest('[data-section]').dataset.section : 'unknown'
                    });
                }
            });

            document.addEventListener('click', function(e) {
                var link = e.target.closest('a[data-affiliate], a[href*="stripchat"], a[href*="xlovecam"], a[href*="stripguru"], a[href*="bongacams"]');
                if (link) {
                    dataLayer.push({
                        'event': 'affiliate_click',
                        'link_url': link.href,
                        'link_domain': link.hostname,
                        'model_name': link.dataset.modelName || (link.closest('[data-model-name]') ? link.closest('[data-model-name]').dataset.modelName : ''),
                        'affiliate_platform': link.dataset.affiliate || '',
                        'link_text': link.textContent.trim().substring(0, 50)
                    });
                }
            });

            document.addEventListener('click', function(e) {
                var favBtn = e.target.closest('.model-card-favorite');
                if (favBtn) {
                    var wasFavorited = favBtn.classList.contains('is-favorited');
                    var card = favBtn.closest('.model-card-wrapper');
                    var modelCard = card ? card.querySelector('.model-card') : null;
                    dataLayer.push({
                        'event': wasFavorited ? 'remove_from_wishlist' : 'add_to_wishlist',
                        'content_type': 'model',
                        'item_id': modelCard ? (modelCard.dataset.modelName || modelCard.dataset.modelId) : '',
                        'model_name': modelCard ? (modelCard.dataset.modelName || '') : ''
                    });
                }
            });
        });
    </script>
    @endif

    @production
    <!-- OpenReplay -->
    <script>
      var initOpts = {
        projectKey: "ZxvDwu52DB5EuuvEhjYC",
        defaultInputMode: 0,
        obscureTextNumbers: false,
        obscureTextEmails: false,
      };
      var startOpts = { userID: {!! json_encode(auth()->check() ? (string) auth()->user()->id : '') !!} };
      (function(A,s,a,y,e,r){
        r=window.OpenReplay=[e,r,y,[s-1, e]];
        s=document.createElement('script');s.src=A;s.async=!a;
        document.getElementsByTagName('head')[0].appendChild(s);
        r.start=function(v){r.push([0])};
        r.stop=function(v){r.push([1])};
        r.setUserID=function(id){r.push([2,id])};
        r.setUserAnonymousID=function(id){r.push([3,id])};
        r.setMetadata=function(k,v){r.push([4,k,v])};
        r.event=function(k,p,i){r.push([5,k,p,i])};
        r.issue=function(k,p){r.push([6,k,p])};
        r.isActive=function(){return false};
        r.getSessionToken=function(){};
      })("//static.openreplay.com/latest/openreplay.js",1,0,initOpts,startOpts);
      @auth
      window.OpenReplay.setUserID({!! json_encode(auth()->user()->email) !!});
      window.OpenReplay.setMetadata('name', {!! json_encode(auth()->user()->name) !!});
      @endauth
    </script>
    @endproduction
</head>
<body>
    @if(config('services.google.gtm_id'))
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google.gtm_id') }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    @endif

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
