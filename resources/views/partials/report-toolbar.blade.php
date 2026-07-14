@php
    $resource = $resource ?? '';
    $scopeValue = request('scope', 'all');
    $monthValue = request('month', now()->format('Y-m'));
@endphp

<div class="surface-card p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Export</p>
            <h2 class="mt-3 text-xl font-black text-slate-900 dark:text-white">{{ $heading }}</h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $description }}
            </p>
        </div>

        <form method="GET" action="{{ route('reports.download', $resource) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div>
                <label for="{{ $resource }}-scope" class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Portée</label>
                <select
                    id="{{ $resource }}-scope"
                    name="scope"
                    class="input-modern text-sm"
                >
                    <option value="all" {{ $scopeValue === 'all' ? 'selected' : '' }}>Toutes les données</option>
                    <option value="month" {{ $scopeValue === 'month' ? 'selected' : '' }}>Par mois</option>
                </select>
            </div>

            <div>
                <label for="{{ $resource }}-month" class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Mois</label>
                <input
                    id="{{ $resource }}-month"
                    type="month"
                    name="month"
                    value="{{ $monthValue }}"
                    class="input-modern text-sm"
                >
            </div>

            <button type="submit" class="btn-primary">
                Télécharger PDF
            </button>

            <button
                type="submit"
                formaction="{{ route('reports.print', $resource) }}"
                formtarget="_blank"
                class="btn-secondary"
            >
                Aperçu impression
            </button>

            <button
                type="submit"
                formaction="{{ route('reports.csv', $resource) }}"
                class="btn-secondary"
            >
                Télécharger CSV
            </button>
        </form>
    </div>
</div>
