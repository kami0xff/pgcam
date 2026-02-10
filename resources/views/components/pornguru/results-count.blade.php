@props(['paginator', 'label' => 'results'])

<div class="stats-bar">
    <p class="stats-bar-text">
        {{ number_format($paginator->total()) }} {{ $label }}
    </p>
</div>
