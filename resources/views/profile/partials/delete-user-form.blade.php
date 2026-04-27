<section class="rounded-2xl border border-red-200/80 dark:border-red-900/40 bg-white dark:bg-slate-900 shadow-sm p-5 sm:p-8">
    <div class="flex items-start gap-3">
        <div class="shrink-0 w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/50 flex items-center justify-center text-red-600 dark:text-red-400" aria-hidden="true">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ __('Delete account') }}
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ __('Permanently remove your account, wallet history, and rentals. This cannot be undone.') }}
            </p>
            <button
                type="button"
                class="mt-4 inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl text-sm font-semibold text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 hover:bg-red-100 dark:hover:bg-red-950/70 transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            >
                {{ __('Delete my account') }}
            </button>
        </div>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ __('Confirm account deletion') }}
            </h3>

            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                {{ __('Enter your current password to confirm. All data tied to this account will be removed.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="text-slate-700 dark:text-slate-300" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1.5 block w-full max-w-md rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="{{ __('Current password') }}"
                    autocomplete="current-password"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-8 flex flex-wrap justify-end gap-3">
                <button type="button" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="inline-flex items-center justify-center min-h-[44px] px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-500 dark:bg-red-600 dark:hover:bg-red-500 transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                    {{ __('Delete permanently') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
