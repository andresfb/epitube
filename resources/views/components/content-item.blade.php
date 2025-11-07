@props([
    'item',
    'showTags',
])

<div class="max-w-sm bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <a href="{{ route('videos', ['slug' => $item->slug]) }}" @class([
        'block',
        'overflow-hidden',
        'rounded-t-lg',
        'bg-gray-100',
        'dark:bg-gray-900',
    ])>
        <x-preview-item :item="$item" />
    </a>
    <div class="p-5">
        <a id="title" href="{{ route('videos', ['slug' => $item->slug]) }}">
            <h5 class="mb-2 text font-bold tracking-tight text-gray-900 dark:text-white">
            @if($item->is_hd)
                <span class="bg-red-100 text-red-800 text-sm font-semibold me-1 px-1 py-0.5 rounded-sm dark:bg-red-900 dark:text-red-300">
                    HD
                </span>
            @endif
                {{ $item->title }}
            </h5>
        </a>
        <p class="mt-4 font-normal text-gray-700 dark:text-gray-400">
            <span @class([
                'bg-gray-100',
                'text-gray-800',
                'text-xs',
                'font-medium',
                'inline-flex',
                'items-center',
                'me-2',
                'px-2.5',
                'py-0.5',
                'rounded-sm',
                'dark:bg-gray-700',
                'dark:text-gray-400',
                'border',
                'border-gray-500',
            ])>
                <svg class="w-3 h-3 me-1.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M16 2H0V14H16V2ZM6.5 5V11H7.5L11 8L7.5 5H6.5Z"
                      fill="#000000"/>
                </svg>
            {{ $item->resolution }}
            </span>
            <span @class([
                'bg-blue-100',
                'text-blue-800',
                'text-xs',
                'font-medium',
                'inline-flex',
                'items-center',
                'px-2.5',
                'py-0.5',
                'rounded-sm',
                'dark:bg-gray-700',
                'dark:text-blue-400',
                'border',
                'border-blue-400',
            ])>
                <svg class="w-2.5 h-2.5 me-1.5"
                     aria-hidden="true"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="currentColor"
                     viewBox="0 0 20 20">
                    <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm3.982 13.982a1 1 0 0 1-1.414 0l-3.274-3.274A1.012 1.012 0 0 1 9 10V6a1 1 0 0 1 2 0v3.586l2.982 2.982a1 1 0 0 1 0 1.414Z"/>
                </svg>
                {{ $item->duration }}
            </span>
        </p>

    @if ($showTags)
        <div id="tags" class="mt-8 font-normal text-gray-700 dark:text-gray-400 flex flex-wrap items-center gap-2">
        @foreach($item->tags as $slug => $tag)
            <a href="{{ route('tags', ['slug' => $slug]) }}" @class([
                'bg-yellow-100',
                'hover:bg-yellow-200',
                'text-yellow-800',
                'text-xs',
                'font-medium',
                'px-2.5',
                'py-0.5',
                'rounded-sm',
                'dark:bg-gray-700',
                'dark:text-yellow-400',
                'border',
                'border-yellow-400',
                'inline-flex',
                'items-center',
                'justify-center',
            ])>
                {{ $tag }}
            </a>
        @endforeach
        </div>

        <p class="mt-5 text-xs font-normal text-gray-500 dark:text-gray-300">
            Added: {{ $item->added_at }}
        </p>
    @endif

    </div>
</div>

<x-preview-scripts />

