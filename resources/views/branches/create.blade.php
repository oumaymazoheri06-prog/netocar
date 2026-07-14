<x-layouts::app :title="__('Add Branch')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Sites</p>
            <h1 class="mt-3 text-3xl font-black">Ajouter une branche</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Créez un site rattaché à votre agence.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <form action="{{ route('branches.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="name">Nom</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input-modern">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="code">Code</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" class="input-modern">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="address">Adresse</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}" class="input-modern">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="phone_number">Téléphone</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="input-modern">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="opening_time">Ouverture</label>
                        <input type="time" name="opening_time" id="opening_time" value="{{ old('opening_time', '08:00') }}" required class="input-modern">
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="closing_time">Fermeture</label>
                        <input type="time" name="closing_time" id="closing_time" value="{{ old('closing_time', '18:00') }}" required class="input-modern">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="simultaneous_capacity">Capacité simultanée</label>
                    <input type="number" name="simultaneous_capacity" id="simultaneous_capacity" value="{{ old('simultaneous_capacity', 1) }}" min="1" max="100" required class="input-modern">
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Nombre maximal de véhicules traités sur le même créneau.</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-900/60">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-3 font-semibold text-slate-800 dark:text-slate-200">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_active', '1') === '1')>
                        Branche active
                    </label>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('branches.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                        Annuler
                    </a>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-500">
                        Ajouter la branche
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
