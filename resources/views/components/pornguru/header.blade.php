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
            @auth
                <a href="{{ route('dashboard') }}" class="nav-user">
                    <span class="nav-user-avatar">{{ auth()->user()->initials() }}</span>
                    <span class="nav-user-name">{{ auth()->user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="nav-link-btn">Sign In</a>
                <a href="{{ route('register') }}" class="nav-link-btn nav-link-btn-primary">Sign Up</a>
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
            <a href="{{ route('dashboard') }}" class="mobile-menu-link">Dashboard</a>
            <form method="POST" action="{{ route('logout') }}" class="mobile-menu-logout">
                @csrf
                <button type="submit" class="mobile-menu-link">Sign Out</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="mobile-menu-link">Sign In</a>
            <a href="{{ route('register') }}" class="mobile-menu-link mobile-menu-link-primary">Sign Up</a>
        @endauth
    </div>
</header>
