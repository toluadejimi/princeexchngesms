<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">User: {{ $user->name }}</h2>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Users</a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <p class="text-sm text-mint-600 dark:text-mint-400">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="text-sm text-red-600 dark:text-red-400">{{ session('error') }}</p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Email</p>
                <p class="font-medium">{{ $user->email }}</p>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Wallet balance</p>
                <p class="font-bold text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount((float) $user->wallet_balance) }}</p>
            </div>
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Status</p>
                @if($user->is_admin)
                    <span class="inline-flex px-2 py-0.5 rounded text-xs bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">Admin</span>
                @elseif($user->is_blocked)
                    <span class="inline-flex px-2 py-0.5 rounded text-xs bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300">Blocked</span>
                @else
                    <span class="inline-flex px-2 py-0.5 rounded text-xs bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300">Active</span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @if(!$user->is_admin && $user->id !== auth()->id())
                @if($user->is_blocked)
                    <form action="{{ route('admin.users.unblock', $user) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex px-4 py-2 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600">Unblock</button>
                    </form>
                @else
                    <form action="{{ route('admin.users.block', $user) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex px-4 py-2 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600">Block user</button>
                    </form>
                @endif
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user and all their data?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex px-4 py-2 rounded-lg bg-red-500 text-white font-medium hover:bg-red-600">Delete user</button>
                </form>
            @endif
            @if($user->id !== auth()->id() && !$user->is_blocked)
                <form action="{{ route('admin.users.login-as', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex px-4 py-2 rounded-lg bg-slate-600 text-white font-medium hover:bg-slate-700">Login as user</button>
                </form>
            @endif
        </div>

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Fund wallet</h3>
            </div>
            <div class="p-4">
                <form action="{{ route('admin.users.fund') }}" method="POST" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Amount</label>
                        <input type="number" name="amount" step="0.01" min="0" required class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 w-32">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Note (optional)</label>
                        <input type="text" name="note" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 w-48" placeholder="e.g. Manual payment">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-mint-500 text-white font-medium hover:bg-mint-600">Add to wallet</button>
                </form>
            </div>
        </div>

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Manual payment receipts</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Uploaded when user chose manual funding</p>
            </div>
            <div class="overflow-x-auto">
                @if($manualReceipts->isEmpty())
                    <p class="p-4 text-slate-500 dark:text-slate-400">No receipts.</p>
                @else
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Amount</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($manualReceipts as $fr)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $fr->created_at->format('M j, Y H:i') }}</td>
                                    <td class="px-4 py-2 font-medium">{{ \App\Models\SiteSetting::formatWalletAmount((float) $fr->amount) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $fr->status === 'completed' ? 'bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300' }}">{{ $fr->status }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('admin.users.receipt', [$user, $fr]) }}" target="_blank" rel="noopener" class="text-mint-600 dark:text-mint-400 hover:underline text-sm">View receipt</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Verifications (rentals)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">SMS verifications / number rentals by this user</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Country</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Service</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Number</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Cost</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($rentals as $r)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ \App\Helpers\DisplayHelper::countryCodeToName($r->country_code ?? '') }}</td>
                                <td class="px-4 py-2 text-sm">{{ \App\Helpers\DisplayHelper::serviceCodeToName($r->service_code ?? '') }}</td>
                                <td class="px-4 py-2 text-sm font-mono">{{ \App\Helpers\DisplayHelper::formatPhoneNumber($r->phone_number) }}</td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs
                                        @if($r->status === 'active') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300
                                        @elseif($r->status === 'completed') bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300
                                        @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                        @endif
                                    ">{{ $r->status }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm">{{ \App\Models\SiteSetting::formatWalletAmount((float) $r->cost) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-500 dark:text-slate-400">{{ $r->created_at->format('M j, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">No verifications yet.</td>
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
</x-app-layout>
