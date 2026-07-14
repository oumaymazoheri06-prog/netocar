<x-layouts::app :title="__('Manager Dashboard')">
    @php
        $money = fn ($value) => 'MAD '.number_format((float) $value, 2, ',', ' ');
        $number = fn ($value) => number_format((float) $value, 0, ',', ' ');
        $percent = fn ($value) => number_format((float) $value, 1, ',', ' ').'%';
        $statusLabels = [
            'waiting' => 'En attente',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
        ];
        $reservationLabels = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
        ];
        $trendClass = [
            'up' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200',
            'down' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200',
            'neutral' => 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300',
        ][$monthlyRevenueTrend['direction']];
        $executiveMetrics = [
            [
                'label' => 'Revenus des tickets terminés',
                'value' => $money($monthIncome),
                'detail' => 'Ce mois, comparé au mois précédent',
                'meta' => $monthlyRevenueTrend['label'],
                'metaClass' => $trendClass,
            ],
            [
                'label' => 'Réservations confirmées',
                'value' => $number($confirmedReservationsCount),
                'detail' => 'Créneaux validés dans l’agence',
                'meta' => $number($todayReservationsCount).' aujourd’hui',
                'metaClass' => 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200',
            ],
            [
                'label' => 'Tickets terminés',
                'value' => $number($completedTicketsCount),
                'detail' => 'Prestations clôturées et facturables',
                'meta' => $percent($completionRate).' clôture',
                'metaClass' => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200',
            ],
            [
                'label' => 'Clients récurrents',
                'value' => $percent($recurringRate),
                'detail' => $number($recurringCustomersCount).' clients avec au moins 2 passages',
                'meta' => $number($activeCustomersCount).' actifs',
                'metaClass' => 'border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-900/50 dark:bg-teal-950/40 dark:text-teal-200',
            ],
        ];
    @endphp

    <div class="page-shell space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,.10)] dark:border-slate-800 dark:bg-slate-950">
            <div class="grid gap-px bg-slate-200 dark:bg-slate-800 lg:grid-cols-[1.45fr_.9fr]">
                <div class="bg-white p-6 dark:bg-slate-950 sm:p-8">
                    <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-teal-600 dark:text-teal-300">Cockpit manager</p>
                            <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 dark:text-white sm:text-5xl">
                                {{ $agency->name }}
                            </h1>
                            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300 sm:text-base">
                                Une vue opérationnelle reliée aux données réelles : réservations, tickets, branches, équipe, clients et revenus terminés.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:w-[26rem]">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Plan</p>
                                <p class="mt-2 text-sm font-black text-slate-950 dark:text-white">{{ $planLabel }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Échéance</p>
                                <p class="mt-2 text-sm font-black text-slate-950 dark:text-white">{{ $nextBillingDate }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($executiveMetrics as $metric)
                            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
                                <div class="flex items-start justify-between gap-4">
                                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $metric['label'] }}</p>
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black {{ $metric['metaClass'] }}">{{ $metric['meta'] }}</span>
                                </div>
                                <p class="mt-4 text-3xl font-black tracking-tight text-slate-950 dark:text-white">{{ $metric['value'] }}</p>
                                <p class="mt-2 text-xs font-semibold leading-5 text-slate-500 dark:text-slate-400">{{ $metric['detail'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>

                <aside class="bg-slate-950 p-6 text-white sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-teal-300">Aujourd’hui</p>
                    <div class="mt-5 grid gap-4">
                        <div>
                            <p class="text-sm text-slate-400">Revenus encaissables</p>
                            <p class="mt-2 text-4xl font-black tracking-tight">{{ $money($todayIncome) }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs font-bold text-slate-400">Tickets actifs</p>
                                <p class="mt-2 text-2xl font-black">{{ $number($activeTicketsCount) }}</p>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs font-bold text-slate-400">En attente</p>
                                <p class="mt-2 text-2xl font-black">{{ $number($pendingReservationsCount) }}</p>
                            </div>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-xs font-bold text-slate-400">Temps moyen par prestation</p>
                                <span class="rounded-full bg-teal-400/15 px-2.5 py-1 text-xs font-black text-teal-200">
                                    {{ $averageTicketMinutes ? $averageTicketMinutes.' min' : 'N/D' }}
                                </span>
                            </div>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                                <div class="h-full rounded-full bg-teal-400" style="width: {{ $averageTicketMinutes ? min(100, ($averageTicketMinutes / 120) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        @php
            $dailyMax = max(1, (float) $dailyRevenue->pluck('total')->max());
            $monthlyMax = max(1, (float) $monthlyRevenue->pluck('total')->max());
            $yearlyMax = max(1, (float) $yearlyRevenue->pluck('total')->max());
        @endphp

        <section class="grid gap-6 xl:grid-cols-[1.35fr_.85fr]">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,.08)] dark:border-slate-800 dark:bg-slate-950 sm:p-7">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">Revenus</p>
                        <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Tickets terminés, revenus réels.</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Chaque barre vient des tickets marqués comme terminés avec une date de clôture.</p>
                    </div>
                    <div class="flex gap-2 text-xs font-black">
                        <span class="rounded-full border border-slate-200 px-3 py-1.5 text-slate-600 dark:border-slate-700 dark:text-slate-300">Jour</span>
                        <span class="rounded-full border border-slate-200 px-3 py-1.5 text-slate-600 dark:border-slate-700 dark:text-slate-300">Mois</span>
                        <span class="rounded-full border border-slate-200 px-3 py-1.5 text-slate-600 dark:border-slate-700 dark:text-slate-300">Année</span>
                    </div>
                </div>

                <div class="mt-7 grid gap-5 lg:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Aujourd’hui</p>
                        <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $money($todayIncome) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Ce mois</p>
                        <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $money($monthIncome) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Cette année</p>
                        <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $money($yearIncome) }}</p>
                    </div>
                </div>

                <div class="mt-7 grid gap-5 xl:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 p-5 dark:border-slate-800">
                        <h3 class="font-black text-slate-950 dark:text-white">7 derniers jours</h3>
                        <div class="mt-5 flex h-52 items-end gap-2">
                            @foreach ($dailyRevenue as $point)
                                <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                                    <div class="flex h-36 w-full items-end rounded-full bg-slate-100 dark:bg-slate-900">
                                        <div class="w-full rounded-full bg-blue-700 dark:bg-blue-400" style="height: {{ max(3, ($point['total'] / $dailyMax) * 100) }}%"></div>
                                    </div>
                                    <span class="max-w-full truncate text-[10px] font-black text-slate-500 dark:text-slate-400">{{ $point['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-5 dark:border-slate-800">
                        <h3 class="font-black text-slate-950 dark:text-white">12 derniers mois</h3>
                        <div class="mt-5 space-y-3">
                            @foreach ($monthlyRevenue->take(-6) as $point)
                                <div>
                                    <div class="flex justify-between gap-3 text-xs font-bold text-slate-500 dark:text-slate-400">
                                        <span>{{ $point['label'] }}</span>
                                        <span>{{ $money($point['total']) }}</span>
                                    </div>
                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-900">
                                        <div class="h-full rounded-full bg-slate-950 dark:bg-white" style="width: {{ ($point['total'] / $monthlyMax) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-5 dark:border-slate-800">
                        <h3 class="font-black text-slate-950 dark:text-white">5 dernières années</h3>
                        <div class="mt-5 space-y-3">
                            @foreach ($yearlyRevenue as $point)
                                <div>
                                    <div class="flex justify-between gap-3 text-xs font-bold text-slate-500 dark:text-slate-400">
                                        <span>{{ $point['label'] }}</span>
                                        <span>{{ $money($point['total']) }}</span>
                                    </div>
                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-900">
                                        <div class="h-full rounded-full bg-teal-500" style="width: {{ ($point['total'] / $yearlyMax) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,.08)] dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-teal-600 dark:text-teal-300">Production</p>
                    <h2 class="mt-2 text-xl font-black text-slate-950 dark:text-white">Répartition des tickets</h2>

                    <div class="mt-5 flex overflow-hidden rounded-full bg-slate-100 dark:bg-slate-900">
                        @foreach ($ticketStatusBreakdown as $status)
                            @php
                                $statusColor = [
                                    'waiting' => 'bg-slate-400',
                                    'in_progress' => 'bg-blue-600',
                                    'completed' => 'bg-teal-500',
                                    'cancelled' => 'bg-rose-500',
                                ][$status['status']];
                            @endphp
                            <div class="h-3 {{ $statusColor }}" style="width: {{ ($status['count'] / $ticketStatusTotal) * 100 }}%"></div>
                        @endforeach
                    </div>

                    <div class="mt-5 grid gap-3">
                        @foreach ($ticketStatusBreakdown as $status)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-900">
                                <span class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ $status['label'] }}</span>
                                <span class="text-lg font-black text-slate-950 dark:text-white">{{ $number($status['count']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,.08)] dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">Qualité du flux</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Jour le plus chargé</p>
                            <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $busiestDay['label'] ?? 'N/D' }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $number($busiestDay['total'] ?? 0) }} opérations</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Taux d’annulation</p>
                            <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $percent($cancellationRate) }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $number($cancelledReservationsCount) }} réservations annulées</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                            <p class="text-xs font-bold text-slate-500 dark:text-slate-400">Capacité active</p>
                            <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $number($branchesCount) }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">branches suivies</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,.08)] dark:border-slate-800 dark:bg-slate-950 sm:p-7">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-teal-600 dark:text-teal-300">Branches</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Performance par site</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Revenus issus des tickets terminés, activité et charge active par branche.</p>
                </div>
                <a href="{{ route('analytics.index') }}" class="btn-ghost px-4 py-2">Analyse complète</a>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @forelse ($branchPerformance as $branch)
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-black text-slate-950 dark:text-white">{{ $branch['name'] }}</h3>
                                <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">{{ $branch['status'] }}</p>
                            </div>
                            <span class="rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-black text-teal-700 dark:border-teal-900/50 dark:bg-teal-950/40 dark:text-teal-200">
                                {{ $money($branch['revenue']) }}
                            </span>
                        </div>
                        <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-white p-3 dark:bg-slate-950">
                                <p class="text-lg font-black text-slate-950 dark:text-white">{{ $number($branch['tickets']) }}</p>
                                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">tickets</p>
                            </div>
                            <div class="rounded-lg bg-white p-3 dark:bg-slate-950">
                                <p class="text-lg font-black text-slate-950 dark:text-white">{{ $number($branch['reservations']) }}</p>
                                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">réserv.</p>
                            </div>
                            <div class="rounded-lg bg-white p-3 dark:bg-slate-950">
                                <p class="text-lg font-black text-slate-950 dark:text-white">{{ $number($branch['active_load']) }}</p>
                                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">actifs</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <div class="flex justify-between text-xs font-bold text-slate-500 dark:text-slate-400">
                                <span>Part du revenu</span>
                                <span>{{ $percent(($branch['revenue'] / $maxBranchRevenue) * 100) }}</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-white dark:bg-slate-950">
                                <div class="h-full rounded-full bg-teal-500" style="width: {{ ($branch['revenue'] / $maxBranchRevenue) * 100 }}%"></div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 dark:border-slate-700 dark:text-slate-400 lg:col-span-3">
                        Aucune branche à analyser pour le moment.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,.08)] dark:border-slate-800 dark:bg-slate-950">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">Services</p>
                <h2 class="mt-2 text-xl font-black text-slate-950 dark:text-white">Ce qui génère l’activité</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($revenueByService as $service)
                        <div>
                            <div class="flex justify-between gap-3">
                                <div>
                                    <p class="font-black text-slate-950 dark:text-white">{{ $service['name'] }}</p>
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $service['branch'] }} · {{ $number($service['demand']) }} opérations</p>
                                </div>
                                <span class="text-sm font-black text-slate-950 dark:text-white">{{ $money($service['revenue']) }}</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-900">
                                <div class="h-full rounded-full bg-blue-700 dark:bg-blue-400" style="width: {{ ($service['revenue'] / $maxServiceRevenue) * 100 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucun service avec activité pour le moment.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,.08)] dark:border-slate-800 dark:bg-slate-950">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-teal-600 dark:text-teal-300">Équipe</p>
                <h2 class="mt-2 text-xl font-black text-slate-950 dark:text-white">Performance terrain</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($employeePerformance as $employee)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black text-slate-950 dark:text-white">{{ $employee['name'] }}</p>
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $employee['job_title'] ?? 'Employé' }} · {{ $employee['branch'] }}</p>
                                </div>
                                <span class="text-sm font-black text-slate-950 dark:text-white">{{ $number($employee['completed']) }}</span>
                            </div>
                            <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">
                                {{ $money($employee['revenue']) }} · {{ $employee['average_minutes'] ? $employee['average_minutes'].' min moy.' : 'temps N/D' }} · {{ $number($employee['active']) }} actif(s)
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucun employé à analyser pour le moment.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,.08)] dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">File récente</p>
                        <h2 class="mt-2 text-xl font-black text-slate-950 dark:text-white">Tickets à surveiller</h2>
                    </div>
                    <a href="{{ route('tickets.index') }}" class="btn-ghost px-3 py-2 text-xs">Voir</a>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($recentTickets as $ticket)
                        <a href="{{ route('tickets.show', $ticket) }}" class="block rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:-translate-y-0.5 hover:border-blue-300 hover:bg-white hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-950">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black text-slate-950 dark:text-white">{{ $ticket->client?->name ?? 'Client direct' }}</p>
                                    <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">{{ $ticket->service?->name ?? 'Service supprimé' }} · {{ $ticket->branch?->name ?? 'Toute l’agence' }}</p>
                                </div>
                                <span class="badge-soft">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                            </div>
                            <p class="mt-3 text-xs font-bold text-slate-500 dark:text-slate-400">{{ $money($ticket->price ?? 0) }} · {{ $ticket->employee?->name ?? 'Non affecté' }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aucun ticket récent.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-[0_18px_50px_rgba(15,23,42,.08)] dark:border-slate-800 dark:bg-slate-950">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-teal-600 dark:text-teal-300">Réservations récentes</p>
                    <h2 class="mt-2 text-xl font-black text-slate-950 dark:text-white">Derniers mouvements clients</h2>
                </div>
                <a href="{{ route('reservations.index') }}" class="btn-ghost px-4 py-2">Tout voir</a>
            </div>

            <div class="overflow-x-auto p-6">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Employé</th>
                            <th>Branche</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentReservations as $reservation)
                            <tr>
                                <td class="font-bold text-slate-900 dark:text-white">{{ $reservation->client?->name ?? 'N/D' }}</td>
                                <td>{{ $reservation->service?->name ?? 'N/D' }}</td>
                                <td>{{ $reservation->employee?->name ?? 'Non affecté' }}</td>
                                <td>{{ $reservation->branch?->name ?? 'Toute l’agence' }}</td>
                                <td>{{ $reservation->reservation_date?->format('d/m/Y H:i') }}</td>
                                <td><span class="badge-soft">{{ $reservationLabels[$reservation->status] ?? $reservation->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">Aucune réservation pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts::app>
