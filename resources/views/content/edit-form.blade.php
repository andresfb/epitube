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

        {{-- Service URL --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Service URL
            </label>
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

        {{-- Active Status --}}
        <div class="flex items-center">
            <input type="checkbox"
                   id="active"
                   name="active"
                   value="1"
                   {{ $content->active ? 'checked' : '' }}
                   class="w-4 h-4 mt-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
            <label for="active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Active
            </label>
        </div>

        {{-- Display Only Info --}}
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Video Information</h4>
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
                Save Changes
            </button>
            <button type="button"
                    @click="$dispatch('close-edit-modal')"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                Cancel
            </button>
        </div>
    </form>
</div>
