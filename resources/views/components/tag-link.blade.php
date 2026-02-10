@props(['tag', 'showHash' => true])

@php
    $validNiches = ['girls', 'couples', 'men', 'trans'];
    $tagName = $tag;
    $tagUrl = null;
    
    // Parse niche/tag format (e.g., "girls/young" -> niche="girls", tagName="young")
    if (str_contains($tag, '/')) {
        $parts = explode('/', $tag, 2);
        $niche = $parts[0];
        $tagName = $parts[1] ?? $tag;
        
        // Only use niche route if niche is valid
        if (in_array($niche, $validNiches)) {
            $tagUrl = route('niche.tag', [$niche, $tagName]);
        }
    }
    
    // Fallback to simple tag route
    if (!$tagUrl) {
        $tagUrl = route('tags.show', \Illuminate\Support\Str::slug($tagName));
    }
    
    $displayName = str_replace(['-', '_'], ' ', $tagName);
@endphp

<a href="{{ $tagUrl }}" {{ $attributes->merge(['class' => 'model-tag']) }}>{{ $showHash ? '#' : '' }}{{ $displayName }}</a>
