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
                <details class="faq-item" open>
                    <summary class="faq-question">{{ __('faq.q_what_is') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_what_is') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_is_free') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_is_free') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_how_works') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_how_works') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_need_account') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_need_account') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_platforms') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_platforms') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_update_frequency') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_update_frequency') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_privacy') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_privacy') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_heatmaps') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_heatmaps') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_explore') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_explore') }}</p>
                    </div>
                </details>

                <details class="faq-item">
                    <summary class="faq-question">{{ __('faq.q_contact') }}</summary>
                    <div class="faq-answer">
                        <p>{{ __('faq.a_contact') }}</p>
                    </div>
                </details>
            </div>
        </div>
    </div>
@endsection
