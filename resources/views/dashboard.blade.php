<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 px-4 py-3 text-red-700 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-mint-100 dark:bg-mint-900/30 border border-mint-200 dark:border-mint-800 px-4 py-3 text-mint-700 dark:text-mint-300">
                    {{ session('success') }}
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-slate-200">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
