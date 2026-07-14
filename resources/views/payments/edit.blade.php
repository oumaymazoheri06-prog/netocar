<x-layouts::app :title="__('Edit Payment')">
    <div class="page-shell max-w-3xl space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Facturation</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Modifier le paiement</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Mettez à jour le statut de ce paiement.
            </p>
        </div>

        <div class="surface-card-elevated p-6">
            <div class="mb-6 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <p><span class="font-semibold text-slate-900 dark:text-white">Agence :</span> {{ $payment->agency?->name ?? 'N/D' }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-white">Plan :</span> {{ $payment->agency?->plan_name ?? '-' }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-white">Montant :</span> MAD {{ number_format($payment->amount, 2) }}</p>
                <p><span class="font-semibold text-slate-900 dark:text-white">Mode :</span> {{ ucfirst($payment->payment_method) }}</p>
            </div>

            <form action="{{ route('payments.update', $payment) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="status" class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Statut</label>
                    <select id="status" name="status" class="input-modern">
                        <option value="pending" @selected(old('status', $payment->status) === 'pending')>En attente</option>
                        <option value="completed" @selected(old('status', $payment->status) === 'completed')>Payé</option>
                        <option value="failed" @selected(old('status', $payment->status) === 'failed')>Échoué</option>
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('payments.index') }}" class="btn-secondary">
                        Retour
                    </a>
                    <button type="submit" class="btn-primary">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
