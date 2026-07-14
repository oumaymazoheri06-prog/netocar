<x-layouts::app :title="__('Payments')">
    @php
        $canManagePayments = auth()->user()?->role === 'admin';
        $canSubmitPayment = auth()->user()?->role === 'manager' && auth()->user()?->agency;
    @endphp

    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Facturation</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Paiements</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">
                Consultez les paiements annuels des agences, les reçus, les modes de paiement, les montants et les statuts.
            </p>
            @if ($canSubmitPayment)
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('payment.virement') }}" class="btn-primary">
                        Nouveau virement
                    </a>
                    <a href="{{ route('payment.cashpluss') }}" class="btn-secondary">
                        Nouveau Cashplus
                    </a>
                </div>
            @endif
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
                {{ session('success') }}
            </div>
        @endif

        @include('partials.report-toolbar', [
            'resource' => 'payments',
            'heading' => 'Exporter les paiements',
            'description' => 'Téléchargez le registre des paiements en PDF ou imprimez un rapport par mois.'
        ])

        <div class="surface-card-elevated overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Agence</th>
                            <th>Plan</th>
                            <th>Montant</th>
                            <th>Reçu</th>
                            <th>Mode</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="text-slate-900 dark:text-white">
                                    {{ $payment->agency?->name ?? 'N/D' }}
                                </td>
                                <td>
                                    {{ $payment->agency?->plan_name ?? '-' }}
                                </td>
                                <td class="font-semibold text-slate-900 dark:text-white">
                                    MAD {{ number_format($payment->amount, 2) }}
                                </td>
                                <td>
                                    @if ($payment->receipt_photo)
                                        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="font-semibold text-blue-700 hover:text-blue-600 dark:text-blue-300 dark:hover:text-blue-200">
                                            Voir le reçu
                                        </a>
                                    @else
                                        <span class="text-slate-400">Aucun reçu</span>
                                    @endif
                                </td>
                                <td>
                                    {{ ucfirst($payment->payment_method) }}
                                </td>
                                <td>
                                    <span class="badge-soft">
                                        {{ ['pending' => 'En attente', 'completed' => 'Payé', 'failed' => 'Échoué'][$payment->status] ?? $payment->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('payments.show', $payment) }}" class="btn-ghost px-3 py-1.5">
                                            Voir
                                        </a>
                                        @if ($canManagePayments)
                                            <a href="{{ route('payments.edit', $payment) }}" class="btn-secondary px-3 py-1.5">
                                                Modifier
                                            </a>
                                            <form action="{{ route('payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Supprimer ce paiement ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost px-3 py-1.5">
                                                    Supprimer
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Aucun paiement pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
