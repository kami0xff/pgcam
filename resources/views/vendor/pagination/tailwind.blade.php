@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('pagination.navigation') }}">
    <ul class="pg-pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li><span class="pg-page pg-page-disabled" aria-disabled="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M15 19l-7-7 7-7"/></svg>
            </span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" class="pg-page" rel="prev">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M15 19l-7-7 7-7"/></svg>
            </a></li>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li><span class="pg-page pg-page-dots">&hellip;</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li><span class="pg-page pg-page-current" aria-current="page">{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}" class="pg-page">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" class="pg-page" rel="next">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M9 5l7 7-7 7"/></svg>
            </a></li>
        @else
            <li><span class="pg-page pg-page-disabled" aria-disabled="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M9 5l7 7-7 7"/></svg>
            </span></li>
        @endif
    </ul>
</nav>
@endif
