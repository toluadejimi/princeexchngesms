<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Pricing</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <p class="text-mint-600 dark:text-mint-400 text-sm">{{ session('success') }}</p>
        @endif

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass p-6">
            <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">These settings apply to <strong>both Server 1 and Server 2</strong>. Customer prices are computed from the provider’s API price (USD) using the rate and margins below. No per-service pricing.</p>

            <form action="{{ route('admin.pricing.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Display currency</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Currency shown to customers on rent pages and wallet.</p>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="display_currency" value="USD" {{ ($display_currency ?? 'USD') === 'USD' ? 'checked' : '' }}>
                            <span>USD ($)</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="display_currency" value="NGN" {{ ($display_currency ?? '') === 'NGN' ? 'checked' : '' }}>
                            <span>Naira (₦)</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">USD to Naira rate (cover fee)</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">e.g. 1500 means 1 USD = 1500 NGN. Used when display currency is Naira.</p>
                    <input type="number" name="usd_to_ngn_rate" step="1" min="0" value="{{ old('usd_to_ngn_rate', $usd_to_ngn_rate ?? 0) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500" required>
                    @error('usd_to_ngn_rate')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Margin in Naira (fixed amount)</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Added to (USD × cover rate) for customer price. e.g. 1000 means +₦1000 per verification.</p>
                    <input type="number" name="naira_margin_amount" step="1" min="0" value="{{ old('naira_margin_amount', $naira_margin_amount ?? 0) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500" required>
                    @error('naira_margin_amount')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Naira margin (%) — optional</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">If you want a percentage on top of (USD × rate + margin amount), set here.</p>
                    <input type="number" name="naira_margin_percent" step="0.01" min="0" value="{{ old('naira_margin_percent', $naira_margin_percent ?? 0) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500" placeholder="0">
                    @error('naira_margin_percent')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full sm:w-auto inline-flex justify-center px-6 py-3 rounded-lg bg-mint-500 text-white font-medium hover:bg-mint-600 transition">Save pricing</button>
            </form>
        </div>
    </div>
</x-app-layout>
