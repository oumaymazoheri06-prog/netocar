<x-layouts::app :title="__('Cashpluss Payment')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Mode de paiement</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Payer via Cashplus</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Envoyez votre demande de paiement annuel. Le montant est calculé automatiquement à partir du plan de votre agence.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
            <div class="surface-card p-6">
                <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Détails de la demande</p>
                    <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Importez votre reçu</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Envoyez la demande pour enregistrer le paiement. Le reçu restera attaché au dossier pour vérification.
                    </p>
                </div>

                <form action="{{ route('payment.cashpluss.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6">
                    @csrf
                    <input type="hidden" name="payment_method" value="cashpluss">

                    <div>
                        <label for="receipt_photo" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Photo du reçu</label>
                        <input
                            type="file"
                            id="receipt_photo"
                            name="receipt_photo"
                            accept="image/*"
                            required
                            class="input-modern file:mr-4 file:rounded-lg file:border-0 file:bg-blue-700 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-800 dark:file:bg-blue-600 dark:file:text-white dark:hover:file:bg-blue-500">
                        @error('receipt_photo')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('agency-billing.edit') }}" class="btn-secondary">
                            Retour à la facturation
                        </a>
                        <button type="submit" class="btn-primary">
                            Envoyer le paiement
                        </button>
                    </div>
                </form>
            </div>

            <aside class="space-y-4">
                <div class="surface-card p-6">
                    <div class="flex items-start gap-4">
                        <span class="kpi-icon text-blue-700 dark:text-blue-300">
                            <flux:icon.credit-card class="size-5" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Avant l’envoi</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                Vérifiez que l’image du reçu est claire, lisible et montre la référence de paiement si elle existe.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="surface-card p-6">
                    <div class="space-y-3">
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Agence</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $agency?->name ?? 'N/D' }}</p>
                        </div>
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Mode</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">Cashpluss</p>
                        </div>
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Plan</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $agency?->plan_name ?? '-' }}</p>
                        </div>
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Montant</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">MAD {{ number_format($agency?->plan_amount ?? 0, 2) }}</p>
                        </div>
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Statut</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">En attente de vérification</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layouts::app>
