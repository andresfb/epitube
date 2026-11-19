@props([
    'items',
    'links',
    'count',
])

@if($count > 0)
<div class="w-full mb-4">
    <span class="text-lg font-semibold text-gray-800 dark:text-gray-200">
        {{ number_format($count) }} {{ Str::plural('video', $count) }}
    </span>
</div>
@endif

@if($items->isEMpty())
<div class="w-full text-3xl text-center text-gray-700">No Videos Found</div>
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5">

@foreach($items as $item)
    <x-content-item :item="$item" />
@endforeach

</div>
@if ($links !== null)
<div id="pages" class="flex justify-center pt-9 pb-3">
    {!! $links !!}
</div>
@endif
