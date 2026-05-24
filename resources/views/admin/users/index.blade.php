<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">User management</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Back</a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if(session('success'))
            <p class="text-sm text-mint-600 dark:text-mint-400">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="text-sm text-red-600 dark:text-red-400">{{ session('error') }}</p>
        @endif

        <section class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-glass">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">User Statistics</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">New signups and currently active sessions.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/70 px-5 py-6 text-center">
                    <p class="text-4xl font-extrabold text-red-600 dark:text-red-400 tabular-nums">{{ number_format($userStats['newToday'] ?? 0) }}</p>
                    <p class="mt-1 text-sm sm:text-base font-medium text-slate-600 dark:text-slate-400">New Today</p>
                </div>
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/70 px-5 py-6 text-center">
                    <p class="text-4xl font-extrabold text-sky-500 dark:text-sky-400 tabular-nums">{{ number_format($userStats['thisWeek'] ?? 0) }}</p>
                    <p class="mt-1 text-sm sm:text-base font-medium text-slate-600 dark:text-slate-400">This Week</p>
                </div>
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/70 px-5 py-6 text-center">
                    <p class="text-4xl font-extrabold text-emerald-500 dark:text-emerald-400 tabular-nums">{{ number_format($userStats['thisMonth'] ?? 0) }}</p>
                    <p class="mt-1 text-sm sm:text-base font-medium text-slate-600 dark:text-slate-400">This Month</p>
                </div>
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/70 px-5 py-6 text-center">
                    <p class="text-4xl font-extrabold text-purple-600 dark:text-purple-400 tabular-nums">{{ number_format($userStats['activeUsers'] ?? 0) }}</p>
                    <p class="mt-1 text-sm sm:text-base font-medium text-slate-600 dark:text-slate-400">Active Users</p>
                </div>
            </div>
        </section>

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by email or name..." class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 flex-1 max-w-sm">
            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium">Search</button>
        </form>

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Wallet</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Joined</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($users as $u)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $u->name }}</div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ $u->email }}</div>
                                </td>
                                <td class="px-4 py-3 font-medium text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount((float) $u->wallet_balance) }}</td>
                                <td class="px-4 py-3">
                                    @if($u->is_admin)
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">Admin</span>
                                    @elseif($u->is_blocked)
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300">Blocked</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300">Active</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $u->created_at->format('M j, Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.users.show', $u) }}" class="text-mint-600 dark:text-mint-400 hover:underline text-sm mr-2">View</a>
                                    @if(!$u->is_admin)
                                        @if($u->is_blocked)
                                            <form action="{{ route('admin.users.unblock', $u) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">Unblock</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.block', $u) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-amber-600 dark:text-amber-400 hover:underline text-sm">Block</button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
