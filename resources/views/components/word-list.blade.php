@props([
    'words'
])

@if(count($words) > 0)
    <ul class="divide-y divide-gray-100 dark:divide-gray-600">
    @foreach($words as $index => $word)
        <li>
            <button type="button"
                    data-word="{{ $word->word }}"
                    @click="selectWord('{{ $word->word }}')"
                    :class="{
                        'bg-blue-100 dark:bg-blue-900': selectedIndex === {{ $index }},
                        'hover:bg-gray-100 dark:hover:bg-gray-600': selectedIndex !== {{ $index }}
                    }"
                    @class([
                        'w-full',
                        'text-left',
                        'px-4',
                        'py-2',
                        'text-sm',
                        'text-gray-900',
                        'dark:text-white',
                        'transition-colors',
                        'duration-150',
                    ])>
                {{ $word->word }}
            </button>
        </li>
    @endforeach
    </ul>
@else
    <div @class([
        'px-4',
        'py-3',
        'text-sm',
        'text-gray-500',
        'dark:text-gray-400',
        'text-center',
    ])>
        No results found
    </div>
@endif
