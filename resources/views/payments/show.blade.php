<x-layouts::app :title="__('Payment Details')">
    <div class="page-shell max-w-3xl space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Facturation</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Détails du paiement</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Consultez le paiement complet de cette agence.
            </p>
        </div>

        <div class="surface-card-elevated p-6">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Agence</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $payment->agency?->name ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Plan</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $payment->agency?->plan_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Montant annuel</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">MAD {{ number_format($payment->amount, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Mode</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ ucfirst($payment->payment_method) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Reçu</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">
                        @if ($payment->receipt_photo)
                            <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="text-blue-700 hover:underline dark:text-blue-300">
                                Voir le reçu
                            </a>
                        @else
                            Aucun reçu
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Statut</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ ['pending' => 'En attente', 'completed' => 'Payé', 'failed' => 'Échoué'][$payment->status] ?? $payment->status }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Créé le</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $payment->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex gap-3">
                @if (auth()->user()?->role === 'admin')
                    <a href="{{ route('payments.edit', $payment) }}" class="btn-primary">
                        Modifier
                    </a>
                @endif
                <a href="{{ route('payments.index') }}" class="btn-secondary">
                    Retour
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>
