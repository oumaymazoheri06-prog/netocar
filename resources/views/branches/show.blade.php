<x-layouts::app :title="__('Branch Details')">
    <div class="p-6 max-w-4xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Sites</p>
            <h1 class="mt-3 text-3xl font-black">{{ $branch->name }}</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                {{ $branch->address ?? 'Aucune adresse renseignée' }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Code</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $branch->code ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Statut</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $branch->is_active ? 'Active' : 'Inactive' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Téléphone</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $branch->phone_number ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Adresse</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $branch->address ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Capacité simultanée</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $branch->simultaneous_capacity }} véhicule(s)</dd>
                </div>
            </dl>
        </div>

        <div class="grid gap-4 md:grid-cols-5">
            <a href="{{ route('clients.index', ['branch_id' => $branch->id]) }}" class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Clients</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $branch->clients_count }}</p>
            </a>
            <a href="{{ route('employees.index', ['branch_id' => $branch->id]) }}" class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Employés</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $branch->employees_count }}</p>
            </a>
            <a href="{{ route('services.index', ['branch_id' => $branch->id]) }}" class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Services</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $branch->services_count }}</p>
            </a>
            <a href="{{ route('reservations.index', ['branch_id' => $branch->id]) }}" class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Réservations</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $branch->reservations_count }}</p>
            </a>
            <a href="{{ route('tickets.index', ['branch_id' => $branch->id]) }}" class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Tickets</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $branch->tickets_count }}</p>
            </a>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('branches.edit', $branch) }}" class="btn-secondary">Modifier</a>
            <a href="{{ route('branches.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                Retour
            </a>
        </div>
    </div>
</x-layouts::app>
