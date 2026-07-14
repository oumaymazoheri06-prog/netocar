<x-layouts::app :title="__('Analytics')">
    @php
        $formatDuration = function (?int $minutes) {
            if ($minutes === null) {
                return 'N/D';
            }

            if ($minutes < 60) {
                return $minutes.' min';
            }

            return intdiv($minutes, 60).' h '.($minutes % 60).' min';
        };

        $paymentStatusLabel = fn (?string $status) => match ($status) {
            'pending' => 'En attente',
            'completed' => 'Validé',
            'failed' => 'Échoué',
            'unpaid' => 'Non payé',
            default => $status ? ucfirst($status) : 'Non payé',
        };
    @endphp

    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Analyses</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">
                {{ $mode === 'admin' ? 'Analyses plateforme' : 'Analyses opérationnelles' }}
            </h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $mode === 'admin' ? 'Suivez les paiements et les agences qui demandent encore une attention de facturation.' : 'Comprenez le chiffre d’affaires, la performance de l’équipe, les clients et les jours les plus chargés.' }}
            </p>
        </div>

        @if ($mode === 'admin')
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="kpi-card">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Agences</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $agenciesCount }}</p>
                </div>

                <div class="kpi-card">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Agences payées</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $paidAgenciesCount }}</p>
                </div>

                <div class="kpi-card">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Agences non payées</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $unpaidAgenciesCount }}</p>
                </div>

                <div class="kpi-card">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Montant à suivre</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">MAD {{ number_format($unpaidAmountTotal, 2) }}</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="surface-card-elevated p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Paiements</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Répartition par statut</h2>

                    <div class="mt-6 space-y-4">
                        @foreach ($paymentStatusCounts as $status => $count)
                            <div class="dashboard-soft-panel rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $paymentStatusLabel($status) }}</span>
                                    <span class="badge-soft">{{ $count }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="surface-card-elevated overflow-hidden xl:col-span-2">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Facturation</p>
                        <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Agences non payées</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Agence</th>
                                    <th>Plan</th>
                                    <th>À payer</th>
                                    <th>Dernier paiement</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($unpaidAgencies as $agency)
                                    @php
                                        $lastPayment = $agency->payments->first();
                                    @endphp
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white">{{ $agency->name }}</td>
                                        <td>{{ $agency->plan_name }}</td>
                                        <td class="font-semibold text-slate-900 dark:text-white">MAD {{ number_format($agency->plan_amount, 2) }}</td>
                                        <td>{{ $lastPayment?->created_at?->format('d/m/Y') ?? 'Aucun paiement' }}</td>
                                        <td>
                                            <span class="badge-soft">{{ $paymentStatusLabel($lastPayment?->status ?? 'unpaid') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                            Aucune agence non payée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="kpi-card">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Chiffre d’affaires total</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">MAD {{ number_format($totalRevenue, 2) }}</p>
                </div>

                <div class="kpi-card">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Tickets terminés</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $completedTicketsCount }}</p>
                </div>

                <div class="kpi-card">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Temps moyen par ticket</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $formatDuration($averageTicketMinutes) }}</p>
                </div>

                <div class="kpi-card">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Réservations annulées</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $cancelledReservationsCount }}</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $cancellationRate }}% de taux d’annulation</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="surface-card-elevated p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Sites</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Chiffre d’affaires par branche</h2>

                    <div class="mt-6 space-y-4">
                        @forelse ($branchPerformance as $branch)
                            <div>
                                <div class="flex items-center justify-between gap-4 text-sm">
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $branch['name'] }}</p>
                                        <p class="text-slate-500 dark:text-slate-400">{{ $branch['tickets'] }} tickets, {{ $branch['reservations'] }} réservations, {{ $branch['cancelled'] }} annulées</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black text-slate-900 dark:text-white">MAD {{ number_format($branch['revenue'], 2) }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $branch['status'] }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-blue-900/10 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-blue-700" style="width: {{ ($branch['revenue'] / $maxBranchRevenue) * 100 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Aucune branche pour le moment.</p>
                        @endforelse
                    </div>
                </div>

                <div class="surface-card-elevated p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Revenus</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Chiffre d’affaires par service</h2>

                    <div class="mt-6 space-y-4">
                        @forelse ($revenueByService as $service)
                            <div>
                                <div class="flex items-center justify-between gap-4 text-sm">
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $service['name'] }}</p>
                                        <p class="text-slate-500 dark:text-slate-400">{{ $service['ticket_count'] }} tickets, {{ $service['reservation_count'] }} réservations</p>
                                    </div>
                                    <p class="font-black text-slate-900 dark:text-white">MAD {{ number_format($service['revenue'], 2) }}</p>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-blue-900/10 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-blue-700" style="width: {{ ($service['revenue'] / $maxServiceRevenue) * 100 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Aucun service pour le moment.</p>
                        @endforelse
                    </div>
                </div>

                <div class="surface-card-elevated p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Demande</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Jours les plus chargés</h2>

                    <div class="mt-6 space-y-4">
                        @foreach ($busiestDays as $day)
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $day['label'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400">{{ $day['total'] }} au total</span>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-blue-900/10 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-blue-600" style="width: {{ ($day['total'] / $maxBusiestDayTotal) * 100 }}%"></div>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $day['tickets'] }} tickets, {{ $day['reservations'] }} réservations</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="surface-card-elevated overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Équipe</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Performance des employés</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Branche</th>
                                <th>Terminés</th>
                                <th>Actifs</th>
                                <th>Réservations</th>
                                <th>Temps moyen</th>
                                <th>Revenus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employeePerformance as $employee)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $employee['name'] }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $employee['job_title'] }}</div>
                                    </td>
                                    <td>{{ $employee['branch'] }}</td>
                                    <td>{{ $employee['completed'] }}</td>
                                    <td>{{ $employee['in_progress'] }} en cours, {{ $employee['waiting'] }} en attente</td>
                                    <td>{{ $employee['reservations'] }}</td>
                                    <td>{{ $formatDuration($employee['average_minutes']) }}</td>
                                    <td class="font-semibold text-slate-900 dark:text-white">MAD {{ number_format($employee['revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Aucun employé pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="surface-card-elevated overflow-hidden">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Clients</p>
                        <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Clients récurrents</h2>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $recurringRate }}% des clients actifs ont plusieurs réservations.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Email</th>
                                    <th>Réservations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recurringCustomers as $client)
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white">{{ $client->name }}</td>
                                        <td>{{ $client->email }}</td>
                                        <td>{{ $client->reservation_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                            Aucun client récurrent pour le moment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="surface-card-elevated overflow-hidden">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Réservations</p>
                        <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Annulations récentes</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Service</th>
                                        <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentCancelledReservations as $reservation)
                                    <tr>
                                        <td class="font-semibold text-slate-900 dark:text-white">{{ $reservation->client?->name ?? 'N/D' }}</td>
                                        <td>{{ $reservation->service?->name ?? 'N/D' }}</td>
                                        <td>{{ $reservation->reservation_date?->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                            Aucune réservation annulée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
