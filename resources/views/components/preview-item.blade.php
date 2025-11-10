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
    x-data="preview(@js($item['previews']))"
    @mouseenter="loadAndPlay()"
    @mouseleave="reset()"
    @touchstart="handleTouchStart()"
    @touchend="handleTouchEnd($event)"
    @touchcancel="reset()"
    class="group relative overflow-hidden bg-gray-800">

    <img class="inset-0 h-full w-full object-contain transition-opacity duration-200 group-hover:opacity-0"
         srcset="{{ $item['thumbnail'] }}" alt="thumbnail" src=""/>

    <!-- Preview video -->
    <video
        x-ref="vid"
        muted
        playsinline
        preload="none"
        loop
        class="absolute inset-0 w-full h-full object-contain opacity-0 transition-opacity duration-200">
        <!-- Sources loaded dynamically on hover -->
    </video>

</div>
