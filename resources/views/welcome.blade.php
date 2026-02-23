<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false') }" x-init="document.documentElement.classList.toggle('dark', darkMode); $watch('darkMode', val => { document.documentElement.classList.toggle('dark', val); localStorage.setItem('darkMode', val) })">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SMS Rental') }} – Virtual SMS Numbers</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 min-h-screen">
    <nav class="border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <span class="text-xl font-bold bg-gradient-to-r from-mint-500 to-blue-500 bg-clip-text text-transparent">SMS Rental</span>
            <div class="flex items-center gap-4">
                <button @click="darkMode = !darkMode" type="button" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Toggle theme">🌓</button>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-mint-500 text-white font-medium hover:bg-mint-600 transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-slate-600 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">Log in</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-mint-500 to-blue-500 text-white font-medium shadow-neon-mint hover:shadow-lg transition">Get started</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <section class="text-center mb-20">
            <h1 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-white mb-4">Rent virtual SMS numbers in seconds</h1>
            <p class="text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-8">USA and global numbers for WhatsApp, Telegram, Google, and 100+ services. Pay from your wallet, get your code.</p>
            @guest
            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-gradient-to-r from-mint-500 to-blue-500 text-white font-semibold shadow-neon-mint hover:shadow-lg transition">Start renting</a>
            @endguest
        </section>

        <section class="grid md:grid-cols-2 gap-8 mb-20">
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-8 shadow-glass">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-200 mb-2">🇺🇸 USA Server</h2>
                <p class="text-slate-600 dark:text-slate-400 mb-4">US numbers only. Best for services that require a USA number.</p>
                <ul class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                    <li>• Fixed USA country</li>
                    <li>• No country selector</li>
                    <li>• Competitive USA pricing</li>
                </ul>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-8 shadow-glass">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-200 mb-2">🌐 Global Server</h2>
                <p class="text-slate-600 dark:text-slate-400 mb-4">Multi-country provider – choose from 150+ countries.</p>
                <ul class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                    <li>• Country dropdown</li>
                    <li>• Services per country</li>
                    <li>• Dynamic pricing</li>
                </ul>
            </div>
        </section>

        <section class="mb-20">
            <h2 class="text-2xl font-bold text-center text-slate-800 dark:text-slate-200 mb-8">Features</h2>
            <div class="grid sm:grid-cols-3 gap-6">
                <div class="text-center p-6 rounded-xl bg-slate-100 dark:bg-slate-800/50">
                    <span class="text-2xl">💳</span>
                    <h3 class="font-semibold mt-2">Wallet</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Add balance once, rent as you go. Refunds on cancel.</p>
                </div>
                <div class="text-center p-6 rounded-xl bg-slate-100 dark:bg-slate-800/50">
                    <span class="text-2xl">⏱</span>
                    <h3 class="font-semibold mt-2">Instant</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Get a number and receive SMS codes in minutes.</p>
                </div>
                <div class="text-center p-6 rounded-xl bg-slate-100 dark:bg-slate-800/50">
                    <span class="text-2xl">🔒</span>
                    <h3 class="font-semibold mt-2">Secure</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Encrypted keys, rate limits, and request logs.</p>
                </div>
            </div>
        </section>

        <section class="mb-20">
            <h2 class="text-2xl font-bold text-center text-slate-800 dark:text-slate-200 mb-8">FAQ</h2>
            <div class="max-w-2xl mx-auto space-y-4">
                <details class="bg-white/80 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                    <summary class="font-medium cursor-pointer">How do I rent a number?</summary>
                    <p class="mt-2 text-slate-600 dark:text-slate-400 text-sm">Choose USA or Global server, pick a service (e.g. WhatsApp), and pay from your wallet. You get a phone number and we poll for the SMS code.</p>
                </details>
                <details class="bg-white/80 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                    <summary class="font-medium cursor-pointer">What if I cancel?</summary>
                    <p class="mt-2 text-slate-600 dark:text-slate-400 text-sm">Cancelling an active rental refunds the cost to your wallet.</p>
                </details>
                <details class="bg-white/80 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                    <summary class="font-medium cursor-pointer">USA vs Global?</summary>
                    <p class="mt-2 text-slate-600 dark:text-slate-400 text-sm">USA Server only offers United States numbers. Global Server lets you select from many countries.</p>
                </details>
            </div>
        </section>

        <section class="text-center">
            <p class="text-slate-600 dark:text-slate-400 mb-4">Ready to get your verification codes?</p>
            @guest
            <a href="{{ route('register') }}" class="inline-flex px-6 py-3 rounded-xl bg-mint-500 text-white font-semibold hover:bg-mint-600 transition">Create account</a>
            @else
            <a href="{{ route('rentals.create') }}" class="inline-flex px-6 py-3 rounded-xl bg-mint-500 text-white font-semibold hover:bg-mint-600 transition">Rent a number</a>
            @endguest
        </section>
    </main>

    <footer class="border-t border-slate-200 dark:border-slate-800 py-8 mt-16">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm text-slate-500 dark:text-slate-400">SMS Rental – Virtual SMS numbers for verification.</div>
    </footer>
</body>
</html>
