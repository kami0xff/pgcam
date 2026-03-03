@extends('layouts.pornguru')

@section('title', '18 U.S.C. § 2257 Compliance - PornGuru.cam')
@section('meta_description', 'PornGuru.cam 18 U.S.C. § 2257 record-keeping requirements exemption statement.')
@section('canonical', url('/2257'))

@section('content')
<div class="legal-page">
    <div class="container">
        <h1>18 U.S.C. &sect; 2257 Compliance Statement</h1>
        <p class="legal-updated">Last updated: {{ now()->format('F j, Y') }}</p>

        <section>
            <h2>Exemption Statement</h2>
            <p>PornGuru.cam, operated by NetHub NV, is not a producer (primary or secondary) of any visual content displayed on the website. PornGuru.cam operates exclusively as a live cam aggregator that indexes and displays publicly available, real-time information from third-party adult cam platforms.</p>
        </section>

        <section>
            <h2>Our Role</h2>
            <p>PornGuru.cam functions as a directory and aggregation service. We:</p>
            <ul>
                <li>Do not produce, host, or store any sexually explicit visual content</li>
                <li>Do not employ or contract any performers</li>
                <li>Do not create, film, or distribute any visual depictions of actual sexually explicit conduct</li>
                <li>Index publicly available metadata (usernames, status, viewer counts) from third-party platforms</li>
                <li>Display thumbnail images and preview data provided by the source platforms' public APIs</li>
            </ul>
        </section>

        <section>
            <h2>Third-Party Platform Responsibility</h2>
            <p>All live cam streams, performer content, and associated media originate from and are hosted by third-party platforms. These platforms are responsible for compliance with 18 U.S.C. &sect; 2257 record-keeping requirements, including:</p>
            <ul>
                <li>Verifying the age and identity of all performers</li>
                <li>Maintaining the required records as specified by law</li>
                <li>Designating a custodian of records</li>
            </ul>
            <p>Users clicking through to third-party platforms should consult those platforms' own 2257 compliance statements for record-keeping custodian information.</p>
        </section>

        <section>
            <h2>Contact</h2>
            <p>For questions regarding this compliance statement:</p>
            <address>
                <strong>NetHub NV</strong><br>
                Perseusweg 40<br>
                Willemstad, Cura&ccedil;ao<br>
                Email: <a href="mailto:contact@pornguru.com">contact@pornguru.com</a>
            </address>
        </section>
    </div>
</div>
@endsection
