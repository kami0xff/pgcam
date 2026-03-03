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
                    <li><a href="https://thepornlinks.com/" title="Best Porn Sites" target="_blank">{{ __('Best Porn Sites') }}</a></li>
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

        {{-- Industry advocacy badges --}}
        <div class="footer-advocacy">
            <a href="https://www.asacp.org/" target="_blank" rel="noopener" title="ASACP – Association of Sites Advocating Child Protection" class="advocacy-badge">
                <svg viewBox="0 0 120 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="advocacy-svg">
                    <rect x="1" y="1" width="118" height="38" rx="6" stroke="#4fc3f7" stroke-width="1.5" fill="rgba(79,195,247,0.08)"/>
                    <path d="M16 28V14l6-4 6 4v14l-6 3-6-3z" fill="none" stroke="#4fc3f7" stroke-width="1.5" stroke-linejoin="round"/>
                    <path d="M19 21l2.5 2.5L26 18" stroke="#4fc3f7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <text x="38" y="18" fill="#4fc3f7" font-family="Inter,system-ui,sans-serif" font-size="11" font-weight="700">ASACP</text>
                    <text x="38" y="30" fill="#8bb8cc" font-family="Inter,system-ui,sans-serif" font-size="7.5">MEMBER</text>
                </svg>
            </a>
            <a href="https://www.rtalabel.org/" target="_blank" rel="noopener" title="RTA – Restricted to Adults Label" class="advocacy-badge">
                <svg viewBox="0 0 100 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="advocacy-svg">
                    <rect x="1" y="1" width="98" height="38" rx="6" stroke="#ff8a65" stroke-width="1.5" fill="rgba(255,138,101,0.08)"/>
                    <rect x="10" y="10" width="20" height="20" rx="3" stroke="#ff8a65" stroke-width="1.5" fill="none"/>
                    <text x="13" y="25" fill="#ff8a65" font-family="Inter,system-ui,sans-serif" font-size="11" font-weight="800">18</text>
                    <text x="38" y="18" fill="#ff8a65" font-family="Inter,system-ui,sans-serif" font-size="12" font-weight="700">RTA</text>
                    <text x="38" y="30" fill="#c4886e" font-family="Inter,system-ui,sans-serif" font-size="7.5">LABELED</text>
                </svg>
            </a>
            <a href="https://pineapplesupport.org/" target="_blank" rel="noopener" title="Pineapple Support – Mental Health for Adult Industry" class="advocacy-badge">
                <svg viewBox="0 0 160 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="advocacy-svg">
                    <rect x="1" y="1" width="158" height="38" rx="6" stroke="#ffd54f" stroke-width="1.5" fill="rgba(255,213,79,0.08)"/>
                    <g transform="translate(12,6)">
                        {{-- Pineapple body --}}
                        <ellipse cx="8" cy="19" rx="6.5" ry="8.5" fill="none" stroke="#ffd54f" stroke-width="1.3"/>
                        <line x1="4" y1="15" x2="12" y2="15" stroke="#ffd54f" stroke-width="0.8" opacity="0.5"/>
                        <line x1="3.5" y1="19" x2="12.5" y2="19" stroke="#ffd54f" stroke-width="0.8" opacity="0.5"/>
                        <line x1="4" y1="23" x2="12" y2="23" stroke="#ffd54f" stroke-width="0.8" opacity="0.5"/>
                        {{-- Pineapple crown --}}
                        <path d="M8 10.5 C6 7 4 4 5 1" stroke="#66bb6a" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                        <path d="M8 10.5 C8 7 8 4 8 1" stroke="#66bb6a" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                        <path d="M8 10.5 C10 7 12 4 11 1" stroke="#66bb6a" stroke-width="1.2" fill="none" stroke-linecap="round"/>
                    </g>
                    <text x="32" y="18" fill="#ffd54f" font-family="Inter,system-ui,sans-serif" font-size="10" font-weight="700">PINEAPPLE</text>
                    <text x="32" y="30" fill="#c4a843" font-family="Inter,system-ui,sans-serif" font-size="7.5">SUPPORT SPONSOR</text>
                </svg>
            </a>
            <a href="{{ url('/good-causes') }}" class="advocacy-learn-more">{{ __('Learn more about our good causes') }} &rarr;</a>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                &copy; {{ date('Y') }} PornGuru.cam · 18+
            </div>
            <div class="footer-legal-links">
                <a href="{{ url('/about') }}">{{ __('About') }}</a>
                <a href="{{ url('/contact') }}">{{ __('Contact') }}</a>
                <a href="{{ url('/good-causes') }}">{{ __('Good Causes') }}</a>
                <a href="{{ url('/privacy') }}">{{ __('Privacy') }}</a>
                <a href="{{ url('/terms') }}">{{ __('Terms') }}</a>
                <a href="{{ url('/dmca') }}">{{ __('DMCA') }}</a>
                <a href="{{ url('/2257') }}">2257</a>
            </div>
        </div>
    </div>
</footer>
