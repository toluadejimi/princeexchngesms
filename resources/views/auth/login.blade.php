<x-guest-layout title="Log in">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Welcome back</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sign in to your account</p>
    </div>

    <x-auth-session-status class="mb-4 p-3 rounded-lg bg-mint-500/10 text-mint-700 dark:bg-mint-500/20 dark:text-mint-300 text-sm" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-3 py-2.5 text-sm shadow-sm focus:border-mint-500 focus:ring-1 focus:ring-mint-500 dark:focus:border-mint-500 dark:focus:ring-mint-500 transition-colors"
                placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-3 py-2.5 text-sm shadow-sm focus:border-mint-500 focus:ring-1 focus:ring-mint-500 dark:focus:border-mint-500 dark:focus:ring-mint-500 transition-colors"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400">
                <input id="remember_me" type="checkbox" name="remember"
                    class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-mint-600 focus:ring-mint-500 dark:focus:ring-mint-500 h-4 w-4" />
                <span>Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-mint-600 dark:text-mint-400 hover:underline font-medium">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="w-full flex justify-center items-center px-4 py-2.5 rounded-lg bg-mint-600 hover:bg-mint-700 dark:bg-mint-600 dark:hover:bg-mint-700 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-mint-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-colors">
            Log in
        </button>
    </form>

    <p class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-700 text-center text-sm text-slate-500 dark:text-slate-400">
        Don't have an account? <a href="{{ route('register') }}" class="font-medium text-mint-600 dark:text-mint-400 hover:underline">Sign up</a>
    </p>
</x-guest-layout>
