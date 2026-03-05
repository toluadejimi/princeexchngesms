<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false') }" x-init="document.documentElement.classList.toggle('dark', darkMode); $watch('darkMode', val => { document.documentElement.classList.toggle('dark', val); localStorage.setItem('darkMode', val) })">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ \App\Models\SiteSetting::siteName() }} – Virtual SMS Verification</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        /* Dark mode hero: image + overlay */
        .hero-bg { background: linear-gradient(135deg, rgba(15, 23, 42, 0.94) 0%, rgba(30, 58, 138, 0.88) 50%, rgba(14, 116, 144, 0.84) 100%); }
        .hero-pattern { background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        /* Light mode hero: gradient + pattern, no image */
        .hero-light { background: linear-gradient(160deg, #f0fdfa 0%, #e0f2fe 35%, #f0f9ff 60%, #ecfeff 100%); }
        .hero-light-pattern { background-image: radial-gradient(circle at 1px 1px, rgba(20, 184, 166, 0.12) 1px, transparent 0); background-size: 32px 32px; }
        .safe-top { padding-top: env(safe-area-inset-top, 0); }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0); }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 min-h-screen overflow-x-hidden">
    <nav class="border-b border-slate-200/80 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md sticky top-0 z-50 safe-top">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 flex justify-between items-center min-h-14 sm:min-h-16 gap-2 sm:gap-4">
            <a href="{{ url('/') }}" class="flex items-center shrink-0 min-w-0 max-w-[45%] sm:max-w-none">
                <img x-show="darkMode" src="{{ asset('images/logo.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-8 sm:h-10 w-auto max-h-10 object-contain object-left" x-cloak />
                <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-8 sm:h-10 w-auto max-h-10 object-contain object-left" />
            </a>
            <div class="flex items-center gap-1.5 sm:gap-4 shrink-0 min-w-0">
                <button @click="darkMode = !darkMode" type="button" class="p-2 rounded-lg sm:rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center justify-center shrink-0" aria-label="Toggle theme">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg sm:rounded-xl bg-mint-500 text-white text-xs sm:text-sm font-semibold hover:bg-mint-600 transition shadow-lg shadow-mint-500/25 whitespace-nowrap">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-2.5 py-2 sm:px-4 sm:py-2.5 rounded-lg sm:rounded-xl text-slate-600 dark:text-slate-400 text-xs sm:text-base font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition whitespace-nowrap">Log in</a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-3 py-2 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl bg-gradient-to-r from-mint-500 to-teal-500 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-mint-500/25 hover:shadow-mint-500/30 hover:from-mint-600 hover:to-teal-600 transition whitespace-nowrap">Get started</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        {{-- Hero: light mode = gradient; dark mode = image + overlay --}}
        <section class="relative min-h-[85vh] sm:min-h-[90vh] flex flex-col justify-center overflow-hidden safe-top">
            {{-- Dark mode: background image + overlay --}}
            <div class="absolute inset-0 z-0 dark:block hidden">
                <img src="https://images.unsplash.com/photo-1540962351504-02499e1b8d85?w=1920" alt="" class="w-full h-full object-cover scale-105" />
                <div class="absolute inset-0 hero-bg hero-pattern"></div>
            </div>
            {{-- Light mode: gradient + subtle pattern (SMS/verification feel) --}}
            <div class="absolute inset-0 z-0 dark:hidden hero-light hero-light-pattern"></div>

            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
                {{-- Light: dark text; Dark: white text --}}
                <p class="text-mint-600 dark:text-mint-400 text-sm font-semibold uppercase tracking-widest mb-4 dark:drop-shadow-sm">Virtual SMS Verification</p>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.1] mb-6 max-w-4xl mx-auto dark:drop-shadow-md">
                    Receive SMS codes on demand
                </h1>
                <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed dark:drop-shadow-sm">
                    USA and global numbers for WhatsApp, Telegram, Google, and 100+ services. Fund your wallet, rent a number, get your code—instantly.
                </p>
                @guest
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 min-h-[48px] w-full max-w-[280px] sm:w-auto sm:max-w-none sm:min-h-[52px] px-6 sm:px-8 py-3.5 sm:py-4 rounded-2xl bg-gradient-to-r from-mint-500 to-teal-500 dark:from-white dark:to-white text-white dark:text-slate-900 font-bold text-sm sm:text-base shadow-xl hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98] transition mx-auto">
                    Start renting
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                @endguest
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-28 sm:h-32 bg-gradient-to-t from-slate-50 dark:from-slate-950 to-transparent pointer-events-none z-10"></div>
        </section>

        {{-- Supported services --}}
        <section class="py-16 sm:py-24 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-4xl font-bold text-center text-slate-800 dark:text-slate-100 mb-3">Verify your accounts</h2>
                <p class="text-center text-slate-600 dark:text-slate-400 max-w-xl mx-auto mb-12 text-base">Get SMS codes for the platforms you use every day.</p>
                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-6">
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
                    <div class="flex flex-col items-center justify-center p-4 sm:p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg hover:border-mint-300 dark:hover:border-mint-700 transition duration-300 {{ $app['color'] }}">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 flex items-center justify-center mb-3">
                            <x-service-icons :name="$app['name']" class="w-8 h-8 sm:w-10 sm:h-10" />
                        </div>
                        <span class="text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 text-center">{{ $app['name'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Visual + copy block (flight / tech imagery) --}}
        <section class="py-16 sm:py-24 bg-slate-100 dark:bg-slate-900/50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl ring-1 ring-slate-200/50 dark:ring-slate-700/50">
                        <img src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800" alt="Mobile verification" class="w-full h-[280px] sm:h-[360px] object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                    </div>
                    <div>
                        <h2 class="text-2xl sm:text-4xl font-bold text-slate-800 dark:text-slate-100 mb-4">Instant numbers, real codes</h2>
                        <p class="text-slate-600 dark:text-slate-400 text-lg leading-relaxed mb-6">Choose a server, pick your service, and receive a dedicated number. When the SMS arrives, we deliver the code to your dashboard—no delays, no hassle.</p>
                        <ul class="space-y-3 text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-mint-500/20 text-mint-600 dark:text-mint-400 text-sm font-bold">1</span> Fund your wallet once</li>
                            <li class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-mint-500/20 text-mint-600 dark:text-mint-400 text-sm font-bold">2</span> Rent a number for your app</li>
                            <li class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-mint-500/20 text-mint-600 dark:text-mint-400 text-sm font-bold">3</span> Get the code and verify</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- Servers --}}
        <section class="py-16 sm:py-24">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-4xl font-bold text-center text-slate-800 dark:text-slate-100 mb-12">Choose your server</h2>
                <div class="grid md:grid-cols-2 gap-6 sm:gap-8">
                    <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-lg hover:shadow-xl hover:border-mint-300 dark:hover:border-mint-700 transition duration-300">
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-4xl">🇺🇸</span>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">Server 1</h3>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 mb-5">US numbers only. Ideal when the service requires a United States phone number.</p>
                        <ul class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><span class="text-mint-500 font-bold">✓</span> Fixed USA country</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500 font-bold">✓</span> Optional operators</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500 font-bold">✓</span> Competitive pricing</li>
                        </ul>
                    </div>
                    <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-lg hover:shadow-xl hover:border-mint-300 dark:hover:border-mint-700 transition duration-300">
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-4xl">🌐</span>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">Server 2</h3>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 mb-5">Multi-country—choose from 150+ countries and local numbers.</p>
                        <ul class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <li class="flex items-center gap-2"><span class="text-mint-500 font-bold">✓</span> Country dropdown</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500 font-bold">✓</span> Services per country</li>
                            <li class="flex items-center gap-2"><span class="text-mint-500 font-bold">✓</span> Live pricing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section class="py-16 sm:py-24 bg-slate-100 dark:bg-slate-900/50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-4xl font-bold text-center text-slate-800 dark:text-slate-100 mb-12">Why use us</h2>
                <div class="grid sm:grid-cols-3 gap-6 sm:gap-8">
                    <div class="text-center p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg transition duration-300">
                        <div class="w-16 h-16 rounded-2xl bg-mint-100 dark:bg-mint-900/30 flex items-center justify-center mx-auto text-mint-600 dark:text-mint-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-200 text-lg">Wallet</h3>
                        <p class="text-slate-600 dark:text-slate-400 mt-2 text-sm">Add balance once, rent as you go. Full refunds on cancel.</p>
                    </div>
                    <div class="text-center p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg transition duration-300">
                        <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mx-auto text-blue-600 dark:text-blue-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-200 text-lg">Instant</h3>
                        <p class="text-slate-600 dark:text-slate-400 mt-2 text-sm">Get a number and receive SMS codes in minutes.</p>
                    </div>
                    <div class="text-center p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-lg transition duration-300">
                        <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mx-auto text-amber-600 dark:text-amber-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-200 text-lg">Secure</h3>
                        <p class="text-slate-600 dark:text-slate-400 mt-2 text-sm">Encrypted keys, rate limits, and secure request handling.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section class="py-16 sm:py-24">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-4xl font-bold text-center text-slate-800 dark:text-slate-100 mb-12">FAQ</h2>
                <div class="space-y-4">
                    <details class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 sm:p-6 shadow-sm group">
                        <summary class="font-semibold cursor-pointer list-none flex justify-between items-center gap-3 text-slate-800 dark:text-slate-200">How do I rent a number? <span class="text-slate-400 group-open:rotate-180 transition shrink-0">▼</span></summary>
                        <p class="mt-4 text-slate-600 dark:text-slate-400 text-sm leading-relaxed">Choose Server 1 (USA) or Server 2 (Global), pick a service (e.g. WhatsApp or Telegram), and pay from your wallet. You get a phone number and we deliver the SMS code when it arrives.</p>
                    </details>
                    <details class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 sm:p-6 shadow-sm group">
                        <summary class="font-semibold cursor-pointer list-none flex justify-between items-center gap-3 text-slate-800 dark:text-slate-200">What if I cancel? <span class="text-slate-400 group-open:rotate-180 transition shrink-0">▼</span></summary>
                        <p class="mt-4 text-slate-600 dark:text-slate-400 text-sm leading-relaxed">Cancelling an active rental refunds the full cost to your wallet. On Server 1, cancel is available 10 minutes after rental start.</p>
                    </details>
                    <details class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 sm:p-6 shadow-sm group">
                        <summary class="font-semibold cursor-pointer list-none flex justify-between items-center gap-3 text-slate-800 dark:text-slate-200">Server 1 vs Server 2? <span class="text-slate-400 group-open:rotate-180 transition shrink-0">▼</span></summary>
                        <p class="mt-4 text-slate-600 dark:text-slate-400 text-sm leading-relaxed">Server 1 offers only United States numbers. Server 2 lets you select from many countries and see live pricing per country.</p>
                    </details>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="py-20 sm:py-28 safe-bottom">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="relative rounded-3xl overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200" alt="" class="w-full h-64 sm:h-80 object-cover" />
                    <div class="absolute inset-0 bg-slate-900/75 flex flex-col items-center justify-center p-8">
                        <h2 class="text-2xl sm:text-4xl font-bold text-white mb-3">Ready to get your verification codes?</h2>
                        <p class="text-slate-300 mb-8 max-w-lg">Join thousands of users who verify accounts with virtual SMS every day.</p>
                        @guest
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 min-h-[48px] px-8 py-3.5 rounded-2xl bg-white text-slate-900 font-bold shadow-xl hover:shadow-2xl hover:scale-[1.02] transition">Create account</a>
                        @else
                        <a href="{{ route('rentals.create') }}" class="inline-flex items-center gap-2 min-h-[48px] px-8 py-3.5 rounded-2xl bg-white text-slate-900 font-bold shadow-xl hover:shadow-2xl hover:scale-[1.02] transition">Rent a number</a>
                        @endguest
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 dark:border-slate-800 py-8 sm:py-12 mt-4 safe-bottom bg-white dark:bg-slate-900/50">
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
