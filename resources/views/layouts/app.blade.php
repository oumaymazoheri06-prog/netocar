<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="app-main-shell min-h-screen overflow-x-hidden text-slate-900 dark:text-slate-100">
        @if (auth()->user()?->isDemoAccount())
            <div class="px-4 pt-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-900 shadow-sm dark:border-teal-400/20 dark:bg-teal-400/10 dark:text-teal-100">
                    Mode demo public : les donnees sont fictives et les actions de modification sont bloquees.
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="px-4 pt-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 shadow-sm dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-100">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
