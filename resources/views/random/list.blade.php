<x-layouts.app>
    <x-list-title>
        Random Videos
    </x-list-title>

    <div class="w-full">

        <div class="py-5 mb-8 border-b border-b-gray-300">
            <p class="text-xl font-semibold">Select Filters</p>

            <form action="{{ route('random') }}" method="GET" class="py-4">

                <label for="categories" class="block my-2 text-sm font-medium text-gray-900 dark:text-white">
                    Choose a Category
                </label>
                <select id="categories"
                        name="category_id"
                        @class([
                            'mb-5',
                            'bg-gray-50',
                            'border',
                            'border-gray-300',
                            'text-gray-900',
                            'text-sm',
                            'rounded-lg',
                            'focus:ring-blue-500',
                            'focus:border-blue-500',
                            'block',
                            'w-full',
                            'md:w-1/4',
                            'p-2.5',
                            'dark:bg-gray-700',
                            'dark:border-gray-600',
                            'dark:placeholder-gray-400',
                            'dark:text-white',
                            'dark:focus:ring-blue-500',
                            'dark:focus:border-blue-500',
                        ])>
                    <option @if($filters->category_id === 0) selected @endif value="0">All</option>
                @foreach($categories as $category)
                    <option @if($filters->category_id === $category->id) selected="selected" @endif value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
                </select>

                <label for="tag" class="block my-2 text-sm font-medium text-gray-900 dark:text-white">
                    Choose a Tag
                </label>
                <select id="tag"
                        name="tag"
                    @class([
                        'mb-5',
                        'bg-gray-50',
                        'border',
                        'border-gray-300',
                        'text-gray-900',
                        'text-sm',
                        'rounded-lg',
                        'focus:ring-blue-500',
                        'focus:border-blue-500',
                        'block',
                        'w-full',
                        'md:w-1/4',
                        'p-2.5',
                        'dark:bg-gray-700',
                        'dark:border-gray-600',
                        'dark:placeholder-gray-400',
                        'dark:text-white',
                        'dark:focus:ring-blue-500',
                        'dark:focus:border-blue-500',
                    ])>
                    <option @if($filters->tag === '') selected="selected" @endif value="">All</option>
                    @foreach($tags as $tag)
                        <option @if($filters->tag === $tag->slug) selected="selected" @endif value="{{ $tag->slug }}">{{ $tag->name }}</option>
                    @endforeach
                </select>

                <label for="count" class="block my-2 text-sm font-medium text-gray-900 dark:text-white">
                    How Many
                </label>
                <select id="count"
                        name="count"
                    @class([
                        'mb-5',
                        'bg-gray-50',
                        'border',
                        'border-gray-300',
                        'text-gray-900',
                        'text-sm',
                        'rounded-lg',
                        'focus:ring-blue-500',
                        'focus:border-blue-500',
                        'block',
                        'w-full',
                        'md:w-1/4',
                        'p-2.5',
                        'dark:bg-gray-700',
                        'dark:border-gray-600',
                        'dark:placeholder-gray-400',
                        'dark:text-white',
                        'dark:focus:ring-blue-500',
                        'dark:focus:border-blue-500',
                    ])>
                    @foreach($range as $item)
                        <option @if($filters->count === $item) selected="selected" @endif value="{{ $item }}">{{ $item }}</option>
                    @endforeach
                </select>

                <button type="submit"
                    @class([
                        'mt-4',
                        'px-5',
                        'py-2',
                        'text-sm',
                        'font-medium',
                        'text-white',
                        'bg-blue-600',
                        'rounded-lg',
                        'hover:bg-blue-700',
                        'focus:ring-4',
                        'focus:ring-blue-300',
                        'dark:bg-blue-600',
                        'dark:hover:bg-blue-700',
                    ])>
                    Filter
                </button>
            </form>
        </div>

        <x-content-list
            class="mt-3"
            :items="$feed"
            :links="$links"
            :count="$count"
        />
    </div>
</x-layouts.app>
