@extends('layouts.pornguru')

@section('title', __('Contact Us') . ' - PornGuru.cam')
@section('meta_description', 'Contact PornGuru.cam. Reach out for general inquiries, copyright issues, or privacy concerns.')
@section('canonical', url('/contact'))

@push('head')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        'name' => 'Contact PornGuru.cam',
        'url' => url('/contact'),
        'mainEntity' => [
            '@type' => 'Organization',
            'name' => 'NetHub NV',
            'email' => 'contact@pornguru.com',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Perseusweg 40',
                'addressLocality' => 'Willemstad',
                'addressCountry' => 'CW',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
    <div class="legal-page">
        <div class="container">
            <h1>{{ __('Contact Us') }}</h1>

            <section>
                <p>Have a question, concern, or business inquiry? We're here to help. Please reach out using the appropriate
                    email address below and we'll respond as soon as possible.</p>
            </section>

            <section>
                <h2>General Inquiries </h2>
                <p>For questions about PornGuru.cam, feedback, suggestions, or partnership opportunities:</p>

                <h2>Copyright &amp; DMCA</h2>
                <p>To report a copyright infringement or submit a DMCA takedown notice, please review our <a
                        href="{{ url('/dmca') }}">DMCA Policy</a> and send your notice to:</p>


                <h2>Privacy</h2>
                <p>For questions about how we handle your data, or to exercise your data rights as described in our <a
                        href="{{ url('/privacy') }}">Privacy Policy</a>:</p>


                <h2>Legal</h2>
                <p>For legal matters, including compliance and regulatory questions:</p>

                <p><a href="mailto:contact@pornguru.com">contact@pornguru.com</a></p>
            </section>

            <section>
                <h2>Mailing Address</h2>
                <address>
                    <strong>NetHub NV</strong><br>
                    Perseusweg 40<br>
                    Willemstad, Cura&ccedil;ao
                </address>
            </section>
        </div>
    </div>
@endsection