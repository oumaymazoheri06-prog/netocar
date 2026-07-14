<x-layouts::app :title="__('Agency Details')">
    <div class="p-6 space-y-6 max-w-5xl mx-auto">
        <div class="relative overflow-hidden rounded-[2rem] border border-indigo-100 bg-gradient-to-br from-white via-indigo-50 to-cyan-50 p-8 shadow-2xl shadow-slate-200/60 dark:border-zinc-700 dark:from-zinc-900 dark:via-zinc-800 dark:to-slate-900 dark:shadow-black/20">
            <div class="absolute -right-10 top-0 h-40 w-40 rounded-full bg-cyan-300/30 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-40 w-40 rounded-full bg-indigo-300/20 blur-3xl"></div>
            <div class="relative">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-indigo-600 dark:text-cyan-200/80">Plateforme</p>
                <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">Détails de l’agence</h1>
                <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-300">
                    Consultez le profil de l’agence et son plan.
                </p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-sm text-slate-500 dark:text-slate-400">ID agence</p>
                <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $agency->id }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-sm text-slate-500 dark:text-slate-400">Plan</p>
                <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $agency->plan_name }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-sm text-slate-500 dark:text-slate-400">Contact</p>
                <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Profil agence</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <p class="text-sm text-slate-500 dark:text-slate-400">Licence</p>
                <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">{{ $agency->license_state_name }}</p>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800">
            <dl class="grid gap-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Nom</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $agency->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Email</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $agency->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Adresse</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $agency->address }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Téléphone</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $agency->phone_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Plan</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                            {{ $agency->plan_name }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Créée le</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $agency->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Statut licence</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $agency->license_state_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Expiration</dt>
                    <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $agency->license_expires_at?->format('d/m/Y') ?? 'Sans date' }}</dd>
                </div>
            </dl>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ url('/agencies/' . $agency->id . '/edit') }}" class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                    Modifier l’agence
                </a>

                <form action="{{ url('/agencies/' . $agency->id) }}" method="POST" onsubmit="return confirm('Supprimer cette agence ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-rose-200 bg-white px-5 py-3 font-semibold text-rose-700 transition hover:bg-rose-50 dark:border-rose-900/60 dark:bg-zinc-900 dark:text-rose-300 dark:hover:bg-rose-950/40">
                        Supprimer l’agence
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
