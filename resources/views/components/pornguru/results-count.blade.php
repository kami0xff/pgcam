@props(['paginator', 'label' => null])

<div class="stats-bar">
    <p class="stats-bar-text">
        {{ number_format($paginator->total()) }} {{ $label ?? __('results') }}
    </p>
</div>
