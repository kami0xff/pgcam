@extends('layouts.pornguru')

@php
    $countryName = isset($country->localized_name) ? $country->localized_name : $country->name;
    $countryUrl = isset($country->url) ? $country->url : localized_route('countries.show', $country->slug);
    $countryFlag = isset($country->flag) ? $country->flag : \App\Helpers\FlagHelper::getFlag($country->code ?? '');
    $countryCode = $country->code ?? '';
    $modelsCount = isset($country->models_count) ? $country->models_count : $models->total();
@endphp

@section('title'){{ $translation?->meta_title ?? $countryName . ' Cams: Free Live ' . $countryName . ' Sex Chat | PornGuru' }}@endsection

@section('meta_description'){{ $translation?->meta_description ?? "Watch {$modelsCount}+ live cam models from {$countryName} free! 🔥 {$countryName} sex cams with free chat & HD streams on PornGuru.cam ❤️" }}@endsection

@section('canonical'){{ $models->currentPage() > 1 ? $models->url($models->currentPage()) : $countryUrl }}@endsection

@push('seo-pagination')
<x-seo.schema :schemas="$seoSchemas" />
@if(!empty($hreflangUrls) && $models->currentPage() === 1)
<x-seo.hreflang :urls="$hreflangUrls" />
@endif
@if($models->currentPage() > 1)
    <link rel="prev" href="{{ $models->previousPageUrl() }}">
@endif
@if($models->hasMorePages())
    <link rel="next" href="{{ $models->nextPageUrl() }}">
@endif
@endpush

@section('content')
<div class="container page-section">
    <x-seo.breadcrumbs :items="[
        ['name' => 'Home', 'url' => localized_route('home')],
        ['name' => 'Countries', 'url' => localized_route('countries.index')],
        ['name' => $countryName, 'url' => $countryUrl],
    ]" />

    <div class="page-title-bar">
        <span class="page-title-flag">{{ $countryFlag }}</span>
        <h1 class="page-title-text">{{ $countryName }} {{ __('countries.cams') }}</h1>
    </div>

    <div class="page-stats">
        <span class="page-stats-total">{{ number_format($modelsCount) }} {{ __('common.models') }}</span>
        <span class="page-stats-separator">•</span>
        <span class="page-stats-online">{{ $models->where('is_online', true)->count() }} {{ __('common.online_now') }}</span>
    </div>

    {{-- Country-specific SEO content (from translation) --}}
    @if($translation?->page_content)
        <section class="seo-text-block seo-text-top">
            <h2 class="seo-text-title">{{ $countryFlag }} {{ __('countries.live_cams_from') }} {{ $countryName }}</h2>
            <div class="seo-text-content">
                {!! nl2br(e($translation->page_content)) !!}
            </div>
        </section>
    @endif

    {{-- Top SEO Content Block --}}
    <x-seo.content-block pageKey="country_{{ $countryCode }}" position="top" />

    {{-- Results Count with Preview Toggle --}}
    <div class="results-bar">
        <x-pornguru.results-count :paginator="$models" />
        <div class="preview-controls">
            <button class="preview-toggle" id="preview-toggle" onclick="toggleAllPreviews()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="preview-off">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                <svg viewBox="0 0 24 24" fill="currentColor" class="preview-on" style="display: none;">
                    <rect x="6" y="4" width="4" height="16"/>
                    <rect x="14" y="4" width="4" height="16"/>
                </svg>
                <span id="preview-label">{{ __('common.play_all') }}</span>
            </button>
        </div>
    </div>

    @if($models->isEmpty())
        <x-pornguru.empty-state 
            title="{{ __('common.no_models_found') }}" 
            text="{{ __('countries.no_models_from_country') }}" 
        />
    @else
        <div class="models-grid" id="models-grid">
            @foreach($models as $model)
                <x-pornguru.model-card :model="$model" />
            @endforeach
        </div>

        {{-- Infinite Scroll Loader --}}
        @if($models->hasMorePages())
            <div class="infinite-scroll-loader" id="infinite-loader">
                <div class="loader-spinner"></div>
                <span>{{ __('common.loading_more_models') }}</span>
            </div>
        @endif

        {{-- Infinite Scroll Trigger --}}
        <div id="infinite-scroll-trigger"></div>

        {{-- SEO Pagination --}}
        <nav class="seo-pagination" aria-label="Pagination">
            @if($models->currentPage() > 1)
                <a href="{{ $models->previousPageUrl() }}" rel="prev">{{ __('pagination.previous_page') }}</a>
            @endif
            
            <span>{{ __('pagination.page') }} {{ $models->currentPage() }} {{ __('pagination.of') }} {{ $models->lastPage() }}</span>
            
            @if($models->hasMorePages())
                <a href="{{ $models->nextPageUrl() }}" rel="next">{{ __('pagination.next_page') }}</a>
            @endif
        </nav>
    @endif

    {{-- Bottom SEO text for country page --}}
    <x-seo.content-block pageKey="country_{{ $countryCode }}" position="bottom" class="seo-text-bottom" />
</div>

{{-- Stream Preview Manager --}}
<script src="{{ asset('js/stream-previews.js') }}"></script>
<script>
    window.infiniteScrollConfig = {
        apiUrl: '{{ route('api.models.load') }}',
        currentPage: {{ $models->currentPage() }},
        hasMore: {{ $models->hasMorePages() ? 'true' : 'false' }},
        filters: {
            country: '{{ $country->slug ?? '' }}'
        }
    };
</script>
@endsection
