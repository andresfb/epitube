<x-layouts.app>
    <x-list-title>
        Tags List
    </x-list-title>

    <div class="w-full" x-data="{ searchTerm: '' }">

        <div>
            <label for="search" class="block mb-2.5 text-sm font-medium text-heading sr-only ">Search</label>
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-body"
                         aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg"
                         width="24"
                         height="24"
                         fill="none"
                         viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input type="search"
                       id="search"
                       name="term"
                       x-model="searchTerm"
                       class="block w-full p-3 ps-9 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                       placeholder="Filter Tags"
                       hx-post="{{ route('tags.search') }}"
                       hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                       hx-trigger="keyup changed delay:500ms[this.value.length >= 2]"
                       hx-target="#tag-list"
                       hx-swap="outerHTML"
                       x-on:keyup.debounce.500ms="if (searchTerm.length < 2) { htmx.ajax('GET', '{{ route('tags.list') }}', {target:'#tag-list', swap:'outerHTML', select:'#tag-list'}) }" />
            </div>
        </div>

    </div>

    <x-tag-list :tags="$tagList" />

</x-layouts.app>
