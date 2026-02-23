<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Pricing</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Back</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <p class="text-mint-600 dark:text-mint-400 text-sm">{{ session('success') }}</p>
        @endif

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass p-6">
            <h3 class="font-medium text-slate-800 dark:text-slate-200 mb-4">Add / Update Price</h3>
            <form action="{{ route('admin.pricing.store') }}" method="POST" class="flex flex-wrap gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Server</label>
                    <select name="server_id" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 text-sm" required>
                        @foreach($servers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Country (blank = USA/default)</label>
                    <input type="text" name="country_code" placeholder="US" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 text-sm w-24">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Service code</label>
                    <input type="text" name="service_code" placeholder="wa" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Price $</label>
                    <input type="number" step="0.01" min="0" name="price" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 text-sm w-24" required>
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-mint-500 text-white text-sm font-medium hover:bg-mint-600">Save</button>
            </form>
        </div>

        @foreach($servers as $server)
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 font-medium">{{ $server->name }}</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400">Country</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400">Service</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-400">Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($server->pricing as $p)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $p->country_code ?? 'default' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $p->service_code }}</td>
                                    <td class="px-4 py-2 text-sm">${{ number_format($p->price, 2) }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <form action="{{ route('admin.pricing.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Remove?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 text-sm hover:underline">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-3 text-slate-500 dark:text-slate-400 text-sm">No pricing set.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
