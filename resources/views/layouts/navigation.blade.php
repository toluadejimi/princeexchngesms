<nav x-data="navWithNotifications('{{ route('notifications.index') }}', '{{ url('/api/notifications') }}', '{{ csrf_token() }}')" @keydown.escape.window="open = false; panelOpen = false" class="bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 safe-top"
     x-init="fetchUnreadCount()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center min-h-[3.5rem] sm:h-16">
            <div class="flex min-w-0">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center py-2 -my-2">
                        @if(\App\Models\SiteSetting::logoUrl())
                            <img src="{{ \App\Models\SiteSetting::logoUrl() }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-7 sm:h-8 object-contain max-w-[140px] sm:max-w-[180px]">
                        @else
                            <img x-show="darkMode" src="{{ asset('images/logo.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-7 sm:h-8 object-contain max-w-[140px] sm:max-w-[180px]" x-cloak>
                            <img x-show="!darkMode" src="{{ asset('images/logo-light.png') }}" alt="{{ \App\Models\SiteSetting::siteName() }}" class="h-7 sm:h-8 object-contain max-w-[140px] sm:max-w-[180px]">
                        @endif
                    </a>
                </div>
                <div class="hidden space-x-1 sm:ml-8 lg:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('fund-wallet.index')" :active="request()->routeIs('fund-wallet.*')">
                        {{ __('Fund Wallet') }}
                    </x-nav-link>
                    <x-nav-link :href="route('rentals.create.usa')" :active="request()->routeIs('rentals.create.usa')">
                        {{ __('USA Server') }}
                    </x-nav-link>
                    <x-nav-link :href="route('rentals.create.countries')" :active="request()->routeIs('rentals.create.countries')">
                        {{ __('Other Countries') }}
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

            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Balance</span>
                    <span class="font-semibold text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount((float)(auth()->user()?->wallet_balance ?? 0)) }}</span>
                </div>
                <button @click="panelOpen = true; fetchNotifications()" type="button" class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Notifications">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span x-show="unreadCount > 0" x-cloak class="absolute -top-0.5 -right-0.5 min-w-[1.25rem] h-5 px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                </button>
                <button @click="darkMode = !darkMode" type="button" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Toggle theme">
                    <svg x-show="!darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
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

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         x-cloak
         class="sm:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-lg"
         style="display: none;">
        <div class="max-h-[min(70vh,400px)] overflow-y-auto overscroll-contain">
            <div class="py-3 px-4 space-y-0.5">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('fund-wallet.index')" :active="request()->routeIs('fund-wallet.*')">{{ __('Fund Wallet') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('rentals.create.usa')" :active="request()->routeIs('rentals.create.usa')">{{ __('USA Server') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('rentals.create.countries')" :active="request()->routeIs('rentals.create.countries')">{{ __('Other Countries') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('support.index')" :active="request()->routeIs('support.*')">{{ __('Support') }}</x-responsive-nav-link>
                @if(auth()->user()?->is_admin)
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">{{ __('Admin') }}</x-responsive-nav-link>
                @endif
            </div>
            <div class="pt-4 pb-4 px-4 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-3 mb-3">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Wallet balance</span>
                    <span class="font-bold text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount((float)(auth()->user()?->wallet_balance ?? 0)) }}</span>
                </div>
                <div class="space-y-0.5">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifications side panel (all users) --}}
    <div x-show="panelOpen"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] overflow-hidden"
         style="display: none;">
        <div class="absolute inset-0 bg-slate-900/50 dark:bg-slate-950/70" @click="panelOpen = false; selectedNotification = null"></div>
        <div class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white dark:bg-slate-900 shadow-xl flex flex-col z-[101]"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             @click.self="panelOpen = false; selectedNotification = null">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800 shrink-0">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Notifications</h3>
                <button type="button" @click="panelOpen = false; selectedNotification = null" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 text-xl leading-none" aria-label="Close">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto min-h-0">
                {{-- Detail view when one notification is selected --}}
                <div x-show="selectedNotification" class="p-4 border-b border-slate-200 dark:border-slate-800">
                    <button type="button" @click="selectedNotification = null" class="text-sm text-mint-600 dark:text-mint-400 hover:underline mb-2">&larr; Back</button>
                    <h4 class="font-semibold text-slate-800 dark:text-slate-200" x-text="selectedNotification ? selectedNotification.title : ''"></h4>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 whitespace-pre-wrap break-words" x-text="selectedNotification ? selectedNotification.message : ''"></p>
                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-2" x-text="selectedNotification && selectedNotification.created_at ? new Date(selectedNotification.created_at).toLocaleString() : ''"></p>
                </div>
                {{-- List view --}}
                <div x-show="!selectedNotification" class="divide-y divide-slate-200 dark:divide-slate-800">
                    <template x-for="n in notifications" :key="n.id">
                        <button type="button" @click="openNotification(n)" class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition" :class="{ 'bg-mint-50 dark:bg-mint-900/20': !n.read_at }">
                            <p class="font-medium text-slate-800 dark:text-slate-200" x-text="n.title"></p>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 line-clamp-2 break-words" x-text="n.message || ''"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" x-text="n.created_at ? new Date(n.created_at).toLocaleDateString() : ''"></p>
                        </button>
                    </template>
                    <p x-show="!loading && notifications.length === 0" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No notifications yet.</p>
                    <p x-show="loading" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</nav>
