@props([
    'items',
    'links',
])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5">

@forelse($items as $item)
    <x-content-item :item="$item" />
@empty
    <div class="text-3xl text-gray-600 text-center">No Videos Found</div>
@endforelse

</div>
@if ($links !== null)
<div id="pages" class="flex justify-center pt-9 pb-3">
    {!! $links !!}
</div>
@endif
