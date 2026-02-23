<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            Manual payment – upload receipt
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 text-sm">
            <p class="font-medium text-amber-800 dark:text-amber-200">Pay exactly <strong>{{ \App\Models\SiteSetting::formatWalletAmount($amount) }}</strong> to the account below, then upload your payment receipt.</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Bank details</h3>
            </div>
            <div class="p-4 space-y-2">
                <p><span class="text-slate-500 dark:text-slate-400">Bank:</span> {{ $manualBankName ?: '—' }}</p>
                <p><span class="text-slate-500 dark:text-slate-400">Account no:</span> {{ $manualAccountNo ?: '—' }}</p>
                <p><span class="text-slate-500 dark:text-slate-400">Account name:</span> {{ $manualAccountName ?: '—' }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Upload receipt</h3>
            </div>
            <div class="p-4">
                <form action="{{ route('fund-wallet.manual-submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="amount" value="{{ $amount }}">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Receipt (image or PDF, max 5MB)</label>
                        <input type="file" name="receipt" accept=".jpeg,.jpg,.png,.pdf" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-mint-50 file:text-mint-700 dark:file:bg-mint-900/30 dark:file:text-mint-300" required>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-mint-500 hover:bg-mint-600 text-white font-medium">Submit receipt</button>
                        <a href="{{ route('fund-wallet.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
