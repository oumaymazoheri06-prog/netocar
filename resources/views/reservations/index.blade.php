<x-layouts::app :title="__('Reservations')">
    @php
        $isStaff = auth()->user()?->role === 'staff';
        $statusLabels = collect(\App\Enums\ReservationStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
        $vehicleLabels = ['car' => 'Voiture', 'motorcycle' => 'Moto', 'van' => 'Utilitaire'];
    @endphp

    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">{{ $isStaff ? 'Mes réservations' : 'Réservations' }}</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $isStaff ? 'Mes réservations' : 'Réservations' }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $isStaff ? 'Consultez les réservations qui vous sont assignées et ouvrez les tickets liés quand le travail est prêt.' : 'Suivez les réservations clients, les employés assignés et le statut de chaque réservation.' }}
            </p>
            @unless ($isStaff)
                <div class="mt-6">
                    <a href="{{ route('reservations.create') }}" class="btn-primary">
                        Ajouter une réservation
                    </a>
                </div>
            @endunless
        </div>

        @unless ($isStaff)
            @include('partials.report-toolbar', [
                'resource' => 'reservations',
                'heading' => 'Exporter les réservations',
                'description' => 'Téléchargez l’historique des réservations en PDF ou imprimez un rapport pour le mois sélectionné.'
            ])

            @include('partials.branch-filter', [
                'branches' => $branches,
                'selectedBranchId' => $selectedBranchId,
                'action' => route('reservations.index'),
            ])
        @endunless

        <div class="surface-card-elevated overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Employé</th>
                            <th>Branche</th>
                            <th>Véhicule</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservations as $reservation)
                            <tr>
                                <td class="text-slate-900 dark:text-white">{{ $reservation->id }}</td>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $reservation->client?->name ?? 'N/D' }}</td>
                                <td>{{ $reservation->service?->name ?? 'N/D' }}</td>
                                <td>{{ $reservation->employee?->name ?? 'N/D' }}</td>
                                <td>{{ $reservation->branch?->name ?? 'Toute l’agence' }}</td>
                                <td>
                                    {{ $vehicleLabels[$reservation->vehicle_type] ?? 'N/D' }}
                                    @if ($reservation->plate_number)
                                        <div class="text-xs text-slate-400 dark:text-slate-500">{{ $reservation->plate_number }}</div>
                                    @endif
                                </td>
                                <td>{{ $reservation->reservation_date?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge-soft">
                                            {{ $statusLabels[$reservation->status] ?? $reservation->status }}
                                    </span>
                                    @if ($isStaff && $reservation->ticket)
                                        <div class="mt-2">
                                            <a href="{{ route('tickets.show', $reservation->ticket) }}" class="text-xs font-bold text-blue-700 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200">
                                                Ouvrir le ticket
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('reservations.show', $reservation) }}" class="btn-ghost px-3 py-1.5">
                                            Voir
                                        </a>
                                        @unless ($isStaff)
                                            <a href="{{ route('reservations.edit', $reservation) }}" class="btn-secondary px-3 py-1.5">
                                                Modifier
                                            </a>
                                            <button type="button" onclick="openDeleteModal('{{ route('reservations.destroy', $reservation) }}')"
                                                    class="btn-ghost px-3 py-1.5">
                                                Supprimer
                                            </button>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Aucune réservation trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @unless ($isStaff)
        <div id="deleteModal" class="fixed inset-0 hidden flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-lg dark:bg-slate-900">
                <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Supprimer la réservation</h2>
                <p class="mb-6 text-slate-700 dark:text-slate-300">Voulez-vous vraiment supprimer cette réservation ?</p>
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
    @endunless
</x-layouts::app>
