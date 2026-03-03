@extends('layouts.pornguru')

@section('title', __('DMCA Policy') . ' - PornGuru.cam')
@section('meta_description', 'PornGuru.cam DMCA takedown policy. Learn how to submit copyright infringement notices.')
@section('canonical', url('/dmca'))

@section('content')
<div class="legal-page">
    <div class="container">
        <h1>{{ __('DMCA Policy') }}</h1>
        <p class="legal-updated">Last updated: {{ now()->format('F j, Y') }}</p>

        <section>
            <h2>Overview</h2>
            <p>NetHub NV respects the intellectual property rights of others and expects its users to do the same. PornGuru.cam operates as a live cam aggregator &mdash; we index and display publicly available information from third-party cam platforms. We do not host any video content, streams, or user-uploaded media on our servers.</p>
            <p>If you believe that content accessible through our Site infringes your copyright, please follow the procedure below.</p>
        </section>

        <section>
            <h2>Filing a DMCA Takedown Notice</h2>
            <p>To file a copyright infringement notice under the Digital Millennium Copyright Act (DMCA), please send a written notification to our designated agent containing the following information:</p>
            <ol>
                <li>Identification of the copyrighted work you claim has been infringed</li>
                <li>Identification of the material on our Site that you claim is infringing, with sufficient detail for us to locate it (e.g., the URL)</li>
                <li>Your contact information, including name, address, telephone number, and email address</li>
                <li>A statement that you have a good faith belief that the use of the material is not authorized by the copyright owner, its agent, or the law</li>
                <li>A statement, under penalty of perjury, that the information in your notice is accurate and that you are the copyright owner or authorized to act on behalf of the owner</li>
                <li>Your physical or electronic signature</li>
            </ol>
        </section>

        <section>
            <h2>Important Note</h2>
            <p>Since PornGuru.cam does not host content directly, most copyright claims should be directed to the source platform where the content originates (e.g., Stripchat, BongaCams, XLoveCam). We can assist you in identifying the appropriate platform and contact information for your takedown request.</p>
            <p>For content that is within our control (such as model profile descriptions, images cached on our servers, or any original content we produce), we will promptly remove or disable access to the infringing material upon receiving a valid DMCA notice.</p>
        </section>

        <section>
            <h2>Counter-Notification</h2>
            <p>If you believe that material was removed or disabled by mistake or misidentification, you may file a counter-notification containing:</p>
            <ol>
                <li>Identification of the material that was removed and its location before removal</li>
                <li>A statement under penalty of perjury that you have a good faith belief the material was removed by mistake or misidentification</li>
                <li>Your name, address, and telephone number</li>
                <li>A statement consenting to the jurisdiction of the courts in Cura&ccedil;ao</li>
                <li>Your physical or electronic signature</li>
            </ol>
        </section>

        <section>
            <h2>Repeat Infringers</h2>
            <p>We maintain a policy of terminating access for users who are repeat copyright infringers in appropriate circumstances.</p>
        </section>

        <section>
            <h2>Designated Agent</h2>
            <p>Please send all DMCA notices to:</p>
            <address>
                <strong>DMCA Agent &mdash; NetHub NV</strong><br>
                Perseusweg 40<br>
                Willemstad, Cura&ccedil;ao<br>
                Email: <a href="mailto:contact@pornguru.com">contact@pornguru.com</a>
            </address>
        </section>
    </div>
</div>
@endsection
