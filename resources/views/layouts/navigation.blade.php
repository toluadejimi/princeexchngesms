<nav x-data="navWithNotifications('{{ route('api.notifications.index') }}', '{{ url('/api/notifications') }}', '{{ csrf_token() }}', {{ ($openNotifications ?? false) ? 'true' : 'false' }})" @keydown.escape.window="open = false; panelOpen = false" class="bg-white/98 dark:bg-slate-900/98 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 sticky top-0 z-50 safe-top shadow-sm"
     x-init="fetchUnreadCount(); if (openNotificationsOnLogin) { panelOpen = true; fetchNotifications(); }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center min-h-[3.5rem] sm:h-16 gap-4">
            {{-- Logo --}}
            <div class="shrink-0 flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center py-2 -my-2 gap-2">
                    @if(\App\Models\SiteSetting::logoUrl())
                        <img src="{{ \App\Models\SiteSetting::logoUrl() }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-7 sm:h-8 object-contain max-w-[140px] sm:max-w-[180px]" onerror="this.style.display='none'; var s=this.nextElementSibling; if(s) s.style.display='inline';">
                        <span class="logo-fallback hidden font-semibold text-slate-800 dark:text-slate-200 text-sm sm:text-base truncate max-w-[140px] sm:max-w-[180px]" style="display: none;">{{ \App\Models\SiteSetting::siteName() }}</span>
                    @else
                        <img x-show="darkMode" src="{{ asset('images/logo.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-7 sm:h-8 object-contain max-w-[140px] sm:max-w-[180px]" x-cloak onerror="this.style.display='none'; var s=this.closest('a').querySelector('.logo-fallback'); if(s) s.style.display='inline';">
                        <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-7 sm:h-8 object-contain max-w-[140px] sm:max-w-[180px]" onerror="this.style.display='none'; var s=this.closest('a').querySelector('.logo-fallback'); if(s) s.style.display='inline';">
                        <span class="logo-fallback hidden font-semibold text-slate-800 dark:text-slate-200 text-sm sm:text-base truncate max-w-[140px] sm:max-w-[180px]" style="display: none;">{{ \App\Models\SiteSetting::siteName() }}</span>
                    @endif
                </a>
            </div>

            {{-- Desktop nav: pill group --}}
            <div class="hidden sm:flex sm:items-center sm:flex-1 sm:justify-center sm:min-w-0">
                <div class="inline-flex items-center gap-0.5 p-1 rounded-2xl bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/80">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('rentals.create.server1')" :active="request()->routeIs('rentals.create.server1')">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-400 text-amber-950 shrink-0">Recommended</span>
                            <span>{{ __('Server 1') }}</span>
                        </span>
                    </x-nav-link>
                    <x-nav-link :href="route('rentals.create.server2')" :active="request()->routeIs('rentals.create.server2')">
                        {{ __('Server 2') }}
                    </x-nav-link>
                    <x-nav-link :href="route('fund-wallet.index')" :active="request()->routeIs('fund-wallet.*')">
                        {{ __('Fund Wallet') }}
                    </x-nav-link>
                    <x-nav-link :href="route('support.index')" :active="request()->routeIs('support.*')">
                        {{ __('Support') }}
                    </x-nav-link>
                    @if(auth()->user()?->is_admin)
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                        {{ __('Admin') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            {{-- Right: balance, actions, user --}}
            <div class="hidden sm:flex sm:items-center sm:gap-2 shrink-0">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 border border-slate-200/60 dark:border-slate-700/80">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Balance</span>
                    <span class="font-semibold text-mint-600 dark:text-mint-400 tabular-nums">{{ \App\Models\SiteSetting::formatWalletAmount((float)(auth()->user()?->wallet_balance ?? 0)) }}</span>
                </div>
                <div class="flex items-center gap-0.5 pl-2 border-l border-slate-200 dark:border-slate-700">
                    <button @click="panelOpen = true; fetchNotifications()" type="button" class="relative p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300 transition" aria-label="Notifications">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-show="unreadCount > 0" x-cloak class="absolute top-1 right-1 min-w-[1.25rem] h-5 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                    </button>
                    <button @click="darkMode = !darkMode" type="button" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300 transition" aria-label="Toggle theme">
                        <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200/60 dark:border-slate-700/80 transition">
                            <span class="max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center gap-2 sm:hidden">
                <button @click="darkMode = !darkMode" type="button" class="min-h-[44px] min-w-[44px] flex items-center justify-center p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Toggle theme">
                    <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <button @click="panelOpen = true; fetchNotifications()" type="button" class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Notifications">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span x-show="unreadCount > 0" x-cloak class="absolute top-0 right-0 min-w-[1.25rem] h-5 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                </button>
                <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 min-w-0 max-w-[100px]">
                    <span class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400 truncate">Bal</span>
                    <span class="font-semibold text-mint-600 dark:text-mint-400 text-sm truncate">{{ \App\Models\SiteSetting::formatWalletAmount((float)(auth()->user()?->wallet_balance ?? 0)) }}</span>
                </div>
                <button @click="open = !open" type="button" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 active:bg-slate-200 dark:active:bg-slate-700 transition" aria-label="Menu" :aria-expanded="open">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu (dark-theme friendly, Server 1 = Recommended with yellow) --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         x-cloak
         class="sm:hidden border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg"
         style="display: none;">
        <div class="max-h-[min(70vh,400px)] overflow-y-auto overscroll-contain">
            <div class="py-3 px-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
                {{-- Server 1: yellow Recommended + yellow bg (mobile only) --}}
                <a href="{{ route('rentals.create.server1') }}" class="flex items-center gap-2 min-h-[48px] w-full px-4 py-3 rounded-xl text-base font-medium transition {{ request()->routeIs('rentals.create.server1') ? 'bg-amber-200 dark:bg-amber-900/50 text-amber-900 dark:text-amber-100 border border-amber-300 dark:border-amber-700' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-900 dark:text-amber-200 border border-amber-200 dark:border-amber-800 hover:bg-amber-200/80 dark:hover:bg-amber-900/50' }}">
                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-400 dark:bg-amber-500 text-amber-950 dark:text-amber-950">Recommended</span>
                    <span>{{ __('Server 1') }}</span>
                </a>
                <x-responsive-nav-link :href="route('rentals.create.server2')" :active="request()->routeIs('rentals.create.server2')">{{ __('Server 2') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('fund-wallet.index')" :active="request()->routeIs('fund-wallet.*')">{{ __('Fund Wallet') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('support.index')" :active="request()->routeIs('support.*')">{{ __('Support') }}</x-responsive-nav-link>
                @if(auth()->user()?->is_admin)
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">{{ __('Admin') }}</x-responsive-nav-link>
                @endif
            </div>
            <div class="pt-4 pb-4 px-3 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-600 px-4 py-3 mb-3">
                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Wallet balance</span>
                    <span class="font-bold text-mint-600 dark:text-mint-400 tabular-nums">{{ \App\Models\SiteSetting::formatWalletAmount((float)(auth()->user()?->wallet_balance ?? 0)) }}</span>
                </div>
                <div class="space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifications: teleport to body so modal is in true center of viewport (not under header) --}}
    <template x-teleport="body">
        <div x-show="panelOpen"
             x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
             style="display: none;">
            <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80" @click="closeNotificationModal()" aria-hidden="true"></div>
            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-2rem)] max-w-lg max-h-[85vh] flex flex-col bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             @click.stop>
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800 shrink-0">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200" x-text="modalNotification ? 'Notification' : 'Notifications'"></h3>
                <button type="button" @click="modalNotification ? closeNotificationDetail() : closeNotificationModal()" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 text-xl leading-none" aria-label="Close">
                    <span x-show="modalNotification">&larr; Back</span>
                    <span x-show="!modalNotification" x-cloak>&times;</span>
                </button>
            </div>
            {{-- Detail view (single notification) --}}
            <div x-show="modalNotification" class="flex-1 overflow-y-auto min-h-0 p-5 sm:p-6">
                <h4 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-3" x-text="modalNotification ? modalNotification.title : ''"></h4>
                <p class="text-slate-600 dark:text-slate-400 whitespace-pre-wrap break-words" x-text="modalNotification ? modalNotification.message : ''"></p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-4" x-text="modalNotification && modalNotification.created_at ? new Date(modalNotification.created_at).toLocaleString() : ''"></p>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="closeNotificationDetail()" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition">Close</button>
                </div>
            </div>
            {{-- List view --}}
            <div x-show="!modalNotification" class="flex-1 overflow-y-auto min-h-0 divide-y divide-slate-200 dark:divide-slate-800" style="min-height: 200px;">
                <template x-for="n in notifications" :key="n.id">
                    <button type="button" @click="openNotification(n)" class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition" :class="{ 'bg-mint-50 dark:bg-mint-900/20': !n.read_at }">
                        <p class="font-medium text-slate-800 dark:text-slate-200" x-text="n.title"></p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 line-clamp-2 break-words" x-text="n.message || ''"></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" x-text="n.created_at ? new Date(n.created_at).toLocaleDateString() : ''"></p>
                        <span class="inline-block mt-1.5 text-xs font-medium text-mint-600 dark:text-mint-400">Read more →</span>
                    </button>
                </template>
                <p x-show="!loading && notifications.length === 0 && !errorMessage" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No notifications yet.</p>
                <p x-show="loading" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">Loading...</p>
                <p x-show="errorMessage" class="px-4 py-4 text-center text-red-500 dark:text-red-400 text-sm" x-text="errorMessage"></p>
            </div>
        </div>
        </div>
    </template>
</nav>
