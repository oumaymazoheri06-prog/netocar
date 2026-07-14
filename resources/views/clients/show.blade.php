<x-layouts::app :title="__('Client Details')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Base clients</p>
            <h1 class="mt-3 text-3xl font-black">Détails du client</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Consultez le profil et les coordonnées du client.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Nom</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $client->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Branche</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $client->branch?->name ?? 'Toute l’agence' }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Email</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $client->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Téléphone</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $client->phone }}</p>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('clients.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Retour
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>
