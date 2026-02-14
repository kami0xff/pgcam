@props(['tag', 'showHash' => true])

@php
    $validNiches = ['girls', 'couples', 'men', 'trans'];
    $englishSlug = $tag;
    $tagUrl = null;
    
    // Parse niche/tag format (e.g., "girls/young" -> niche="girls", englishSlug="young")
    if (str_contains($tag, '/')) {
        $parts = explode('/', $tag, 2);
        $niche = $parts[0];
        $englishSlug = $parts[1] ?? $tag;
        
        // Only use niche route if niche is valid
        if (in_array($niche, $validNiches)) {
            $localizedSlug = \App\Models\Tag::localizeSlug($englishSlug);
            $tagUrl = localized_route('niche.tag', [$niche, $localizedSlug]);
        }
    }
    
    // Fallback to simple tag route
    if (!$tagUrl) {
        $englishSlug = \Illuminate\Support\Str::slug($englishSlug);
        $localizedSlug = \App\Models\Tag::localizeSlug($englishSlug);
        $tagUrl = localized_route('tags.show', $localizedSlug);
    }
    
    // Get proper localized display name
    $displayName = \App\Models\Tag::localizeName($englishSlug);
@endphp

<a href="{{ $tagUrl }}" {{ $attributes->merge(['class' => 'model-tag']) }}>{{ $showHash ? '#' : '' }}{{ $displayName }}</a>
