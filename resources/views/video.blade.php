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

        <script>
            function videoPlayer() {
                return {
                    playing: false,
                    currentTime: 0,
                    duration: 0,
                    lastSentTime: 0,
                    updateInterval: 10, // Send update every 10 seconds

                    handlePlay() {
                        this.playing = true;
                        this.duration = this.$refs.videoPlayer.duration;
                    },

                    handleTimeUpdate() {
                        this.currentTime = this.$refs.videoPlayer.currentTime;

                        // Only send update if 10 seconds have passed since last update
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

                        // Using htmx.ajax to send the progress data
                        console.log(this.currentTime + "| " + this.duration + " | " + completed);
                        {{--htmx.ajax('POST', '{{ route("video", $video->slug) }}/progress', {--}}
                        {{--    values: {--}}
                        {{--        video_id: {{ $video->id }},--}}
                        {{--        current_time: this.currentTime,--}}
                        {{--        duration: this.duration,--}}
                        {{--        completed: completed,--}}
                        {{--        _token: '{{ csrf_token() }}'--}}
                        {{--    }--}}
                        {{--});--}}
                    }
                }
            }
        </script>

        {{-- Video Info Section --}}
        <div class="w-full bg-white dark:bg-gray-900 rounded-lg shadow-md p-6">
            <div class="space-y-4">
                {{-- Title and Stats --}}
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $video->title }}
                    </h1>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
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
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
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
                    <a href="{{ route('switch.category', $video->category_id) }}"
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-900 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 transition-colors">
                        {{ $video->category }}
                    </a>
                </div>

                {{-- Tags --}}
                @if(count($video->tags) > 0)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($video->tags as $tag)
                                <a href="{{ route('tags', $tag) }}"
                                   class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800 transition-colors">
                                    #{{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Related Videos Section --}}
        @if(count($video->related) > 0)
            <div class="w-full">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Related Videos</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($video->related as $related)
                        <a href="{{ route('video', $related['slug']) }}"
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
</x-layouts.app>

<x-preview-scripts />
