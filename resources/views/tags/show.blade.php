@extends('layouts.pornguru')

@section('title', ($translation?->meta_title ?? $tag->localized_name . ' Cams'))

@section('meta_description'){{ $translation?->meta_description ?? "Watch live {$tag->localized_name} cam shows. Browse {$tag->models_count} models." }}@endsection

@section('canonical'){{ $tag->url }}@endsection

@push('seo-pagination')
<x-seo.schema :schemas="$seoSchemas" />
<x-seo.hreflang :urls="$hreflangUrls" />
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
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Tags', 'url' => route('tags.index')],
        ['name' => $tag->localized_name, 'url' => $tag->url],
    ]" />

    <div class="page-title-bar">
        <h1 class="page-title-text">{{ $tag->localized_name }} Cams</h1>
    </div>

    <div class="page-stats">
        <span class="page-stats-total">{{ number_format($tag->models_count) }} models</span>
        <span class="page-stats-separator">•</span>
        <span class="page-stats-online">{{ $models->where('is_online', true)->count() }} online now</span>
    </div>

    {{-- Tag-specific SEO content (from translation) --}}
    @if($translation?->page_content)
        <section class="seo-text-block seo-text-top">
            <h2 class="seo-text-title">About {{ $tag->localized_name }} Cams</h2>
            <div class="seo-text-content">
                {!! nl2br(e($translation->page_content)) !!}
            </div>
        </section>
    @endif

    @if($models->isEmpty())
        <x-pornguru.empty-state 
            title="No models found" 
            text="No models with this tag are currently available." 
        />
    @else
        <div class="models-grid">
            @foreach($models as $model)
                <x-pornguru.model-card :model="$model" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrapper">
            {{ $models->links() }}
        </div>
    @endif

    {{-- Bottom SEO text for category page --}}
    <x-seo.content-block pageKey="tag_{{ $tag->slug }}" position="bottom" class="seo-text-bottom" />
</div>
@endsection
