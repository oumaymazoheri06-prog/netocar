<x-layouts::app :title="__('Reservation Details')">
    @php
        $statusLabels = collect(\App\Enums\ReservationStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
        $vehicleLabels = ['car' => 'Voiture', 'motorcycle' => 'Moto', 'van' => 'Utilitaire'];
    @endphp

    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Réservations</p>
            <h1 class="mt-3 text-3xl font-black">Détails de la réservation</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Consultez les détails de la réservation et son statut.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Client</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $reservations->client?->name ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Service</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $reservations->service?->name ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Employé</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $reservations->employee?->name ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Branche</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $reservations->branch?->name ?? 'Toute l’agence' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Type de véhicule</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $vehicleLabels[$reservations->vehicle_type] ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Immatriculation</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $reservations->plate_number ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Date</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $reservations->reservation_date?->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Statut</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $statusLabels[$reservations->status] ?? $reservations->status }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Ticket</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">
                        @if ($reservations->ticket)
                            <a href="{{ route('tickets.show', $reservations->ticket) }}" class="text-blue-700 hover:text-blue-900 dark:text-blue-300 dark:hover:text-blue-200">
                                Ticket n°{{ $reservations->ticket->id }}
                            </a>
                        @else
                            Pas encore créé
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-6">
                <a href="{{ route('reservations.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Retour
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>
