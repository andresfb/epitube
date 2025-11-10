<x-layouts.app>
    <div class="w-full space-y-6">
        {{-- Video Player Section --}}
        <div class="w-full" x-data="videoPlayer()">
            <div class="relative bg-black rounded-lg overflow-hidden shadow-xl group">
                <img
                    x-show="!playing"
                    srcset="{{ $video->thumbnail }}"
                    alt="{{ $video->title }}"
                    class="absolute inset-0 w-full h-full object-cover"
                >
                <div
                    x-show="!playing"
                    class="absolute inset-0 flex items-center justify-center cursor-pointer transition-all"
                    @click="$refs.videoPlayer.play(); playing = true"
                >
                    <div class="bg-gray-200 bg-opacity-10 rounded-full p-3 sm:p-4 md:p-6 shadow-2xl group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 sm:w-10 sm:h-10 md:w-14 md:h-14 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                        </svg>
                    </div>
                </div>
                <video
                    x-ref="videoPlayer"
                    class="w-full aspect-video"
                    controls
                    playsinline
                    preload="metadata"
                    @play="handlePlay()"
                    @timeupdate="handleTimeUpdate()"
                    @pause="handlePause()"
                    @ended="handleEnded()"
                    @seeked="handleSeeked()"
                >
                    @foreach($video->videos as $videoSource)
                        <source src="{{ $videoSource['url'] }}" type="{{ $videoSource['mimeType'] }}">
                    @endforeach
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>

        {{-- Video Info Section --}}
        <div class="w-full bg-white dark:bg-gray-900 rounded-lg shadow-md p-6">
            <div class="space-y-4">
                {{-- Title and Stats --}}
                <div>
                    {{-- Title --}}
                    <h1 id="title" class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $video->title }}
                    </h1>

                    {{-- Stats --}}
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd"
                                      d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                      clip-rule="evenodd"/>
                            </svg>
                            {{ number_format($video->view_count) }} views
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            {{ $video->duration }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                      clip-rule="evenodd"/>
                            </svg>
                            {{ $video->added_at }}
                        </span>
                        @if($video->is_hd)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                HD
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $video->resolution }}
                        </span>
                    </div>
                </div>

                {{-- Category --}}
                <div>
                    <a id="category" href="{{ route('switch.category', $video->category_id) }}"
                       @class([
                           'inline-flex',
                           'items-center',
                           'px-3',
                           'py-1.5',
                           'text-sm',
                           'font-medium',
                           'text-gray-900',
                           'bg-gray-100',
                           'rounded-lg',
                           'hover:bg-gray-200',
                           'dark:bg-gray-700',
                           'dark:text-white',
                           'dark:hover:bg-gray-600',
                           'transition-colors',
                       ])>
                        {{ $video->category }}
                    </a>
                </div>

            {{-- Tags --}}
            @if(count($video->tags) > 0)
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($video->tags as $slug => $tag)
                            <a href="{{ route('tags', $slug) }}"
                               @class([
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
                </div>
            @endif

                {{-- Action Buttons --}}
                <div class="flex gap-2 pt-4" x-data="{ likeStatus: {{ $video->like_status }} }">
                    <a id="like"
                       href="#"
                       hx-post="{{ route('videos.like', $video->slug) }}"
                       hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                       hx-swap="none"
                       hx-on::after-request="handleLikeResponse(event)"
                       @class([
                           'inline-flex',
                           'items-center',
                           'justify-center',
                           'w-10',
                           'h-10',
                           'rounded-lg',
                           'bg-gray-100',
                           'hover:bg-gray-200',
                           'dark:bg-gray-800',
                           'dark:hover:bg-gray-700',
                           'text-gray-700',
                           'dark:text-gray-300',
                           'transition-colors',
                       ])
                       title="Like">
                        <svg x-show="likeStatus === 1" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3zM7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/>
                        </svg>
                        <svg x-show="likeStatus !== 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                        </svg>
                    </a>

                    <a id="dislike"
                       href="#"
                       hx-delete="{{ route('videos.dislike', $video->slug) }}"
                       hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                       hx-swap="none"
                       hx-on::after-request="handleLikeResponse(event)"
                       @class([
                           'inline-flex',
                           'items-center',
                           'justify-center',
                           'w-10',
                           'h-10',
                           'rounded-lg',
                           'bg-gray-100',
                           'hover:bg-gray-200',
                           'dark:bg-gray-800',
                           'dark:hover:bg-gray-700',
                           'text-gray-700',
                           'dark:text-gray-300',
                           'transition-colors',
                       ])
                       title="Dislike">
                        <svg x-show="likeStatus === -1" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M10 15v4a3 3 0 003 3l4-9V2H5.72a2 2 0 00-2 1.7l-1.38 9a2 2 0 002 2.3zm7-13h3a2 2 0 012 2v7a2 2 0 01-2 2h-3"/>
                        </svg>
                        <svg x-show="likeStatus !== -1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                        </svg>
                    </a>

                    <a id="viewed"
                       href="#"
                       hx-post="{{ route('videos.viewed', $video->slug) }}"
                       hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                       hx-swap="none"
                       hx-on::after-request="handleDeleteResponse(event)"
                       @class([
                           'inline-flex',
                           'items-center',
                           'justify-center',
                           'w-10',
                           'h-10',
                           'rounded-lg',
                           'bg-gray-100',
                           'hover:bg-gray-200',
                           'dark:bg-gray-800',
                           'dark:hover:bg-gray-700',
                           'text-gray-700',
                           'dark:text-gray-300',
                           'transition-colors',
                       ])
                       title="Mark Viewed">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd"
                                  d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </a>

                    <a id="feature"
                       href="#"
                       hx-put="{{ route('videos.feature', $video->slug) }}"
                       hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                       hx-swap="none"
                       hx-on::after-request="handleDeleteResponse(event)"
                       @class([
                           'inline-flex',
                           'items-center',
                           'justify-center',
                           'w-10',
                           'h-10',
                           'rounded-lg',
                           'bg-gray-100',
                           'hover:bg-gray-200',
                           'dark:bg-gray-800',
                           'dark:hover:bg-gray-700',
                           'text-gray-700',
                           'dark:text-gray-300',
                           'transition-colors',
                       ])
                       title="Feature">
                        {{ config('content.featured_icon') }}
                    </a>

                    <a id="disable"
                       href="#"
                       hx-delete="{{ route('videos.disable', $video->slug) }}"
                       hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                       hx-confirm="Are you sure you want to disable this video?"
                       hx-on::after-request="handleDeleteResponse(event)"
                       @class([
                           'inline-flex',
                           'items-center',
                           'justify-center',
                           'w-10',
                           'h-10',
                           'rounded-lg',
                           'bg-gray-100',
                           'hover:bg-gray-200',
                           'dark:bg-gray-800',
                           'dark:hover:bg-gray-700',
                           'text-gray-700',
                           'dark:text-gray-300',
                           'transition-colors',
                       ])
                       title="Disable">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </a>

                    <a id="edit"
                       href="#"
                       @click.prevent="$dispatch('open-edit-modal')"
                       hx-get="{{ route('contents.edit', $video->slug) }}"
                       hx-target="#edit-modal-content"
                       hx-trigger="click"
                       @class([
                           'inline-flex',
                           'items-center',
                           'justify-center',
                           'w-10',
                           'h-10',
                           'rounded-lg',
                           'bg-gray-100',
                           'hover:bg-gray-200',
                           'dark:bg-gray-800',
                           'dark:hover:bg-gray-700',
                           'text-gray-700',
                           'dark:text-gray-300',
                           'transition-colors',
                       ])
                       title="Edit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                </div>

            </div>
        </div>

        {{-- Related Videos Section --}}
        @if(count($video->related) > 0)
            <div class="w-full">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Related Videos</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($video->related as $related)
                        <a href="{{ route('videos', $related['slug']) }}"
                           class="group bg-white dark:bg-gray-900 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow">
                            <div class="relative aspect-video bg-gray-200 dark:bg-gray-800">

                                <x-preview-item :item="$related" />

                                <div class="absolute bottom-2 right-2 bg-black bg-opacity-80 text-white text-xs px-2 py-1 rounded">
                                    {{ $related['duration'] }}
                                </div>
                                @if($related['is_hd'])
                                    <div class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded font-semibold">
                                        HD
                                    </div>
                                @endif
                            </div>
                            <div class="p-3">
                                <h3 class="font-medium text-gray-900 dark:text-white line-clamp-2 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ $related['title'] }}
                                </h3>
                                <div class="flex items-center gap-3 text-xs text-gray-600 dark:text-gray-400">
                                    <span>{{ number_format($related['view_count']) }} views</span>
                                    <span>{{ $related['added_at'] }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    <div x-data="{ open: false }"
         @open-edit-modal.window="open = true"
         @close-edit-modal.window="open = false"
         @keydown.escape.window="open = false"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        {{-- Modal Backdrop --}}
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="open = false"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75 z-40">
            </div>

            {{-- Modal Content --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg relative z-50">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Edit Video
                    </h3>
                    <button @click="open = false"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div id="edit-modal-content">
                    {{-- Content will be loaded here via htmx --}}
                    <div class="flex items-center justify-center py-8">
                        <svg class="w-8 h-8 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

<x-preview-scripts />

<script>
    function handleDeleteResponse(event) {
        if (event.detail.successful) {
            window.location.href = '{{ route('home') }}';
        } else if (event.detail.xhr.status === 500) {
            const response = JSON.parse(event.detail.xhr.response);
            alert('Error: ' + response.data.message);
        }
    }

    function handleLikeResponse(event) {
        if (event.detail.successful) {
            const response = JSON.parse(event.detail.xhr.response);
            const container = event.target.closest('[x-data]');
            const alpineData = Alpine.$data(container);
            alpineData.likeStatus = response.data.like_status;
        } else if (event.detail.xhr.status === 500) {
            const response = JSON.parse(event.detail.xhr.response);
            alert('Error: ' + response.data.message);
        }
    }

    function handleEditResponse(event) {
        if (event.detail.successful) {
            const response = JSON.parse(event.detail.xhr.response);
            const content = response.data.content;

            // If video is inactive, redirect to home
            if (!content.active) {
                window.location.href = '{{ route('home') }}';
                return;
            }

            // Update title in DOM
            const titleElement = document.getElementById('title');
            if (titleElement) {
                titleElement.textContent = content.title;
            }

            // Update category link and text in DOM
            const categoryLink = document.getElementById('category');
            if (categoryLink) {
                categoryLink.href = '/switch/' + content.category_id;
                categoryLink.textContent = content.category;
            }

            // Close the modal
            window.dispatchEvent(new CustomEvent('close-edit-modal'));
        } else if (event.detail.xhr.status === 422) {
            const response = JSON.parse(event.detail.xhr.response);
            alert('Validation Error: ' + Object.values(response.errors).flat().join(', '));
        } else if (event.detail.xhr.status === 500) {
            const response = JSON.parse(event.detail.xhr.response);
            alert('Error: ' + response.data.message);
        }
    }

    function videoPlayer() {
        return {
            playing: false,
            currentTime: 0,
            duration: 0,
            lastSentTime: 0,
            updateInterval: 10, // Send an update every 10 seconds

            handlePlay() {
                this.playing = true;
                this.duration = this.$refs.videoPlayer.duration;
            },

            handleTimeUpdate() {
                this.currentTime = this.$refs.videoPlayer.currentTime;

                // Only send an update if 10 seconds have passed since the last update
                if (this.currentTime - this.lastSentTime >= this.updateInterval) {
                    this.sendProgress();
                }
            },

            handlePause() {
                this.sendProgress();
            },

            handleEnded() {
                this.currentTime = this.$refs.videoPlayer.duration;
                this.sendProgress(true);
            },

            handleSeeked() {
                this.currentTime = this.$refs.videoPlayer.currentTime;
            },

            sendProgress(completed = false) {
                this.lastSentTime = this.currentTime;

                // Send progress data using Fetch API
                fetch('{{ route("videos.progress", $video->slug) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        current_time: this.currentTime,
                        duration: this.duration,
                        completed: completed
                    })
                }).catch(error => console.error('Progress tracking error:', error));
            }
        }
    }
</script>
