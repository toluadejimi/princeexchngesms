<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                All transactions
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Dashboard</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap gap-3 items-center">
                <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex flex-wrap gap-2 items-center">
                    <select name="type" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 text-sm" onchange="this.form.submit()">
                        <option value="">All types</option>
                        <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                        <option value="rental_charge" {{ request('type') === 'rental_charge' ? 'selected' : '' }}>Rental charge</option>
                        <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refund</option>
                        <option value="admin_adjustment" {{ request('type') === 'admin_adjustment' ? 'selected' : '' }}>Admin adjustment</option>
                    </select>
                </form>
                <span class="text-sm text-slate-500 dark:text-slate-400">In = green, Out = red</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Balance after</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $tx->created_at->format('M j, Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($tx->user)
                                        <a href="{{ route('admin.users.show', $tx->user) }}" class="text-mint-600 dark:text-mint-400 hover:underline font-medium">{{ $tx->user->name ?? '—' }}</a>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $tx->user->email ?? '' }}</div>
                                    @else
                                        <span class="text-slate-500 dark:text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                        @if($tx->type === 'deposit') bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300
                                        @elseif($tx->type === 'refund' || $tx->type === 'admin_adjustment') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300
                                        @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                        @endif
                                    ">{{ str_replace('_', ' ', $tx->type) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    @if((float) $tx->amount >= 0)
                                        <span class="text-mint-600 dark:text-mint-400">+{{ \App\Models\SiteSetting::formatWalletAmount((float) $tx->amount) }}</span>
                                    @else
                                        <span class="text-red-600 dark:text-red-400">{{ \App\Models\SiteSetting::formatWalletAmount((float) $tx->amount) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-slate-600 dark:text-slate-400">
                                    {{ \App\Models\SiteSetting::formatWalletAmount((float) $tx->balance_after) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">No transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
