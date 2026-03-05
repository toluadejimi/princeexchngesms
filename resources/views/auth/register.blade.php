<x-guest-layout title="Sign up">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Create account</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Get started with virtual SMS numbers in minutes</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-4 py-3 text-sm shadow-sm focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 dark:focus:border-mint-500 dark:focus:ring-mint-500/20 transition-colors"
                placeholder="Your name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-4 py-3 text-sm shadow-sm focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 dark:focus:border-mint-500 dark:focus:ring-mint-500/20 transition-colors"
                placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-4 py-3 text-sm shadow-sm focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 dark:focus:border-mint-500 dark:focus:ring-mint-500/20 transition-colors"
                placeholder="At least 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="block w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-4 py-3 text-sm shadow-sm focus:border-mint-500 focus:ring-2 focus:ring-mint-500/20 dark:focus:border-mint-500 dark:focus:ring-mint-500/20 transition-colors"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full flex justify-center items-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-mint-500 to-teal-500 hover:from-mint-600 hover:to-teal-600 text-white text-sm font-semibold shadow-lg shadow-mint-500/25 focus:outline-none focus:ring-2 focus:ring-mint-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-all active:scale-[0.99]">
            Create account
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
    </form>

    <p class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-700 text-center text-sm text-slate-500 dark:text-slate-400">
        Already have an account? <a href="{{ route('login') }}" class="font-semibold text-mint-600 dark:text-mint-400 hover:underline">Log in</a>
    </p>
</x-guest-layout>
