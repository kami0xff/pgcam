@props(['pageKey', 'position' => 'bottom', 'class' => ''])

@php
    use App\Models\PageSeoContent;
    $seoContent = PageSeoContent::forPage($pageKey);
    
    // Only show if position matches
    if ($seoContent && $seoContent->position !== $position) {
        $seoContent = null;
    }
@endphp

@if($seoContent)
<section class="seo-text-block {{ $class }}" aria-label="About this page">
    @if($seoContent->title)
        <h2 class="seo-text-title">{{ $seoContent->title }}</h2>
    @endif
    
    <div class="seo-text-content">
        @if(preg_match('/<[a-z][\s\S]*>/i', $seoContent->content))
            {!! $seoContent->content !!}
        @else
            {!! nl2br(e($seoContent->content)) !!}
        @endif
    </div>
    
    @if($seoContent->keywords_array)
        <div class="seo-text-keywords">
            @foreach($seoContent->keywords_array as $keyword)
                <span class="seo-keyword">{{ $keyword }}</span>
            @endforeach
        </div>
    @endif
</section>
@endif
