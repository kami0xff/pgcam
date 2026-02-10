@props(['label'])

<div class="filter-field">
    <label class="filter-label">{{ $label }}</label>
    {{ $slot }}
</div>
