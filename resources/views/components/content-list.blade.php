@props([
    'items',
    'links',
    'timeout' => 5000,
    'maxRefresh' => 3,
    'reloadTimer' => true
    ])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5">
@forelse($items as $item)
{{--    <div class="overflow-hidden rounded-md bg-white shadow dark:bg-gray-800">--}}
        <x-content-item :item="$item" />
{{--    </div>--}}
    @once
    <script>
        // Clear refresh counter when content loads successfully
        localStorage.removeItem('content_list_refresh_count');
    </script>
    @endonce
@empty
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
@endforelse
</div>
<div class="container w-full lg:w-1/2 xl:w-1/3 mx-auto px-4 pt-9 pb-3">
@if ($links !== null)
    {!! $links !!}
@endif
</div>
