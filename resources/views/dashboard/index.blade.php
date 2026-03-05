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
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="flex flex-row gap-2">
                <a href="{{ route('rentals.create.server1') }}" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl bg-gradient-to-r from-mint-500 to-blue-500 text-white font-medium shadow-neon-mint hover:shadow-lg active:scale-[0.98] transition">Server 1</a>
                <a href="{{ route('rentals.create.server2') }}" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-medium active:scale-[0.98] transition">Server 2</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
        @if (session('message'))
            <div class="rounded-lg bg-mint-100 dark:bg-mint-900/30 text-mint-800 dark:text-mint-200 px-4 py-3">
                {{ session('message') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif
        @if (isset($unreadNotificationCount) && $unreadNotificationCount > 0)
            <div class="rounded-xl bg-mint-50 dark:bg-mint-900/20 border border-mint-200 dark:border-mint-800 px-4 py-3 flex flex-wrap items-center justify-between gap-2" x-data="{ dismissed: false }" x-show="!dismissed">
                <p class="text-mint-800 dark:text-mint-200 text-sm font-medium">
                    You have {{ $unreadNotificationCount }} new notification{{ $unreadNotificationCount === 1 ? '' : 's' }}.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.querySelector('[aria-label=Notifications]')?.click()" class="text-sm font-medium text-mint-600 dark:text-mint-400 hover:underline">View</button>
                    <button type="button" @click="dismissed = true" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 text-sm" aria-label="Dismiss">&times;</button>
                </div>
            </div>
        @endif
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-glass">
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Wallet Balance</p>
                <p class="text-xl sm:text-2xl font-bold text-mint-600 dark:text-mint-400 mt-0.5 sm:mt-1">{{ \App\Models\SiteSetting::formatWalletAmount((float) $user->wallet_balance) }}</p>
                <a href="{{ route('fund-wallet.index') }}" class="inline-flex items-center justify-center gap-2 mt-3 min-h-[44px] px-4 py-2 rounded-xl bg-mint-500 hover:bg-mint-600 text-white text-sm font-medium transition active:scale-[0.98]">Fund Wallet</a>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-glass">
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Active Rentals</p>
                <p class="text-xl sm:text-2xl font-bold text-blue-600 dark:text-blue-400 mt-0.5 sm:mt-1">{{ $activeCount }}</p>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-glass">
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Total Rentals</p>
                <p class="text-xl sm:text-2xl font-bold text-slate-700 dark:text-slate-300 mt-0.5 sm:mt-1">{{ $rentals->total() }}</p>
            </div>
        </div>

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2 overflow-x-auto overscroll-x-contain -mx-4 px-4 sm:mx-0 sm:px-4 flex-nowrap sm:flex-wrap">
                <span class="font-medium text-slate-700 dark:text-slate-300 shrink-0">Rentals</span>
                <a href="{{ route('dashboard', request()->except('server','status')) }}" class="text-sm px-3 py-2 rounded-full shrink-0 min-h-[36px] inline-flex items-center {{ !request('server') && !request('status') ? 'bg-mint-500/20 text-mint-600 dark:text-mint-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">All</a>
                @foreach($servers as $s)
                    <a href="{{ route('dashboard', ['server' => $s->id] + request()->except('server')) }}" class="text-sm px-3 py-2 rounded-full shrink-0 min-h-[36px] inline-flex items-center {{ request('server') == $s->id ? 'bg-mint-500/20 text-mint-600 dark:text-mint-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">{{ $s->display_name }}</a>
                @endforeach
                <a href="{{ route('dashboard', ['status' => 'active'] + request()->except('status')) }}" class="text-sm px-3 py-2 rounded-full shrink-0 min-h-[36px] inline-flex items-center {{ request('status') === 'active' ? 'bg-mint-500/20 text-mint-600 dark:text-mint-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">Active</a>
                <a href="{{ route('dashboard', ['status' => 'completed'] + request()->except('status')) }}" class="text-sm px-3 py-2 rounded-full shrink-0 min-h-[36px] inline-flex items-center {{ request('status') === 'completed' ? 'bg-mint-500/20 text-mint-600 dark:text-mint-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">Completed</a>
            </div>
            {{-- Mobile: card list --}}
            <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($rentals as $r)
                    @include('dashboard.partials.rental-card', ['r' => $r])
                @empty
                    <div class="p-6 text-center text-slate-500 dark:text-slate-400">
                        No rentals yet. <a href="{{ route('rentals.create.server1') }}" class="text-mint-600 dark:text-mint-400 hover:underline">Server 1</a> or <a href="{{ route('rentals.create.server2') }}" class="text-mint-600 dark:text-mint-400 hover:underline">Server 2</a>
                    </div>
                @endforelse
            </div>
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Country</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Service</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Number</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Code / Expires</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($rentals as $r)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $r->getCountryDisplayName() }}</td>
                                <td class="px-4 py-3 text-sm">{{ $r->getServiceDisplayName() }}</td>
                                <td class="px-4 py-3 text-sm font-mono">
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
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                        @if($r->status === 'active') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300
                                        @elseif($r->status === 'completed') bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300
                                        @elseif($r->status === 'cancelled' || $r->status === 'expired') bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                        @else bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300
                                        @endif
                                    ">{{ $r->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
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
                                <td class="px-4 py-3 text-right text-sm font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                    {{ \App\Models\SiteSetting::formatWalletAmount((float) $r->cost) }}
                                </td>
                                <td class="px-4 py-3 text-right">
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
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No rentals yet. <a href="{{ route('rentals.create.server1') }}" class="text-mint-600 dark:text-mint-400 hover:underline">Server 1</a> or <a href="{{ route('rentals.create.server2') }}" class="text-mint-600 dark:text-mint-400 hover:underline">Server 2</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rentals->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
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
