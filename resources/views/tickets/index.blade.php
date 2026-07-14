<x-layouts::app :title="__('Tickets')">
    @php
        $isStaff = auth()->user()?->role === 'staff';
        $vehicleLabels = ['car' => 'Voiture', 'motorcycle' => 'Moto', 'van' => 'Utilitaire'];
    @endphp

    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">{{ $isStaff ? 'Ma file' : 'File en direct' }}</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $isStaff ? 'Mes tickets' : 'Tickets' }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $isStaff ? 'Traitez vos tickets assignés et gardez chaque statut à jour.' : 'Suivez en temps réel les tickets en attente, en cours et terminés.' }}
            </p>
            @unless ($isStaff)
                <div class="mt-6">
                    <a href="{{ route('tickets.create') }}" class="btn-primary">
                        Nouveau ticket
                    </a>
                </div>
            @endunless
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-blue-800 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
                {{ session('success') }}
            </div>
        @endif

@unless ($isStaff)
    @include('partials.report-toolbar', [
        'resource' => 'tickets',
        'heading' => 'Exporter la file des tickets',
        'description' => 'Téléchargez la file actuelle en PDF ou imprimez un rapport par mois.'
    ])
@endunless

<form method="GET" action="{{ route('tickets.index') }}" class="surface-card flex flex-col gap-3 p-4 lg:flex-row lg:items-end">
    <div class="flex-1">
        <label for="period" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">
            Filtrer par période
        </label>
        <select name="period" id="period" class="input-modern">
            <option value="">Toute la période</option>
            <option value="day" {{ request('period') === 'day' ? 'selected' : '' }}>Aujourd’hui</option>
            <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Cette semaine</option>
            <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Ce mois</option>
        </select>
    </div>

    <div class="flex-1">
        <label for="sort" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">
            Trier par
        </label>
        <select name="sort" id="sort" class="input-modern">
            <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Plus récents</option>
            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Plus anciens</option>
        </select>
    </div>

    @unless ($isStaff)
        @if ($branches->isNotEmpty())
            <div class="flex-1">
                <label for="branch_id" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Branche
                </label>
                <select name="branch_id" id="branch_id" class="input-modern">
                    <option value="">Toutes les branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) ($selectedBranchId ?? '') === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}{{ $branch->is_active ? '' : ' (inactive)' }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    @endunless

    <div class="flex gap-2">
        <button type="submit" class="btn-primary">
            Appliquer
        </button>

        <a href="{{ route('tickets.index') }}" class="btn-secondary">
            Réinitialiser
        </a>
    </div>
</form>



@php
    $waitingCount = $tickets->where('status', 'waiting')->count();
    $progressCount = $tickets->where('status', 'in_progress')->count();
    $completedCount = $tickets->where('status', 'completed')->count();
    $cancelledCount = $tickets->where('status', 'cancelled')->count();
@endphp

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="kpi-card">
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">En attente</p>
        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $waitingCount }}</p>
    </div>

    <div class="kpi-card">
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">En cours</p>
        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $progressCount }}</p>
    </div>

    <div class="kpi-card">
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Terminés</p>
        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $completedCount }}</p>
    </div>

    <div class="kpi-card">
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Annulés</p>
        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $cancelledCount }}</p>
    </div>
</div>


        
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="surface-card p-5">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">En attente</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($tickets->where('status', 'waiting') as $ticket)
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $ticket->service?->name }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Branche : {{ $ticket->branch?->name ?? 'Toute l’agence' }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $vehicleLabels[$ticket->vehicle_type] ?? $ticket->vehicle_type }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Prix : MAD {{ number_format($ticket->price, 2) }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Employé : {{ $ticket->employee?->name ?? 'Non assigné' }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                Source : {{ $ticket->reservation_id ? 'Réservation n°' . $ticket->reservation_id : 'Ticket manuel' }}
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn-ghost px-3 py-1.5">
                                    Voir
                                </a>
                                <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="btn-secondary px-3 py-1.5">
                                        Démarrer
                                    </button>
                                </form>
                                @unless ($isStaff)
                                    <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn-ghost px-3 py-1.5">Annuler</button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucun ticket en attente.</p>
                    @endforelse
                </div>
            </div>

            <div class="surface-card p-5">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">En cours</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($tickets->where('status', 'in_progress') as $ticket)
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $ticket->service?->name }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Branche : {{ $ticket->branch?->name ?? 'Toute l’agence' }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $vehicleLabels[$ticket->vehicle_type] ?? $ticket->vehicle_type }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Employé : {{ $ticket->employee?->name ?? 'Non assigné' }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Prix : MAD {{ number_format($ticket->price, 2) }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                Source : {{ $ticket->reservation_id ? 'Réservation n°' . $ticket->reservation_id : 'Ticket manuel' }}
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn-ghost px-3 py-1.5">
                                    Voir
                                </a>
                                <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn-secondary px-3 py-1.5">
                                        Terminer
                                    </button>
                                </form>
                                @unless ($isStaff)
                                    <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn-ghost px-3 py-1.5">Annuler</button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucun ticket en cours.</p>
                    @endforelse
                </div>
            </div>

            <div class="surface-card p-5">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">Terminés</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($tickets->where('status', 'completed') as $ticket)
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="font-bold text-slate-900 dark:text-white">{{ $ticket->service?->name }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">Branche : {{ $ticket->branch?->name ?? 'Toute l’agence' }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $vehicleLabels[$ticket->vehicle_type] ?? $ticket->vehicle_type }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">MAD {{ number_format($ticket->price, 2) }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                Source : {{ $ticket->reservation_id ? 'Réservation n°' . $ticket->reservation_id : 'Ticket manuel' }}
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn-ghost px-3 py-1.5">
                                    Voir
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucun ticket terminé.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
