<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="{{ localized_route('home') }}" class="footer-logo">
                    <span class="logo-porn">PORNGURU</span><span class="logo-guru">.CAM</span>
                </a>
                <p class="footer-description">
                    {{ __('footer.description') }}
                </p>
            </div>

            <!-- Browse Column -->
            <div class="footer-column">
                <h3>{{ __('common.browse') }}</h3>
                <ul class="footer-links">
                    <li><a href="{{ localized_route('home') }}">{{ __('common.live_cams') }}</a></li>
                    <li><a href="{{ localized_route('niche.show', 'girls') }}">{{ __('common.girls') }}</a></li>
                    <li><a href="{{ localized_route('niche.show', 'couples') }}">{{ __('common.couples') }}</a></li>
                    <li><a href="{{ localized_route('niche.show', 'men') }}">{{ __('common.men') }}</a></li>
                    <li><a href="{{ localized_route('niche.show', 'trans') }}">{{ __('common.trans') }}</a></li>
                </ul>
            </div>

            <!-- Explore Column -->
            <div class="footer-column">
                <h3>{{ __('common.explore') }}</h3>
                <ul class="footer-links">
                    <li><a href="{{ localized_route('tags.index') }}">{{ __('common.tags') }}</a></li>
                    <li><a href="{{ localized_route('countries.index') }}">{{ __('common.countries') }}</a></li>
                    <li><a href="https://thepornlinks.com/" title="Best Porn Sites" target="_blank" rel="nofollow noopener">{{ __('common.best_porn_sites') }}</a></li>
                </ul>
            </div>

            <!-- Secure Badge (desktop only) -->
            <div class="footer-column footer-secure-col">
                <div class="footer-secure-box">
                    <div class="footer-secure-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        {{ __('footer.secure_private') }}
                    </div>
                    <p class="footer-secure-text">
                        {{ __('footer.privacy_matters') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Industry advocacy badges --}}
        <div class="footer-advocacy">
            <a href="https://www.asacp.org/" target="_blank" rel="nofollow noopener" title="ASACP – Association of Sites Advocating Child Protection" class="advocacy-badge">
                <img src="{{ asset('img/asacp.webp') }}" alt="ASACP Member" height="40" loading="lazy">
            </a>
            <a href="https://www.rtalabel.org/" target="_blank" rel="nofollow noopener" title="RTA – Restricted to Adults Label" class="advocacy-badge">
                <img src="{{ asset('img/rta.gif') }}" alt="RTA Labeled Site" height="40" loading="lazy">
            </a>
            <a href="https://pineapplesupport.org/" target="_blank" rel="nofollow noopener" title="Pineapple Support – Mental Health for Adult Industry" class="advocacy-badge">
                <img src="{{ asset('img/pineapple-support.svg') }}" alt="Pineapple Support Sponsor" height="40" loading="lazy">
            </a>
            <a href="{{ url('/good-causes') }}" class="advocacy-learn-more">{{ __('footer.learn_good_causes') }} &rarr;</a>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                &copy; {{ date('Y') }} PornGuru.cam · 18+
            </div>
            <div class="footer-legal-links">
                <a href="{{ url('/about') }}">{{ __('common.about') }}</a>
                <a href="{{ url('/faq') }}">{{ __('faq.title_short') }}</a>
                <a href="{{ url('/contact') }}">{{ __('footer.contact') }}</a>
                <a href="{{ url('/good-causes') }}">{{ __('footer.good_causes') }}</a>
                <a href="{{ url('/privacy') }}">{{ __('footer.privacy') }}</a>
                <a href="{{ url('/terms') }}">{{ __('footer.terms') }}</a>
                <a href="{{ url('/dmca') }}">{{ __('footer.dmca') }}</a>
                <a href="{{ url('/2257') }}">2257</a>
            </div>
        </div>
    </div>
</footer>
