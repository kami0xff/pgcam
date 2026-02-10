@props(['models'])

<div class="models-grid">
    @foreach($models as $model)
        <x-pornguru.model-card :model="$model" />
    @endforeach
</div>
