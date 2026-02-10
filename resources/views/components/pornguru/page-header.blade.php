@props(['title', 'icon' => 'fire'])

<div class="section-header">
    <div class="section-icon section-icon-{{ $icon }}">
        @if($icon === 'fire')
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 23a7.5 7.5 0 01-5.138-12.963C8.204 8.774 11.5 6.5 11 1.5c6 4 9 8 3 14 1 0 2.5 0 5-2.47.27.773.5 1.604.5 2.47A7.5 7.5 0 0112 23z"/>
            </svg>
        @elseif($icon === 'heart')
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        @elseif($icon === 'trophy')
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 14a3 3 0 100-6 3 3 0 000 6zM6 4h12v2H6V4zm0 14h12v2H6v-2zm8-4h-4v6h4v-6z"/>
            </svg>
        @endif
    </div>
    <h1 class="section-title">{{ $title }}</h1>
</div>
