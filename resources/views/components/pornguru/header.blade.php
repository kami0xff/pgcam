<header class="site-header">
    <div class="navbar">
        <!-- Logo -->
        <a href="/" class="logo">
            <span class="logo-porn">PORNGURU</span><span class="logo-guru">.CAM</span>
        </a>

        <!-- Desktop Nav -->
        <nav class="nav-center">
            <a href="https://pornguru.com" class="nav-link">Best Porn Sites</a>
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Live Cams</a>
            <a href="#" class="nav-link">Blog</a>
        </nav>

        <!-- Right Side -->
        <div class="nav-right">
            {{-- Language Selector --}}
            @php
                $currentLocale = app()->getLocale();
                $allLocales = config('locales.supported', []);
                $priorityLocales = config('locales.priority', ['en','es','fr','de','pt','it','nl','pl','ru','ja','ko','zh','ar','tr','pt-BR','es-MX']);
            @endphp
            <div class="lang-selector" id="lang-selector">
                <button class="lang-selector-btn" onclick="document.getElementById('lang-selector').classList.toggle('open')" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <span>{{ strtoupper($currentLocale) }}</span>
                </button>
                <div class="lang-selector-dropdown">
                    @foreach($priorityLocales as $loc)
                        @php
                            // Build URL for this locale
                            $path = request()->path();
                            if ($loc === 'en') {
                                // Strip any locale prefix for English
                                $langUrl = url(preg_replace('#^[a-z]{2}(-[A-Z]{2})?/#', '', $path));
                            } elseif ($currentLocale !== 'en') {
                                // Replace existing locale prefix
                                $langUrl = url(preg_replace('#^[a-z]{2}(-[A-Z]{2})?/#', $loc . '/', $path));
                            } else {
                                // Add locale prefix
                                $langUrl = url($loc . '/' . $path);
                            }
                        @endphp
                        <a href="{{ $langUrl }}" class="lang-option {{ $currentLocale === $loc ? 'active' : '' }}">
                            <span class="lang-option-code">{{ strtoupper($loc) }}</span>
                            <span class="lang-option-name">{{ $allLocales[$loc]['native'] ?? $loc }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @auth
                <a href="{{ route('dashboard') }}" class="nav-user">
                    <span class="nav-user-avatar">{{ auth()->user()->initials() }}</span>
                    <span class="nav-user-name">{{ auth()->user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="nav-link-btn">{{ __('Sign In') }}</a>
                <a href="{{ route('register') }}" class="nav-link-btn nav-link-btn-primary">{{ __('Sign Up') }}</a>
            @endauth
            
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" onclick="document.getElementById('mobile-menu').classList.toggle('open')">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Niche Navigation -->
    <nav class="niche-bar">
        <div class="niche-bar-inner">
            <a href="{{ route('niche.show', 'girls') }}" class="niche-bar-link {{ request()->segment(1) === 'girls' ? 'active' : '' }}">Girls</a>
            <a href="{{ route('niche.show', 'couples') }}" class="niche-bar-link {{ request()->segment(1) === 'couples' ? 'active' : '' }}">Couples</a>
            <a href="{{ route('niche.show', 'men') }}" class="niche-bar-link {{ request()->segment(1) === 'men' ? 'active' : '' }}">Men</a>
            <a href="{{ route('niche.show', 'trans') }}" class="niche-bar-link {{ request()->segment(1) === 'trans' ? 'active' : '' }}">Trans</a>
            <span class="niche-bar-separator"></span>
            <a href="{{ route('tags.index') }}" class="niche-bar-link {{ request()->routeIs('tags.index*') ? 'active' : '' }}">Tags</a>
            <a href="{{ route('countries.index') }}" class="niche-bar-link {{ request()->routeIs('countries.index*') ? 'active' : '' }}">Countries</a>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu">
        <div class="mobile-menu-niches">
            <a href="{{ route('niche.show', 'girls') }}" class="mobile-niche-link">Girls</a>
            <a href="{{ route('niche.show', 'couples') }}" class="mobile-niche-link">Couples</a>
            <a href="{{ route('niche.show', 'men') }}" class="mobile-niche-link">Men</a>
            <a href="{{ route('niche.show', 'trans') }}" class="mobile-niche-link">Trans</a>
        </div>
        <div class="mobile-menu-divider"></div>
        <a href="https://pornguru.com" class="mobile-menu-link">Best Porn Sites</a>
        <a href="{{ route('home') }}" class="mobile-menu-link {{ request()->routeIs('home') ? 'active' : '' }}">Live Cams</a>
        <a href="{{ route('tags.index') }}" class="mobile-menu-link">Tags</a>
        <a href="{{ route('countries.index') }}" class="mobile-menu-link">Countries</a>
        <a href="#" class="mobile-menu-link">Blog</a>
        <div class="mobile-menu-divider"></div>
        @auth
            <a href="{{ route('dashboard') }}" class="mobile-menu-link">{{ __('Dashboard') }}</a>
            <form method="POST" action="{{ route('logout') }}" class="mobile-menu-logout">
                @csrf
                <button type="submit" class="mobile-menu-link">{{ __('Sign Out') }}</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="mobile-menu-link">{{ __('Sign In') }}</a>
            <a href="{{ route('register') }}" class="mobile-menu-link mobile-menu-link-primary">{{ __('Sign Up') }}</a>
        @endauth
        <div class="mobile-menu-divider"></div>
        <div class="mobile-lang-grid">
            @foreach(array_slice($priorityLocales, 0, 8) as $loc)
                @php
                    $path = request()->path();
                    if ($loc === 'en') {
                        $mLangUrl = url(preg_replace('#^[a-z]{2}(-[A-Z]{2})?/#', '', $path));
                    } elseif ($currentLocale !== 'en') {
                        $mLangUrl = url(preg_replace('#^[a-z]{2}(-[A-Z]{2})?/#', $loc . '/', $path));
                    } else {
                        $mLangUrl = url($loc . '/' . $path);
                    }
                @endphp
                <a href="{{ $mLangUrl }}" class="mobile-lang-item {{ $currentLocale === $loc ? 'active' : '' }}">
                    {{ strtoupper($loc) }}
                </a>
            @endforeach
        </div>
    </div>
</header>
