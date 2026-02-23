<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Verification logs
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Dashboard</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <p class="text-sm text-slate-600 dark:text-slate-400">All SMS verification requests (rentals) with user and status.</p>
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap gap-3 items-center">
                <form method="GET" action="{{ route('admin.verifications.index') }}" class="flex flex-wrap gap-2 items-center">
                    <select name="status" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 text-sm" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                    <select name="server" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 text-sm" onchange="this.form.submit()">
                        <option value="">All servers</option>
                        @foreach($servers as $s)
                            <option value="{{ $s->id }}" {{ request('server') == $s->id ? 'selected' : '' }}>{{ $s->display_name ?? $s->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Server</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Country</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Service</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Number</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($rentals as $r)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $r->created_at->format('M j, Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($r->user)
                                        <a href="{{ route('admin.users.show', $r->user) }}" class="text-mint-600 dark:text-mint-400 hover:underline font-medium">{{ $r->user->name ?? '—' }}</a>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $r->user->email ?? '' }}</div>
                                    @else
                                        <span class="text-slate-500 dark:text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    {{ $r->server ? $r->server->display_name : '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $r->getCountryDisplayName() }}</td>
                                <td class="px-4 py-3 text-sm">{{ $r->getServiceDisplayName() }}</td>
                                <td class="px-4 py-3 text-sm font-mono">{{ $r->phone_number ? \App\Helpers\DisplayHelper::formatPhoneNumber($r->phone_number) : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                        @if($r->status === 'active') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300
                                        @elseif($r->status === 'completed') bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300
                                        @elseif($r->status === 'cancelled' || $r->status === 'expired') bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                        @else bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300
                                        @endif
                                    ">{{ $r->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-slate-700 dark:text-slate-300">
                                    {{ \App\Models\SiteSetting::formatWalletAmount((float) $r->cost) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">No verifications yet.</td>
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
