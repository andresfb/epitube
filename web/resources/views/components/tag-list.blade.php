@props([
    'tags'
])

<div id="tag-list" class="mt-8 grid grid-cols-1 gap-2 sm:grid-cols-3 md:grid-cols-4 2xl:grid-cols-6">
@forelse($tags as $tag)
    <div class="text-sm xl:text-base font-semibold text-gray-900">
        <a href="{{ route('tag', ['slug' => $tag->slug]) }}" class="me-1">
            {{ $tag->name }}
        </a>
        <span class="bg-sky-100 text-gray-600 text-xs font-medium px-2 py-0.5 rounded-lg">
        {{ $tag->count }}
    </span>
    </div>
@empty
    <div> </div>
@endforelse
</div>
