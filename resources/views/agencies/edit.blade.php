<x-layouts::app :title="__('Edit Agency')">
    @php($plans = config('netocar.plans'))

    <div class="p-6 space-y-6 max-w-4xl mx-auto">
        <div class="relative overflow-hidden rounded-[2rem] border border-indigo-100 bg-gradient-to-br from-white via-indigo-50 to-cyan-50 p-8 shadow-2xl shadow-slate-200/60 dark:border-zinc-700 dark:from-zinc-900 dark:via-zinc-800 dark:to-slate-900 dark:shadow-black/20">
            <div class="absolute -right-10 top-0 h-40 w-40 rounded-full bg-cyan-300/30 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-40 w-40 rounded-full bg-indigo-300/20 blur-3xl"></div>
            <div class="relative">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-indigo-600 dark:text-cyan-200/80">Plateforme</p>
                <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Modifier l’agence</h1>
                <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-300">
                    Modifiez le profil de l’agence et son plan.
                </p>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-200">
                <p class="font-semibold">Corrigez les erreurs suivantes :</p>
                <ul class="mt-3 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ url('/agencies/' . $agency->id) }}" method="POST" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="name">Nom</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $agency->name) }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $agency->email) }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="address">Adresse</label>
                <input type="text" name="address" id="address" value="{{ old('address', $agency->address) }}" required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="phone_number">Téléphone</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $agency->phone_number) }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="package">Plan</label>
                    <select name="package" id="package"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                        @foreach ($plans as $key => $plan)
                            <option value="{{ $key }}" {{ old('package', $agency->package) === $key ? 'selected' : '' }}>
                                {{ $plan['label'] }} - MAD {{ number_format($plan['price_yearly_mad']) }}/an
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Changer le plan met à jour les futurs montants de paiement et les limites appliquées.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-950">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Licence</p>
                <h2 class="mt-2 text-lg font-black text-slate-900 dark:text-white">Activation et renouvellement</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Si la licence est suspendue ou expirée, l’agence garde la facturation mais ne peut plus utiliser les pages opérationnelles.
                </p>

                <div class="mt-5 grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="license_status">Statut</label>
                        <select name="license_status" id="license_status"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                            <option value="active" {{ old('license_status', $agency->license_status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('license_status', $agency->license_status) === 'suspended' ? 'selected' : '' }}>Suspendue</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="license_expires_at">Expiration</label>
                        <input type="date" name="license_expires_at" id="license_expires_at" value="{{ old('license_expires_at', $agency->license_expires_at?->toDateString() ?? now()->addYearNoOverflow()->toDateString()) }}" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('agencies.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-900 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Annuler
                </a>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
