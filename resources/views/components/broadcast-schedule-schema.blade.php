{{--
    Broadcast Schedule JSON-LD Schema Component
    
    Include this in the <head> section of model profile pages for SEO.
    
    Usage:
    <x-broadcast-schedule-schema 
        :model-id="$model->username"
        :model-name="$model->username"
        :profile-url="route('model.show', $model)"
        :image-url="$model->preview_url"
    />
--}}
@props([
    'modelId',
    'modelName',
    'profileUrl',
    'imageUrl' => null,
])

@php
    use App\Models\ModelHeatmap;
    
    $schema = ModelHeatmap::getFullBroadcastSchema($modelId, $modelName, $profileUrl, $imageUrl);
@endphp

@if($schema)
<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
