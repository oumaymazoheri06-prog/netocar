<x-layouts::app :title="__('Ticket Details')">
    @php
        $isStaff = auth()->user()?->role === 'staff';
        $statusLabels = collect(\App\Enums\TicketStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
        $vehicleLabels = ['car' => 'Voiture', 'motorcycle' => 'Moto', 'van' => 'Utilitaire'];
    @endphp

    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-sky-600 p-8 text-white shadow-xl shadow-slate-200/60 dark:bg-sky-900 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.6em] text-white/70">File en direct</p>
            <h1 class="mt-3 text-3xl font-black">Détails du ticket</h1>
            <p class="mt-3 max-w-2xl text-white/80">
                Consultez toutes les informations du ticket.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Client</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $ticket->client?->name ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Service</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $ticket->service?->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Type de véhicule</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $vehicleLabels[$ticket->vehicle_type] ?? $ticket->vehicle_type }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Immatriculation</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $ticket->plate_number ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Employé</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $ticket->employee?->name ?? 'Non assigné' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Branche</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $ticket->branch?->name ?? 'Toute l’agence' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Source</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">
                        {{ $ticket->reservation_id ? 'Réservation n°' . $ticket->reservation_id : 'Ticket manuel' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Statut</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Prix</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">MAD {{ number_format($ticket->price, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Créé le</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $ticket->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400">Mis à jour le</dt>
                    <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $ticket->updated_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('tickets.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Retour
                </a>

                @if ($ticket->status === 'waiting')
                    <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="btn-secondary">Démarrer</button>
                    </form>
                @elseif ($ticket->status === 'in_progress')
                    <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn-secondary">Terminer</button>
                    </form>
                @endif

                @unless ($isStaff)
                    @if (in_array($ticket->status, ['waiting', 'in_progress'], true))
                        <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn-ghost">Annuler</button>
                        </form>
                    @endif
                @endunless
            </div>
        </div>
    </div>
</x-layouts::app>
