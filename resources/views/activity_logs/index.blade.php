<x-layouts::app :title="__('Journal d’activité')">
    @php
        $actionLabel = function (?string $action) {
            return match ($action) {
                'agency.created' => 'Agence créée',
                'agency.updated' => 'Agence modifiée',
                'agency.deleted' => 'Agence supprimée',
                'branch.created' => 'Branche créée',
                'branch.updated' => 'Branche modifiée',
                'branch.deleted' => 'Branche supprimée',
                'client.created' => 'Client créé',
                'client.updated' => 'Client modifié',
                'client.deleted' => 'Client supprimé',
                'employee.created' => 'Employé créé',
                'employee.created_with_staff_account' => 'Employé créé avec compte staff',
                'employee.updated' => 'Employé modifié',
                'employee.deleted' => 'Employé supprimé',
                'service.created' => 'Service créé',
                'service.updated' => 'Service modifié',
                'service.deleted' => 'Service supprimé',
                'reservation.created' => 'Réservation créée',
                'reservation.updated' => 'Réservation modifiée',
                'reservation.deleted' => 'Réservation supprimée',
                'ticket.created' => 'Ticket créé',
                'ticket.created_from_reservation' => 'Ticket créé depuis une réservation',
                'ticket.updated' => 'Ticket modifié',
                'ticket.deleted' => 'Ticket supprimé',
                'payment.submitted' => 'Paiement envoyé',
                'payment.status_changed' => 'Statut de paiement modifié',
                'payment.deleted' => 'Paiement supprimé',
                'import.clients.completed' => 'Import clients terminé',
                'import.employees.completed' => 'Import employés terminé',
                'import.services.completed' => 'Import services terminé',
                'agency.license_renewed' => 'Licence renouvelée',
                default => $action
                    ? \Illuminate\Support\Str::of($action)->replace(['.', '_'], ' ')->headline()
                    : 'Action inconnue',
            };
        };

        $roleLabel = fn (?string $role) => match ($role) {
            'admin' => 'Admin',
            'manager' => 'Manager',
            'staff' => 'Staff',
            default => 'Inconnu',
        };

        $subjectTypeLabel = function (?string $type) {
            $name = strtolower(class_basename((string) $type));

            return match ($name) {
                'agencies' => 'Agence',
                'branch' => 'Branche',
                'clients' => 'Client',
                'employees' => 'Employé',
                'services' => 'Service',
                'reservations' => 'Réservation',
                'ticket' => 'Ticket',
                'payment' => 'Paiement',
                default => \Illuminate\Support\Str::headline(class_basename((string) $type)),
            };
        };

        $fieldLabel = fn (string $field) => match ($field) {
            'name' => 'Nom',
            'email' => 'E-mail',
            'phone', 'phone_number' => 'Téléphone',
            'package' => 'Plan',
            'address' => 'Adresse',
            'code' => 'Code',
            'is_active' => 'Actif',
            'description' => 'Description',
            'price' => 'Prix',
            'photo' => 'Photo',
            'branch_id' => 'Branche',
            'client_id' => 'Client',
            'service_id' => 'Service',
            'employee_id' => 'Employé',
            'reservation_date' => 'Date de réservation',
            'vehicle_type' => 'Type de véhicule',
            'plate_number' => 'Immatriculation',
            'status' => 'Statut',
            'payment_method' => 'Mode de paiement',
            'amount' => 'Montant',
            'receipt' => 'Reçu',
            'job_title' => 'Poste',
            'salary' => 'Salaire',
            'user_id' => 'Compte utilisateur',
            'license_status' => 'Statut licence',
            'license_expires_at' => 'Expiration licence',
            'activated_at' => 'Activée le',
            default => \Illuminate\Support\Str::headline($field),
        };

        $formatValue = function ($value) {
            if ($value === null || $value === '') {
                return 'Vide';
            }

            if (is_bool($value)) {
                return $value ? 'Oui' : 'Non';
            }

            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            return match ((string) $value) {
                'pending' => 'En attente',
                'completed' => 'Validé',
                'failed' => 'Échoué',
                'confirmed' => 'Confirmée',
                'cancelled' => 'Annulée',
                'waiting' => 'En attente',
                'in_progress' => 'En cours',
                'active' => 'Active',
                'suspended' => 'Suspendue',
                default => (string) $value,
            };
        };
    @endphp

    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Piste d’audit</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Journal d’activité</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Vérifiez qui a modifié une fiche importante, quand l’action a eu lieu et à quelle agence elle appartient.
            </p>
        </div>

        <form method="GET" action="{{ route('activity-logs.index') }}" class="surface-card grid gap-3 p-4 lg:grid-cols-[minmax(0,1fr)_220px_220px_auto] lg:items-end">
            <div>
                <label for="q" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Recherche</label>
                <input id="q" name="q" value="{{ request('q') }}" class="input-modern" placeholder="Utilisateur, fiche, agence ou IP">
            </div>

            <div>
                <label for="action" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Action</label>
                <select id="action" name="action" class="input-modern">
                    <option value="">Toutes les actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>
                            {{ $actionLabel($action) }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if (auth()->user()?->role === 'admin')
                <div>
                    <label for="agency_id" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Agence</label>
                    <select id="agency_id" name="agency_id" class="input-modern">
                        <option value="">Toutes les agences</option>
                        @foreach ($agencies as $agency)
                            <option value="{{ $agency->id }}" @selected((string) request('agency_id') === (string) $agency->id)>
                                {{ $agency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Appliquer</button>
                <a href="{{ route('activity-logs.index') }}" class="btn-secondary">Réinitialiser</a>
            </div>
        </form>

        <div class="surface-card-elevated overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Agence</th>
                            <th>Action</th>
                            <th>Fiche</th>
                            <th>Modifications</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap text-slate-900 dark:text-white">
                                    {{ $log->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $log->user_name ?? 'Système' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $roleLabel($log->user_role) }}</div>
                                </td>
                                <td>
                                    {{ $log->agency_name ?? $log->agency?->name ?? 'Plateforme' }}
                                </td>
                                <td>
                                    <span class="badge-soft">
                                        {{ $actionLabel($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $log->subject_label ?? 'Fiche #'.$log->subject_id }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $subjectTypeLabel($log->subject_type) }}</div>
                                </td>
                                <td>
                                    @if ($log->changes)
                                        <div class="space-y-2">
                                            @foreach ($log->changes as $field => $change)
                                                <div class="text-xs leading-5 text-slate-600 dark:text-slate-300">
                                                    <span class="font-bold text-slate-900 dark:text-white">{{ $fieldLabel($field) }}</span>:
                                                    <span>{{ $formatValue(data_get($change, 'from')) }}</span>
                                                    <span class="text-slate-400">vers</span>
                                                    <span>{{ $formatValue(data_get($change, 'to')) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-500 dark:text-slate-400">Aucun champ modifié</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                    {{ $log->ip_address ?? 'N/D' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Aucune activité enregistrée pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
