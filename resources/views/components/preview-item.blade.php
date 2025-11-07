@php use App\Dtos\Tube\FeedItem; @endphp

@props([
    'item',
])

@php
    /** @var FeedItem $item */
    if (! is_array($item)) {
        $item = $item->toArray();
    }
@endphp

<div
    x-data="preview()"
    @mouseenter="play()"
    @mouseleave="reset()"
    @touchstart.prevent="toggle()"
    {{-- TODO: remove the touchend event --}}
    @touchend="reset()"
    @touchcancel="reset()"
    class="group relative overflow-hidden bg-gray-800">

    <img class="inset-0 h-full w-full object-contain transition-opacity duration-200 group-hover:opacity-0"
         srcset="{{ $item['thumbnail'] }}" alt="thumbnail" src=""/>

    <!-- Preview video -->
    <video
        x-ref="vid"
        muted
        playsinline
        preload="metadata"
        loop
        class="absolute inset-0 w-full h-full object-contain opacity-0 transition-opacity duration-200">

        @foreach($item['previews'] as $preview)
            <source src="{{ $preview['url'] }}" type="{{ $preview['mimeType'] }}">
        @endforeach

    </video>

</div>
