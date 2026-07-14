<x-layouts::app :title="__('Edit Client')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Base clients</p>
            <h1 class="mt-3 text-3xl font-black">Modifier le client</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Mettez à jour les informations du client.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <form action="{{ route('clients.update', $client) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Nom</label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}"
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Email <span class="font-normal text-slate-400">(facultatif)</span></label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                @include('partials.branch-select', ['branches' => $branches, 'selected' => $client->branch_id, 'label' => 'Branche préférée', 'emptyLabel' => 'Aucune préférence'])

                <div class="flex justify-end gap-4">
                    <a href="{{ route('clients.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                        Annuler
                    </a>
                    <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 font-semibold text-white hover:bg-amber-600">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
