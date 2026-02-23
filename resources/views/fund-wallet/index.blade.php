<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Fund Wallet
            </h2>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl bg-slate-600 hover:bg-slate-700 text-white font-medium transition active:scale-[0.98]">Dashboard</a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
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
        @if ($errors->any())
            <div class="rounded-lg bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 px-4 py-3">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Wallet card --}}
        <div class="bg-gradient-to-br from-mint-600 to-blue-600 rounded-2xl border-0 p-4 sm:p-6 text-white shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-white/80 text-xs sm:text-sm truncate">{{ $user->name ?? $user->email }}</p>
                        <p class="text-xl sm:text-2xl font-bold">{{ \App\Models\SiteSetting::formatWalletAmount((float) $user->wallet_balance) }}</p>
                        <p class="text-white/70 text-xs">Available balance</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-sm text-slate-600 dark:text-slate-400">
            You can fund your wallet by getting a permanent virtual account (pay any amount anytime) or by entering an amount and paying via Instant or Manual.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            {{-- Virtual account or generate --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
                <div class="px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200">Bank account details</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Use the details below to fund your wallet</p>
                </div>
                <div class="p-4">
                    @if($account)
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-slate-500 dark:text-slate-400">Account number</label>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="font-mono font-semibold" id="accountNoText">{{ $account_no }}</p>
                                    <button type="button" onclick="copyAccountNo()" class="rounded px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm hover:bg-slate-200 dark:hover:bg-slate-700" title="Copy">Copy</button>
                                    <span id="copyMsg" class="text-mint-600 text-sm hidden">Copied!</span>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 dark:text-slate-400">Account name</label>
                                <p class="font-medium">{{ $account_name }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 dark:text-slate-400">Bank name</label>
                                <p class="font-medium">{{ $bank_name }}</p>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">This account can receive payments anytime. Funds are credited automatically.</p>
                        </div>
                    @else
                        <form action="{{ route('fund-wallet.generate') }}" method="POST" class="space-y-3">
                            @csrf
                            <p class="text-sm text-slate-600 dark:text-slate-400">Get a permanent virtual account to receive payments.</p>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Full name</label>
                                <input type="text" name="fullname" value="{{ old('fullname', $user->name) }}" class="mt-1 block w-full min-h-[48px] rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-base" placeholder="Name on account" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Phone (optional)</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full min-h-[48px] rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-base" placeholder="Phone number">
                            </div>
                            <button type="submit" class="w-full min-h-[48px] inline-flex justify-center items-center px-4 py-3 rounded-xl bg-mint-500 hover:bg-mint-600 text-white font-medium active:scale-[0.98] transition">Get account number</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Fund form: amount + type --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
                <div class="px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-800 dark:text-slate-200">Fund wallet</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Enter amount and pay via Instant or Manual</p>
                </div>
                <div class="p-4">
                    <form action="{{ route('fund-wallet.fund-now') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Amount (NGN)</label>
                            <input type="number" name="amount" min="100" max="100000" step="1" value="{{ old('amount') }}" class="mt-1 block w-full min-h-[48px] rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-base" placeholder="e.g. 5000" required>
                            <p class="mt-1 text-xs text-slate-500">Instant: min ₦1,000. Manual: min ₦100. Max ₦100,000.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Payment mode</label>
                            <select name="type" class="mt-1 block w-full min-h-[48px] rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white shadow-sm text-base">
                                <option value="1">Instant (redirect to pay)</option>
                                @if($manualFundingEnabled)
                                    <option value="2">Manual (bank transfer + receipt)</option>
                                @endif
                            </select>
                        </div>
                        <button type="submit" class="w-full min-h-[48px] inline-flex justify-center items-center px-4 py-3 rounded-xl bg-mint-500 hover:bg-mint-600 text-white font-medium active:scale-[0.98] transition">Add funds</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Latest transactions --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Latest transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Balance after</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($transactions as $tx)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $tx->type }}</td>
                                <td class="px-4 py-2 text-sm {{ $tx->amount >= 0 ? 'text-mint-600 dark:text-mint-400' : 'text-slate-600 dark:text-slate-400' }}">
                                    {{ $tx->amount >= 0 ? '+' : '' }}{{ \App\Models\SiteSetting::formatWalletAmount((float) $tx->amount) }}
                                </td>
                                <td class="px-4 py-2 text-sm">{{ \App\Models\SiteSetting::formatWalletAmount((float) $tx->balance_after) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-500 dark:text-slate-400">{{ $tx->created_at->format('M j, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">No transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="px-4 py-2 border-t border-slate-200 dark:border-slate-800">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function copyAccountNo() {
            var el = document.getElementById('accountNoText');
            if (!el) return;
            var text = el.textContent.trim();
            navigator.clipboard.writeText(text).then(function() {
                var msg = document.getElementById('copyMsg');
                if (msg) { msg.classList.remove('hidden'); setTimeout(function() { msg.classList.add('hidden'); }, 1500); }
            });
        }
    </script>
</x-app-layout>
