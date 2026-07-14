@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'auth-status rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200']) }}>
        {{ $status }}
    </div>
@endif
