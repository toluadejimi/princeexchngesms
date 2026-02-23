<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Broadcast notifications
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">Back to dashboard</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('message'))
            <div class="rounded-lg bg-mint-100 dark:bg-mint-900/30 text-mint-800 dark:text-mint-200 px-4 py-3">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Send notification to all users</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">All logged-in users will see this in their notification panel.</p>
            </div>
            <form action="{{ route('admin.notifications.store') }}" method="POST" class="p-4 space-y-4">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255"
                           class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500"
                           placeholder="e.g. Scheduled maintenance">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Message</label>
                    <textarea name="message" id="message" rows="4" required maxlength="5000"
                              class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500"
                              placeholder="Your message to all users...">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-mint-500 hover:bg-mint-600 text-white font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Send to all users
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800">
                <h3 class="font-semibold text-slate-800 dark:text-slate-200">Recent notifications</h3>
            </div>
            <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($notifications as $n)
                    <li class="px-4 py-3">
                        <p class="font-medium text-slate-800 dark:text-slate-200">{{ $n->title }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5 line-clamp-2">{{ Str::limit($n->message, 120) }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No notifications sent yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
