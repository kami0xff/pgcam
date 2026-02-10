@extends('layouts.pornguru')

@section('title', __('Cam Tags') . ' - ' . __('Browse by Category'))

@section('meta_description'){{ __('Browse live cam models by category. Find the perfect cam show with our extensive tag system.') }}@endsection

@push('seo-pagination')
<x-seo.schema :schemas="$seoSchemas" />
@endpush

@section('content')
<div class="container page-section">
    {{-- Breadcrumbs --}}
    <nav class="breadcrumbs">
        <a href="{{ route('home') }}" class="breadcrumb-link">{{ __('Home') }}</a>
        <span class="breadcrumbs-separator">/</span>
        <span class="breadcrumb-current">{{ __('Tags') }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-title-bar">
        <div class="page-title-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/>
            </svg>
        </div>
        <h1 class="page-title-text">{{ __('BROWSE BY TAG') }}</h1>
    </div>

    {{-- Top SEO Content --}}
    <x-seo.content-block pageKey="tags_index" position="top" />

    {{-- Popular Tags --}}
    @if(count($featuredTags) > 0)
        <section class="tags-section">
            <h2 class="tags-section-title">{{ __('Popular Tags') }}</h2>
            <div class="tags-cloud">
                @foreach($featuredTags as $tag)
                    @php
                        $slug = is_array($tag) ? $tag['slug'] : $tag->slug;
                        $name = is_array($tag) ? $tag['localized_name'] : $tag->localized_name;
                        $count = is_array($tag) ? ($tag['models_count'] ?? 0) : ($tag->models_count ?? 0);
                    @endphp
                    <a href="{{ route('niche.tag', ['niche' => 'girls', 'tagSlug' => $slug]) }}" class="tag-pill tag-pill-featured">
                        {{ $name }}
                        @if($count > 0)
                            <span class="tag-pill-count">{{ number_format($count) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Tags by Category in Columns --}}
    <div class="tags-categories-grid">
        @foreach($tagsByCategory as $category => $tags)
            <section class="tags-category-box">
                <h2 class="tags-category-title">{{ __(ucfirst($category ?? 'Other')) }}</h2>
                <ul class="tags-column-list">
                    @foreach($tags as $tag)
                        @php
                            $slug = is_array($tag) ? $tag['slug'] : $tag->slug;
                            $name = is_array($tag) ? $tag['localized_name'] : $tag->localized_name;
                        @endphp
                        @php
                            $count = is_array($tag) ? ($tag['models_count'] ?? 0) : ($tag->models_count ?? 0);
                        @endphp
                        <li class="tags-column-item">
                            <a href="{{ route('niche.tag', ['niche' => 'girls', 'tagSlug' => $slug]) }}" class="tags-column-link">
                                <span class="tag-name">{{ $name }}</span>
                                @if($count > 0)
                                    <span class="tags-column-count">{{ number_format($count) }}</span>
                                @endif
                            </a>
                            <span class="tags-column-niches">
                                <a href="{{ route('niche.tag', ['niche' => 'girls', 'tagSlug' => $slug]) }}" title="{{ __('Girls') }}">G</a>
                                <a href="{{ route('niche.tag', ['niche' => 'couples', 'tagSlug' => $slug]) }}" title="{{ __('Couples') }}">C</a>
                                <a href="{{ route('niche.tag', ['niche' => 'men', 'tagSlug' => $slug]) }}" title="{{ __('Men') }}">M</a>
                                <a href="{{ route('niche.tag', ['niche' => 'trans', 'tagSlug' => $slug]) }}" title="{{ __('Trans') }}">T</a>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    {{-- Bottom SEO Content --}}
    <x-seo.content-block pageKey="tags_index" position="bottom" />
</div>
@endsection
