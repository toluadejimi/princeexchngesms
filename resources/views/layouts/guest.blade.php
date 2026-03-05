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

        <script>
            (function() {
                try {
                    var d = localStorage.getItem('darkMode');
                    document.documentElement.classList.toggle('dark', d === 'true' || d === true);
                } catch (e) {}
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak]{display:none!important}
            .auth-panel-bg { background: linear-gradient(160deg, rgba(15, 23, 42, 0.97) 0%, rgba(30, 58, 138, 0.9) 50%, rgba(14, 116, 144, 0.88) 100%); }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-200">
        <div class="min-h-screen flex flex-col lg:flex-row">
            {{-- Left: Branding + image (desktop) --}}
            <div class="hidden lg:flex lg:w-[52%] lg:min-h-screen lg:flex-col lg:justify-between relative overflow-hidden">
                <img src="https://images.unsplash.com/photo-1540962351504-02499e1b8d85?w=1200" alt="" class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 auth-panel-bg"></div>
                <div class="relative z-10 p-10 xl:p-14 flex flex-col">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-slate-300 hover:text-white text-sm font-semibold transition-colors">
                        <img src="{{ asset('images/logo.png') }}" alt="" class="h-8 w-auto opacity-90" onerror="this.style.display='none'" />
                        <span>{{ \App\Models\SiteSetting::siteName() }}</span>
                    </a>
                    <div class="mt-auto pt-8">
                        <h1 class="text-3xl xl:text-4xl font-bold text-white leading-tight max-w-[320px]">
                            Virtual numbers for verification.
                        </h1>
                        <p class="mt-4 text-slate-300 text-base leading-relaxed max-w-[300px]">
                            USA and global SMS numbers for WhatsApp, Telegram, Google, and more. Get your code in minutes.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3 text-white/80 text-sm">
                            <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-mint-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Instant delivery</span>
                            <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-mint-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Refund on cancel</span>
                            <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-mint-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Secure</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Form --}}
            <div class="flex-1 flex flex-col lg:justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-12 xl:px-20 bg-slate-100 dark:bg-slate-950">
                <div class="w-full max-w-[420px] mx-auto lg:mx-0">
                    {{-- Mobile: logo --}}
                    <a href="{{ route('home') }}" class="lg:hidden inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400 mb-6 transition-colors">
                        <img src="{{ asset('images/logo-light.png') }}" alt="" class="h-8 w-auto dark:hidden" />
                        <img src="{{ asset('images/logo.png') }}" alt="" class="h-8 w-auto hidden dark:block" onerror="this.style.display='none'" />
                        <span>{{ \App\Models\SiteSetting::siteName() }}</span>
                    </a>

                    <div class="flex justify-end -mt-1 mb-4 lg:mb-6">
                        <button type="button" id="theme-toggle" class="p-2.5 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors" aria-label="Toggle theme">
                            <svg class="dark:hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            <svg class="hidden dark:block h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </button>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl shadow-slate-200/50 dark:shadow-none p-6 sm:p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 hover:text-mint-600 dark:hover:text-mint-400 transition-colors font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Back to home
                        </a>
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
