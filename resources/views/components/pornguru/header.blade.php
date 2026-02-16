<header class="site-header">
    <div class="navbar">
        <!-- Logo -->
        <a href="{{ localized_route('home') }}" class="logo">
            <span class="logo-porn">PORNGURU</span><span class="logo-guru">.CAM</span>
        </a>

        <!-- Desktop Nav -->
        <nav class="nav-center">
            <a href="https://pornguru.com" class="nav-link">{{ __('Best Porn Sites') }}</a>
            <a href="{{ localized_route('home') }}" class="nav-link {{ request()->routeIs('home*') ? 'active' : '' }}">{{ __('Live Cams') }}</a>
            <a href="#" class="nav-link">{{ __('Blog') }}</a>
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
                        // Build URL for this locale by manipulating the current path
                        $path = request()->path();
                        // Strip any existing locale prefix and leading/trailing slashes
                        $cleanPath = preg_replace('#^[a-z]{2}(-[A-Z]{2})?(/|$)#', '', $path);
                        $cleanPath = trim($cleanPath, '/');
                        if ($loc === 'en') {
                            $langUrl = url('/' . $cleanPath);
                        } else {
                            $langUrl = url('/' . $loc . ($cleanPath ? '/' . $cleanPath : ''));
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
            <a href="{{ localized_route('niche.show', 'girls') }}" class="niche-bar-link {{ request()->segment(1) === 'girls' || (request()->segment(2) === 'girls') ? 'active' : '' }}">{{ __('Girls') }}</a>
            <a href="{{ localized_route('niche.show', 'couples') }}" class="niche-bar-link {{ request()->segment(1) === 'couples' || (request()->segment(2) === 'couples') ? 'active' : '' }}">{{ __('Couples') }}</a>
            <a href="{{ localized_route('niche.show', 'men') }}" class="niche-bar-link {{ request()->segment(1) === 'men' || (request()->segment(2) === 'men') ? 'active' : '' }}">{{ __('Men') }}</a>
            <a href="{{ localized_route('niche.show', 'trans') }}" class="niche-bar-link {{ request()->segment(1) === 'trans' || (request()->segment(2) === 'trans') ? 'active' : '' }}">{{ __('Trans') }}</a>
            <span class="niche-bar-separator"></span>
            <a href="{{ localized_route('tags.index') }}" class="niche-bar-link {{ request()->routeIs('tags.index*') ? 'active' : '' }}">{{ __('Tags') }}</a>
            <a href="{{ localized_route('countries.index') }}" class="niche-bar-link {{ request()->routeIs('countries.index*') ? 'active' : '' }}">{{ __('Countries') }}</a>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu">
        <a href="{{ localized_route('home') }}" class="mobile-menu-link {{ request()->routeIs('home*') ? 'active' : '' }}">{{ __('Live Cams') }}</a>
        <a href="{{ localized_route('tags.index') }}" class="mobile-menu-link">{{ __('Tags') }}</a>
        <a href="{{ localized_route('countries.index') }}" class="mobile-menu-link">{{ __('Countries') }}</a>
        <a href="https://pornguru.com" class="mobile-menu-link">{{ __('Best Porn Sites') }}</a>
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
                    $cleanPath = preg_replace('#^[a-z]{2}(-[A-Z]{2})?(/|$)#', '', $path);
                    $cleanPath = trim($cleanPath, '/');
                    if ($loc === 'en') {
                        $mLangUrl = url('/' . $cleanPath);
                    } else {
                        $mLangUrl = url('/' . $loc . ($cleanPath ? '/' . $cleanPath : ''));
                    }
                @endphp
                <a href="{{ $mLangUrl }}" class="mobile-lang-item {{ $currentLocale === $loc ? 'active' : '' }}">
                    {{ strtoupper($loc) }}
                </a>
            @endforeach
        </div>
    </div>
</header>
