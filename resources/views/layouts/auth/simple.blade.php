<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="auth-shell min-h-screen antialiased text-slate-900 transition-colors dark:text-slate-100">
        <div class="auth-page relative min-h-screen overflow-hidden">
            <div class="relative z-10 grid min-h-screen lg:grid-cols-[minmax(0,0.92fr)_minmax(420px,1fr)]">
                <aside class="auth-showcase relative hidden min-h-screen overflow-hidden border-r border-white/10 bg-slate-950 px-10 py-8 text-white lg:flex lg:flex-col lg:justify-between xl:px-14">
                    <div class="auth-showcase-grid" aria-hidden="true"></div>

                    <a href="{{ route('home') }}" class="relative z-10 inline-flex w-fit items-center gap-3">
                        <span class="auth-brand-mark auth-brand-mark-dark">
                            N
                        </span>
                        <span>
                            <span class="block text-sm font-black uppercase text-white">NetoCar</span>
                            <span class="mt-1 block text-xs font-medium text-slate-400">Gestion SaaS lavage auto</span>
                        </span>
                    </a>

                    <div class="relative z-10 max-w-lg">
                        <p class="text-sm font-bold uppercase text-teal-300">Espace securise</p>
                        <h1 class="mt-5 text-4xl font-black leading-[1.04] xl:text-5xl">
                            Une facon plus claire de piloter votre agence.
                        </h1>
                        <p class="mt-6 max-w-md text-base leading-7 text-slate-300">
                            Reservations, tickets, equipe et paiements restent connectes dans un meme espace de travail.
                        </p>
                    </div>

                    <div class="relative z-10 grid gap-4">
                        <div class="auth-insight-card">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase text-slate-400">Aujourd'hui</p>
                                    <p class="mt-2 text-2xl font-black text-white">24 reservations</p>
                                </div>
                                <span class="rounded-full border border-teal-300/20 bg-teal-300/10 px-3 py-1 text-xs font-bold text-teal-200">Live</span>
                            </div>
                            <div class="mt-5 grid grid-cols-3 gap-2">
                                <span class="auth-mini-stat">
                                    <span>8</span>
                                    <small>attente</small>
                                </span>
                                <span class="auth-mini-stat">
                                    <span>11</span>
                                    <small>cours</small>
                                </span>
                                <span class="auth-mini-stat">
                                    <span>5</span>
                                    <small>termine</small>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="auth-insight-card auth-insight-card-soft">
                                <p class="text-sm text-slate-400">Equipe</p>
                                <p class="mt-2 text-lg font-black text-white">Planning partage</p>
                            </div>
                            <div class="auth-insight-card auth-insight-card-soft">
                                <p class="text-sm text-slate-400">Paiements</p>
                                <p class="mt-2 text-lg font-black text-white">Suivi clair</p>
                            </div>
                        </div>
                    </div>
                </aside>

                <main class="relative flex min-h-screen flex-col">
                    <header class="auth-topbar flex items-center justify-between px-5 py-4 sm:px-8">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-3 lg:hidden">
                            <span class="auth-brand-mark">N</span>
                            <span>
                                <span class="block text-sm font-black uppercase text-slate-950 dark:text-white">NetoCar</span>
                                <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Gestion lavage auto</span>
                            </span>
                        </a>

                        <a href="{{ route('home') }}" class="hidden text-sm font-bold text-slate-500 transition hover:text-slate-950 dark:text-slate-400 dark:hover:text-white lg:inline-flex">
                            Retour au site
                        </a>

                        <button
                            type="button"
                            data-theme-toggle
                            class="auth-theme-toggle ml-auto"
                            aria-label="Changer le theme"
                        >
                            <svg data-theme-dark-icon class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3a6.5 6.5 0 0 0 9.8 9.8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg data-theme-light-icon class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z" stroke="currentColor" stroke-width="2" />
                                <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            <span>Theme</span>
                        </button>
                    </header>

                    <section class="flex flex-1 items-center justify-center px-4 py-8 sm:px-6 lg:px-10">
                        <div class="auth-card w-full max-w-md">
                            {{ $slot }}
                        </div>
                    </section>
                </main>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
