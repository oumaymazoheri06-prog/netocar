<x-layouts::app :title="__('Services')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Offre</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Services</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Gérez les services proposés par l’agence et leurs tarifs.
            </p>
            <div class="mt-6">
                <a href="{{ route('services.create') }}" class="btn-primary">
                    Ajouter un service
                </a>
            </div>
        </div>

        @include('partials.report-toolbar', [
            'resource' => 'services',
            'heading' => 'Exporter les services',
            'description' => 'Téléchargez le catalogue des services en PDF ou imprimez un aperçu mensuel.'
        ])

        @include('partials.branch-filter', [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'action' => route('services.index'),
        ])

        <div class="surface-card-elevated overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Branche</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Prix</th>
                            <th>Photo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td class="text-slate-900 dark:text-white">{{ $service->id }}</td>
                                <td>{{ $service->branch?->name ?? 'Toute l’agence' }}</td>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $service->name ?? 'N/D' }}</td>
                                <td>{{ $service->description ?? 'N/D' }}</td>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $service->price ?? 'N/D' }}</td>
                                <td>
                                    @if ($service->photo)
                                        <img src="{{ asset('storage/' . $service->photo) }}" alt="{{ $service->name }}"
                                             class="h-12 w-12 rounded-lg border border-slate-200 object-cover dark:border-zinc-600">
                                    @else
                                        <span class="text-slate-500 dark:text-slate-300">Aucune photo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('services.show', $service) }}" class="btn-ghost px-3 py-1.5">
                                            Voir
                                        </a>
                                        <a href="{{ route('services.edit', $service) }}" class="btn-secondary px-3 py-1.5">
                                            Modifier
                                        </a>
                                        <button type="button" onclick="openDeleteModal('{{ route('services.destroy', $service) }}')"
                                                class="btn-ghost px-3 py-1.5">
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Aucun service trouvé.
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
            <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Supprimer le service</h2>
            <p class="mb-6 text-slate-700 dark:text-slate-300">Voulez-vous vraiment supprimer ce service ?</p>
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
