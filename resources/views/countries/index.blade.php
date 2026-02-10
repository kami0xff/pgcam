@extends('layouts.pornguru')

@section('title', 'Cam Models by Country')

@section('meta_description')Browse live cam models from around the world. Find models from your favorite country.@endsection

@push('seo-pagination')
<x-seo.schema :schemas="$seoSchemas" />
@endpush

@section('content')
<div class="container page-section">
    <x-seo.breadcrumbs :items="[
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Countries', 'url' => route('countries.index')],
    ]" />

    <div class="page-title-bar">
        <h1 class="page-title-text">Browse by Country</h1>
    </div>

    {{-- Top SEO Content --}}
    <x-seo.content-block pageKey="countries_index" position="top" class="seo-text-top" />

    <div class="countries-grid">
        @foreach($countries as $country)
            @php
                $url = isset($country->url) ? $country->url : route('countries.show', $country->slug);
                $flag = isset($country->flag) ? $country->flag : \App\Helpers\FlagHelper::getFlag($country->code ?? '');
                $name = isset($country->localized_name) ? $country->localized_name : $country->name;
                $count = isset($country->models_count) ? $country->models_count : 0;
            @endphp
            <a href="{{ $url }}" class="country-card">
                <span class="country-card-flag">{{ $flag }}</span>
                <span class="country-card-name">{{ $name }}</span>
                <span class="country-card-count">{{ number_format($count) }} {{ __('models') }}</span>
            </a>
        @endforeach
    </div>

    {{-- Bottom SEO Content --}}
    <x-seo.content-block pageKey="countries_index" position="bottom" class="seo-text-bottom" />
</div>
@endsection
