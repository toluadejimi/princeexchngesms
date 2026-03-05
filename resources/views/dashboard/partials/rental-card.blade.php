@if(isset($r) && $r)
<div class="p-4 bg-white dark:bg-slate-900/50 first:rounded-t-2xl">
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">{{ $r->getCountryDisplayName() }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $r->getServiceDisplayName() }}</p>
        </div>
        <div class="flex flex-col items-end gap-1 shrink-0">
            <span class="text-sm font-medium text-mint-600 dark:text-mint-400">{{ \App\Models\SiteSetting::formatWalletAmount((float) $r->cost) }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium
            @if($r->status === 'active') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300
            @elseif($r->status === 'completed') bg-mint-100 dark:bg-mint-900/40 text-mint-800 dark:text-mint-300
            @elseif($r->status === 'cancelled' || $r->status === 'expired') bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
            @else bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300
            @endif
        ">{{ $r->status }}</span>
        </div>
    </div>
    @if($r->phone_number)
        <div class="flex items-center gap-2 mb-3">
            <button type="button" onclick="copyToClipboard('{{ preg_replace('/\D/', '', $r->phone_number) }}', this)" class="font-mono text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg px-2 py-1.5 -mx-1 min-h-[44px] inline-flex items-center gap-1.5 transition flex-1 text-left" title="Copy number">
                <span class="truncate">{{ \App\Helpers\DisplayHelper::formatPhoneNumber($r->phone_number) }}</span>
                <span class="copy-icon text-slate-400 shrink-0">📋</span>
                <span class="copy-feedback text-mint-600 text-xs hidden shrink-0">Copied!</span>
            </button>
        </div>
    @endif
    <div class="mb-3">
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Code / Expires</p>
        @php $smsList = $r->getSmsMessagesList(); @endphp
        @if(!empty($smsList))
            @php
                $codesOnly = array_map(function($m) { return is_array($m) ? ($m['code'] ?? $m) : $m; }, $smsList);
                $codesText = implode("\n", $codesOnly);
            @endphp
            <div class="space-y-1">
                @foreach($smsList as $idx => $msg)
                    @php $code = is_array($msg) ? ($msg['code'] ?? $msg) : $msg; @endphp
                    <button type="button" onclick="copyToClipboard({{ json_encode($code) }}, this)" class="w-full text-left font-mono text-mint-600 dark:text-mint-400 {{ $idx === 0 && count($smsList) === 1 ? 'font-semibold text-base' : 'text-sm' }} hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg px-3 py-2 min-h-[44px] inline-flex items-center gap-2 transition" title="Copy code">
                        <span class="truncate flex-1">{{ $code }}</span>
                        <span class="copy-icon text-slate-400">📋</span>
                        <span class="copy-feedback text-mint-600 text-xs hidden">Copied!</span>
                    </button>
                @endforeach
                @if(count($codesOnly) > 1)
                    <button type="button" onclick="copyToClipboard({{ json_encode($codesText) }}, this)" class="text-xs text-slate-500 dark:text-slate-400 hover:underline mt-1 min-h-[36px]" title="Copy all">Copy all</button>
                @endif
            </div>
        @elseif($r->expires_at && $r->isActive())
            <div class="space-y-1.5" x-data="rentalCountdown({{ $r->id }}, '{{ $r->expires_at->toIso8601String() }}', '{{ route('rentals.expire', $r->id) }}', '{{ csrf_token() }}', '{{ route('rentals.status', $r->id) }}')" x-init="start()">
                <span class="text-slate-600 dark:text-slate-300 block font-mono tabular-nums text-sm" x-text="display"></span>
                <div class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400" x-show="!expired">
                    <span class="inline-flex gap-0.5" aria-hidden="true">
                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-bounce" style="animation-delay: 0ms;"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-bounce" style="animation-delay: 150ms;"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-current animate-bounce" style="animation-delay: 300ms;"></span>
                    </span>
                    <span>Waiting for code…</span>
                </div>
                <span class="text-xs text-amber-600 dark:text-amber-400" x-show="expired" x-cloak>Expiring…</span>
            </div>
        @else
            <span class="text-slate-500 dark:text-slate-400 text-sm">—</span>
        @endif
    </div>
    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
        @if($r->isActive())
            @if($r->server && $r->server->isMultiCountry())
                <form action="{{ route('rentals.resend', $r->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="min-h-[40px] px-3 py-2 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition active:scale-[0.98]">Resend</button>
                </form>
                <form action="{{ route('rentals.activate', $r->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="min-h-[40px] px-3 py-2 rounded-xl text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition active:scale-[0.98]">Activate</button>
                </form>
                <form action="{{ route('rentals.reactivate', $r->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="min-h-[40px] px-3 py-2 rounded-xl text-sm font-medium text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition active:scale-[0.98]">Reactivate</button>
                </form>
            @endif
            @php
                $cancelAllowedAt = $r->cancelAllowedAt();
                $cancelAllowed = $r->isCancelAllowed();
            @endphp
            @if($cancelAllowed)
                <form action="{{ route('rentals.cancel', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel rental and refund to wallet?')">
                    @csrf
                    <button type="submit" class="min-h-[40px] inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition active:scale-[0.98]" title="Cancel &amp; refund">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Cancel</span>
                    </button>
                </form>
            @elseif($cancelAllowedAt)
                <div class="min-h-[40px] inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800" title="Cancel available 10 min after rental start">
                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-data="cancelCountdown('{{ $cancelAllowedAt->toIso8601String() }}')" x-init="start()" x-text="label">Cancel in 10:00</span>
                </div>
            @endif
        @endif
    </div>
</div>
@endif
