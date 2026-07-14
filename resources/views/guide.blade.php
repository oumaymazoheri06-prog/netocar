<x-layouts::app :title="__('Guide d’utilisation')">
    @php
        $user = auth()->user();
        $role = $user?->role;
        $isAdmin = $role === 'admin';
        $isStaff = $role === 'staff';

        $roleLabel = match ($role) {
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'staff' => 'Employé',
            default => 'Utilisateur',
        };

        if ($isAdmin) {
            $title = 'Piloter la plateforme NetoCar';
            $subtitle = 'Un guide court pour contrôler les agences, les paiements, les plans et les actions sensibles.';
            $mainAction = ['label' => 'Ouvrir le tableau de bord', 'href' => route('dashboard')];
            $secondaryAction = ['label' => 'Voir les paiements', 'href' => route('payments.index')];

            $quickActions = [
                ['title' => 'Tableau de bord', 'meta' => 'Chaque matin', 'copy' => 'Vérifier les agences, les plans actifs et les signaux qui demandent une action.', 'href' => route('dashboard')],
                ['title' => 'Agences', 'meta' => 'Gestion SaaS', 'copy' => 'Créer, corriger ou suivre les agences et leurs informations principales.', 'href' => route('agencies.index')],
                ['title' => 'Paiements', 'meta' => 'Facturation', 'copy' => 'Contrôler les reçus, valider les paiements et repérer les dossiers en attente.', 'href' => route('payments.index')],
                ['title' => 'Analyses', 'meta' => 'Pilotage', 'copy' => 'Lire les revenus, les agences impayées et les tendances importantes.', 'href' => route('analytics.index')],
                ['title' => 'Journal d’activité', 'meta' => 'Contrôle', 'copy' => 'Savoir qui a modifié quoi, quand, et dans quelle agence.', 'href' => route('activity-logs.index')],
            ];

            $routine = [
                ['title' => 'Contrôler les nouvelles agences', 'copy' => 'Vérifier le nom, le plan, le contact principal et les limites activées.'],
                ['title' => 'Valider les paiements avec preuve', 'copy' => 'Ne changer le statut qu’après lecture du reçu ou du virement.'],
                ['title' => 'Surveiller les impayés', 'copy' => 'Prioriser les agences avec paiement en attente ou échéance dépassée.'],
                ['title' => 'Lire le journal d’activité', 'copy' => 'Repérer les suppressions, changements de statut et modifications sensibles.'],
            ];

            $decisionRows = [
                ['when' => 'Une agence démarre', 'screen' => 'Agences', 'result' => 'Créer le dossier, choisir le plan et vérifier les coordonnées.'],
                ['when' => 'Un reçu arrive', 'screen' => 'Paiements', 'result' => 'Contrôler la preuve puis passer le paiement au bon statut.'],
                ['when' => 'Un changement semble étrange', 'screen' => 'Journal d’activité', 'result' => 'Identifier l’utilisateur, l’agence, l’objet et la date.'],
                ['when' => 'La croissance ralentit', 'screen' => 'Analyses', 'result' => 'Comparer revenus, annulations, services et agences impayées.'],
            ];

            $watchItems = ['Agences non payées', 'Paiements en attente', 'Plans actifs', 'Suppressions et changements sensibles'];
            $checklist = ['Chaque paiement validé a une preuve.', 'Chaque agence a un plan clair.', 'Les changements critiques sont visibles dans le journal.', 'Les comptes admin ne sont pas partagés.'];
            $avoidItems = ['Valider un paiement sans reçu.', 'Créer plusieurs agences pour le même client.', 'Laisser un compte admin utilisé par plusieurs personnes.'];
        } elseif ($isStaff) {
            $title = 'Travailler sur les jobs assignés';
            $subtitle = 'Un guide simple pour voir vos tickets, suivre vos réservations et mettre les statuts à jour au bon moment.';
            $mainAction = ['label' => 'Ouvrir mes tickets', 'href' => route('tickets.index')];
            $secondaryAction = ['label' => 'Voir mes réservations', 'href' => route('reservations.index')];

            $quickActions = [
                ['title' => 'Mes tickets', 'meta' => 'Priorité', 'copy' => 'Voir les jobs assignés, démarrer le travail et le terminer dès que le lavage est fini.', 'href' => route('tickets.index')],
                ['title' => 'Réservations', 'meta' => 'Planning', 'copy' => 'Consulter les prochains créneaux sans modifier les réglages de l’agence.', 'href' => route('reservations.index')],
                ['title' => 'Profil', 'meta' => 'Compte', 'copy' => 'Garder vos informations et votre mot de passe à jour.', 'href' => route('profile.edit')],
            ];

            $routine = [
                ['title' => 'Regarder les tickets assignés', 'copy' => 'Commencer par les jobs en attente et les réservations les plus proches.'],
                ['title' => 'Passer le ticket en cours', 'copy' => 'Changer le statut seulement quand le véhicule est réellement pris en charge.'],
                ['title' => 'Terminer immédiatement', 'copy' => 'Mettre le ticket en terminé dès que le service est fini.'],
                ['title' => 'Signaler les erreurs', 'copy' => 'Prévenir le manager si le client, le véhicule ou le service ne correspond pas.'],
            ];

            $decisionRows = [
                ['when' => 'Un job vous est assigné', 'screen' => 'Tickets', 'result' => 'Ouvrir le détail et préparer le service.'],
                ['when' => 'Le véhicule arrive', 'screen' => 'Tickets', 'result' => 'Passer le statut en cours.'],
                ['when' => 'Le lavage est fini', 'screen' => 'Tickets', 'result' => 'Passer le statut en terminé.'],
                ['when' => 'Un client demande son créneau', 'screen' => 'Réservations', 'result' => 'Consulter l’heure, le service et le statut.'],
            ];

            $watchItems = ['Tickets en attente', 'Tickets en cours', 'Prochaines réservations', 'Informations véhicule'];
            $checklist = ['Le client et le véhicule correspondent.', 'Le bon service est affiché.', 'Le statut est mis à jour au moment réel.', 'Le manager est prévenu en cas d’erreur.'];
            $avoidItems = ['Utiliser le compte d’un manager.', 'Modifier un ticket qui ne vous est pas assigné.', 'Attendre la fin de journée pour changer les statuts.'];
        } else {
            $title = 'Gérer une agence au quotidien';
            $subtitle = 'Un guide pratique pour organiser les branches, les clients, les réservations, les tickets, l’équipe et les rapports.';
            $mainAction = ['label' => 'Ouvrir le tableau de bord', 'href' => route('dashboard')];
            $secondaryAction = ['label' => 'Importer des données', 'href' => route('imports.index')];

            $quickActions = [
                ['title' => 'Tableau de bord', 'meta' => 'Chaque matin', 'copy' => 'Voir les revenus, réservations en attente, tickets actifs et prochaines échéances.', 'href' => route('dashboard')],
                ['title' => 'Branches', 'meta' => 'Organisation', 'copy' => 'Séparer les sites de lavage sous la même agence pour garder des chiffres propres.', 'href' => route('branches.index')],
                ['title' => 'Réservations', 'meta' => 'Planning', 'copy' => 'Créer, confirmer, annuler et suivre les créneaux clients.', 'href' => route('reservations.index')],
                ['title' => 'Tickets', 'meta' => 'Opérationnel', 'copy' => 'Transformer les réservations en travail assigné à l’équipe.', 'href' => route('tickets.index')],
                ['title' => 'Import CSV', 'meta' => 'Démarrage', 'copy' => 'Importer clients, employés et services sans tout retaper.', 'href' => route('imports.index')],
                ['title' => 'Analyses', 'meta' => 'Décision', 'copy' => 'Suivre revenus par service, performance employé, annulations et clients récurrents.', 'href' => route('analytics.index')],
            ];

            $routine = [
                ['title' => 'Ouvrir le tableau de bord', 'copy' => 'Repérer les réservations en attente, les tickets actifs et les alertes de facturation.'],
                ['title' => 'Confirmer le planning', 'copy' => 'Traiter les demandes client et vérifier que la bonne branche est sélectionnée.'],
                ['title' => 'Assigner le travail', 'copy' => 'Créer ou suivre les tickets, puis affecter les employés disponibles.'],
                ['title' => 'Clôturer la journée', 'copy' => 'Contrôler les tickets terminés, les paiements et les annulations.'],
            ];

            $decisionRows = [
                ['when' => 'Un client appelle', 'screen' => 'Clients puis Réservations', 'result' => 'Créer ou retrouver sa fiche, puis réserver le bon service.'],
                ['when' => 'Une voiture arrive', 'screen' => 'Tickets', 'result' => 'Créer le ticket, l’assigner et suivre son statut.'],
                ['when' => 'Un nouveau site ouvre', 'screen' => 'Branches', 'result' => 'Créer la branche avant de saisir ses clients et réservations.'],
                ['when' => 'Un fichier Excel existe', 'screen' => 'Import CSV', 'result' => 'Télécharger le modèle, prévisualiser, corriger puis importer par lots de 1000 lignes.'],
                ['when' => 'Un résultat baisse', 'screen' => 'Analyses', 'result' => 'Comparer services, employés, jours chargés, annulations et impayés.'],
            ];

            $watchItems = ['Réservations en attente', 'Tickets non terminés', 'Jours les plus chargés', 'Clients récurrents', 'Paiements agence'];
            $checklist = ['Chaque réservation a une branche correcte.', 'Chaque ticket important est assigné.', 'Les services ont des prix à jour.', 'Les imports sont prévisualisés avant validation.'];
            $avoidItems = ['Laisser toutes les branches mélangées.', 'Créer une réservation sans client propre.', 'Donner un compte manager à un employé terrain.'];
        }
    @endphp

    <div class="page-shell space-y-6">
        <div class="page-hero dashboard-reveal" style="--dashboard-delay: 0ms;">
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">Guide d’utilisation</p>
                    <h1 class="mt-3 text-3xl font-black text-slate-950 dark:text-white sm:text-4xl">{{ $title }}</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">{{ $subtitle }}</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ $mainAction['href'] }}" class="btn-primary">{{ $mainAction['label'] }}</a>
                        <a href="{{ $secondaryAction['href'] }}" class="btn-secondary">{{ $secondaryAction['label'] }}</a>
                    </div>
                </div>

                <div class="dashboard-soft-panel rounded-xl p-5 lg:w-80">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Rôle actif</p>
                    <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $roleLabel }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        {{ $user?->name ?? 'Compte connecté' }}
                    </p>
                    <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
                        Utilisez le compte de la personne qui fait l’action pour garder un historique fiable.
                    </div>
                </div>
            </div>
        </div>

        <section>
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Accès utiles</p>
                    <h2 class="text-2xl font-black text-slate-950 dark:text-white">Où aller en premier</h2>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Les raccourcis changent selon votre rôle.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($quickActions as $action)
                    <a href="{{ $action['href'] }}" class="surface-card-elevated dashboard-hover-lift group block p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="badge-soft">{{ $action['meta'] }}</span>
                                <h3 class="mt-4 text-lg font-black text-slate-950 dark:text-white">{{ $action['title'] }}</h3>
                            </div>
                            <span class="kpi-icon shrink-0 transition group-hover:border-blue-300 group-hover:bg-blue-100 dark:group-hover:bg-blue-950/60">
                                <flux:icon.chevron-right class="size-5" />
                            </span>
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $action['copy'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="surface-card-elevated p-6 xl:col-span-2">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Routine</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Flux de travail recommandé</h2>

                <div class="mt-6 space-y-4">
                    @foreach ($routine as $step)
                        <div class="dashboard-soft-panel rounded-xl p-4">
                            <div class="flex gap-4">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-950 text-sm font-black text-white dark:bg-slate-100 dark:text-slate-950">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-950 dark:text-white">{{ $step['title'] }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $step['copy'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card-elevated p-6">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">À surveiller</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Signaux importants</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($watchItems as $item)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-700 dark:bg-blue-300"></span>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="surface-card-elevated overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Décision rapide</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Quel écran utiliser ?</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Situation</th>
                            <th>Écran</th>
                            <th>Résultat attendu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($decisionRows as $row)
                            <tr>
                                <td class="font-semibold text-slate-900 dark:text-white">{{ $row['when'] }}</td>
                                <td><span class="badge-soft">{{ $row['screen'] }}</span></td>
                                <td>{{ $row['result'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="surface-card-elevated p-6">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Qualité</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Avant de valider</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($checklist as $item)
                        <div class="flex gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-700 text-white dark:bg-blue-500">
                                <flux:icon.check class="size-4" />
                            </span>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card-elevated p-6">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-300">À éviter</p>
                <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Les erreurs qui coûtent du temps</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($avoidItems as $item)
                        <div class="rounded-xl border border-slate-300 bg-slate-50 p-4 text-sm font-semibold leading-6 text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                            {{ $item }}
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
