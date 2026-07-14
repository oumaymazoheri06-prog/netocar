@php
    $user = auth()->user();
    $isAdmin = $user?->role === 'admin';
    $agency = $user?->agency;
    $subheading = $isAdmin
        ? __('Consultez les paiements et les factures au même endroit')
        : __('Choisissez comment payer la cotisation annuelle de votre agence');
    $paymentStatusLabel = fn (?string $status) => match ($status) {
        'pending' => 'En attente',
        'completed' => 'Validé',
        'failed' => 'Échoué',
        default => $status ? ucfirst($status) : 'N/D',
    };
    $planLabel = $agency?->package
        ? config("netocar.plans.{$agency->package}.label", ucfirst($agency->package))
        : '-';

    if ($isAdmin) {
        $payments = \App\Models\Payment::with('agency')->latest()->take(5)->get();
        $invoices = \App\Models\Payment::with('agency')->where('status', 'completed')->latest()->take(5)->get();
        $totalCount = \App\Models\Payment::count();
        $completedCount = \App\Models\Payment::where('status', 'completed')->count();
        $pendingCount = \App\Models\Payment::where('status', 'pending')->count();
        $totalAmount = \App\Models\Payment::where('status', 'completed')->sum('amount');
    } else {
        $recentPayments = \App\Models\Payment::where('agency_id', $agency->id)->latest()->take(5)->get();
        $totalCount = \App\Models\Payment::where('agency_id', $agency->id)->count();
        $completedCount = \App\Models\Payment::where('agency_id', $agency->id)->where('status', 'completed')->count();
        $pendingCount = \App\Models\Payment::where('agency_id', $agency->id)->where('status', 'pending')->count();
        $nextYearlyFee = $agency?->plan_amount ?? 0;
    }
@endphp

<x-layouts::app :title="__('Agence et facturation')">
    <section class="w-full">
        @include('partials.settings-heading')

        <flux:heading class="sr-only">{{ __('Réglages agence et facturation') }}</flux:heading>

        <x-settings.layout max-width="max-w-none" :heading="__('Agence et facturation')" :subheading="$subheading">
            <div class="space-y-6">
                @if (session('license_error'))
                    <div class="rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-800 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        {{ session('license_error') }}
                    </div>
                @endif

                @if ($isAdmin)
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="kpi-card">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Paiements totaux</p>
                            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $totalCount }}</p>
                        </div>

                        <div class="kpi-card">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Factures validées</p>
                            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $completedCount }}</p>
                        </div>

                        <div class="kpi-card">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Paiements en attente</p>
                            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $pendingCount }}</p>
                        </div>

                        <div class="kpi-card">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Montant validé</p>
                            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">MAD {{ number_format($totalAmount, 2) }}</p>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="surface-card p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Paiements</p>
                                    <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Paiements récents</h2>
                                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                        Les dernières transactions et les reçus envoyés.
                                    </p>
                                </div>

                                <a href="{{ route('payments.index') }}" class="btn-secondary">
                                    Tout voir
                                </a>
                            </div>

                            <div class="mt-6 space-y-3">
                                @forelse ($payments as $payment)
                                    <div class="dashboard-soft-panel rounded-xl p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="font-bold text-slate-900 dark:text-white">MAD {{ number_format($payment->amount, 2) }}</p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                                    {{ $payment->agency?->name ?? 'N/D' }} - {{ ucfirst($payment->payment_method) }} - {{ $payment->created_at?->format('d/m/Y') }}
                                                </p>
                                            </div>
                                            <span class="badge-soft">
                                                {{ $paymentStatusLabel($payment->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucun paiement pour le moment.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="surface-card p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Factures</p>
                                    <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Factures émises</h2>
                                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                        Les factures sont générées à partir des paiements validés.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 space-y-3">
                                @forelse ($invoices as $invoice)
                                    <div class="dashboard-soft-panel rounded-xl p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="font-bold text-slate-900 dark:text-white">
                                                    Facture #INV-{{ str_pad((string) $invoice->id, 4, '0', STR_PAD_LEFT) }}
                                                </p>
                                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                                    MAD {{ number_format($invoice->amount, 2) }} - {{ $invoice->created_at?->format('d/m/Y') }}
                                                </p>
                                            </div>
                                            <a href="{{ route('payments.show', $invoice) }}" class="text-sm font-semibold text-blue-700 hover:text-blue-600 dark:text-blue-300 dark:hover:text-blue-200">
                                                Voir
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Aucune facture validée pour le moment.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="kpi-card">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Agence</p>
                            <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">{{ $agency?->name ?? 'N/D' }}</p>
                        </div>

                        <div class="kpi-card">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Plan</p>
                            <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">{{ $planLabel }}</p>
                        </div>

                        <div class="kpi-card">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Cotisation annuelle</p>
                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">MAD {{ number_format($nextYearlyFee, 2) }}</p>
                        </div>

                        <div class="kpi-card">
                            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Demandes</p>
                            <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $pendingCount }} en attente</p>
                        </div>
                    </div>

                    <div class="surface-card p-6">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Licence</p>
                                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Accès de l’agence</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    Cette licence contrôle l’accès aux réservations, tickets, clients, services et analyses. La facturation reste accessible pour envoyer un renouvellement.
                                </p>
                            </div>

                            <span class="badge-soft {{ $agency->hasActiveLicense() ? '' : 'border-slate-300 bg-slate-100 text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200' }}">
                                {{ $agency->license_state_name }}
                            </span>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="dashboard-soft-panel rounded-xl p-4">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Expiration</p>
                                <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">{{ $agency->license_expires_at?->format('d/m/Y') ?? 'Sans date' }}</p>
                            </div>
                            <div class="dashboard-soft-panel rounded-xl p-4">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Renouvellement</p>
                                <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">MAD {{ number_format($nextYearlyFee, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <a href="{{ route('payment.cashpluss') }}" class="group surface-card p-6 transition hover:-translate-y-0.5 hover:shadow-md">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Cashpluss</p>
                            <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Payer par Cashplus</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                Envoyez votre reçu et soumettez la demande de paiement annuel en quelques étapes.
                            </p>
                            <div class="btn-secondary mt-6">
                                Ouvrir Cashplus
                            </div>
                        </a>

                        <a href="{{ route('payment.virement') }}" class="group surface-card p-6 transition hover:-translate-y-0.5 hover:shadow-md">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Virement</p>
                            <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Payer par virement</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                Utilisez les coordonnées bancaires, effectuez le virement annuel, puis envoyez votre reçu.
                            </p>
                            <div class="btn-secondary mt-6">
                                Ouvrir le virement
                            </div>
                        </a>
                    </div>

                    <div class="surface-card p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Historique</p>
                                <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Demandes récentes</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    Vos dernières demandes de paiement et leur statut de validation.
                                </p>
                            </div>

                            <a href="{{ route('payments.index') }}" class="btn-secondary">
                                Voir les paiements
                            </a>
                        </div>

                        <div class="mt-6 space-y-3">
                            @forelse ($recentPayments as $payment)
                                <div class="dashboard-soft-panel rounded-xl p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white">
                                                MAD {{ number_format($payment->amount, 2) }}
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                                {{ ucfirst($payment->payment_method) }} - {{ $payment->created_at?->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <span class="badge-soft">
                                            {{ $paymentStatusLabel($payment->status) }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 dark:text-slate-400">Aucune demande de paiement pour le moment.</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </x-settings.layout>
    </section>
</x-layouts::app>
