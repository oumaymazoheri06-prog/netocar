<x-layouts::app :title="__('Edit Employee')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Équipe</p>
            <h1 class="mt-3 text-3xl font-black">Modifier l’employé</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Mettez à jour les informations de l’employé.
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
            <form action="{{ route('employees.update', $employees) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="name">Nom</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $employees->name) }}" required
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $employees->email) }}" required
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="phone_number">Téléphone</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $employees->phone_number) }}" required
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="job_title">Poste</label>
                    <input type="text" name="job_title" id="job_title" value="{{ old('job_title', $employees->job_title) }}" required
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="salary">Salaire</label>
                    <input type="number" step="0.01" name="salary" id="salary" value="{{ old('salary', $employees->salary) }}" required
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                @include('partials.branch-select', ['branches' => $branches, 'selected' => $employees->branch_id])

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-900/60">
                    @if ($employees->user)
                        <div class="mb-4">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Compte personnel actif</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $employees->user->email }}</p>
                        </div>
                    @else
                        <label class="flex items-center gap-3 font-semibold text-slate-800 dark:text-slate-200">
                            <input type="checkbox" name="create_staff_account" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('create_staff_account') === '1')>
                            Créer un compte de connexion pour cet employé
                        </label>
                    @endif

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="password">
                                {{ $employees->user ? 'Nouveau mot de passe' : 'Mot de passe temporaire' }}
                            </label>
                            <input type="password" name="password" id="password" autocomplete="new-password"
                                   class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="password_confirmation">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password"
                                   class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('employees.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
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
