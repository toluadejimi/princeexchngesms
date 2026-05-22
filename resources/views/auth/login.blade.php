<x-guest-layout title="Log in">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Welcome back</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Sign in to your account to continue</p>
    </div>

    <x-auth-session-status class="mb-5 p-4 rounded-xl bg-mint-500/10 text-mint-700 dark:text-mint-300 text-sm border border-mint-500/20" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-4 py-3 text-sm shadow-sm focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 dark:focus:border-mint-500 dark:focus:ring-mint-500/20 transition-colors"
                placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-4 py-3 text-sm shadow-sm focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 dark:focus:border-mint-500 dark:focus:ring-mint-500/20 transition-colors"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center gap-2.5 cursor-pointer text-slate-600 dark:text-slate-400">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-mint-600 focus:ring-mint-500 dark:focus:ring-mint-500 h-4 w-4" />
                <span>Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-medium text-mint-600 dark:text-mint-400 hover:underline">Forgot password?</a>
            @endif
        </div>

        <button type="submit" :disabled="submitting" :aria-busy="submitting.toString()" class="w-full flex justify-center items-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-mint-500 to-teal-500 hover:from-mint-600 hover:to-teal-600 text-white text-sm font-semibold shadow-lg shadow-mint-500/25 focus:outline-none focus:ring-2 focus:ring-mint-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-all active:scale-[0.99] disabled:opacity-75 disabled:cursor-not-allowed disabled:active:scale-100">
            <svg x-show="submitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span x-show="!submitting">Log in</span>
            <span x-show="submitting" x-cloak>Logging in...</span>
            <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </form>

    <p class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700 text-center text-sm text-slate-500 dark:text-slate-400">
        Don't have an account? <a href="{{ route('register') }}" class="font-semibold text-mint-600 dark:text-mint-400 hover:underline">Sign up</a>
    </p>
</x-guest-layout>
