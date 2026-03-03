@extends('layouts.pornguru')

@section('title', __('Terms of Service') . ' - PornGuru.cam')
@section('meta_description', 'PornGuru.cam terms of service. Read the terms and conditions governing your use of our website.')
@section('canonical', url('/terms'))

@section('content')
<div class="legal-page">
    <div class="container">
        <h1>{{ __('Terms of Service') }}</h1>
        <p class="legal-updated">Last updated: {{ now()->format('F j, Y') }}</p>

        <section>
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using PornGuru.cam ("the Site"), operated by NetHub NV, you agree to be bound by these Terms of Service. If you do not agree to these terms, you must not use the Site.</p>
        </section>

        <section>
            <h2>2. Age Requirement</h2>
            <p>The Site contains adult content and is strictly intended for individuals who are at least 18 years of age (or the age of majority in your jurisdiction, whichever is greater). By using the Site, you represent and warrant that you meet this age requirement. We reserve the right to terminate access for anyone who misrepresents their age.</p>
        </section>

        <section>
            <h2>3. Description of Service</h2>
            <p>PornGuru.cam is a live cam aggregator that indexes and displays publicly available information about cam models from third-party platforms. We do not host, produce, or distribute any adult content directly. All live streams, images, and model profiles originate from their respective source platforms.</p>
        </section>

        <section>
            <h2>4. User Accounts</h2>
            <p>You may create an account to access features such as favorites. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use.</p>
        </section>

        <section>
            <h2>5. Acceptable Use</h2>
            <p>You agree not to:</p>
            <ul>
                <li>Use the Site for any unlawful purpose</li>
                <li>Attempt to gain unauthorized access to any part of the Site or its systems</li>
                <li>Scrape, crawl, or use automated means to collect data from the Site without permission</li>
                <li>Interfere with or disrupt the Site's infrastructure</li>
                <li>Impersonate any person or entity</li>
                <li>Upload or transmit malicious code</li>
            </ul>
        </section>

        <section>
            <h2>6. Intellectual Property</h2>
            <p>The Site's design, layout, code, and original content are the property of NetHub NV and are protected by applicable intellectual property laws. Model images and stream data are the property of their respective platforms and creators.</p>
        </section>

        <section>
            <h2>7. Third-Party Links and Content</h2>
            <p>The Site contains links to third-party cam platforms. We are not responsible for the content, privacy practices, or terms of these external sites. Clicking affiliate links may redirect you to third-party platforms where their own terms apply.</p>
        </section>

        <section>
            <h2>8. Disclaimer of Warranties</h2>
            <p>The Site is provided "as is" and "as available" without warranties of any kind, either express or implied. We do not guarantee that the Site will be uninterrupted, error-free, or free from harmful components. Model availability, online status, and stream information may not always be accurate in real time.</p>
        </section>

        <section>
            <h2>9. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, NetHub NV shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the Site, including but not limited to loss of data, revenue, or profits.</p>
        </section>

        <section>
            <h2>10. Indemnification</h2>
            <p>You agree to indemnify and hold harmless NetHub NV, its officers, directors, and employees from any claims, damages, or expenses arising from your use of the Site or violation of these Terms.</p>
        </section>

        <section>
            <h2>11. Modifications</h2>
            <p>We reserve the right to modify these Terms at any time. Changes will be posted on this page. Your continued use of the Site after modifications constitutes acceptance of the updated Terms.</p>
        </section>

        <section>
            <h2>12. Governing Law</h2>
            <p>These Terms are governed by and construed in accordance with the laws of Cura&ccedil;ao. Any disputes arising from these Terms shall be subject to the exclusive jurisdiction of the courts in Willemstad, Cura&ccedil;ao.</p>
        </section>

        <section>
            <h2>13. Contact</h2>
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
