@props([
    'title',
    'description',
])

<div class="auth-header flex w-full flex-col text-center">
    <div class="auth-kicker mx-auto mb-5 inline-flex items-center gap-2">
        <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
        NetoCar
    </div>
    <flux:heading size="xl" class="auth-title text-slate-950 dark:text-white">{{ $title }}</flux:heading>
    <flux:subheading class="auth-description mx-auto mt-2 max-w-sm text-slate-500 dark:text-slate-400">{{ $description }}</flux:subheading>
</div>
