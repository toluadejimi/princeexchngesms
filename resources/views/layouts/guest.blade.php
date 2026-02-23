<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} – {{ \App\Models\SiteSetting::siteName() }}</title>
        @if(\App\Models\SiteSetting::faviconUrl())
        <link rel="icon" href="{{ \App\Models\SiteSetting::faviconUrl() }}">
        @endif
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />

        {{-- Apply theme before paint to avoid flash --}}
        <script>
            (function() {
                try {
                    var d = localStorage.getItem('darkMode');
                    document.documentElement.classList.toggle('dark', d === 'true' || d === true);
                } catch (e) {}
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-200">
        <div class="min-h-screen flex flex-col lg:flex-row">
            {{-- Left: Branding (desktop only) --}}
            <div class="hidden lg:flex lg:w-[48%] lg:min-h-screen lg:flex-col lg:justify-between bg-slate-800 dark:bg-slate-900 p-10 xl:p-14">
                <a href="{{ route('home') }}" class="text-slate-300 hover:text-white text-sm font-semibold transition-colors">SMS Rental</a>
                <div class="mt-auto">
                    <h1 class="text-2xl xl:text-[1.75rem] font-semibold text-white leading-tight max-w-[280px]">
                        Virtual numbers for verification.
                    </h1>
                    <p class="mt-3 text-slate-400 text-sm leading-relaxed max-w-[260px]">
                        USA and global SMS numbers for WhatsApp, Telegram, Google, and more.
                    </p>
                </div>
            </div>

            {{-- Right: Form --}}
            <div class="flex-1 flex flex-col lg:justify-center py-8 px-4 sm:px-6 lg:px-12 xl:px-16 bg-slate-100 dark:bg-slate-950">
                <div class="w-full max-w-[400px] mx-auto lg:mx-0 lg:max-w-[380px]">
                    {{-- Mobile: logo --}}
                    <a href="{{ route('home') }}" class="lg:hidden inline-block text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400 mb-6 transition-colors">SMS Rental</a>

                    {{-- Theme toggle --}}
                    <div class="flex justify-end -mt-1 mb-4 lg:mb-6">
                        <button type="button" id="theme-toggle" class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors" aria-label="Toggle theme">
                            <svg class="dark:hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            <svg class="hidden dark:block h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </button>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 sm:p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        <a href="{{ route('home') }}" class="hover:text-mint-600 dark:hover:text-mint-400 transition-colors">← Back to home</a>
                    </p>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('theme-toggle').addEventListener('click', function() {
                var html = document.documentElement;
                var isDark = html.classList.toggle('dark');
                try { localStorage.setItem('darkMode', isDark); } catch (e) {}
            });
        </script>
    </body>
</html>
