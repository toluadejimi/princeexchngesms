<div class="rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm p-4 sm:p-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Bills & VTU</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Quick purchase shortcuts</p>
        </div>
        <a href="{{ route('vtu.index') }}" class="text-sm font-semibold text-mint-600 dark:text-mint-400 hover:underline">Open VTU</a>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <a href="{{ route('vtu.index', ['type' => 'airtime']) }}" class="group rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 hover:bg-mint-50 dark:hover:bg-mint-900/20 hover:border-mint-300 dark:hover:border-mint-700 p-4 transition">
            <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-yellow-400 to-orange-500 text-white flex items-center justify-center shadow-sm mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </span>
            <span class="block text-sm font-bold text-slate-900 dark:text-white">Airtime</span>
            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-1">Top up any network</span>
        </a>
        <a href="{{ route('vtu.index', ['type' => 'data']) }}" class="group rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 hover:bg-mint-50 dark:hover:bg-mint-900/20 hover:border-mint-300 dark:hover:border-mint-700 p-4 transition">
            <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 text-white flex items-center justify-center shadow-sm mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a10 10 0 0114.142 0M1.394 9.393a15 15 0 0121.213 0"/></svg>
            </span>
            <span class="block text-sm font-bold text-slate-900 dark:text-white">Data</span>
            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-1">Daily to yearly plans</span>
        </a>
        <a href="{{ route('vtu.index', ['type' => 'electricity']) }}" class="group rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 hover:bg-mint-50 dark:hover:bg-mint-900/20 hover:border-mint-300 dark:hover:border-mint-700 p-4 transition">
            <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-500 to-red-500 text-white flex items-center justify-center shadow-sm mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <span class="block text-sm font-bold text-slate-900 dark:text-white">Electricity</span>
            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-1">Buy meter tokens</span>
        </a>
        <a href="{{ route('vtu.index', ['type' => 'cable']) }}" class="group rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 hover:bg-mint-50 dark:hover:bg-mint-900/20 hover:border-mint-300 dark:hover:border-mint-700 p-4 transition">
            <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white flex items-center justify-center shadow-sm mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </span>
            <span class="block text-sm font-bold text-slate-900 dark:text-white">Cable TV</span>
            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-1">Renew TV packages</span>
        </a>
    </div>
</div>
