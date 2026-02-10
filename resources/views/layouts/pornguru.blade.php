<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(in_array(app()->getLocale(), config('locales.rtl', [])))dir="rtl"@endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PornGuru - The Ultimate Adult Guide')</title>
    <meta name="description" content="@yield('meta_description', 'Watch free live cam shows from the hottest models. Browse thousands of live sex cams on PornGuru.cam')">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- SEO Pagination (for search engines to crawl all pages) -->
    @stack('seo-pagination')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Head Content (JSON-LD, etc.) -->
    @stack('head')
</head>
<body>
    <div class="page-wrapper">
        <x-pornguru.header />

        <main class="main-content">
            @yield('content')
        </main>

        <x-pornguru.footer />
    </div>
</body>
</html>
