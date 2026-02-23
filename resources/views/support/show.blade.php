<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight truncate">
                {{ $ticket->subject }}
            </h2>
            <a href="{{ route('support.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← Back to support</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <span>Created {{ $ticket->created_at->format('M j, Y H:i') }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium
                @if($ticket->status === 'open') bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300
                @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                @endif">
                {{ $ticket->status }}
            </span>
        </div>
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Your message</p>
            </div>
            <div class="p-4">
                <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $ticket->message }}</p>
            </div>
            @if($ticket->admin_reply)
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-mint-50/50 dark:bg-mint-900/10">
                    <p class="text-xs font-medium text-mint-600 dark:text-mint-400 uppercase tracking-wide mb-2">Support reply</p>
                    @if($ticket->replied_at)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">{{ $ticket->replied_at->format('M j, Y H:i') }}</p>
                    @endif
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $ticket->admin_reply }}</p>
                </div>
            @elseif($ticket->status === 'open')
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30">
                    <p class="text-sm text-slate-500 dark:text-slate-400">We will respond to your ticket soon.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
