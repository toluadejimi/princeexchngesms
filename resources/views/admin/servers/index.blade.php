<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Servers</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Back</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('success'))
            <div class="rounded-lg bg-mint-100 dark:bg-mint-900/30 text-mint-800 dark:text-mint-200 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($servers as $s)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-sm">{{ $s->type === 'usa_only' ? 'USA Only' : 'Multi-Country' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $s->status ? 'bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">{{ $s->status ? 'Active' : 'Disabled' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <form action="{{ route('admin.servers.toggle', $s) }}" method="POST" class="inline">
                                    @csrf
                                    @if($s->status)
                                        <button type="submit" class="text-amber-600 dark:text-amber-400 hover:underline text-sm">Disable</button>
                                    @else
                                        <button type="submit" class="text-mint-600 dark:text-mint-400 hover:underline text-sm font-medium">Activate</button>
                                    @endif
                                </form>
                                <a href="{{ route('admin.servers.edit', $s) }}" class="text-slate-600 dark:text-slate-400 hover:underline text-sm">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400">When a server is <strong>Active</strong>, users can rent numbers from it. Use <strong>Activate</strong> to enable the Multi-Country server for &quot;Other Countries&quot;.</p>
    </div>
</x-app-layout>
