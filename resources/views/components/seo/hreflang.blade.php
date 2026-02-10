@props(['urls' => []])

@foreach($urls as $locale => $url)
<link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}" />
@endforeach
