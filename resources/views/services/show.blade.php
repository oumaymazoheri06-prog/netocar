<x-layouts::app :title="__('Service Details')">
    <div class="p-6 max-w-4xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Offre</p>
            <h1 class="mt-3 text-3xl font-black">Détails du service</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Consultez les informations du service et sa photo.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <p class="text-slate-500 dark:text-slate-300">ID</p>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ $service->id }}</p>
                </div>
                <div>
                    <p class="text-slate-500 dark:text-slate-300">Branche</p>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ $service->branch?->name ?? 'Toute l’agence' }}</p>
                </div>
                <div>
                    <p class="text-slate-500 dark:text-slate-300">Nom</p>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ $service->name }}</p>
                </div>
                <div>
                    <p class="text-slate-500 dark:text-slate-300">Prix</p>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ $service->price }}</p>
                </div>
            </div>

            <div class="mt-6">
                <p class="mb-2 text-slate-500 dark:text-slate-300">Description</p>
                <p class="leading-relaxed text-slate-900 dark:text-white">{{ $service->description }}</p>
            </div>

            <div class="mt-6">
                <p class="mb-2 text-slate-500 dark:text-slate-300">Photo</p>
                @if ($service->photo)
                    <img src="{{ asset('storage/' . $service->photo) }}" alt="{{ $service->name }}"
                         class="h-56 w-full max-w-md rounded-xl border border-slate-200 object-cover dark:border-zinc-600">
                @else
                    <p class="text-slate-900 dark:text-white">Aucune photo importée.</p>
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('services.edit', $service) }}" class="rounded-lg bg-amber-500 px-4 py-2.5 font-semibold text-white hover:bg-amber-600">
                    Modifier
                </a>
                <a href="{{ route('services.index') }}" class="ml-3 rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Retour
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>
