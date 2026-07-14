<x-layouts::app :title="__('Clients')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Base clients</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Clients</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Gérez les fiches clients, les coordonnées et les mises à jour.
            </p>
            <div class="mt-6">
                <a href="{{ route('clients.create') }}" class="btn-primary">
                    Ajouter un client
                </a>
            </div>
        </div>

        @include('partials.report-toolbar', [
            'resource' => 'clients',
            'heading' => 'Exporter les clients',
            'description' => 'Téléchargez un PDF de tous les clients ou filtrez l’export par mois.'
        ])

        @include('partials.branch-filter', [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'action' => route('clients.index'),
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
                            <th>Téléphone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td class="text-slate-900 dark:text-white">{{ $client->id }}</td>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $client->name }}</td>
                                <td>{{ $client->branch?->name ?? 'Toute l’agence' }}</td>
                                <td>{{ $client->email }}</td>
                                <td>{{ $client->phone }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('clients.show', $client->id) }}" class="btn-ghost px-3 py-1.5">
                                            Voir
                                        </a>
                                        <a href="{{ route('clients.edit', $client->id) }}" class="btn-secondary px-3 py-1.5">
                                            Modifier
                                        </a>
                                        <button type="button" onclick="openDeleteModal('{{ route('clients.destroy', $client) }}')"
                                                class="btn-ghost px-3 py-1.5">
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Aucun client trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 hidden flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-lg dark:bg-slate-900">
            <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Supprimer le client</h2>
            <p class="mb-6 text-slate-700 dark:text-slate-300">Voulez-vous vraiment supprimer ce client ?</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="btn-secondary">
                    Annuler
                </button>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-primary bg-slate-900 hover:bg-black dark:bg-slate-100 dark:text-slate-950 dark:hover:bg-white">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(action) {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteForm').action = action;
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</x-layouts::app>
