<x-layouts::app :title="__('Edit Service')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Offre</p>
            <h1 class="mt-3 text-3xl font-black">Modifier le service</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Modifiez les informations du service et sa photo.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <form action="{{ route('services.update', $service) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="name">Nom</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $service->name) }}" required
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="description">Description</label>
                    <textarea name="description" id="description" rows="4" required
                              class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">{{ old('description', $service->description) }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="price">Prix</label>
                    <input type="number" name="price" id="price" min="0" step="0.01" value="{{ old('price', $service->price) }}" required
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="duration_minutes">Durée estimée (minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" min="15" max="480" step="5" value="{{ old('duration_minutes', $service->duration_minutes) }}" required
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                @include('partials.branch-select', ['branches' => $branches, 'selected' => $service->branch_id])

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="photo">Photo</label>
                    <input type="file" name="photo" id="photo" accept="image/*"
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">

                    @if ($service->photo)
                        <div class="mt-3">
                            <p class="mb-2 text-sm text-slate-500 dark:text-slate-400">Photo actuelle</p>
                            <img src="{{ asset('storage/' . $service->photo) }}" alt="{{ $service->name }}"
                                 class="h-36 w-36 rounded-lg border border-slate-200 object-cover dark:border-zinc-600">
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('services.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
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
