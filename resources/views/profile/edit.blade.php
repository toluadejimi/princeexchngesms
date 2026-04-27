<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                {{ __('Profile') }}
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm sm:text-base mt-0.5">{{ __('Account security and preferences') }}</p>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 space-y-8">
        @if (session('message'))
            <div class="rounded-2xl border border-mint-200 dark:border-mint-800/60 bg-mint-50 dark:bg-mint-950/40 px-4 py-3 text-sm text-mint-800 dark:text-mint-200" role="status">
                {{ session('message') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/30 px-4 py-3 text-sm text-red-800 dark:text-red-200" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @include('profile.partials.update-profile-information-form')

        @include('profile.partials.update-password-form')

        @include('profile.partials.delete-user-form')
    </div>
</x-app-layout>
