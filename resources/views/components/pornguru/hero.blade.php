@props(['title', 'subtitle' => null, 'image' => null, 'ctaText' => 'Watch Now', 'ctaLink' => '#', 'badge' => null])

<div class="header-banner header-banner-single">
    <a href="{{ $ctaLink }}" class="header-banner-link">
        <div class="header-banner-bg">
            @if($image)
                <img src="{{ $image }}" alt="{{ $title }}">
            @else
                <div style="width: 100%; height: 100%; background: var(--bg-secondary);"></div>
            @endif
        </div>
        <div class="header-banner-overlay"></div>
        
        <div class="container">
            <div class="header-banner-content">
                <div class="header-banner-info">
                    @if($badge)
                        <span class="header-banner-platform">{{ $badge }}</span>
                    @endif
                    <h1 class="header-banner-name">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="header-banner-tagline">{{ $subtitle }}</p>
                    @endif
                    <div class="header-banner-desc">
                        {{ $slot }}
                    </div>
                </div>
                
                <div class="header-banner-cta">
                    <span class="header-banner-btn">
                        {{ $ctaText }}
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </span>
                </div>
            </div>
        </div>
    </a>
</div>
