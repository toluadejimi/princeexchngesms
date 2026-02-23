<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false') }" x-init="document.documentElement.classList.toggle('dark', darkMode); $watch('darkMode', val => { document.documentElement.classList.toggle('dark', val); localStorage.setItem('darkMode', val) })">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ \App\Models\SiteSetting::siteName() }} – Virtual SMS Verification</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .hero-gradient { background: linear-gradient(135deg, rgba(16, 185, 129, 0.06) 0%, rgba(59, 130, 246, 0.06) 50%, rgba(139, 92, 246, 0.04) 100%); }
        .dark .hero-gradient { background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(59, 130, 246, 0.08) 50%, rgba(139, 92, 246, 0.06) 100%); }
        .safe-top { padding-top: env(safe-area-inset-top, 0); }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 min-h-screen overflow-x-hidden">
    <nav class="border-b border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur sticky top-0 z-50 safe-top">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 flex justify-between items-center min-h-14 sm:h-16 gap-2">
            <a href="{{ url('/') }}" class="flex items-center shrink-0 min-w-0 max-w-[50%] sm:max-w-none">
                <img x-show="darkMode" src="{{ asset('images/logo.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-8 sm:h-10 w-auto max-h-10 object-contain object-left" x-cloak />
                <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-8 sm:h-10 w-auto max-h-10 object-contain object-left" />
            </a>
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <button @click="darkMode = !darkMode" type="button" class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 p-2 rounded-xl sm:rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center justify-center" aria-label="Toggle theme">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="min-h-[44px] inline-flex items-center px-3 sm:px-4 py-2.5 rounded-xl bg-mint-500 text-white text-sm sm:text-base font-medium hover:bg-mint-600 transition shadow-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="min-h-[44px] inline-flex items-center px-4 sm:px-4 py-2.5 rounded-xl sm:rounded-xl text-slate-600 dark:text-slate-400 sm:bg-transparent bg-gradient-to-r from-mint-500 to-blue-500 sm:text-slate-600 sm:dark:text-slate-400 text-white font-semibold sm:font-medium shadow-lg sm:shadow-none shadow-mint-500/25 hover:shadow-mint-500/30 sm:hover:bg-slate-100 sm:dark:hover:bg-slate-800 transition">Login</a>
                    <a href="{{ route('register') }}" class="min-h-[44px] hidden sm:inline-flex items-center px-4 sm:px-5 py-2.5 rounded-xl bg-gradient-to-r from-mint-500 to-blue-500 text-white text-sm sm:text-base font-semibold shadow-lg shadow-mint-500/25 hover:shadow-mint-500/30 transition">Get started</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        {{-- Hero --}}
        <section class="hero-gradient py-10 sm:py-16 lg:py-24 safe-top">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="flex justify-center mb-4 sm:mb-6">
                    <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-14 sm:h-20 w-auto max-w-[85vw] object-contain drop-shadow-sm" />
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-bold text-slate-900 dark:text-white mb-3 sm:mb-4 tracking-tight leading-tight px-1">Virtual SMS numbers for verification</h1>
                <p class="text-base sm:text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-6 sm:mb-10 px-1">USA and global numbers for WhatsApp, Telegram, Google, and 100+ services. Fund your wallet, rent a number, receive your code.</p>
                @guest
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center min-h-[48px] w-full max-w-sm mx-auto sm:w-auto sm:max-w-none px-8 py-3.5 sm:py-4 rounded-xl bg-gradient-to-r from-mint-500 to-blue-500 text-white font-semibold text-base sm:text-lg shadow-lg shadow-mint-500/25 hover:shadow-mint-500/30 active:scale-[0.98] transition">Start renting</a>
                @endguest
            </div>
        </section>

        {{-- Supported services – social / app icons --}}
        <section class="py-10 sm:py-16 lg:py-20 border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl sm:text-3xl font-bold text-center text-slate-800 dark:text-slate-200 mb-2 sm:mb-4">Verify your accounts</h2>
                <p class="text-center text-slate-600 dark:text-slate-400 max-w-xl mx-auto mb-8 sm:mb-12 text-sm sm:text-base px-1">Get SMS codes for the platforms you use every day.</p>
                <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-6">
                    @foreach([
                        ['name' => 'WhatsApp', 'color' => 'text-emerald-600 dark:text-emerald-400'],
                        ['name' => 'Telegram', 'color' => 'text-sky-600 dark:text-sky-400'],
                        ['name' => 'Google', 'color' => 'text-slate-700 dark:text-slate-300'],
                        ['name' => 'Instagram', 'color' => 'text-pink-600 dark:text-pink-400'],
                        ['name' => 'Facebook', 'color' => 'text-blue-600 dark:text-blue-400'],
                        ['name' => 'Twitter', 'color' => 'text-slate-700 dark:text-slate-300'],
                        ['name' => 'Discord', 'color' => 'text-indigo-600 dark:text-indigo-400'],
                        ['name' => 'TikTok', 'color' => 'text-slate-800 dark:text-slate-200'],
                        ['name' => 'Amazon', 'color' => 'text-amber-600 dark:text-amber-400'],
                        ['name' => 'Microsoft', 'color' => 'text-blue-600 dark:text-blue-400'],
                        ['name' => 'Apple', 'color' => 'text-slate-800 dark:text-slate-200'],
                        ['name' => 'LinkedIn', 'color' => 'text-blue-700 dark:text-blue-400'],
                    ] as $app)
                    <div class="flex flex-col items-center justify-center p-3 sm:p-6 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-mint-200 dark:hover:border-mint-800 transition {{ $app['color'] }}">
                        <div class="w-10 h-10 sm:w-14 sm:h-14 flex items-center justify-center mb-2 sm:mb-3 [&>svg]:w-8 [&>svg]:h-8 sm:[&>svg]:w-12 sm:[&>svg]:h-12">
                            <x-service-icons :name="$app['name']" class="w-8 h-8 sm:w-12 sm:h-12" />
                        </div>
                        <span class="text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 text-center leading-tight">{{ $app['name'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- USA vs Global --}}
        <section class="py-10 sm:py-16 lg:py-20 bg-slate-100/50 dark:bg-slate-900/30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl sm:text-3xl font-bold text-center text-slate-800 dark:text-slate-200 mb-6 sm:mb-12">Choose your server</h2>
                <div class="grid md:grid-cols-2 gap-4 sm:gap-8">
                    <div class="bg-white dark:bg-slate-900/80 backdrop-blur rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-8 shadow-lg">
                        <div class="flex items-center gap-3 mb-3 sm:mb-4">
                            <span class="text-2xl sm:text-3xl">🇺🇸</span>
                            <h3 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-200">USA Server</h3>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 mb-4 sm:mb-5 text-sm sm:text-base">US numbers only. Ideal when the service requires a United States phone number.</p>
                        <ul class="space-y-1.5 sm:space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Fixed USA country</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> No country selector</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Competitive USA pricing</li>
                        </ul>
                    </div>
                    <div class="bg-white dark:bg-slate-900/80 backdrop-blur rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-8 shadow-lg">
                        <div class="flex items-center gap-3 mb-3 sm:mb-4">
                            <span class="text-2xl sm:text-3xl">🌐</span>
                            <h3 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-200">Global Server</h3>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 mb-4 sm:mb-5 text-sm sm:text-base">Multi-country provider – choose from 150+ countries and local numbers.</p>
                        <ul class="space-y-1.5 sm:space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Country dropdown</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Services per country</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Live pricing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section class="py-10 sm:py-16 lg:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl sm:text-3xl font-bold text-center text-slate-800 dark:text-slate-200 mb-6 sm:mb-12">Why use us</h2>
                <div class="grid sm:grid-cols-3 gap-4 sm:gap-8">
                    <div class="text-center p-5 sm:p-8 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-mint-100 dark:bg-mint-900/30 flex items-center justify-center mx-auto text-mint-600 dark:text-mint-400">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-200 mt-3 sm:mt-4">Wallet</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 sm:mt-2">Add balance once, rent as you go. Full refunds on cancel.</p>
                    </div>
                    <div class="text-center p-5 sm:p-8 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto text-blue-600 dark:text-blue-400">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-200 mt-3 sm:mt-4">Instant</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 sm:mt-2">Get a number and receive SMS codes in minutes.</p>
                    </div>
                    <div class="text-center p-5 sm:p-8 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mx-auto text-amber-600 dark:text-amber-400">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-200 mt-3 sm:mt-4">Secure</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 sm:mt-2">Encrypted keys, rate limits, and secure request handling.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section class="py-10 sm:py-16 lg:py-20 bg-slate-100/50 dark:bg-slate-900/30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-xl sm:text-3xl font-bold text-center text-slate-800 dark:text-slate-200 mb-6 sm:mb-12">FAQ</h2>
                <div class="max-w-2xl mx-auto space-y-3 sm:space-y-4">
                    <details class="bg-white dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-sm group">
                        <summary class="font-medium cursor-pointer list-none flex justify-between items-start sm:items-center gap-2 text-left min-h-[44px] py-1">How do I rent a number? <span class="text-slate-400 group-open:rotate-180 transition shrink-0 mt-0.5 sm:mt-0">▼</span></summary>
                        <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm">Choose USA or Global server, pick a service (e.g. WhatsApp or Telegram), and pay from your wallet. You get a phone number and we deliver the SMS code when it arrives.</p>
                    </details>
                    <details class="bg-white dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-sm group">
                        <summary class="font-medium cursor-pointer list-none flex justify-between items-center gap-2 text-left min-h-[44px] py-1">What if I cancel? <span class="text-slate-400 group-open:rotate-180 transition shrink-0">▼</span></summary>
                        <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm">Cancelling an active rental refunds the full cost to your wallet.</p>
                    </details>
                    <details class="bg-white dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-4 sm:p-5 shadow-sm group">
                        <summary class="font-medium cursor-pointer list-none flex justify-between items-start sm:items-center gap-2 text-left min-h-[44px] py-1">USA vs Global? <span class="text-slate-400 group-open:rotate-180 transition shrink-0 mt-0.5 sm:mt-0">▼</span></summary>
                        <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm">USA Server offers only United States numbers. Global Server lets you select from many countries and see live pricing per country.</p>
                    </details>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="py-10 sm:py-16 lg:py-20 safe-bottom">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-slate-600 dark:text-slate-400 mb-4 sm:mb-6 text-base sm:text-lg">Ready to get your verification codes?</p>
                @guest
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center min-h-[48px] w-full max-w-sm mx-auto sm:w-auto sm:max-w-none px-8 py-3.5 sm:py-4 rounded-xl bg-mint-500 text-white font-semibold hover:bg-mint-600 transition shadow-lg">Create account</a>
                @else
                <a href="{{ route('rentals.create') }}" class="inline-flex items-center justify-center min-h-[48px] w-full max-w-sm mx-auto sm:w-auto sm:max-w-none px-8 py-3.5 sm:py-4 rounded-xl bg-mint-500 text-white font-semibold hover:bg-mint-600 transition shadow-lg">Rent a number</a>
                @endguest
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 dark:border-slate-800 py-6 sm:py-10 mt-4 safe-bottom">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <a href="{{ url('/') }}" class="flex items-center">
                <img x-show="darkMode" src="{{ asset('images/logo.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-8 w-auto object-contain opacity-90" x-cloak />
                <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-8 w-auto object-contain opacity-90" />
            </a>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ \App\Models\SiteSetting::siteName() }} – Virtual SMS verification.</p>
        </div>
    </footer>
</body>
</html>
