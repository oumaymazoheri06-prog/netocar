<x-layouts::app :title="__('Add Agency')">
    @php
        $plans = config('netocar.plans');
        $defaultLicenseDate = now()->addYearNoOverflow()->toDateString();
    @endphp

    <div class="p-6 space-y-6 max-w-4xl mx-auto">
        <div class="relative overflow-hidden rounded-[2rem] border border-indigo-100 bg-gradient-to-br from-white via-indigo-50 to-cyan-50 p-8 shadow-2xl shadow-slate-200/60 dark:border-zinc-700 dark:from-zinc-900 dark:via-zinc-800 dark:to-slate-900 dark:shadow-black/20">
            <div class="absolute -right-10 top-0 h-40 w-40 rounded-full bg-cyan-300/30 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-40 w-40 rounded-full bg-indigo-300/20 blur-3xl"></div>
            <div class="relative">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-indigo-600 dark:text-cyan-200/80">Plateforme</p>
                <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Ajouter une agence</h1>
                <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-300">
                    Créez une agence et choisissez son plan.
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

        <form action="{{ route('agencies.store') }}" method="POST" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800 space-y-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="name">Nom</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="address">Adresse</label>
                <input type="text" name="address" id="address" value="{{ old('address') }}" required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="phone_number">Téléphone</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                </div>
                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="package">Plan</label>
                    <select name="package" id="package"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                        @foreach ($plans as $key => $plan)
                            <option value="{{ $key }}" {{ old('package') === $key ? 'selected' : '' }}>
                                {{ $plan['label'] }} - MAD {{ number_format($plan['price_yearly_mad']) }}/an
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Les prix suivent la grille de plans configurée pour le marché marocain.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-950">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Manager principal</p>
                <div class="mt-5 grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="manager_name">Nom du manager</label>
                        <input type="text" name="manager_name" id="manager_name" value="{{ old('manager_name') }}" required class="input-modern">
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="manager_email">Email de connexion</label>
                        <input type="email" name="manager_email" id="manager_email" value="{{ old('manager_email') }}" required class="input-modern">
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="manager_password">Mot de passe</label>
                        <input type="password" name="manager_password" id="manager_password" required class="input-modern">
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="manager_password_confirmation">Confirmation</label>
                        <input type="password" name="manager_password_confirmation" id="manager_password_confirmation" required class="input-modern">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-950">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Licence</p>
                <h2 class="mt-2 text-lg font-black text-slate-900 dark:text-white">Activation de l’agence</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    La date d’expiration et le statut contrôlent l’accès aux pages opérationnelles.
                </p>

                <div class="mt-5 grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="license_status">Statut</label>
                        <select name="license_status" id="license_status"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                            <option value="active" {{ old('license_status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('license_status') === 'suspended' ? 'selected' : '' }}>Suspendue</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="license_expires_at">Expiration</label>
                        <input type="date" name="license_expires_at" id="license_expires_at" value="{{ old('license_expires_at', $defaultLicenseDate) }}" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('agencies.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-900 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Annuler
                </a>
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                    Ajouter l’agence
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
