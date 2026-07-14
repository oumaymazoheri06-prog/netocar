# Sécurité et mise en production

Avant chaque déploiement :

- partir de `.env.production.example`, générer une clé avec `php artisan key:generate` et ne jamais versionner le vrai `.env` ;
- terminer TLS sur le serveur, forcer HTTPS et conserver les cookies `secure`, `http_only` et chiffrés ;
- exécuter `php artisan migrate --force`, `php artisan optimize` et un worker de queue supervisé ;
- sauvegarder quotidiennement la base et `storage/app/private`, puis tester régulièrement une restauration ;
- expédier les journaux et exceptions vers un service de surveillance avec alertes ;
- lancer `composer audit`, `npm audit` et la suite de tests dans l’intégration continue ;
- limiter l’accès administrateur, activer la double authentification et faire tourner les secrets ;
- conserver les reçus hors du dossier public et utiliser un scanner antivirus externe si les volumes deviennent importants.

La route `/up` peut servir de sonde de disponibilité. Les sauvegardes, TLS, l’antivirus et la supervision restent des responsabilités de l’infrastructure d’hébergement.
