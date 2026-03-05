@extends('layouts.pornguru')

@section('title', __('common.frequently_asked_questions') . ' - PornGuru.cam')
@section('meta_description', __('faq.meta_description'))
@section('canonical', url('/faq'))

@push('head')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => __('faq.q_what_is'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_what_is'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_is_free'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_is_free'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_how_works'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_how_works'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_need_account'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_need_account'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_platforms'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_platforms'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_update_frequency'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_update_frequency'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_privacy'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_privacy'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_heatmaps'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_heatmaps'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_explore'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_explore'),
                ],
            ],
            [
                '@type' => 'Question',
                'name' => __('faq.q_contact'),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => __('faq.a_contact'),
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
    <div class="legal-page">
        <div class="container">
            <h1>{{ __('common.frequently_asked_questions') }}</h1>
            <p class="faq-intro">{{ __('faq.intro') }}</p>

            <div class="faq-list">
                @php
                    $faqKeys = [
                        'what_is', 'is_free', 'how_works', 'need_account', 'platforms',
                        'update_frequency', 'privacy', 'heatmaps', 'explore', 'contact',
                    ];
                @endphp

                @foreach($faqKeys as $i => $key)
                <details class="faq-item" {{ $i === 0 ? 'open' : '' }}>
                    <summary class="faq-question">
                        <span>{{ __("faq.q_{$key}") }}</span>
                        <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </summary>
                    <div class="faq-answer">
                        <p>{{ __("faq.a_{$key}") }}</p>
                    </div>
                </details>
                @endforeach
            </div>
        </div>
    </div>
@endsection
