<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="{{ localized_route('home') }}" class="footer-logo">
                    <span class="logo-porn">PORNGURU</span><span class="logo-guru">.CAM</span>
                </a>
                <p class="footer-description">
                    {{ __('The ultimate live cam aggregator. Browse thousands of models from top adult platforms.') }}
                </p>
            </div>

            <!-- Browse Column -->
            <div class="footer-column">
                <h3>{{ __('Browse') }}</h3>
                <ul class="footer-links">
                    <li><a href="{{ localized_route('home') }}">{{ __('Live Cams') }}</a></li>
                    <li><a href="{{ localized_route('niche.show', 'girls') }}">{{ __('Girls') }}</a></li>
                    <li><a href="{{ localized_route('niche.show', 'couples') }}">{{ __('Couples') }}</a></li>
                    <li><a href="{{ localized_route('niche.show', 'men') }}">{{ __('Men') }}</a></li>
                    <li><a href="{{ localized_route('niche.show', 'trans') }}">{{ __('Trans') }}</a></li>
                </ul>
            </div>

            <!-- Explore Column -->
            <div class="footer-column">
                <h3>{{ __('Explore') }}</h3>
                <ul class="footer-links">
                    <li><a href="{{ localized_route('tags.index') }}">{{ __('Tags') }}</a></li>
                    <li><a href="{{ localized_route('countries.index') }}">{{ __('Countries') }}</a></li>
                    <li><a href="https://pornguru.com">{{ __('Best Porn Sites') }}</a></li>
                </ul>
            </div>

            <!-- Secure Badge (desktop only) -->
            <div class="footer-column footer-secure-col">
                <div class="footer-secure-box">
                    <div class="footer-secure-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        {{ __('Secure & Private') }}
                    </div>
                    <p class="footer-secure-text">
                        {{ __('Your privacy matters. SSL encrypted, no tracking.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                &copy; {{ date('Y') }} PornGuru.cam · 18+
            </div>
            <div class="footer-legal-links">
                <a href="#">{{ __('Privacy') }}</a>
                <a href="#">{{ __('Terms') }}</a>
                <a href="#">{{ __('DMCA') }}</a>
                <a href="#">2257</a>
            </div>
        </div>
    </div>
</footer>
