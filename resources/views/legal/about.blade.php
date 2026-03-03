@extends('layouts.pornguru')

@section('title', __('About') . ' - PornGuru.cam')
@section('meta_description', 'About PornGuru.cam — a live cam aggregator that helps you discover and compare thousands of models across top adult platforms.')
@section('canonical', url('/about'))

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'NetHub NV',
        'url' => config('app.url'),
        'logo' => asset('favicon-nobg.png'),
        'description' => 'NetHub NV operates PornGuru.cam, a live cam model aggregator and discovery platform.',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Perseusweg 40',
            'addressLocality' => 'Willemstad',
            'addressCountry' => 'CW',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'email' => 'contact@pornguru.com',
            'contactType' => 'customer support',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
@endpush

@section('content')
    <div class="legal-page">
        <div class="container">
            <h1>{{ __('About PornGuru.cam') }}</h1>

            <section>
                <h2>Who am i</h2>
                <p>
                    I am your spiritual guide and authority on the adult internet space, after undergoing my awakening i
                    realised i must create the best network of porn sites this world has to offer
                    after being in the industry for two decades now it is time i bring to my visitors and adepts temples in
                    which they can find the same enlightenments i have gone through
                    you are here in one such temple welcome my dear disciple.
                </p>
            </section>

            <section>
                <h2>What is Pornguru.cam</h2>
                <p>
                    PornGuru.cam is one of the the temples of the pornguru a live cam aggregator, search engine and
                    discovery platform. We bring together thousands of cam models
                    from leading adult platforms into one easy-to-browse directory, updated in real time. My goal is to help
                    you find the best livecam experience tailored to you, whether you're looking for a specific type of
                    performer,
                    a particular niche tags giving you a 360 CIA view of the sex cams industry</p>
                <p>
                    it also provides you great realtime insights on which livecam rooms are trending which free chat rooms
                    have goals close to completion in order to benefits from the best free shows without having to browse
                    through them one by one on other platforms a big time saver.
                </p>
            </section>

            <section>
                <h2>How It Workk</h2>
                <p>We continuously sync data from multiple cam platforms to provide you with:</p>
                <ul>
                    <li><strong>Real-time status</strong> &mdash; See which models are online right now with live viewer
                        counts and stream previews</li>
                    <li><strong>Smart filtering</strong> &mdash; Browse by tags, niches, countries, and platforms to find
                        exactly what you want</li>
                    <li><strong>Schedule insights</strong> &mdash; Our broadcast heatmaps analyze weeks of activity data to
                        show you when your favorite models are most likely to be online</li>
                    <li><strong>Goal insights</strong> &mdash; We keep track of the history of completed roomgoals for you
                        make sure you don't miss out on the show</li>

                    <li><strong>Multi-language support</strong> &mdash; Available in over 15 languages to serve a global
                        audience</li>
                    <li><strong>Favorites</strong> &mdash; Create an account to save your favorite models and access them
                        from any device</li>

                    <li><strong>Ratings</strong> &mdash; Create an account to leave public ratings and comments</li>
                    <li><strong>Cam Scrolling</strong> &mdash; its like tiktok but for sex cams </li>
                    <li><strong>Live Community Chat</strong> &mdash; Create an account to talk in the live chat of the
                        template coordinate raids with other users on cam rooms and get the most out of livecams</li>
                </ul>
            </section>

            <section>
                <h2>Our Approach</h2>
                <p>I believe in providing a useful, honest, and transparent service. to help my adepts make informed
                    choices. Every model profile links directly to their official platform page where you can watch their
                    live shows.</p>
                <p>I invest in data quality and site performance to make sure the information you see is as accurate and
                    up-to-date as possible. Our model data refreshes every few minutes, and we use engagement metrics to
                    surface the most popular and active performers.</p>
                
                <p>
                    All the contents and live streams are provided by the platforms directly to pornguru
                    through their affiliate programs and they are the 
                </p>
            </section>

            <section>
                <h2>Legal</h2>
                <p>PornGuru.cam is operated by <strong>NetHub NV</strong>, a company registered in Cura&ccedil;ao.</p>
                <address>
                    <strong>NetHub NV</strong><br>
                    Perseusweg 40<br>
                    Willemstad, Cura&ccedil;ao
                </address>
            </section>

            <section>
                <h2>Contact Us</h2>
                <p>We'd love to hear from you. Whether you have a question, feedback, or a business inquiry, reach out at:
                </p>
                <a href="mailto:contact@pornguru.com">contact@pornguru.com</a>
            </section>
        </div>
    </div>
@endsection