@props(['title' => null, 'text' => null])

<div class="empty-state">
    <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <h3 class="empty-state-title">{{ $title ?? __('No results found') }}</h3>
    <p class="empty-state-text">{{ $text ?? __('Try adjusting your filters.') }}</p>
</div>
