# Onboarding — Ultreiataku

> Point d'entrée pour un nouvel arrivant sur le projet. Pour le détail, ce document renvoie vers la documentation de référence — il ne la duplique pas.

## En une phrase

**Ultreiataku** est l'application compagnon de pèlerinage **Liège → Santiago** (~2 500 km) : carte interactive offline, journal de voyage collaboratif, gestion du sac et voyages partagés entre pèlerins. Sous-projet du monorepo **SiteV26** (Police de Liège), aligné sur les patterns des autres backends (Oikotaku, Agorataku).

**Statut** : V1.0.0 — périmètre Belgique complet (24 étapes, Voie Mosane + Voie Monastique), prêt pour staging. Voir [docs/RELEASE_NOTES_V1.md](docs/RELEASE_NOTES_V1.md).

## Stack en bref

| Couche | Techno | Emplacement |
| --- | --- | --- |
| Backend | Laravel 13 + Filament 4, PHP 8.3, module `App\Modules\Pilgrimage` (15 entités) | `backend/` |
| Frontend | React 19 + Vite + TanStack Query + Leaflet, PWA offline-first | `frontend/` |
| Données | Postgres (base `ultreiataku` sur cluster partagé `site-pgsql`), Redis, MinIO (GPX + photos) | cluster SiteV26 |
| Auth | SSO par cookies de session via le projet **Auth** central | `backend/app/Modules/OAuth` |
| Langues | fr / nl / de (obligatoire sur tout nouveau texte) | — |

## Démarrer en local (résumé)

L'infra Docker vit dans **`InfraDocker/`** (jamais de compose autonome par sous-projet).

```bash
# depuis la racine SiteV26
docker compose -f InfraDocker/compose.yaml up -d ultreiataku-app ultreiataku-frontend
# créer la DB si absente
docker exec sitev26-site-pgsql-1 psql -U sail -c "CREATE DATABASE ultreiataku OWNER sail;"
# migrations + seeds (données Belgique réelles)
docker exec sitev26-ultreiataku-app-1 php artisan migrate --force
docker exec sitev26-ultreiataku-app-1 php artisan db:seed --force
```

- Backend : http://localhost:8096 · Frontend : http://localhost:5181
- Détail complet (variables `.env`, buckets MinIO, SSO) : [README.md § Démarrage](README.md#démarrage-développement)

## Où lire quoi

| Besoin | Document |
| --- | --- |
| Vue d'ensemble, stack, démarrage détaillé | [README.md](README.md) |
| Endpoints REST (par domaine, auth, exemples) | [docs/API.md](docs/API.md) |
| ADRs, ERD, flux critiques, modèle d'auth | [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) |
| Les 15 entités, champs, règles de gestion RG-01→08 | [docs/DATA_MODEL.md](docs/DATA_MODEL.md) |
| Périmètre livré, known issues, reliquats V1.1 | [docs/RELEASE_NOTES_V1.md](docs/RELEASE_NOTES_V1.md) |
| Changements par version | [CHANGELOG.md](CHANGELOG.md) |

## Conventions (monorepo SiteV26)

- **Scaffolding** via CLI officielle uniquement ; **compose** dans `InfraDocker/` exclusivement.
- **Tests** : backend PHPUnit (`php artisan test`, sqlite in-memory), frontend Vitest ; toute vague = migrations + Filament + API + front + tests. CI GitHub Actions (Pint, PHPStan 6, oxlint, build).
- **i18n** : tout nouveau texte dans les 3 locales (fr/nl/de) simultanément.
- **MinIO** : jamais `disk('public')` pour les médias ; proxy backend + disks privés.
- **Auth** : pas de `->login()` Filament natif ; SSO par cookies de session (pattern des backends frères).
- **Git** : commits Conventional (`feat(pilgrimage): …`), branche `main` du submodule ; bump du submodule côté SiteV26 après validation.

## Accès requis

<!-- À compléter selon l'environnement de l'arrivant -->
- Accès au dépôt GitHub `bocal-pol/ultreiataku` (submodule) et au monorepo SiteV26
- Docker Desktop (Windows) avec le cluster SiteV26 démarré
- Compte sur le projet **Auth** central pour tester les parcours authentifiés (un compte de test headless reste à créer pour les E2E authentifiés)
