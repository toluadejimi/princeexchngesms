@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium bg-mint-500/15 dark:bg-mint-500/20 text-mint-700 dark:text-mint-300 focus:outline-none focus:ring-2 focus:ring-mint-500/30 transition'
    : 'inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-400/30 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
