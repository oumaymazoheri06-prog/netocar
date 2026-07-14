<x-layouts::app :title="__('Staff Dashboard')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Espace personnel</p>
                    <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Mes travaux assignés</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ $employee->name }} at {{ $agency->name }}
                    </p>
                </div>

                <div class="dashboard-soft-panel rounded-xl px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Poste</p>
                    <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">{{ $employee->job_title }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">En attente</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $waitingTicketsCount }}</p>
            </div>

            <div class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">En cours</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $inProgressTicketsCount }}</p>
            </div>

            <div class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Terminés aujourd’hui</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $completedTodayCount }}</p>
            </div>

            <div class="kpi-card">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Réservations</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $assignedReservationsCount }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="surface-card-elevated overflow-hidden xl:col-span-2">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">File</p>
                            <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Tickets actifs</h2>
                        </div>
                        <a href="{{ route('tickets.index') }}" class="btn-ghost px-0 hover:bg-transparent hover:text-slate-700 dark:hover:bg-transparent dark:hover:text-slate-200">
                            Tout voir
                        </a>
                    </div>
                </div>

                <div class="space-y-4 p-6">
                    @forelse ($activeTickets as $ticket)
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $ticket->service?->name ?? 'N/D' }}</p>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                        {{ ['car' => 'Voiture', 'motorcycle' => 'Moto', 'van' => 'Utilitaire'][$ticket->vehicle_type] ?? $ticket->vehicle_type }}{{ $ticket->plate_number ? ' - '.$ticket->plate_number : '' }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $ticket->reservation_id ? 'Réservation n°'.$ticket->reservation_id : 'Ticket manuel' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn-ghost px-3 py-1.5">Voir</a>

                                    @if ($ticket->status === 'waiting')
                                        <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="btn-secondary px-3 py-1.5">Démarrer</button>
                                        </form>
                                    @elseif ($ticket->status === 'in_progress')
                                        <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn-secondary px-3 py-1.5">Terminer</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="p-2 text-sm text-slate-500 dark:text-slate-400">Aucun ticket actif assigné.</p>
                    @endforelse
                </div>
            </div>

            <div class="surface-card-elevated overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Réservations</p>
                            <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">À venir</h2>
                        </div>
                        <a href="{{ route('reservations.index') }}" class="btn-ghost px-0 hover:bg-transparent hover:text-slate-700 dark:hover:bg-transparent dark:hover:text-slate-200">
                            Tout voir
                        </a>
                    </div>
                </div>

                <div class="space-y-4 p-6">
                    @forelse ($upcomingReservations as $reservation)
                        <a href="{{ route('reservations.show', $reservation) }}" class="dashboard-soft-panel block rounded-xl p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $reservation->client?->name ?? 'N/D' }}</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $reservation->service?->name ?? 'N/D' }}</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $reservation->reservation_date?->format('d/m/Y H:i') }}</p>
                            <span class="mt-3 inline-flex">
                                <span class="badge-soft">{{ ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée'][$reservation->status] ?? $reservation->status }}</span>
                            </span>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucune réservation à venir.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
