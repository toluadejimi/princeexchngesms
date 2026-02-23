<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                New support ticket
            </h2>
            <a href="{{ route('support.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Back to support</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass p-6">
            <form action="{{ route('support.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required maxlength="255"
                        class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500"
                        placeholder="Brief description of your issue">
                    @error('subject')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Message</label>
                    <textarea name="message" id="message" rows="6" required maxlength="5000"
                        class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500"
                        placeholder="Describe your complaint or request in detail">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-mint-500 hover:bg-mint-600 text-white font-medium transition active:scale-[0.98]">
                        Submit
                    </button>
                    <a href="{{ route('support.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
