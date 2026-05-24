<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Admin Dashboard
            </h2>
            <a href="{{ route('admin.settings.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">Settings</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        {{-- Overview stats --}}
        <section>
            <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Overview</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Users</p>
                            <p class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ number_format($totalUsers) }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="mt-3 inline-block text-xs font-medium text-mint-600 dark:text-mint-400 hover:underline">View all</a>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-mint-100 dark:bg-mint-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-mint-600 dark:text-mint-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total wallet balance</p>
                            <p class="text-xl font-bold text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount($totalWalletBalance) }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Across all users</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Total rentals</p>
                            <p class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ number_format($totalRentals) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Active rentals</p>
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($activeRentals) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-mint-100 dark:bg-mint-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-mint-600 dark:text-mint-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Revenue</p>
                            <p class="text-xl font-bold text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount((float) $totalRevenue) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Servers</p>
                            <p class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ $servers->count() }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.servers.index') }}" class="mt-3 inline-block text-xs font-medium text-mint-600 dark:text-mint-400 hover:underline">Manage</a>
                </div>
            </div>
        </section>

        {{-- Revenue by server --}}
        <section>
            <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Revenue by server</h3>
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Server</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($servers as $s)
                                @php $rev = $revenueByServer->get($s->id); $total = $rev ? (float) $rev->total : 0; @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                    <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $s->name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount($total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- Quick actions --}}
        <section>
            <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Quick actions</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.transactions.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-800 dark:bg-slate-700 text-white font-medium hover:bg-slate-700 dark:hover:bg-slate-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    All transactions
                </a>
                <a href="{{ route('admin.verifications.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-800 dark:bg-slate-700 text-white font-medium hover:bg-slate-700 dark:hover:bg-slate-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verification logs
                </a>
                <a href="{{ route('admin.support.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-800 dark:bg-slate-700 text-white font-medium hover:bg-slate-700 dark:hover:bg-slate-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Support tickets
                </a>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                    User management
                </a>
                <a href="{{ route('admin.servers.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                    Manage servers
                </a>
                <a href="{{ route('admin.pricing.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-mint-500 text-white font-medium hover:bg-mint-600 transition">
                    Pricing
                </a>
                <a href="{{ route('admin.settings.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                    Settings
                </a>
                <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Broadcast notifications
                </a>
                <a href="{{ route('admin.logs') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600 transition" target="_blank" rel="noopener">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Logs
                </a>
            </div>
        </section>
    </div>
</x-app-layout>
