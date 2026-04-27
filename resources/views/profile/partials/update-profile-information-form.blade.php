@php
    $name = trim((string) $user->name);
    $initials = collect(preg_split('/\s+/', $name) ?: [])
        ->filter()
        ->take(2)
        ->map(fn ($w) => strtoupper(mb_substr((string) $w, 0, 1)))
        ->implode('');
    if ($initials === '') {
        $initials = '?';
    }
@endphp

<section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
    <div class="h-20 sm:h-24 bg-gradient-to-br from-mint-500/25 via-teal-500/15 to-slate-100 dark:from-mint-600/20 dark:via-teal-600/10 dark:to-slate-800/80"></div>
    <div class="px-5 sm:px-8 pb-8 -mt-10 sm:-mt-12">
        <div class="flex flex-col sm:flex-row sm:items-end gap-5 sm:gap-6">
            <div class="shrink-0 w-20 h-20 sm:w-24 sm:h-24 rounded-2xl ring-4 ring-white dark:ring-slate-900 bg-gradient-to-br from-mint-500 to-teal-600 flex items-center justify-center text-2xl sm:text-3xl font-bold text-white shadow-lg shadow-mint-500/25" aria-hidden="true">
                {{ $initials }}
            </div>
            <div class="min-w-0 flex-1 pt-1 sm:pb-1">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h2 class="text-lg sm:text-xl font-semibold text-slate-900 dark:text-white tracking-tight">
                        {{ __('Account') }}
                    </h2>
                    @if ($user->email_verified_at)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-mint-100 dark:bg-mint-900/50 text-mint-800 dark:text-mint-200 border border-mint-200/80 dark:border-mint-700/50">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ __('Verified') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-200 border border-amber-200 dark:border-amber-800/50">
                            {{ __('Unverified') }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Your sign-in identity is fixed for security. Contact support if you need it updated.') }}
                </p>
            </div>
        </div>

        <dl class="mt-8 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3.5">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ __('Username') }}</dt>
                <dd class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate" title="{{ $name }}">{{ $name !== '' ? $name : '—' }}</dd>
            </div>
            <div class="rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3.5">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ __('Email') }}</dt>
                <dd class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate font-mono" title="{{ $user->email }}">{{ $user->email }}</dd>
            </div>
            @if ($user->created_at)
                <div class="rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 px-4 py-3.5 sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ __('Member since') }}</dt>
                    <dd class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $user->created_at->timezone(config('app.timezone'))->translatedFormat('F j, Y') }}</dd>
                </div>
            @endif
        </dl>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-6 rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50/80 dark:bg-amber-950/25 px-4 py-4">
                <p class="text-sm text-slate-800 dark:text-slate-200 font-medium mb-3">
                    {{ __('Your email address is not verified yet.') }}
                </p>
                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-mint-600 hover:bg-mint-500 dark:bg-mint-500 dark:hover:bg-mint-400 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-mint-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        {{ __('Resend verification email') }}
                    </button>
                </form>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-3 text-sm font-medium text-mint-700 dark:text-mint-300" role="status">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif
    </div>
</section>
