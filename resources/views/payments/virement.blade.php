<x-layouts::app :title="__('Payment via Virement')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Mode de paiement</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Payer par virement</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Envoyez votre demande de paiement annuel par virement. Le montant est calculé automatiquement à partir du plan de votre agence.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <div class="space-y-6">
                <div class="surface-card p-6">
                    <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Résumé du paiement</p>
                        <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Détails de l’agence</h2>
                    </div>
                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Agence</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $agency?->name ?? 'N/D' }}</p>
                        </div>
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Plan</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $agency?->plan_name ?? '-' }}</p>
                        </div>
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Montant annuel</p>
                            <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">MAD {{ number_format($agency?->plan_amount ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="surface-card p-6">
                    <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Coordonnées bancaires</p>
                        <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Informations de virement</h2>
                    </div>
                    <ul class="mt-6 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                        <li><span class="font-semibold text-slate-900 dark:text-white">Banque :</span> XYZ Bank</li>
                        <li><span class="font-semibold text-slate-900 dark:text-white">Titulaire :</span> NetoCar Agency</li>
                        <li><span class="font-semibold text-slate-900 dark:text-white">IBAN:</span> XX00 0000 0000 0000 0000 00</li>
                        <li><span class="font-semibold text-slate-900 dark:text-white">BIC/SWIFT:</span> XYZBIC12</li>
                    </ul>
                </div>
            </div>

            <div class="space-y-6">
                <div class="surface-card p-6">
                    <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Instructions</p>
                        <h2 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">Étapes de paiement</h2>
                    </div>
                    <ol class="mt-6 list-decimal space-y-3 pl-5 text-sm text-slate-600 dark:text-slate-300">
                        <li>Effectuez le virement vers le compte indiqué.</li>
                        <li>Vérifiez que le montant correspond au plan de votre agence.</li>
                        <li>Importez une photo claire du reçu.</li>
                        <li>Envoyez la demande pour enregistrer le paiement.</li>
                        <li>Le paiement pourra ensuite être vérifié et marqué comme payé.</li>
                    </ol>
                </div>

                <div class="surface-card p-6">
                    <form action="{{ route('payment.virement.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="payment_method" value="virement">

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

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('agency-billing.edit') }}" class="btn-secondary">
                                Retour à la facturation
                            </a>
                            <button type="submit" class="btn-primary">
                                Envoyer le paiement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
