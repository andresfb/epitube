<script>
    function preview() {
        return {
            playing: false,
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
            toggle() {
                // Mobile tap: start if stopped, otherwise stop
                this.playing ? this.reset() : this.play();
            }
        };
    }
</script>
