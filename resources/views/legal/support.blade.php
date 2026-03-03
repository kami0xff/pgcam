@extends('layouts.pornguru')

@section('title', __('Good Causes') . ' - PornGuru.cam')
@section('meta_description', 'PornGuru.cam is a proud supporter of ASACP, the RTA labeling initiative, and Pineapple Support. Learn about our commitment to child protection, responsible labeling, and performer mental health.')
@section('canonical', url('/good-causes'))

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => 'Good Causes',
        'description' => 'PornGuru.cam supports ASACP, RTA, and Pineapple Support — organizations dedicated to child protection, responsible content labeling, and adult industry mental health.',
        'url' => url('/good-causes'),
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'NetHub NV',
            'url' => config('app.url'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
@endpush

@section('content')
    <div class="legal-page">
        <div class="container">
            <h1>{{ __('Good Causes') }}</h1>
            <p>At PornGuru.cam we believe that operating in the adult industry comes with a responsibility to make it safer, more transparent, and more humane. We financially support and actively participate in the following organizations.</p>

            <section>
                <div class="advocacy-highlight">
                    <div class="advocacy-highlight-icon">
                        <img src="{{ asset('img/asacp.webp') }}" alt="ASACP" height="48" loading="lazy">
                    </div>
                    <div>
                        <h2>ASACP &mdash; Association of Sites Advocating Child Protection</h2>
                        <p><a href="https://www.asacp.org/" target="_blank" rel="nofollow noopener">asacp.org</a></p>
                    </div>
                </div>
                <p>ASACP is a nonprofit organization founded in 1996 that battles child sexual exploitation online. They operate a reporting tipline that forwards suspected illegal content to law enforcement including the FBI and the National Center for Missing &amp; Exploited Children (NCMEC).</p>
                <p>As an ASACP member, PornGuru.cam adheres to their Code of Ethics, which sets strict standards for responsible operation in the adult space. Our membership demonstrates our zero-tolerance stance toward illegal content and our commitment to proactive child protection.</p>
                <ul>
                    <li>We display the ASACP member badge and encourage users to report suspected illegal content via their <a href="https://www.asacp.org/index.html?content=report" target="_blank" rel="nofollow noopener">online tipline</a></li>
                    <li>We comply with the ASACP Code of Ethics for responsible adult website operation</li>
                    <li>Our financial contribution supports ASACP's investigative and educational programs</li>
                </ul>
            </section>

            <section>
                <div class="advocacy-highlight">
                    <div class="advocacy-highlight-icon">
                        <img src="{{ asset('img/rta.gif') }}" alt="RTA Label" height="48" loading="lazy">
                    </div>
                    <div>
                        <h2>RTA &mdash; Restricted to Adults</h2>
                        <p><a href="https://www.rtalabel.org/" target="_blank" rel="nofollow noopener">rtalabel.org</a></p>
                    </div>
                </div>
                <p>The RTA (Restricted To Adults) label is a free, voluntary website classification system created by ASACP. It embeds a machine-readable meta tag in the page source that parental filtering software, browsers, ISPs, and search engines can detect to block access for minors.</p>
                <p>PornGuru.cam implements the RTA label on every page of our site. This means that parents who have configured filtering tools on their devices can automatically prevent their children from accessing our content.</p>
                <ul>
                    <li>The <code>&lt;meta name="rating" content="RTA-5042-1996-1400-1577-RTA" /&gt;</code> tag is present on every page</li>
                    <li>This enables automatic detection by parental control software and safe-search filters</li>
                    <li>We encourage all parents to install appropriate filtering software on devices used by minors</li>
                </ul>
            </section>

            <section>
                <div class="advocacy-highlight">
                    <div class="advocacy-highlight-icon">
                        <img src="{{ asset('img/pineapple-support.svg') }}" alt="Pineapple Support" height="48" loading="lazy">
                    </div>
                    <div>
                        <h2>Pineapple Support</h2>
                        <p><a href="https://pineapplesupport.org/" target="_blank" rel="nofollow noopener">pineapplesupport.org</a></p>
                    </div>
                </div>
                <p>Pineapple Support is a 501(c)(3) nonprofit founded in 2018 that provides free and subsidized mental health services to performers and workers in the adult industry. Named after a commonly used safeword, the organization was created in response to a tragic wave of deaths in the industry from mental illness and addiction.</p>
                <p>Their network of nearly 500 sex-worker-friendly, kink-aware therapists has helped over 10,000 people access therapy sessions, support groups, crisis intervention, and educational resources. PornGuru.cam is a proud financial sponsor of Pineapple Support because we believe that the wellbeing of the people who make this industry possible should be a priority for every platform that profits from it.</p>
                <ul>
                    <li>Our sponsorship helps fund free therapy sessions for performers who could not otherwise afford them</li>
                    <li>Pineapple Support offers 24/7 text-based crisis support, online therapy, and in-person sessions</li>
                    <li>If you or someone you know in the adult industry needs mental health support, visit <a href="https://pineapplesupport.org/get-support/" target="_blank" rel="nofollow noopener">pineapplesupport.org/get-support</a></li>
                </ul>
            </section>

            <section>
                <h2>Why This Matters</h2>
                <p>The adult industry serves hundreds of millions of users worldwide. We believe it should operate with the same ethical standards as any other major industry. By financially supporting these organizations, we aim to:</p>
                <ul>
                    <li><strong>Protect children</strong> from accidental or intentional exposure to adult content through labeling and proactive enforcement</li>
                    <li><strong>Support performers</strong> by funding accessible mental health resources for the people at the heart of the industry</li>
                    <li><strong>Set an example</strong> for other platforms that responsible operation and commercial success are not mutually exclusive</li>
                </ul>
                <p>If you operate an adult website and are not yet involved with these organizations, we strongly encourage you to <a href="https://www.asacp.org/page.php?content=apply" target="_blank" rel="nofollow noopener">become an ASACP member</a>, <a href="https://www.rtalabel.org/" target="_blank" rel="nofollow noopener">implement the RTA label</a>, and <a href="https://pineapplesupport.org/donate/" target="_blank" rel="nofollow noopener">support Pineapple Support</a>.</p>
            </section>

            <section>
                <h2>Contact</h2>
                <p>Questions about our advocacy efforts? Reach out at <a href="mailto:contact@pornguru.com">contact@pornguru.com</a>.</p>
            </section>
        </div>
    </div>
@endsection
