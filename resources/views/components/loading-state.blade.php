@props(['timeout' => 5000, 'maxRefresh' => 3])

<div class="col-span-full flex flex-col items-center justify-center py-12"
     x-data="{
        timeoutMs: {{ $timeout }},
        maxRefresh: {{ $maxRefresh }},
        countdown: Math.floor({{ $timeout }} / 1000),
        refreshCount: 0,
        maxReached: false,
        cancelled: false,
        countdownInterval: null,
        reloadTimeout: null,
        init() {
            // Get current refresh count from localStorage
            this.refreshCount = parseInt(localStorage.getItem('content_list_refresh_count') || '0');

            // Check if max refreshes reached
            if (this.refreshCount >= this.maxRefresh) {
                this.maxReached = true;
                return;
            }

            // Start countdown
            this.countdownInterval = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(this.countdownInterval);
                }
            }, 1000);

            // Set timeout to reload
            this.reloadTimeout = setTimeout(() => {
                // Increment refresh count
                localStorage.setItem('content_list_refresh_count', this.refreshCount + 1);
                window.location.reload();
            }, this.timeoutMs);
        },
        cancel() {
            clearInterval(this.countdownInterval);
            clearTimeout(this.reloadTimeout);
            this.cancelled = true;
        },
        resetCounter() {
            localStorage.removeItem('content_list_refresh_count');
            window.location.reload();
        }
     }">

    <template x-if="!maxReached && !cancelled">
        <div class="flex flex-col items-center">
            <div role="status">
                <svg aria-hidden="true" class="h-12 w-12 animate-spin fill-blue-600 text-gray-200 dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                Loading content in <span x-text="countdown"></span> <span x-text="countdown === 1 ? 'second' : 'seconds'"></span>
            </p>
            <button
                @click="cancel()"
                @class([
                    'mt-4',
                    'rounded-lg',
                    'border',
                    'border-gray-300',
                    'bg-white',
                    'px-4',
                    'py-2',
                    'text-sm',
                    'font-medium',
                    'text-gray-700',
                    'hover:bg-gray-100',
                    'focus:outline-none',
                    'focus:ring-4',
                    'focus:ring-gray-200',
                    'dark:border-gray-600',
                    'dark:bg-gray-800',
                    'dark:text-gray-300',
                    'dark:hover:bg-gray-700',
                    'dark:focus:ring-gray-700',
                ])>
                Cancel
            </button>
        </div>
    </template>

    <template x-if="maxReached || cancelled">
        <div class="flex flex-col items-center text-center">
            <svg class="h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Can't Load Content</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                <span x-show="maxReached">Unable to load content after <span x-text="maxRefresh"></span> attempts.</span>
                <span x-show="cancelled && !maxReached">Loading cancelled by user.</span>
            </p>
            <button
                @click="resetCounter()"
                @class([
                    'mt-4',
                    'rounded-lg',
                    'bg-blue-600',
                    'px-4',
                    'py-2',
                    'text-sm',
                    'font-medium',
                    'text-white',
                    'hover:bg-blue-700',
                    'focus:outline-none',
                    'focus:ring-4',
                    'focus:ring-blue-300',
                    'dark:bg-blue-500',
                    'dark:hover:bg-blue-600',
                    'dark:focus:ring-blue-800',
                ])>
                Try Again
            </button>
        </div>
    </template>
</div>
