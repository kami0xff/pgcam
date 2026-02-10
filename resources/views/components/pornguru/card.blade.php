@props(['title', 'image' => null, 'rank' => null, 'rating' => null, 'link' => '#', 'tags' => []])

<a href="{{ $link }}" class="pick-card group">
    <div class="pick-card-image">
        @if($image)
            <img src="{{ $image }}" alt="{{ $title }}">
        @else
            <div style="width: 100%; height: 100%; background: var(--bg-elevated); display: flex; align-items: center; justify-content: center; color: var(--text-faint);">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        @endif

        @if($rank)
            <div class="pick-card-rank {{ $rank == 1 ? 'pick-card-rank-1' : 'pick-card-rank-default' }}">
                {{ $rank }}
            </div>
        @endif

        @if($rating)
            <div class="pick-card-rating">
                <svg viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                {{ $rating }}
            </div>
        @endif
    </div>

    <div class="pick-card-content">
        <h3 class="pick-card-title">{{ $title }}</h3>
        
        <div class="pick-card-description">
            {{ $slot }}
        </div>

        @if(count($tags) > 0)
            <div class="pick-card-tags">
                @foreach($tags as $tag)
                    <span class="pick-card-tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        <span class="pick-card-btn">
            Visit Site
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
        </span>
    </div>
</a>
