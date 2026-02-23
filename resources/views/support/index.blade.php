<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Support
            </h2>
            <a href="{{ route('support.create') }}" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl bg-mint-500 hover:bg-mint-600 text-white font-medium transition active:scale-[0.98]">
                New ticket
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('message'))
            <div class="rounded-lg bg-mint-100 dark:bg-mint-900/30 text-mint-800 dark:text-mint-200 px-4 py-3">
                {{ session('message') }}
            </div>
        @endif
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-glass">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400">Submit a complaint or request and we will respond as soon as possible.</p>
            </div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse($tickets as $ticket)
                    <a href="{{ route('support.show', $ticket->id) }}" class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-800 dark:text-slate-200 truncate">{{ $ticket->subject }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $ticket->created_at->format('M j, Y H:i') }}</p>
                            </div>
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium shrink-0
                                @if($ticket->status === 'open') bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300
                                @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                @endif">
                                {{ $ticket->status }}
                            </span>
                        </div>
                        @if($ticket->admin_reply)
                            <p class="text-xs text-mint-600 dark:text-mint-400 mt-2">Reply received</p>
                        @endif
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                        <p>You have no support tickets yet.</p>
                        <a href="{{ route('support.create') }}" class="inline-block mt-3 text-mint-600 dark:text-mint-400 hover:underline">Submit a complaint</a>
                    </div>
                @endforelse
            </div>
            @if($tickets->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
