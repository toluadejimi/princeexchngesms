<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">Site Settings</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <p class="text-sm text-mint-600 dark:text-mint-400">{{ session('success') }}</p>
        @endif

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass p-6">
            <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-4">Provider balances</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/50">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">USA provider</p>
                    @if(isset($daisy_error))
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $daisy_error }}</p>
                    @else
                        <p class="mt-1 text-xl font-semibold text-mint-600 dark:text-mint-400">${{ number_format($daisy_balance ?? 0, 2) }}</p>
                    @endif
                </div>
                <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/50">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Other countries provider</p>
                    @if(isset($smspool_error))
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $smspool_error }}</p>
                    @else
                        <p class="mt-1 text-xl font-semibold text-mint-600 dark:text-mint-400">${{ number_format($smspool_balance ?? 0, 2) }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass p-6">
            <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Site branding</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Site name</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Shown in the navigation bar and browser tab.</p>
                    <input type="text" name="site_name" value="{{ old('site_name', $site_name ?? '') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm" placeholder="e.g. SMS Rental">
                    @error('site_name')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Logo</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Image shown in the navigation bar. PNG, JPG, GIF, WebP or SVG, max 2MB.</p>
                    @if(!empty($site_logo_url))
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $site_logo_url }}" alt="Current logo" class="h-10 object-contain border border-slate-200 dark:border-slate-700 rounded">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Current logo</span>
                        </div>
                    @endif
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-mint-50 file:text-mint-700 dark:file:bg-mint-900/30 dark:file:text-mint-300">
                    @error('logo')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Favicon</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Browser tab icon. PNG, GIF or JPG, max 512KB.</p>
                    @if(!empty($site_favicon_url))
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $site_favicon_url }}" alt="Current favicon" class="h-8 w-8 object-contain border border-slate-200 dark:border-slate-700 rounded">
                            <span class="text-xs text-slate-500 dark:text-slate-400">Current favicon</span>
                        </div>
                    @endif
                    <input type="file" name="favicon" accept="image/png,image/gif,image/jpeg,image/webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-mint-50 file:text-mint-700 dark:file:bg-mint-900/30 dark:file:text-mint-300">
                    @error('favicon')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-6 mb-3">Price display (verification / rent pages)</h3>
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="display_currency" value="USD" {{ ($display_currency ?? 'USD') === 'USD' ? 'checked' : '' }}>
                        <span>Show prices in USD ($)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer mt-2">
                        <input type="radio" name="display_currency" value="NGN" {{ ($display_currency ?? '') === 'NGN' ? 'checked' : '' }}>
                        <span>Show prices in Naira (₦)</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">USD to Naira rate (cover fee)</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">e.g. 1500 means 1 USD = 1500 NGN. Used when display currency is Naira.</p>
                    <input type="number" name="usd_to_ngn_rate" step="0.01" min="0" value="{{ old('usd_to_ngn_rate', $usd_to_ngn_rate ?? 0) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
                    @error('usd_to_ngn_rate')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Margin in Naira (fixed amount)</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Added to (USD × cover rate) for customer price. e.g. 500 means +₦500 per verification.</p>
                    <input type="number" name="naira_margin_amount" step="1" min="0" value="{{ old('naira_margin_amount', $naira_margin_amount ?? 0) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
                    @error('naira_margin_amount')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Naira margin (%) — optional</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">If you also want a percentage on top of (USD × rate + margin amount), set here.</p>
                    <input type="number" name="naira_margin_percent" step="0.01" min="0" value="{{ old('naira_margin_percent', $naira_margin_percent ?? 0) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
                    @error('naira_margin_percent')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-6 mb-3">Manual wallet funding (bank transfer + receipt)</h3>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="manual_funding_enabled" value="1" {{ ($manual_funding_enabled ?? '0') === '1' ? 'checked' : '' }}>
                    <span>Enable manual funding option on fund-wallet page</span>
                </label>
                <div class="grid grid-cols-1 gap-3 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Bank name</label>
                        <input type="text" name="manual_bank_name" value="{{ old('manual_bank_name', $manual_bank_name ?? '') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm" placeholder="e.g. GTBank">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Account number</label>
                        <input type="text" name="manual_account_no" value="{{ old('manual_account_no', $manual_account_no ?? '') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Account name</label>
                        <input type="text" name="manual_account_name" value="{{ old('manual_account_name', $manual_account_name ?? '') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm">
                    </div>
                </div>

                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-6 mb-3">Telegram</h3>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Telegram URL</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Link for the floating Telegram icon (e.g. https://t.me/yourchannel). Leave empty to hide the icon.</p>
                    <input type="url" name="telegram_url" value="{{ old('telegram_url', $telegram_url ?? '') }}" placeholder="https://t.me/yourchannel"
                        class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500">
                    @error('telegram_url')<p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-mint-500 text-white font-medium hover:bg-mint-600 transition mt-4">Save settings</button>
            </form>
        </div>
    </div>
</x-app-layout>
