<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight truncate">
                Ticket #{{ $ticket->id }}
            </h2>
            <a href="{{ route('admin.support.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-mint-600 dark:hover:text-mint-400">← All tickets</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('success'))
            <div class="rounded-lg bg-mint-100 dark:bg-mint-900/30 text-mint-800 dark:text-mint-200 px-4 py-3">{{ session('success') }}</div>
        @endif
        <div class="flex flex-wrap items-center gap-2 text-sm">
            @if($ticket->user)
                <a href="{{ route('admin.users.show', $ticket->user) }}" class="text-mint-600 dark:text-mint-400 hover:underline font-medium">{{ $ticket->user->name }}</a>
                <span class="text-slate-500 dark:text-slate-400">{{ $ticket->user->email }}</span>
            @endif
            <span class="text-slate-500 dark:text-slate-400">{{ $ticket->created_at->format('M j, Y H:i') }}</span>
            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium
                @if($ticket->status === 'open') bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300
                @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                @endif">{{ $ticket->status }}</span>
        </div>
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-slate-200 dark:border-slate-800 shadow-glass overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <p class="font-medium text-slate-800 dark:text-slate-200">{{ $ticket->subject }}</p>
            </div>
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Customer message</p>
                <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $ticket->message }}</p>
            </div>
            @if($ticket->admin_reply)
                <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-mint-50/50 dark:bg-mint-900/10">
                    <p class="text-xs font-medium text-mint-600 dark:text-mint-400 uppercase tracking-wide mb-2">Your reply @if($ticket->replied_at)({{ $ticket->replied_at->format('M j, Y H:i') }})@endif</p>
                    <p class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $ticket->admin_reply }}</p>
                </div>
            @endif
            @if($ticket->isOpen())
                <div class="p-4 space-y-4">
                    @if(!$ticket->admin_reply)
                        <form action="{{ route('admin.support.reply', $ticket) }}" method="POST" class="space-y-3">
                            @csrf
                            <label for="admin_reply" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reply to customer</label>
                            <textarea name="admin_reply" id="admin_reply" rows="4" required maxlength="5000" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 shadow-sm focus:border-mint-500 focus:ring-mint-500" placeholder="Type your response...">{{ old('admin_reply') }}</textarea>
                            @error('admin_reply')<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl bg-mint-500 hover:bg-mint-600 text-white font-medium transition">Send reply</button>
                        </form>
                    @endif
                    <form action="{{ route('admin.support.close', $ticket) }}" method="POST" class="inline" onsubmit="return confirm('Close this ticket?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">Close ticket</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
