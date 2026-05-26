<div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm px-3 py-3 sm:px-4 sm:py-3">
    <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full bg-mint-500/10 dark:bg-mint-500/5 pointer-events-none"></div>
    <div class="relative flex flex-row items-center justify-between gap-3 mb-2">
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">Bills & VTU</h2>
            <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 leading-tight">Quick purchase shortcuts</p>
        </div>
        <a href="{{ route('vtu.index') }}" class="shrink-0 inline-flex items-center justify-center min-h-[30px] px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">Open VTU</a>
    </div>
    <div class="relative grid gap-2" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));">
        <a href="{{ route('vtu.index', ['type' => 'airtime']) }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/70 hover:-translate-y-0.5 hover:shadow-md transition" style="min-height:64px;display:flex;align-items:center;gap:10px;padding:10px 12px;">
            <span class="absolute -right-7 -top-7 w-20 h-20 rounded-full" style="background:rgba(251,191,36,.14);"></span>
            <span class="relative rounded-xl flex items-center justify-center shadow-sm shrink-0" style="width:38px;height:38px;color:#fff;background:linear-gradient(135deg,#f59e0b,#f97316);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </span>
            <span class="relative min-w-0">
                <span class="block text-sm font-bold text-slate-900 dark:text-white">Airtime</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400 truncate">Top up any network</span>
            </span>
        </a>
        <a href="{{ route('vtu.index', ['type' => 'data']) }}" class="group relative overflow-hidden rounded-2xl border border-mint-200 dark:border-mint-800 bg-mint-50/60 dark:bg-mint-900/15 hover:-translate-y-0.5 hover:shadow-md transition" style="min-height:64px;display:flex;align-items:center;gap:10px;padding:10px 12px;">
            <span class="absolute -right-7 -top-7 w-20 h-20 rounded-full" style="background:rgba(20,184,166,.16);"></span>
            <span class="relative rounded-xl flex items-center justify-center shadow-sm shrink-0" style="width:38px;height:38px;color:#fff;background:linear-gradient(135deg,#3b82f6,#06b6d4);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a10 10 0 0114.142 0M1.394 9.393a15 15 0 0121.213 0"/></svg>
            </span>
            <span class="relative min-w-0">
                <span class="block text-sm font-bold text-slate-900 dark:text-white">Data</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400 truncate">Daily to yearly plans</span>
            </span>
        </a>
        <a href="{{ route('vtu.index', ['type' => 'electricity']) }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/70 hover:-translate-y-0.5 hover:shadow-md transition" style="min-height:64px;display:flex;align-items:center;gap:10px;padding:10px 12px;">
            <span class="absolute -right-7 -top-7 w-20 h-20 rounded-full" style="background:rgba(245,158,11,.14);"></span>
            <span class="relative rounded-xl flex items-center justify-center shadow-sm shrink-0" style="width:38px;height:38px;color:#fff;background:linear-gradient(135deg,#f59e0b,#ef4444);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <span class="relative min-w-0">
                <span class="block text-sm font-bold text-slate-900 dark:text-white">Electricity</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400 truncate">Buy meter tokens</span>
            </span>
        </a>
        <a href="{{ route('vtu.index', ['type' => 'cable']) }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/70 hover:-translate-y-0.5 hover:shadow-md transition" style="min-height:64px;display:flex;align-items:center;gap:10px;padding:10px 12px;">
            <span class="absolute -right-7 -top-7 w-20 h-20 rounded-full" style="background:rgba(139,92,246,.14);"></span>
            <span class="relative rounded-xl flex items-center justify-center shadow-sm shrink-0" style="width:38px;height:38px;color:#fff;background:linear-gradient(135deg,#8b5cf6,#4f46e5);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </span>
            <span class="relative min-w-0">
                <span class="block text-sm font-bold text-slate-900 dark:text-white">Cable TV</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400 truncate">Renew TV packages</span>
            </span>
        </a>
    </div>
</div>
