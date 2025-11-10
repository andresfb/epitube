<script>
    function preview(previews = []) {
        return {
            playing: false,
            loaded: false,
            previews: previews,
            touchStartTime: 0,
            longPressThreshold: 300, // milliseconds for long press

            // init() {
            //     // Preload videos when they come into viewport
            //     const observer = new IntersectionObserver((entries) => {
            //         entries.forEach(entry => {
            //             if (entry.isIntersecting) {
            //                 this.loadSources();
            //                 observer.unobserve(entry.target);
            //             }
            //         });
            //     }, { rootMargin: '100px' }); // Start loading 100px before entering viewport
            //
            //     observer.observe(this.$el);
            // },

            loadSources() {
                if (this.loaded) return;

                const v = this.$refs.vid;
                this.previews.forEach(preview => {
                    const source = document.createElement('source');
                    source.src = preview.url;
                    source.type = preview.mimeType;
                    v.appendChild(source);
                });

                v.load();
                this.loaded = true;
            },

            loadAndPlay() {
                this.loadSources();
                this.play();
            },

            play() {
                const v = this.$refs.vid;
                v.style.opacity = '1';
                v.play();
                this.playing = true;
            },

            reset() {
                const v = this.$refs.vid;
                v.pause();
                v.currentTime = 0;
                v.style.opacity = '0';
                this.playing = false;
            },

            handleTouchStart() {
                this.touchStartTime = Date.now();
            },

            handleTouchEnd(event) {
                const touchDuration = Date.now() - this.touchStartTime;

                // Long press: play/toggle preview video
                if (touchDuration >= this.longPressThreshold) {
                    event.preventDefault();
                    if (this.playing) {
                        this.reset();
                    } else {
                        this.loadAndPlay();
                    }
                }
                // Short tap: allow default link navigation (do nothing)
            }
        };
    }
</script>
