<x-layouts::app :title="__('Employees')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Équipe</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Employés</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Gérez les membres de l’équipe, leurs postes, salaires et coordonnées.
            </p>
            <div class="mt-6">
                <a href="{{ route('employees.create') }}" class="btn-primary">
                    Ajouter un employé
                </a>
            </div>
        </div>

        @include('partials.report-toolbar', [
            'resource' => 'employees',
            'heading' => 'Exporter les employés',
            'description' => 'Téléchargez les données des employés en PDF ou ouvrez un aperçu imprimable pour un mois donné.'
        ])

        @include('partials.branch-filter', [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'action' => route('employees.index'),
        ])

        <div class="surface-card-elevated overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Branche</th>
                            <th>Email</th>
                            <th>Poste</th>
                            <th>Connexion</th>
                            <th>Salaire</th>
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td class="text-slate-900 dark:text-white">{{ $employee->id }}</td>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $employee->name }}</td>
                                <td>{{ $employee->branch?->name ?? 'Toute l’agence' }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->job_title }}</td>
                                <td>
                                    <span class="badge-soft">
                                        {{ $employee->user ? 'Compte personnel' : 'Profil seulement' }}
                                    </span>
                                </td>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $employee->salary }}</td>
                                <td>{{ $employee->phone_number }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('employees.show', $employee) }}" class="btn-ghost px-3 py-1.5">
                                            Voir
                                        </a>
                                        <a href="{{ route('employees.edit', $employee) }}" class="btn-secondary px-3 py-1.5">
                                            Modifier
                                        </a>
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet employé ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost px-3 py-1.5">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Aucun employé trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
