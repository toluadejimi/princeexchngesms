<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Support tickets
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Dashboard</a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap gap-2 items-center">
                <a href="{{ route('admin.support.index') }}" class="text-sm px-3 py-2 rounded-full min-h-[36px] inline-flex items-center {{ !request('status') ? 'bg-mint-500/20 text-mint-600 dark:text-mint-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">All</a>
                <a href="{{ route('admin.support.index', ['status' => 'open']) }}" class="text-sm px-3 py-2 rounded-full min-h-[36px] inline-flex items-center {{ request('status') === 'open' ? 'bg-mint-500/20 text-mint-600 dark:text-mint-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">Open</a>
                <a href="{{ route('admin.support.index', ['status' => 'closed']) }}" class="text-sm px-3 py-2 rounded-full min-h-[36px] inline-flex items-center {{ request('status') === 'closed' ? 'bg-mint-500/20 text-mint-600 dark:text-mint-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">Closed</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($tickets as $t)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $t->created_at->format('M j, Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    @if($t->user)
                                        <a href="{{ route('admin.users.show', $t->user) }}" class="text-mint-600 dark:text-mint-400 hover:underline">{{ $t->user->name ?? '—' }}</a>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $t->user->email ?? '' }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200 max-w-xs truncate">{{ $t->subject }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2.5 py-1 rounded text-xs font-medium
                                        @if($t->status === 'open') bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300
                                        @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                        @endif">{{ $t->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.support.show', $t) }}" class="text-mint-600 dark:text-mint-400 hover:underline text-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">No tickets yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
