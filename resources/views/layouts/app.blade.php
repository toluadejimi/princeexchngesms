<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false') }" x-init="document.documentElement.classList.toggle('dark', darkMode); $watch('darkMode', val => { document.documentElement.classList.toggle('dark', val); localStorage.setItem('darkMode', val) })">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\SiteSetting::siteName() }} - @yield('title', 'Dashboard')</title>
        @if(\App\Models\SiteSetting::faviconUrl())
        <link rel="icon" href="{{ \App\Models\SiteSetting::faviconUrl() }}">
        @endif
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 transition-colors duration-200">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white/80 dark:bg-slate-900/80 backdrop-blur border-b border-slate-200 dark:border-slate-800 shadow-sm safe-top">
                    <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="py-4 sm:py-6 px-4 sm:px-0 safe-bottom">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
