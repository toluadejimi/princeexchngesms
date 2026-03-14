<x-app-layout>
    {{-- Login promotional popup (shown once per login when admin has set content) --}}
    @if(!empty($showLoginPopup))
    <div x-data="loginPopup('{{ route('api.dismiss-login-popup') }}', '{{ csrf_token() }}')"
         x-show="open"
         x-cloak
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         style="display: none;">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80" @click="dismiss()" aria-hidden="true"></div>
        <div class="relative z-[101] w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             @click.stop>
            <div class="p-6">
                @if(!empty($loginPopupTitle))
                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-3">{{ $loginPopupTitle }}</h3>
                @endif
                @if(!empty($loginPopupMessage))
                <p class="text-slate-600 dark:text-slate-400 whitespace-pre-wrap break-words">{{ $loginPopupMessage }}</p>
                @endif
                <div class="mt-6 flex flex-wrap items-center justify-end gap-2">
                    <button type="button" @click="dismiss()" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                        Disable
                    </button>
                    <button type="button" @click="dismiss()" class="px-4 py-2 rounded-xl bg-mint-500 text-white text-sm font-medium hover:bg-mint-600 transition">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                        {{ __('Dashboard') }}
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm sm:text-base mt-0.5">Overview and rentals</p>
                </div>
                <div class="flex gap-2 sm:gap-3 flex-shrink-0">
                    <a href="{{ route('rentals.create.server1') }}" class="relative flex-1 sm:flex-none inline-flex items-center justify-center min-h-[48px] px-5 py-2.5 rounded-2xl bg-gradient-to-r from-mint-500 to-teal-500 text-white font-semibold shadow-lg shadow-mint-500/25 hover:shadow-xl hover:shadow-mint-500/30 active:scale-[0.98] transition">
                        <span class="absolute -top-0.5 -right-0.5 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-400 text-amber-950 shadow-sm">Recommended</span>
                        Server 1
                    </a>
                    <a href="{{ route('rentals.create.server2') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center min-h-[48px] px-5 py-2.5 rounded-2xl bg-slate-700 hover:bg-slate-600 dark:bg-slate-600 dark:hover:bg-slate-500 text-white font-semibold active:scale-[0.98] transition">Server 2</a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-5 sm:space-y-6">
        @if (session('message'))
            <div class="rounded-2xl bg-mint-50 dark:bg-mint-900/20 border border-mint-200 dark:border-mint-800 px-4 sm:px-5 py-3.5 text-mint-800 dark:text-mint-200 text-sm font-medium">
                {{ session('message') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 sm:px-5 py-3.5 text-red-800 dark:text-red-200 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif
        @if (isset($unreadNotificationCount) && $unreadNotificationCount > 0)
            <div class="rounded-2xl bg-mint-50 dark:bg-mint-900/20 border border-mint-200 dark:border-mint-800 px-4 sm:px-5 py-3.5 flex flex-wrap items-center justify-between gap-2" x-data="{ dismissed: false }" x-show="!dismissed">
                <p class="text-mint-800 dark:text-mint-200 text-sm font-medium">
                    You have {{ $unreadNotificationCount }} new notification{{ $unreadNotificationCount === 1 ? '' : 's' }}.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.querySelector('[aria-label=Notifications]')?.click()" class="min-h-[40px] px-4 py-2 rounded-xl text-sm font-semibold text-mint-600 dark:text-mint-400 hover:bg-mint-100 dark:hover:bg-mint-900/40 transition">View</button>
                    <button type="button" @click="dismissed = true" class="min-h-[40px] w-10 flex items-center justify-center rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition" aria-label="Dismiss">&times;</button>
                </div>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <div class="group relative overflow-hidden rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition p-5 sm:p-6">
                <div class="absolute top-0 right-0 w-24 h-24 rounded-full -translate-y-1/2 translate-x-1/2 bg-mint-500/10 dark:bg-mint-500/5"></div>
                <div class="relative flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-mint-100 dark:bg-mint-900/40 text-mint-600 dark:text-mint-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Wallet</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tabular-nums">{{ \App\Models\SiteSetting::formatWalletAmount((float) $user->wallet_balance) }}</p>
                    <a href="{{ route('fund-wallet.index') }}" class="mt-4 inline-flex items-center justify-center gap-2 min-h-[44px] px-4 rounded-xl bg-mint-500 hover:bg-mint-600 text-white text-sm font-semibold transition active:scale-[0.98]">
                        Fund Wallet
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </a>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition p-5 sm:p-6">
                <div class="absolute top-0 right-0 w-24 h-24 rounded-full -translate-y-1/2 translate-x-1/2 bg-blue-500/10 dark:bg-blue-500/5"></div>
                <div class="relative flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Active</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tabular-nums">{{ $activeCount }}</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">rentals in progress</p>
                </div>
            </div>
            <div class="group relative overflow-hidden rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition p-5 sm:p-6 sm:col-span-2 lg:col-span-1">
                <div class="absolute top-0 right-0 w-24 h-24 rounded-full -translate-y-1/2 translate-x-1/2 bg-slate-400/10 dark:bg-slate-500/5"></div>
                <div class="relative flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </span>
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Rentals</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tabular-nums">{{ $rentals->total() }}</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">all time</p>
                </div>
            </div>
        </div>

        {{-- Rentals --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Rentals</h2>
                <div class="flex items-center gap-2 overflow-x-auto overscroll-x-contain pb-1 -mx-1 sm:mx-0 sm:pb-0 sm:flex-wrap">
                    <a href="{{ route('dashboard', request()->except('server','status')) }}" class="shrink-0 min-h-[44px] px-4 py-2.5 rounded-xl text-sm font-medium inline-flex items-center {{ !request('server') && !request('status') ? 'bg-mint-500 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition">All</a>
                    @foreach($servers as $s)
                        <a href="{{ route('dashboard', ['server' => $s->id] + request()->except('server')) }}" class="shrink-0 min-h-[44px] px-4 py-2.5 rounded-xl text-sm font-medium inline-flex items-center {{ request('server') == $s->id ? 'bg-mint-500 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition">{{ $s->type === 'smsconfirmed' ? 'Server 1' : 'Server 2' }}</a>
                    @endforeach
                    <a href="{{ route('dashboard', ['status' => 'active'] + request()->except('status')) }}" class="shrink-0 min-h-[44px] px-4 py-2.5 rounded-xl text-sm font-medium inline-flex items-center {{ request('status') === 'active' ? 'bg-mint-500 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition">Active</a>
                    <a href="{{ route('dashboard', ['status' => 'completed'] + request()->except('status')) }}" class="shrink-0 min-h-[44px] px-4 py-2.5 rounded-xl text-sm font-medium inline-flex items-center {{ request('status') === 'completed' ? 'bg-mint-500 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition">Completed</a>
                </div>
            </div>
            {{-- Mobile: card list --}}
            <div class="md:hidden p-4 sm:p-5 space-y-4">
                @forelse($rentals as $r)
                    @include('dashboard.partials.rental-card', ['r' => $r])
                @empty
                    <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 p-8 sm:p-10 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 mb-4">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 font-medium mb-1">No rentals yet</p>
                        <p class="text-sm text-slate-500 dark:text-slate-500 mb-4">Rent a number to get started</p>
                        <div class="flex flex-col sm:flex-row gap-2 justify-center">
                            <a href="{{ route('rentals.create.server1') }}" class="min-h-[48px] px-5 py-3 rounded-xl bg-mint-500 hover:bg-mint-600 text-white font-semibold inline-flex items-center justify-center transition">Server 1</a>
                            <a href="{{ route('rentals.create.server2') }}" class="min-h-[48px] px-5 py-3 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-semibold inline-flex items-center justify-center transition">Server 2</a>
                        </div>
                    </div>
                @endforelse
            </div>
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Country</th>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Service</th>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Number</th>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Code / Expires</th>
                            <th class="px-4 sm:px-6 py-3.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                            <th class="px-4 sm:px-6 py-3.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        @forelse($rentals as $r)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 sm:px-6 py-3.5 text-sm font-medium text-slate-800 dark:text-slate-200">{{ $r->getCountryDisplayName() }}</td>
                                <td class="px-4 sm:px-6 py-3.5 text-sm text-slate-700 dark:text-slate-300">{{ $r->getServiceDisplayName() }}</td>
                                <td class="px-4 sm:px-6 py-3.5 text-sm font-mono">
                                    @if($r->phone_number)
                                        <button type="button" onclick="copyToClipboard('{{ preg_replace('/\D/', '', $r->phone_number) }}', this)" class="inline-flex items-center gap-1.5 text-left hover:bg-slate-100 dark:hover:bg-slate-800 rounded px-1 -mx-1 transition" title="Copy number">
                                            {{ \App\Helpers\DisplayHelper::formatPhoneNumber($r->phone_number) }}
                                            <span class="copy-icon text-slate-400">📋</span>
                                            <span class="copy-feedback text-mint-600 text-xs hidden">Copied!</span>
                                        </button>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold
                                        @if($r->status === 'active') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300
                                        @elseif($r->status === 'completed') bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300
                                        @elseif($r->status === 'cancelled' || $r->status === 'expired') bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                        @else bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300
                                        @endif
                                    ">{{ $r->status }}</span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-sm">
                                    @php $smsList = $r->getSmsMessagesList(); @endphp
                                    @if(!empty($smsList))
                                        @php
                                            $codesOnly = array_map(function($m) { return is_array($m) ? ($m['code'] ?? $m) : $m; }, $smsList);
                                            $codesText = implode("\n", $codesOnly);
                                        @endphp
                                        <div class="space-y-0.5">
                                            @foreach($smsList as $idx => $msg)
                                                @php $code = is_array($msg) ? ($msg['code'] ?? $msg) : $msg; @endphp
                                                <button type="button" onclick="copyToClipboard({{ json_encode($code) }}, this)" class="block text-left w-full font-mono text-mint-600 dark:text-mint-400 {{ $idx === 0 && count($smsList) === 1 ? 'font-semibold' : 'text-xs' }} hover:bg-slate-100 dark:hover:bg-slate-800 rounded px-1 -mx-1 transition inline-flex items-center gap-1 flex-wrap" title="Copy code">
                                                    <span>{{ $code }}</span>
                                                    <span class="copy-icon text-slate-400">📋</span>
                                                    <span class="copy-feedback text-mint-600 text-xs hidden">Copied!</span>
                                                </button>
                                            @endforeach
                                            @if(count($codesOnly) > 1)
                                                <button type="button" onclick="copyToClipboard({{ json_encode($codesText) }}, this)" class="text-xs text-slate-500 dark:text-slate-400 hover:underline mt-1" title="Copy all codes">Copy all</button>
                                            @endif
                                        </div>
                                    @elseif($r->expires_at && $r->isActive())
                                        <div class="space-y-1.5" x-data="rentalCountdown({{ $r->id }}, '{{ $r->expires_at->toIso8601String() }}', '{{ route('rentals.expire', $r->id) }}', '{{ csrf_token() }}', '{{ route('rentals.status', $r->id) }}')" x-init="start()">
                                            <span class="text-slate-500 dark:text-slate-400 block font-mono tabular-nums" x-text="display"></span>
                                            <div class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400" x-show="!expired">
                                                <span class="inline-flex gap-0.5" aria-hidden="true">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-current animate-bounce" style="animation-delay: 0ms;"></span>
                                                    <span class="w-1.5 h-1.5 rounded-full bg-current animate-bounce" style="animation-delay: 150ms;"></span>
                                                    <span class="w-1.5 h-1.5 rounded-full bg-current animate-bounce" style="animation-delay: 300ms;"></span>
                                                </span>
                                                <span>Waiting for code…</span>
                                            </div>
                                            <span class="text-xs text-amber-600 dark:text-amber-400" x-show="expired" x-cloak>Expiring…</span>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-right text-sm font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                    {{ \App\Models\SiteSetting::formatWalletAmount((float) $r->cost) }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-right">
                                    @if($r->isActive())
                                        @if($r->server && $r->server->isMultiCountry())
                                            <form action="{{ route('rentals.resend', $r->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-slate-600 dark:text-slate-400 text-sm hover:underline">Resend</button>
                                            </form>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <form action="{{ route('rentals.activate', $r->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-600 dark:text-blue-400 text-sm hover:underline">Activate</button>
                                            </form>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                            <form action="{{ route('rentals.reactivate', $r->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-amber-600 dark:text-amber-400 text-sm hover:underline">Reactivate</button>
                                            </form>
                                            <span class="text-slate-300 dark:text-slate-600">|</span>
                                        @endif
                                        @php
                                            $cancelAllowedAt = $r->cancelAllowedAt();
                                            $cancelAllowed = $r->isCancelAllowed();
                                        @endphp
                                        @if($cancelAllowed)
                                            <form action="{{ route('rentals.cancel', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel rental and refund to wallet?')">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 text-red-600 dark:text-red-400 text-sm hover:underline" title="Cancel &amp; refund">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Cancel
                                                </button>
                                            </form>
                                        @elseif($cancelAllowedAt)
                                            <span class="inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400 text-xs" title="Cancel available 10 min after rental start">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span x-data="cancelCountdown('{{ $cancelAllowedAt->toIso8601String() }}')" x-init="start()" x-text="label">Cancel in 10:00</span>
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 sm:px-6 py-12 text-center">
                                    <div class="inline-block rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 px-8 py-6">
                                        <p class="text-slate-600 dark:text-slate-400 font-medium mb-2">No rentals yet</p>
                                        <p class="text-sm text-slate-500 dark:text-slate-500 mb-3">Rent a number to get started.</p>
                                        <div class="flex flex-wrap gap-2 justify-center">
                                            <a href="{{ route('rentals.create.server1') }}" class="px-4 py-2 rounded-xl bg-mint-500 hover:bg-mint-600 text-white text-sm font-semibold transition">Server 1</a>
                                            <a href="{{ route('rentals.create.server2') }}" class="px-4 py-2 rounded-xl bg-slate-600 hover:bg-slate-700 text-white text-sm font-semibold transition">Server 2</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rentals->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                    {{ $rentals->links() }}
                </div>
            @endif
        </div>
    </div>
    <script>
        function copyToClipboard(text, buttonEl) {
            if (!text) return;
            navigator.clipboard.writeText(String(text)).then(function() {
                var btn = buttonEl && buttonEl.closest ? buttonEl.closest('button') : buttonEl;
                if (!btn) return;
                var icon = btn.querySelector && btn.querySelector('.copy-icon');
                var feedback = btn.querySelector && btn.querySelector('.copy-feedback');
                if (feedback) { feedback.classList.remove('hidden'); feedback.classList.add('inline'); }
                if (icon) icon.classList.add('hidden');
                setTimeout(function() {
                    if (feedback) { feedback.classList.add('hidden'); feedback.classList.remove('inline'); }
                    if (icon) icon.classList.remove('hidden');
                }, 1500);
            });
        }
    </script>
</x-app-layout>
