<script>
    function preview() {
        return {
            playing: false,
            touchStartTime: 0,
            longPressThreshold: 300, // milliseconds for long press

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
                    this.playing ? this.reset() : this.play();
                }
                // Short tap: allow default link navigation (do nothing)
            }
        };
    }
</script>
