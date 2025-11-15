<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  data-theme="emerald">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Epicurus Tube') }}</title>

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/icon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/manifest.json">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="EpiTube">
    <meta name="theme-color" content="#1d4ed8">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-base-300"
      x-data="{
          toasts: [],
          addToast(message, type = 'info') {
              const id = Date.now();
              this.toasts.push({ id, message, type });
              setTimeout(() => this.removeToast(id), 5000);
          },
          removeToast(id) {
              this.toasts = this.toasts.filter(toast => toast.id !== id);
          }
      }"
      x-init="
          @if(session('message'))
              addToast('{{ session('message') }}', 'info');
          @endif
          @if(session('success'))
              addToast('{{ session('success') }}', 'success');
          @endif
          @if(session('error'))
              addToast('{{ session('error') }}', 'error');
          @endif
          @if(session('warning'))
              addToast('{{ session('warning') }}', 'warning');
          @endif
      ">
    <x-navbar />

    <main class="flex-1 bg-white rounded-md shadow-sm m-2 dark:bg-gray-800">
        <div class="w-full max-w-[90%] mx-auto sm:max-w-screen-lg lg:max-w-screen-xl xl:max-w-screen-2xl 2xl:max-w-[90%] py-2 md:p-4">
            {{ $slot }}
        </div>
    </main>

    <!-- Toast Container -->
    <div class="fixed top-20 right-4 z-50 space-y-4" role="alert">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 :class="{
                     'bg-blue-100 dark:bg-blue-800': toast.type === 'info',
                     'bg-green-100 dark:bg-green-800': toast.type === 'success',
                     'bg-red-100 dark:bg-red-800': toast.type === 'error',
                     'bg-yellow-100 dark:bg-yellow-800': toast.type === 'warning'
                 }"
                 @class([
                     'flex',
                     'items-center',
                     'w-full',
                     'max-w-xs',
                     'p-4',
                     'text-gray-500',
                     'bg-white',
                     'rounded-lg',
                     'shadow',
                     'dark:text-gray-400',
                     'dark:bg-gray-800',
                 ])>
                <div :class="{
                         'bg-blue-200 text-blue-500 dark:bg-blue-700 dark:text-blue-200': toast.type === 'info',
                         'bg-green-200 text-green-500 dark:bg-green-700 dark:text-green-200': toast.type === 'success',
                         'bg-red-200 text-red-500 dark:bg-red-700 dark:text-red-200': toast.type === 'error',
                         'bg-yellow-200 text-yellow-500 dark:bg-yellow-700 dark:text-yellow-200': toast.type === 'warning'
                     }"
                     class="inline-flex items-center justify-center shrink-0 w-8 h-8 rounded-lg">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                        </svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z"/>
                        </svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                    </template>
                    <span class="sr-only" x-text="toast.type + ' icon'"></span>
                </div>
                <div class="ms-3 text-sm font-normal" x-text="toast.message"></div>
                <button type="button"
                        @click="removeToast(toast.id)"
                        @class([
                            'ms-auto',
                            '-mx-1.5',
                            '-my-1.5',
                            'bg-white',
                            'text-gray-400',
                            'hover:text-gray-900',
                            'rounded-lg',
                            'focus:ring-2',
                            'focus:ring-gray-300',
                            'p-1.5',
                            'hover:bg-gray-100',
                            'inline-flex',
                            'items-center',
                            'justify-center',
                            'h-8',
                            'w-8',
                            'dark:text-gray-500',
                            'dark:hover:text-white',
                            'dark:bg-gray-800',
                            'dark:hover:bg-gray-700',
                        ])
                        aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <footer class="bg-zinc-50 rounded-md shadow-sm mx-2 mb-2 dark:bg-gray-800">
        <div class="w-full max-w-[90%] mx-auto sm:max-w-screen-lg lg:max-w-screen-xl xl:max-w-screen-2xl 2xl:max-w-[90%] py-2 md:p-4 flex flex-wrap items-center justify-between">
            <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">
            © {{ now()->year }} {{ config('constants.admin_name') }}
            </span>
            <ul class="flex flex-wrap items-center mt-3 text-sm font-medium text-gray-500 dark:text-gray-400 sm:mt-0">
                <li>
                    <a href="{{ route('home') }}" class="hover:underline me-4 md:me-6">Home</a>
                </li>
                <li>
                    <a href="{{ route('tags.list') }}" class="hover:underline me-4 md:me-6">All Tags</a>
                </li>
                <li>
                    <a href="#" class="hover:underline me-4 md:me-6">Refresh Feed</a>
                </li>
            </ul>
        </div>
    </footer>

</body>
</html>
