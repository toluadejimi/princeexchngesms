<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false') }" x-init="document.documentElement.classList.toggle('dark', darkMode); $watch('darkMode', val => { document.documentElement.classList.toggle('dark', val); localStorage.setItem('darkMode', val) })">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \App\Models\SiteSetting::siteName() }} – Virtual SMS Verification</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .hero-gradient { background: linear-gradient(135deg, rgba(16, 185, 129, 0.06) 0%, rgba(59, 130, 246, 0.06) 50%, rgba(139, 92, 246, 0.04) 100%); }
        .dark .hero-gradient { background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(59, 130, 246, 0.08) 50%, rgba(139, 92, 246, 0.06) 100%); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 min-h-screen">
    <nav class="border-b border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <a href="{{ url('/') }}" class="flex items-center gap-2 shrink-0">
                <img x-show="darkMode" src="{{ asset('images/logo.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-9 sm:h-10 w-auto object-contain" x-cloak />
                <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-9 sm:h-10 w-auto object-contain" />
            </a>
            <div class="flex items-center gap-3">
                <button @click="darkMode = !darkMode" type="button" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Toggle theme">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl bg-mint-500 text-white font-medium hover:bg-mint-600 transition shadow-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium transition">Log in</a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-mint-500 to-blue-500 text-white font-semibold shadow-lg shadow-mint-500/25 hover:shadow-mint-500/30 transition">Get started</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        {{-- Hero --}}
        <section class="hero-gradient py-16 sm:py-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="flex justify-center mb-6">
                    <img x-show="darkMode" src="{{ asset('images/logo.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-16 sm:h-20 w-auto object-contain drop-shadow-sm" x-cloak />
                    <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-16 sm:h-20 w-auto object-contain drop-shadow-sm" />
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 dark:text-white mb-4 tracking-tight">Virtual SMS numbers for verification</h1>
                <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-10">USA and global numbers for WhatsApp, Telegram, Google, and 100+ services. Fund your wallet, rent a number, receive your code.</p>
                @guest
                <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 rounded-xl bg-gradient-to-r from-mint-500 to-blue-500 text-white font-semibold text-lg shadow-lg shadow-mint-500/25 hover:shadow-mint-500/30 hover:scale-[1.02] transition">Start renting</a>
                @endguest
            </div>
        </section>

        {{-- Supported services – social / app icons --}}
        <section class="py-16 sm:py-20 border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-center text-slate-800 dark:text-slate-200 mb-4">Verify your accounts</h2>
                <p class="text-center text-slate-600 dark:text-slate-400 max-w-xl mx-auto mb-12">Get SMS codes for the platforms you use every day.</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-6">
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
                    <div class="flex flex-col items-center justify-center p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-mint-200 dark:hover:border-mint-800 transition {{ $app['color'] }}">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center mb-3 [&>svg]:w-10 [&>svg]:h-10 sm:[&>svg]:w-12 sm:[&>svg]:h-12">
                            <x-service-icons :name="$app['name']" class="w-10 h-10 sm:w-12 sm:h-12" />
                        </div>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $app['name'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- USA vs Global --}}
        <section class="py-16 sm:py-20 bg-slate-100/50 dark:bg-slate-900/30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-center text-slate-800 dark:text-slate-200 mb-12">Choose your server</h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-8 shadow-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-3xl">🇺🇸</span>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">USA Server</h3>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 mb-5">US numbers only. Ideal when the service requires a United States phone number.</p>
                        <ul class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Fixed USA country</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> No country selector</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Competitive USA pricing</li>
                        </ul>
                    </div>
                    <div class="bg-white dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-8 shadow-lg">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-3xl">🌐</span>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">Global Server</h3>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 mb-5">Multi-country provider – choose from 150+ countries and local numbers.</p>
                        <ul class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Country dropdown</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Services per country</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500">✓</span> Live pricing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section class="py-16 sm:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-center text-slate-800 dark:text-slate-200 mb-12">Why use us</h2>
                <div class="grid sm:grid-cols-3 gap-8">
                    <div class="text-center p-8 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-mint-100 dark:bg-mint-900/30 flex items-center justify-center mx-auto text-mint-600 dark:text-mint-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-200 mt-4">Wallet</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">Add balance once, rent as you go. Full refunds on cancel.</p>
                    </div>
                    <div class="text-center p-8 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto text-blue-600 dark:text-blue-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-200 mt-4">Instant</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">Get a number and receive SMS codes in minutes.</p>
                    </div>
                    <div class="text-center p-8 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="w-14 h-14 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mx-auto text-amber-600 dark:text-amber-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h3 class="font-semibold text-slate-800 dark:text-slate-200 mt-4">Secure</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">Encrypted keys, rate limits, and secure request handling.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section class="py-16 sm:py-20 bg-slate-100/50 dark:bg-slate-900/30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-center text-slate-800 dark:text-slate-200 mb-12">FAQ</h2>
                <div class="max-w-2xl mx-auto space-y-4">
                    <details class="bg-white dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm group">
                        <summary class="font-medium cursor-pointer list-none flex justify-between items-center">How do I rent a number? <span class="text-slate-400 group-open:rotate-180 transition">▼</span></summary>
                        <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm">Choose USA or Global server, pick a service (e.g. WhatsApp or Telegram), and pay from your wallet. You get a phone number and we deliver the SMS code when it arrives.</p>
                    </details>
                    <details class="bg-white dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm group">
                        <summary class="font-medium cursor-pointer list-none flex justify-between items-center">What if I cancel? <span class="text-slate-400 group-open:rotate-180 transition">▼</span></summary>
                        <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm">Cancelling an active rental refunds the full cost to your wallet.</p>
                    </details>
                    <details class="bg-white dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm group">
                        <summary class="font-medium cursor-pointer list-none flex justify-between items-center">USA vs Global? <span class="text-slate-400 group-open:rotate-180 transition">▼</span></summary>
                        <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm">USA Server offers only United States numbers. Global Server lets you select from many countries and see live pricing per country.</p>
                    </details>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="py-16 sm:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-slate-600 dark:text-slate-400 mb-6 text-lg">Ready to get your verification codes?</p>
                @guest
                <a href="{{ route('register') }}" class="inline-flex px-8 py-4 rounded-xl bg-mint-500 text-white font-semibold hover:bg-mint-600 transition shadow-lg">Create account</a>
                @else
                <a href="{{ route('rentals.create') }}" class="inline-flex px-8 py-4 rounded-xl bg-mint-500 text-white font-semibold hover:bg-mint-600 transition shadow-lg">Rent a number</a>
                @endguest
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 dark:border-slate-800 py-10 mt-4">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center">
                <img x-show="darkMode" src="{{ asset('images/logo.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-8 w-auto object-contain opacity-90" x-cloak />
                <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-8 w-auto object-contain opacity-90" />
            </a>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ \App\Models\SiteSetting::siteName() }} – Virtual SMS verification.</p>
        </div>
    </footer>
</body>
</html>
