@props(['active'])

@php
$classes = ($active ?? false)
    ? 'flex items-center min-h-[48px] w-full px-4 py-3 rounded-xl text-base font-medium text-mint-700 dark:text-mint-300 bg-mint-50 dark:bg-mint-900/30 border border-mint-200 dark:border-mint-800 focus:outline-none focus:ring-2 focus:ring-mint-500/50 transition'
    : 'flex items-center min-h-[48px] w-full px-4 py-3 rounded-xl text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400/30 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
