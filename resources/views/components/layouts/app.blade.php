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
<body class="flex flex-col min-h-screen bg-base-300">
    <x-navbar />

    <main class="flex-1 bg-white rounded-md shadow-sm m-2 dark:bg-gray-800">
        <div class="w-full max-w-[90%] mx-auto sm:max-w-screen-lg lg:max-w-screen-xl xl:max-w-screen-2xl 2xl:max-w-[90%] py-2 md:p-4">
            {{ $slot }}
        </div>
    </main>

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
