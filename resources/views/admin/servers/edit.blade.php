<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Edit Server: {{ $server->name }}</h2>
            <a href="{{ route('admin.servers.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('admin.servers.update', $server) }}" method="POST" class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $server->name) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500" required>
                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Base URL</label>
                <input type="url" name="base_url" value="{{ old('base_url', $server->base_url) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500" required>
                @error('base_url')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">API Key (leave blank to keep current)</label>
                <input type="password" name="api_key" placeholder="••••••••" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Type</label>
                <select name="type" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
                    <option value="usa_only" {{ old('type', $server->type) === 'usa_only' ? 'selected' : '' }}>USA Only</option>
                    <option value="multi_country" {{ old('type', $server->type) === 'multi_country' ? 'selected' : '' }}>Multi-Country</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Profit Margin %</label>
                <input type="number" step="0.01" min="0" max="100" name="profit_margin_percent" value="{{ old('profit_margin_percent', $server->profit_margin_percent) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $server->sort_order) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="status" value="0">
                <input type="checkbox" name="status" value="1" {{ old('status', $server->status) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-600 text-mint-600 focus:ring-mint-500">
                <label class="text-sm text-slate-700 dark:text-slate-300">Enabled</label>
            </div>
            <button type="submit" class="w-full inline-flex justify-center px-4 py-3 rounded-lg bg-mint-500 text-white font-medium hover:bg-mint-600 transition">Save</button>
        </form>
    </div>
</x-app-layout>
