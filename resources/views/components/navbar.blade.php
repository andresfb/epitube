@php use App\Enums\Durations;use App\Enums\Selects; @endphp
<nav class="bg-zinc-50 border-gray-200 dark:bg-gray-900">
    <div class="w-full max-w-[90%] mx-auto sm:max-w-screen-lg lg:max-w-screen-xl xl:max-w-screen-2xl 2xl:max-w-[90%] py-2 md:p-4 flex flex-wrap items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
            <x-entypo-video class="h-8 text-blue-700"/>
            <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">
                {{ config('app.name') }}
            </span>
        </a>
        <div class="flex md:order-2">
            <button type="button"
                    data-collapse-toggle="navbar-search"
                    aria-controls="navbar-search"
                    aria-expanded="false"
                @class([
                    'md:hidden',
                    'text-gray-500',
                    'dark:text-gray-400',
                    'hover:bg-gray-100',
                    'dark:hover:bg-gray-700',
                    'focus:outline-none',
                    'focus:ring-4',
                    'focus:ring-gray-200',
                    'dark:focus:ring-gray-700',
                    'rounded-lg',
                    'text-sm',
                    'p-2.5',
                    'me-1',
                ])>
                <svg class="w-5 h-5"
                     aria-hidden="true"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 20 20">
                    <path stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
                <span class="sr-only">Search</span>
            </button>
            <div class="relative hidden md:block"
                 x-data="{
                     searchTerm: '{{ $term ?? '' }}',
                     showResults: false,
                     selectedIndex: -1,
                     words: [],
                     selectWord(word) {
                         this.searchTerm = word;
                         this.showResults = false;
                         this.selectedIndex = -1;
                         this.$nextTick(() => {
                             this.$refs.searchForm.submit();
                         });
                     },
                     handleKeydown(event) {
                         if (event.key === 'ArrowDown') {
                             if (this.showResults && this.words.length > 0) {
                                 event.preventDefault();
                                 this.selectedIndex = (this.selectedIndex + 1) % this.words.length;
                             }
                         } else if (event.key === 'ArrowUp') {
                             if (this.showResults && this.words.length > 0) {
                                 event.preventDefault();
                                 this.selectedIndex = this.selectedIndex <= 0 ? this.words.length - 1 : this.selectedIndex - 1;
                             }
                         } else if (event.key === 'Enter') {
                             if (this.showResults && this.selectedIndex >= 0 && this.words.length > 0) {
                                 event.preventDefault();
                                 this.selectWord(this.words[this.selectedIndex]);
                             }
                         } else if (event.key === 'Escape') {
                             this.showResults = false;
                             this.selectedIndex = -1;
                         }
                     }
                 }"
                 @htmx:after-swap.window="if ($event.detail.target.id === 'search-results') {
                     words = Array.from($event.detail.target.querySelectorAll('[data-word]')).map(el => el.dataset.word);
                     selectedIndex = -1;
                 }">
                <form action="{{ route('search') }}" method="GET" x-ref="searchForm">
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg @class([
                                'w-4',
                                'h-4',
                                'text-gray-500',
                                'dark:text-gray-400',
                            ]) aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                            <span class="sr-only">Search icon</span>
                        </div>
                        <input type="text"
                               id="search-navbar"
                               name="term"
                               x-model="searchTerm"
                               @keydown="handleKeydown"
                               @class([
                                   'block',
                                   'w-full',
                                   'p-2',
                                   'ps-10',
                                   'text-sm',
                                   'text-gray-900',
                                   'border',
                                   'border-gray-300',
                                   'rounded-lg',
                                   'bg-gray-50',
                                   'focus:ring-blue-500',
                                   'focus:border-blue-500',
                                   'dark:bg-gray-700',
                                   'dark:border-gray-600',
                                   'dark:placeholder-gray-400',
                                   'dark:text-white',
                                   'dark:focus:ring-blue-500',
                                   'dark:focus:border-blue-500',
                               ])
                               placeholder="Search..."
                               hx-post="{{ route('words.search') }}"
                               hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                               hx-trigger="keyup changed delay:300ms[event.key != 'ArrowDown' && event.key != 'ArrowUp' && event.key != 'Enter' && event.key != 'Escape']"
                               hx-target="#search-results"
                               hx-include="[name='term']"
                               @focus="showResults = true"
                               @click.away="showResults = false; selectedIndex = -1"
                               autocomplete="off">
                    </div>
                    <div id="search-results"
                         x-show="showResults && searchTerm.length >= 2"
                         x-cloak
                         @class([
                             'absolute',
                             'z-50',
                             'w-full',
                             'mt-1',
                             'bg-white',
                             'border',
                             'border-gray-300',
                             'rounded-lg',
                             'shadow-lg',
                             'max-h-96',
                             'overflow-y-auto',
                             'dark:bg-gray-700',
                             'dark:border-gray-600',
                         ])>
                    </div>
                </form>
            </div>
            <button data-collapse-toggle="navbar-search"
                    type="button"
                    @class([
                        'inline-flex',
                        'items-center',
                        'p-2',
                        'w-10',
                        'h-10',
                        'justify-center',
                        'text-sm',
                        'text-gray-500',
                        'rounded-lg',
                        'md:hidden',
                        'hover:bg-gray-100',
                        'focus:outline-none',
                        'focus:ring-2',
                        'focus:ring-gray-200',
                        'dark:text-gray-400',
                        'dark:hover:bg-gray-700',
                        'dark:focus:ring-gray-600',
                    ])
                    aria-controls="navbar-search"
                    aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-5 h-5"
                     aria-hidden="true"
                     xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 17 14">
                    <path stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>
        </div>
        <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-search">
            <div class="relative mt-3 md:hidden"
                 x-data="{
                     searchTerm: '{{ $term ?? '' }}',
                     showResults: false,
                     selectedIndex: -1,
                     words: [],
                     selectWord(word) {
                         this.searchTerm = word;
                         this.showResults = false;
                         this.selectedIndex = -1;
                         this.$nextTick(() => {
                             this.$refs.searchFormMobile.submit();
                         });
                     },
                     handleKeydown(event) {
                         if (event.key === 'ArrowDown') {
                             if (this.showResults && this.words.length > 0) {
                                 event.preventDefault();
                                 this.selectedIndex = (this.selectedIndex + 1) % this.words.length;
                             }
                         } else if (event.key === 'ArrowUp') {
                             if (this.showResults && this.words.length > 0) {
                                 event.preventDefault();
                                 this.selectedIndex = this.selectedIndex <= 0 ? this.words.length - 1 : this.selectedIndex - 1;
                             }
                         } else if (event.key === 'Enter') {
                             if (this.showResults && this.selectedIndex >= 0 && this.words.length > 0) {
                                 event.preventDefault();
                                 this.selectWord(this.words[this.selectedIndex]);
                             }
                         } else if (event.key === 'Escape') {
                             this.showResults = false;
                             this.selectedIndex = -1;
                         }
                     }
                 }"
                 @htmx:after-swap.window="if ($event.detail.target.id === 'search-results-mobile') {
                     words = Array.from($event.detail.target.querySelectorAll('[data-word]')).map(el => el.dataset.word);
                     selectedIndex = -1;
                 }">
                <form action="{{ route('search') }}" method="GET" x-ref="searchFormMobile">
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                 aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg"
                                 fill="none"
                                 viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input type="text"
                               id="search-navbar-mobile"
                               name="term"
                               x-model="searchTerm"
                               @keydown="handleKeydown"
                               @class([
                                   'block',
                                   'w-full',
                                   'p-2',
                                   'ps-10',
                                   'text-sm',
                                   'text-gray-900',
                                   'border',
                                   'border-gray-300',
                                   'rounded-lg',
                                   'bg-gray-50',
                                   'focus:ring-blue-500',
                                   'focus:border-blue-500',
                                   'dark:bg-gray-700',
                                   'dark:border-gray-600',
                                   'dark:placeholder-gray-400',
                                   'dark:text-white',
                                   'dark:focus:ring-blue-500',
                                   'dark:focus:border-blue-500',
                               ])
                               placeholder="Search..."
                               hx-post="{{ route('words.search') }}"
                               hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                               hx-trigger="keyup changed delay:300ms[event.key != 'ArrowDown' && event.key != 'ArrowUp' && event.key != 'Enter' && event.key != 'Escape']"
                               hx-target="#search-results-mobile"
                               hx-include="[name='term']"
                               @focus="showResults = true"
                               @click.away="showResults = false; selectedIndex = -1"
                               autocomplete="off">
                    </div>
                    <div id="search-results-mobile"
                         x-show="showResults && searchTerm.length >= 2"
                         x-cloak
                         @class([
                             'absolute',
                             'z-50',
                             'w-full',
                             'mt-1',
                             'bg-white',
                             'border',
                             'border-gray-300',
                             'rounded-lg',
                             'shadow-lg',
                             'max-h-96',
                             'overflow-y-auto',
                             'dark:bg-gray-700',
                             'dark:border-gray-600',
                         ])>
                    </div>
                </form>
            </div>
            <ul @class([
                'flex',
                'flex-col',
                'p-4',
                'md:p-0',
                'mt-4',
                'text-lg',
                'font-medium',
                'border',
                'border-gray-100',
                'rounded-lg',
                'bg-gray-50',
                'md:space-x-8',
                'rtl:space-x-reverse',
                'md:flex-row',
                'md:mt-0',
                'md:border-0',
                'md:bg-zinc-50',
                'dark:bg-gray-800',
                '2d:dark:bg-gray-900',
                'dark:border-gray-700',
            ])>
                <button id="categoryDropdownLink"
                        data-dropdown-toggle="categoryDropdown"
                    @class([
                        'flex',
                        'items-center',
                        'justify-between',
                        'w-full',
                        'py-2',
                        'px-3',
                        'text-gray-900',
                        'hover:bg-gray-100',
                        'md:hover:bg-transparent',
                        'md:border-0',
                        'md:hover:text-blue-700',
                        'md:p-0',
                        'md:w-auto',
                        'dark:text-white',
                        'md:dark:hover:text-blue-500',
                        'dark:focus:text-white',
                        'dark:hover:bg-gray-700',
                        'md:dark:hover:bg-transparent',
                    ])>
                    {{ $category }} {{ $icon }}
                    <svg class="w-2.5 h-2.5 ms-2.5"
                         aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <!-- Dropdown menu -->
                <div id="categoryDropdown"
                    @class([
                        'z-10',
                        'hidden',
                        'font-normal',
                        'bg-white',
                        'divide-y',
                        'divide-gray-100',
                        'rounded-lg',
                        'shadow-sm',
                        'w-44',
                        'dark:bg-gray-700',
                        'dark:divide-gray-600',
                    ])>
                    <ul @class([
                        'py-2',
                        'text-sm',
                        'text-gray-700',
                        'dark:text-gray-200',
                    ]) aria-labelledby="dropdownLargeButton">
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('switch.category', ['category' => $category['slug']]) }}"
                                    @class([
                                        'block',
                                        'px-4',
                                        'py-2',
                                        'text-lg',
                                        'hover:bg-gray-100',
                                        'dark:hover:bg-gray-600',
                                        'dark:hover:text-white',
                                    ])>{{ $category['icon'] }} {{ $category['name'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <li>
                    <button id="mega-menu-full-dropdown-button"
                            data-collapse-toggle="mega-menu-full-dropdown"
                        @class([
                            'flex',
                            'items-center',
                            'justify-between',
                            'w-full',
                            'py-2',
                            'px-3',
                            'text-gray-900',
                            'rounded-sm',
                            'md:w-auto',
                            'hover:bg-gray-100',
                            'md:hover:bg-transparent',
                            'md:border-0',
                            'md:hover:text-blue-600',
                            'md:p-0',
                            'dark:text-white',
                            'md:dark:hover:text-blue-500',
                            'dark:hover:bg-gray-700',
                            'dark:hover:text-blue-500',
                            'md:dark:hover:bg-transparent',
                            'dark:border-gray-700',
                        ])>
                        Tags
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                             fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>
                </li>
                <button id="durationDropdownLink"
                        data-dropdown-toggle="durationDropdown"
                    @class([
                        'flex',
                        'items-center',
                        'justify-between',
                        'w-full',
                        'py-2',
                        'px-3',
                        'text-gray-900',
                        'hover:bg-gray-100',
                        'md:hover:bg-transparent',
                        'md:border-0',
                        'md:hover:text-blue-700',
                        'md:p-0',
                        'md:w-auto',
                        'dark:text-white',
                        'md:dark:hover:text-blue-500',
                        'dark:focus:text-white',
                        'dark:hover:bg-gray-700',
                        'md:dark:hover:bg-transparent',
                    ])>
                    Duration
                    <svg class="w-2.5 h-2.5 ms-2.5"
                         aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <div id="durationDropdown"
                    @class([
                        'z-10',
                        'hidden',
                        'font-normal',
                        'bg-white',
                        'divide-y',
                        'divide-gray-100',
                        'rounded-lg',
                        'shadow-sm',
                        'w-44',
                        'dark:bg-gray-700',
                        'dark:divide-gray-600',
                    ])>
                    <ul @class([
                        'py-2',
                        'text-sm',
                        'text-gray-700',
                        'dark:text-gray-200',
                    ]) aria-labelledby="dropdownLargeButton">
                        @foreach(Durations::cases() as $duration)
                            <li>
                                <a href="{{ route('duration', $duration) }}"
                                    @class([
                                       'block',
                                       'px-4',
                                       'py-2',
                                       'hover:bg-gray-100',
                                       'dark:hover:bg-gray-600',
                                       'dark:hover:text-white',
                                    ])>
                                    {{ Durations::title($duration) }} <small class="text-sm">{{ Durations::description($duration) }}</small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button id="optionsDropdownLink"
                        data-dropdown-toggle="optionsDropdown"
                    @class([
                        'flex',
                        'items-center',
                        'justify-between',
                        'w-full',
                        'py-2',
                        'px-3',
                        'text-gray-900',
                        'hover:bg-gray-100',
                        'md:hover:bg-transparent',
                        'md:border-0',
                        'md:hover:text-blue-700',
                        'md:p-0',
                        'md:w-auto',
                        'dark:text-white',
                        'md:dark:hover:text-blue-500',
                        'dark:focus:text-white',
                        'dark:hover:bg-gray-700',
                        'md:dark:hover:bg-transparent',
                    ])>
                    Selects
                    <svg class="w-2.5 h-2.5 ms-2.5"
                         aria-hidden="true"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <div id="optionsDropdown"
                    @class([
                        'z-10',
                        'hidden',
                        'font-normal',
                        'bg-white',
                        'divide-y',
                        'divide-gray-100',
                        'rounded-lg',
                        'shadow-sm',
                        'w-44',
                        'dark:bg-gray-700',
                        'dark:divide-gray-600',
                    ])>
                    <ul @class([
                        'py-2',
                        'text-sm',
                        'text-gray-700',
                        'dark:text-gray-200',
                    ]) aria-labelledby="dropdownLargeButton">
                    @foreach(Selects::cases() as $select)
                        <li>
                            <a href="{{ route('selects', $select) }}"
                                @class([
                                   'block',
                                   'px-4',
                                   'py-2',
                                   'text-lg',
                                   'hover:bg-gray-100',
                                   'dark:hover:bg-gray-600',
                                   'dark:hover:text-white',
                                ])>
                                {{ Selects::icon($select) }}&nbsp;&nbsp;{{ Selects::title($select) }}
                            </a>
                        </li>
                    @endforeach
                    </ul>
                </div>
                    {{-- TODO: implement the "Tools" section, eventually --}}
{{--                <button id="toolsDropdownLink"--}}
{{--                        data-dropdown-toggle="toolsDropdown"--}}
{{--                    @class([--}}
{{--                        'flex',--}}
{{--                        'items-center',--}}
{{--                        'justify-between',--}}
{{--                        'w-full',--}}
{{--                        'py-2',--}}
{{--                        'px-3',--}}
{{--                        'text-gray-900',--}}
{{--                        'hover:bg-gray-100',--}}
{{--                        'md:hover:bg-transparent',--}}
{{--                        'md:border-0',--}}
{{--                        'md:hover:text-blue-700',--}}
{{--                        'md:p-0',--}}
{{--                        'md:w-auto',--}}
{{--                        'dark:text-white',--}}
{{--                        'md:dark:hover:text-blue-500',--}}
{{--                        'dark:focus:text-white',--}}
{{--                        'dark:hover:bg-gray-700',--}}
{{--                        'md:dark:hover:bg-transparent',--}}
{{--                    ])>--}}
{{--                    Tools--}}
{{--                    <svg class="w-2.5 h-2.5 ms-2.5"--}}
{{--                         aria-hidden="true"--}}
{{--                         xmlns="http://www.w3.org/2000/svg"--}}
{{--                         fill="none"--}}
{{--                         viewBox="0 0 10 6">--}}
{{--                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"--}}
{{--                              stroke-width="2" d="m1 1 4 4 4-4"/>--}}
{{--                    </svg>--}}
{{--                </button>--}}
{{--                <div id="toolsDropdown"--}}
{{--                    @class([--}}
{{--                        'z-10',--}}
{{--                        'hidden',--}}
{{--                        'font-normal',--}}
{{--                        'bg-white',--}}
{{--                        'divide-y',--}}
{{--                        'divide-gray-100',--}}
{{--                        'rounded-lg',--}}
{{--                        'shadow-sm',--}}
{{--                        'w-44',--}}
{{--                        'dark:bg-gray-700',--}}
{{--                        'dark:divide-gray-600',--}}
{{--                    ])>--}}
{{--                    <ul @class([--}}
{{--                        'py-2',--}}
{{--                        'text-sm',--}}
{{--                        'text-gray-700',--}}
{{--                        'dark:text-gray-200',--}}
{{--                    ]) aria-labelledby="dropdownLargeButton">--}}
{{--                        <li>--}}
{{--                            <a href="{{ route('contents.list') }}" @class([--}}
{{--                               'block',--}}
{{--                               'px-4',--}}
{{--                               'py-2',--}}
{{--                               'text-lg',--}}
{{--                               'hover:bg-gray-100',--}}
{{--                               'dark:hover:bg-gray-600',--}}
{{--                               'dark:hover:text-white',--}}
{{--                            ])>✏️ Edit Contents</a>--}}
{{--                        </li>--}}
{{--                        <li>--}}
{{--                            <a href="#" @class([--}}
{{--                               'block',--}}
{{--                               'px-4',--}}
{{--                               'py-2',--}}
{{--                               'text-lg',--}}
{{--                               'hover:bg-gray-100',--}}
{{--                               'dark:hover:bg-gray-600',--}}
{{--                               'dark:hover:text-white',--}}
{{--                            ])>📥 Downloads</a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </div>--}}
            </ul>

        </div>
    </div>

    <!-- Tags list display -->
    <div id="mega-menu-full-dropdown"
        @class([
            'mt-1',
            'border-gray-200',
            'shadow-xs',
            'bg-gray-50',
            'md:bg-white',
            'border-y',
            'dark:bg-gray-800',
            'dark:border-gray-600',
            'hidden',
        ])>
        <div
            @class([
                'max-w-screen-xl',
                'px-4',
                'py-5',
                'mx-auto',
                'text-sm',
                'text-gray-900',
                'dark:text-white',
                'md:px-6',
            ])>
            <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                @foreach($tags as $tag)
                    <li>
                        <a href="{{ route('tag', ['slug' => $tag->slug]) }}"
                           class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <div class="font-semibold">{{ $tag->name }}</div>
                        </a>
                    </li>
                @endforeach
                <li>
                    <a href="{{ route('tags.list') }}"
                       class="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="font-semibold">
                            <x-bi-tags class="inline mr-2"/>
                            All Tags
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

</nav>
