@props(['paginator', 'total' => null, 'label' => null])

<div class="stats-bar">
    <p class="stats-bar-text">
        {{ number_format($total ?? (method_exists($paginator, 'total') ? $paginator->total() : $paginator->count())) }} {{ $label ?? __('pagination.results') }}
    </p>
</div>
