@extends('layouts.pornguru')

@section('title', $nicheTitle . ' Live Cams' . ($models->currentPage() > 1 ? ' - Page ' . $models->currentPage() : ''))

@section('meta_description')Watch live {{ strtolower($nicheTitle) }} cams. {{ number_format($models->total()) }} {{ strtolower($nicheTitle) }} models streaming now.@endsection

@section('canonical'){{ $models->currentPage() > 1 ? $models->url($models->currentPage()) : route('niche.show', $niche) }}@endsection

@push('seo-pagination')
    @if($models->currentPage() > 1)
        <link rel="prev" href="{{ $models->previousPageUrl() }}">
    @endif
    @if($models->hasMorePages())
        <link rel="next" href="{{ $models->nextPageUrl() }}">
    @endif
@endpush

@section('content')
    <div class="container page-section">
        {{-- Page Header --}}
        <div class="page-title-bar">
            <div class="page-title-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 23a7.5 7.5 0 01-5.138-12.963C8.204 8.774 11.5 6.5 11 1.5c6 4 9 8 3 14 1 0 2.5 0 5-2.47.27.773.5 1.604.5 2.47A7.5 7.5 0 0112 23z"/>
                </svg>
            </div>
            <h1 class="page-title-text">{{ strtoupper($nicheTitle) }} LIVE CAMS</h1>
        </div>
        
        {{-- Stats --}}
        <div class="page-stats">
            <span class="page-stats-total">{{ number_format($models->total()) }} models</span>
            <span class="page-stats-separator">•</span>
            <span class="page-stats-online">streaming now</span>
        </div>

        {{-- Top SEO Content --}}
        <x-seo.content-block pageKey="niche_{{ $niche }}" position="top" />

        {{-- Popular Tags for this Niche --}}
        @if(!empty($popularTags))
            <div class="niche-tags">
                <span class="niche-tags-label">Popular:</span>
                @foreach($popularTags as $tag)
                    <a href="{{ route('niche.tag', [$niche, $tag]) }}" class="niche-tag">
                        {{ ucfirst(str_replace('-', ' ', $tag)) }}
                    </a>
                @endforeach
            </div>
        @endif

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
                    <span id="preview-label">Play All</span>
                </button>
            </div>
        </div>

        {{-- Models Grid --}}
        @if($models->isEmpty())
            <x-pornguru.empty-state 
                title="No models found" 
                text="No {{ strtolower($nicheTitle) }} models are currently online." 
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
                    <span>Loading more models...</span>
                </div>
            @endif

            {{-- Infinite Scroll Trigger --}}
            <div id="infinite-scroll-trigger"></div>

            {{-- SEO Pagination --}}
            <nav class="seo-pagination" aria-label="Pagination">
                @if($models->currentPage() > 1)
                    <a href="{{ $models->previousPageUrl() }}" rel="prev">Previous Page</a>
                @endif
                
                <span>Page {{ $models->currentPage() }} of {{ $models->lastPage() }}</span>
                
                @if($models->hasMorePages())
                    <a href="{{ $models->nextPageUrl() }}" rel="next">Next Page</a>
                @endif
            </nav>
        @endif

        {{-- Bottom SEO Content --}}
        <x-seo.content-block pageKey="niche_{{ $niche }}" position="bottom" />
    </div>

    {{-- HLS.js and Stream Preview Manager --}}
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script src="{{ asset('js/stream-previews.js') }}"></script>
@endsection
