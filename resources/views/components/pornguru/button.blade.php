@props(['variant' => 'primary', 'type' => 'button', 'href' => null])

@php
    $classes = 'btn';
    
    switch ($variant) {
        case 'primary':
            $classes .= ' btn-primary';
            break;
        case 'secondary':
            $classes .= ' btn-secondary';
            break;
        case 'ghost':
            $classes .= ' btn-ghost';
            break;
    }
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
