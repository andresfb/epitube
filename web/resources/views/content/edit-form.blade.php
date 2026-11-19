<div>
    <form hx-put="{{ route('contents.update', $content->slug) }}"
          hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
          hx-on::after-request="handleEditResponse(event)"
          hx-swap="none"
          class="space-y-4">

        {{-- Slug --}}
        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Slug
            </label>
            <input type="text"
                   id="slug"
                   name="slug"
                   value="{{ $content->slug }}"
                   required
                   readonly
                   class="w-full px-3 py-2 border text-gray-500 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
        </div>

        {{-- Category --}}
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Category
            </label>
            <select id="category_id"
                    name="category_id"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $content->category_id === $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Title --}}
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Title
            </label>
            <input type="text"
                   id="title"
                   name="title"
                   value="{{ $content->title }}"
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
        </div>

        {{-- Tags --}}
        <div>
            <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Tags
            </label>
            <input type="text"
                   id="tags"
                   name="tags"
                   value=""
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
        </div>

        {{-- Active Status --}}
        <div>
            <label for="active" class="inline-flex items-center cursor-pointer mt-2 mb-1">
                <input type="checkbox"
                       id="active"
                       name="active"
                       value="1"
                       class="sr-only peer"
                    {{ $content->active ? 'checked' : '' }}>
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Active</span>
            </label>
        </div>

        {{-- Service URL --}}
        <div class="pt-5 border-t border-gray-200 dark:border-gray-700 space-y-2">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Service URL
            </div>
            <a href="{{ $content->service_url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                Watch on Jellyfin
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>
        </div>

        {{-- Display Only Info --}}
        <div class="pt-5 border-t border-gray-200 dark:border-gray-700 space-y-2">
            <h4 class="text-sm mt-3 font-semibold text-gray-700 dark:text-gray-300">Video Information</h4>
            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                <div>
                    <span class="font-medium">Duration:</span> {{ $content->duration }}
                </div>
                <div>
                    <span class="font-medium">Resolution:</span> {{ $content->resolution }}
                </div>
                <div>
                    <span class="font-medium">Views:</span> {{ number_format($content->view_count) }}
                </div>
                <div>
                    <span class="font-medium">Added:</span> {{ $content->added_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex gap-3 pt-4">
            <button type="submit"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700">
                Save
            </button>
            <button type="button"
                    @click="$dispatch('close-edit-modal')"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                Cancel
            </button>
        </div>
    </form>
</div>

<script>
    let input = document.querySelector('input[name=tags]');
    let tagify = new Tagify(input, {
        enforceWhitelist: true,
        autoComplete: {
            enabled: true,
            tabKey: true
        },
        whitelist: {!! $tags !!}
    });

    tagify.addTags({!! $content->tag_list !!})

</script>
