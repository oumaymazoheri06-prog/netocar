<x-layouts::app :title="__('Branches')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Sites</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Branches</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Gérez chaque site de votre agence et séparez le travail, l’équipe, les services et les rapports par emplacement.
            </p>
            <div class="mt-6">
                <a href="{{ route('branches.create') }}" class="btn-primary">
                    Ajouter une branche
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-blue-800 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 shadow-sm dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            <div class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Total des branches</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $activeCount + $inactiveCount }}</p>
            </div>
            <div class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Actives</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $activeCount }}</p>
            </div>
            <div class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Inactives</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $inactiveCount }}</p>
            </div>
        </div>

        @include('partials.report-toolbar', [
            'resource' => 'branches',
            'heading' => 'Exporter les branches',
            'description' => 'Téléchargez les branches en PDF ou ouvrez un aperçu imprimable pour un mois donné.'
        ])

        <form method="GET" action="{{ route('branches.index') }}" class="surface-card flex flex-col gap-3 p-4 lg:flex-row lg:items-end">
            <div class="flex-1">
                <label for="status" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Statut</label>
                <select name="status" id="status" class="input-modern">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Appliquer</button>
                <a href="{{ route('branches.index') }}" class="btn-secondary">Réinitialiser</a>
            </div>
        </form>

        <div class="surface-card-elevated overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Code</th>
                            <th>Adresse</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th>Capacité</th>
                            <th>Données liées</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branches as $branch)
                            <tr>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $branch->name }}</td>
                                <td>{{ $branch->code ?? 'N/D' }}</td>
                                <td>{{ $branch->address ?? 'N/D' }}</td>
                                <td>{{ $branch->phone_number ?? 'N/D' }}</td>
                                <td>
                                    <span class="badge-soft">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td>{{ $branch->simultaneous_capacity }}</td>
                                <td>
                                    {{ $branch->clients_count }} clients,
                                    {{ $branch->employees_count }} employés,
                                    {{ $branch->services_count }} services,
                                    {{ $branch->reservations_count }} réservations,
                                    {{ $branch->tickets_count }} tickets
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('branches.show', $branch) }}" class="btn-ghost px-3 py-1.5">Voir</a>
                                        <a href="{{ route('branches.edit', $branch) }}" class="btn-secondary px-3 py-1.5">Modifier</a>
                                        <form action="{{ route('branches.destroy', $branch) }}" method="POST" onsubmit="return confirm('Supprimer cette branche ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost px-3 py-1.5">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Aucune branche trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
