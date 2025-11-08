<x-layouts.app>
    <div class="w-full text-2xl text-left font-semibold text-gray-700 mt-2 mb-8">Videos of the Day</div>

@props([
    'reloadTimer' => true,
])

@if (blank($feed))
    <script>
        // Clear refresh counter when content loads successfully
        localStorage.removeItem('content_list_refresh_count');
    </script>
        @if($reloadTimer)
            <x-loading-state :timeout="$timeout" :maxRefresh="$maxRefresh" />
        @else
            <div class="col-span-full flex flex-col items-center justify-center py-12">
                <svg class="h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                    </path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-700 dark:text-gray-300">No Records Found</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    There are no items to display at this time.
                </p>
            </div>
        @endif
@else
    <x-content-list
        :items="$feed"
        :links="$links"
    />
@endif

</x-layouts.app>
