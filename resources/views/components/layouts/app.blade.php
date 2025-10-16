<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  data-theme="emerald">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Epicurus Tube') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen bg-base-300">
    <x-navbar />

    <main class="flex-1 bg-white rounded-md shadow-sm m-2 dark:bg-gray-800">
        <div class="w-full mx-auto sm:max-w-screen lg:max-w-screen-xl xl:max-w-screen-2xl p-4 md:flex md:items-center md:justify-between">
            {{ $slot }}
        </div>
    </main>

    <footer class="bg-zinc-50 rounded-md shadow-sm mx-2 mb-2 dark:bg-gray-800">
        <div class="w-full mx-auto sm:max-w-screen lg:max-w-screen-xl xl:max-w-screen-2xl p-4 md:flex md:items-center md:justify-between">
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
