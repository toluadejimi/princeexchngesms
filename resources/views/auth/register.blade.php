<x-guest-layout title="Sign up">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Create account</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Get started with virtual SMS numbers</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-3 py-2.5 text-sm shadow-sm focus:border-mint-500 focus:ring-1 focus:ring-mint-500 dark:focus:border-mint-500 dark:focus:ring-mint-500 transition-colors"
                placeholder="Your name" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-3 py-2.5 text-sm shadow-sm focus:border-mint-500 focus:ring-1 focus:ring-mint-500 dark:focus:border-mint-500 dark:focus:ring-mint-500 transition-colors"
                placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-3 py-2.5 text-sm shadow-sm focus:border-mint-500 focus:ring-1 focus:ring-mint-500 dark:focus:border-mint-500 dark:focus:ring-mint-500 transition-colors"
                placeholder="At least 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="block w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 px-3 py-2.5 text-sm shadow-sm focus:border-mint-500 focus:ring-1 focus:ring-mint-500 dark:focus:border-mint-500 dark:focus:ring-mint-500 transition-colors"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button type="submit" class="w-full flex justify-center items-center px-4 py-2.5 rounded-lg bg-mint-600 hover:bg-mint-700 dark:bg-mint-600 dark:hover:bg-mint-700 text-white text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-mint-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-colors">
            Create account
        </button>
    </form>

    <p class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-700 text-center text-sm text-slate-500 dark:text-slate-400">
        Already have an account? <a href="{{ route('login') }}" class="font-medium text-mint-600 dark:text-mint-400 hover:underline">Log in</a>
    </p>
</x-guest-layout>
