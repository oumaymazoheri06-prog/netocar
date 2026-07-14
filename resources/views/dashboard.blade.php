<x-layouts::app :title="__('Dashboard')">
    <div class="page-shell space-y-6">
        <div class="page-hero">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <video
                        src="{{ asset('images/LOGO.mp4') }}"
                        class="brand-logo-large"
                        autoplay
                        muted
                        loop
                        playsinline
                        preload="metadata"
                        aria-hidden="true"
                    ></video>
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-700 dark:text-blue-300">Tableau de bord admin</p>
                        <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white sm:text-4xl">
                            Vue d’ensemble de la plateforme
                        </h1>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">
                            Suivez les agences, les managers, les plans et l’activité récente depuis un espace clair.
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:w-[30rem]">
                    <div class="dashboard-soft-panel rounded-xl px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Agences</p>
                        <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $agenciesCount }}</p>
                    </div>
                    <div class="dashboard-soft-panel rounded-xl px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Managers</p>
                        <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $managersCount }}</p>
                    </div>
                    <div class="dashboard-soft-panel rounded-xl px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Utilisateurs</p>
                        <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $usersCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="kpi-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Agences</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $agenciesCount }}</p>
                    </div>
                    <span class="kpi-icon text-slate-600 dark:text-slate-300">
                        <flux:icon.building-office class="size-5" />
                    </span>
                </div>
            </div>

            <div class="kpi-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Managers</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $managersCount }}</p>
                    </div>
                    <span class="kpi-icon text-slate-600 dark:text-slate-300">
                        <flux:icon.users class="size-5" />
                    </span>
                </div>
            </div>

            <div class="kpi-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Utilisateurs</p>
                        <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $usersCount }}</p>
                    </div>
                    <span class="kpi-icon text-slate-700 dark:text-slate-300">
                        <flux:icon.user class="size-5" />
                    </span>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-1 surface-card-elevated p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Aperçu plateforme</p>
                        <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Agences par plan</h2>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="dashboard-soft-panel rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Basique</span>
                            <span class="badge-soft">{{ $basicAgenciesCount }}</span>
                        </div>
                    </div>

                    <div class="dashboard-soft-panel rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Standard</span>
                            <span class="badge-soft">{{ $standardAgenciesCount }}</span>
                        </div>
                    </div>

                    <div class="dashboard-soft-panel rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Premium</span>
                            <span class="badge-soft">{{ $premiumAgenciesCount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2 surface-card-elevated overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Agences récentes</p>
                    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Dernières agences ajoutées</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Agence</th>
                                <th>Responsable</th>
                                <th>Plan</th>
                                <th>Créée le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAgencies as $agency)
                                <tr>
                                    <td class="text-slate-900 dark:text-white">
                                        {{ $agency->name }}
                                    </td>
                                    <td>
                                        {{ $agency->user?->name ?? 'N/D' }}
                                    </td>
                                    <td>
                                        <span class="badge-soft">
                                            {{ $agency->plan_name }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $agency->created_at?->format('d/m/Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                        Aucune agence trouvée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="surface-card-elevated overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Managers récents</p>
                <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Derniers comptes managers</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Manager</th>
                            <th>Email</th>
                            <th>Agence</th>
                            <th>Inscrit le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentManagers as $manager)
                            <tr>
                                <td class="text-slate-900 dark:text-white">
                                    {{ $manager->name }}
                                </td>
                                <td>
                                    {{ $manager->email }}
                                </td>
                                <td>
                                    {{ $manager->agency?->name ?? 'Aucune agence' }}
                                </td>
                                <td>
                                    {{ $manager->created_at?->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                    Aucun compte manager pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
