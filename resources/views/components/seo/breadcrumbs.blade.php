@props(['items' => []])

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol class="breadcrumbs-list">
        @foreach($items as $index => $item)
            <li class="breadcrumbs-item">
                @if($index < count($items) - 1)
                    <a href="{{ $item['url'] }}" class="breadcrumbs-link">{{ $item['name'] }}</a>
                    <span class="breadcrumbs-separator">/</span>
                @else
                    <span class="breadcrumbs-current" aria-current="page">{{ $item['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
