<x-layouts::app :title="__('Show Employee')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Équipe</p>
            <h1 class="mt-3 text-3xl font-black">Détails de l’employé</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Consultez le profil et les informations de l’employé.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Nom</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $employees->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Email</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $employees->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Téléphone</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $employees->phone_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Poste</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $employees->job_title }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Branche</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $employees->branch?->name ?? 'Toute l’agence' }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Salaire</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $employees->salary }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Compte de connexion</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-white">
                        {{ $employees->user ? 'Compte personnel actif' : 'Profil seulement' }}
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('employees.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Retour
                </a>
            </div>
        </div>
    </div>
</x-layouts::app>
