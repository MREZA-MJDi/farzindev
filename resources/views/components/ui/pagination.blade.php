@props([
    'paginator' => null,
])

@if ($paginator && $paginator->hasPages())
    {{ $paginator->links('vendor.pagination.farzin') }}
@endif
