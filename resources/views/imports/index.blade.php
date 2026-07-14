<x-layouts::app :title="__('Import Data')">
    @php
        $columnLabel = fn (string $column) => match ($column) {
            'name' => 'Nom',
            'email' => 'E-mail',
            'phone', 'phone_number' => 'Téléphone',
            'job_title' => 'Poste',
            'salary' => 'Salaire',
            'description' => 'Description',
            'price' => 'Prix',
            'branch_code' => 'Code branche',
            default => \Illuminate\Support\Str::headline($column),
        };
    @endphp

    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Import de données</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Centre d’import</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Importez vos clients, employés et services existants depuis des fichiers CSV.
            </p>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-blue-800 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 shadow-sm dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-3">
            @foreach ($resources as $key => $resource)
                <div class="surface-card-elevated p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">{{ $resource['label'] }}</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Importer {{ $resource['label'] }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ $resource['description'] }}
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('imports.template', $key) }}" class="btn-secondary">
                            Télécharger le modèle
                        </a>
                    </div>

                    <form action="{{ route('imports.preview', $key) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="{{ $key }}_file" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">Fichier CSV</label>
                            <input
                                type="file"
                                name="file"
                                id="{{ $key }}_file"
                                accept=".csv,text/csv,text/plain"
                                required
                                class="input-modern"
                            >
                        </div>

                        <button type="submit" class="btn-primary">
                            Prévisualiser l’import
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        @if ($preview)
            <div class="surface-card-elevated overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Prévisualisation</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Aperçu de l’import {{ $preview['label'] }}</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $preview['filename'] }}</p>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-5">
                    <div class="kpi-card">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Lignes</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $preview['summary']['total'] }}</p>
                    </div>
                    <div class="kpi-card">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Valides</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $preview['summary']['valid'] }}</p>
                    </div>
                    <div class="kpi-card">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Invalides</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $preview['summary']['invalid'] }}</p>
                    </div>
                    <div class="kpi-card">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">À créer</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $preview['summary']['create'] }}</p>
                    </div>
                    <div class="kpi-card">
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">À mettre à jour</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $preview['summary']['update'] }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Ligne</th>
                                <th>Statut</th>
                                <th>Action</th>
                                @foreach ($preview['columns'] as $column)
                                    <th>{{ $columnLabel($column) }}</th>
                                @endforeach
                                <th>Problèmes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($preview['rows'] as $row)
                                <tr>
                                    <td class="font-semibold text-slate-900 dark:text-white">{{ $row['row_number'] }}</td>
                                    <td>
                                        <span class="badge-soft">{{ $row['valid'] ? 'Valide' : 'Invalide' }}</span>
                                    </td>
                                    <td>{{ ['create' => 'Créer', 'update' => 'Mettre à jour', 'skip' => 'Ignorer'][$row['action']] ?? $row['action'] }}</td>
                                    @foreach ($preview['columns'] as $column)
                                        <td>{{ $row['data'][$column] ?? 'N/D' }}</td>
                                    @endforeach
                                    <td>
                                        @if ($row['errors'])
                                            <span class="text-sm text-rose-600 dark:text-rose-300">{{ implode(' ', $row['errors']) }}</span>
                                        @else
                                            <span class="text-sm text-slate-500 dark:text-slate-400">Prêt</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($preview['columns']) + 4 }}" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Aucune ligne trouvée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-6 py-5 dark:border-slate-800">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Les lignes invalides sont ignorées. Les lignes valides seront créées ou mises à jour après confirmation.
                    </p>

                    <form action="{{ route('imports.store', $preview['resource']) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary" @disabled($preview['summary']['valid'] === 0)>
                            Importer les lignes valides
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
