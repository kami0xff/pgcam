@props(['paginator'])

@if($paginator->hasPages())
    <div class="pagination-wrapper">
        {{ $paginator->links() }}
    </div>
@endif
