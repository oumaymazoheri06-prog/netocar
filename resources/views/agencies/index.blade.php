<x-layouts::app :title="__('Agencies')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Plateforme</p>
            <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Agences</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Gérez les comptes agences, leurs plans et leurs informations depuis un seul endroit.
            </p>
            <div class="mt-6">
                <a href="{{ route('agencies.create') }}" class="btn-primary">
                    Ajouter une agence
                </a>
            </div>
        </div>

        @php
            $activeLicenseCount = $agencies->filter(fn ($agency) => $agency->hasActiveLicense())->count();
            $suspendedLicenseCount = $agencies->where('license_status', 'suspended')->count();
            $expiredLicenseCount = $agencies->filter(fn ($agency) => $agency->license_status === 'active' && $agency->license_expires_at && $agency->license_expires_at->copy()->endOfDay()->isPast())->count();
        @endphp

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="kpi-card">
                <p class="text-sm text-slate-500 dark:text-slate-400">Total agences</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $agencies->count() }}</p>
            </div>
            <div class="kpi-card">
                <p class="text-sm text-slate-500 dark:text-slate-400">Licences actives</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $activeLicenseCount }}</p>
            </div>
            <div class="kpi-card">
                <p class="text-sm text-slate-500 dark:text-slate-400">Suspendues</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $suspendedLicenseCount }}</p>
            </div>
            <div class="kpi-card">
                <p class="text-sm text-slate-500 dark:text-slate-400">Expirées</p>
                <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $expiredLicenseCount }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
                {{ session('success') }}
            </div>
        @endif

        @include('partials.report-toolbar', [
            'resource' => 'agencies',
            'heading' => 'Exporter les agences',
            'description' => 'Téléchargez l’annuaire des agences en PDF ou imprimez un rapport par mois.'
        ])

        <div class="surface-card-elevated overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Annuaire des agences</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Vue de toutes les agences enregistrées sur la plateforme.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Adresse</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Licence</th>
                            <th>Expiration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agencies as $agency)
                            <tr>
                                <td class="text-slate-900 dark:text-white">{{ $agency->id }}</td>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $agency->name }}</td>
                                <td>{{ $agency->address }}</td>
                                <td>{{ $agency->phone_number }}</td>
                                <td>{{ $agency->email }}</td>
                                <td>
                                    <span class="badge-soft">
                                        {{ $agency->plan_name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-soft {{ $agency->hasActiveLicense() ? '' : 'border-slate-300 bg-slate-100 text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200' }}">
                                        {{ $agency->license_state_name }}
                                    </span>
                                </td>
                                <td>{{ $agency->license_expires_at?->format('d/m/Y') ?? 'Sans date' }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ url('/agencies/' . $agency->id) }}" class="btn-ghost px-3 py-1.5">
                                            Voir
                                        </a>
                                        <a href="{{ url('/agencies/' . $agency->id . '/edit') }}" class="btn-secondary px-3 py-1.5">
                                            Modifier
                                        </a>
                                        <button
                                            type="button"
                                            onclick="openDeleteModal('{{ url('/agencies/' . $agency->id) }}')"
                                            class="btn-ghost px-3 py-1.5">
                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Aucune agence pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 hidden flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-lg dark:bg-slate-900">
            <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Supprimer l’agence</h2>
            <p class="mb-6 text-slate-700 dark:text-slate-300">Voulez-vous vraiment supprimer cette agence ?</p>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="btn-secondary">
                    Annuler
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-primary bg-slate-900 hover:bg-black dark:bg-slate-100 dark:text-slate-950 dark:hover:bg-white">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(action) {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteForm').action = action;
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</x-layouts::app>
