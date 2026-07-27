# Ultreiataku

> Application compagnon de pèlerinage **Liège → Santiago de Compostela** (~2 500 km).
> _« Ultreïa e suseia »_ — toujours plus haut, toujours plus loin.

Ultreiataku est la transformation d'une PWA statique de préparation du Chemin (plans par pays, traces GPX, carnets d'hébergement et de ravitaillement, inventaire du sac) en une **application full-stack offline-first** : carte interactive, journal de voyage collaboratif, gestion du sac, et voyages partagés entre pèlerins.

Sous-projet du monorepo **SiteV26** (Police de Liège), aligné sur les patterns éprouvés des autres backends (Oikotaku, Agorataku, Faktaku).

---

## Sommaire

- [Présentation fonctionnelle](#présentation-fonctionnelle)
- [Stack technique](#stack-technique)
- [Architecture](#architecture)
- [Démarrage développement](#démarrage-développement)
- [Authentification SSO](#authentification-sso)
- [Internationalisation](#internationalisation)
- [Tests et qualité](#tests-et-qualité)
- [Contenu source du pèlerinage](#contenu-source-du-pèlerinage)
- [Documentation](#documentation)

---

## Présentation fonctionnelle

L'application couvre cinq domaines fonctionnels, livrés en vagues verticales :

| Vague | Domaine | Contenu |
| --- | --- | --- |
| **1a** | Le Chemin | Routes, étapes, waypoints/POI, traces GPX, carte Leaflet offline |
| **1b** | Vivre | Hébergements (gîtes, bivouac, abbayes) et repas par étape |
| **1c** | Pèlerins | Trips partagés, membres et rôles, départs planifiés, occupation prévue des hébergements |
| **1d** | Le Sac | Scénarios de sac pesés, objets par catégorie, assignations « qui porte quoi » en duo |
| **1e** | Le Carnet | Journal de voyage offline-first, photos, visibilité par entrée (privé / membres / public) |

L'application est **pays-agnostique** : le modèle Route / Stage / Waypoint fonctionne identiquement pour la Belgique, la France et l'Espagne. La V1 charge les données Belgique (Voie Mosane + Voie Monastique, Liège → Rocroi) ; France et Espagne s'ajoutent uniquement par des seeds.

---

## Stack technique

### Backend — `backend/`

- **Laravel 13.8** + **PHP 8.3** (`declare(strict_types=1)` sur Services, Enums, Support)
- **Filament 4** — panel d'administration `/admin` (sans login natif, SSO via redirection)
- **Postgres 18** — base dédiée `ultreiataku` dans le cluster partagé `site-pgsql`
- **Redis** partagé (préfixe `ultreiataku:`) — cache GPX simplifié, sessions, file de travail
- **MinIO** — stockage S3-compatible privé (traces GPX + photos journal)
- **spatie/laravel-translatable** — champs i18n JSON en base
- Le module métier vit dans `App\Modules\Pilgrimage` (voir [Architecture](#architecture))

### Frontend — `frontend/`

- **React 19** + **Vite 8** + **TypeScript** (`strict: true`)
- **TanStack Query** — état serveur et cache client
- **react-router-dom 7** — routage
- **Leaflet 1.9** — carte interactive (API directe, pas de react-leaflet, pour la perf offline)
- **Tailwind 4** — design aligné SiteV26
- **i18next / react-i18next** — namespace `pilgrimage.*` fr/nl/de
- **idb** — IndexedDB (stores offline + file de synchronisation du journal)
- **PWA** — Service Worker (cache tiles OSM, données étapes, sync journal offline)
- **Vitest** (unit) + **oxlint** (lint)

### Legacy — `legacy/`

La PWA HTML/JS/Leaflet originale (~1 560 lignes) est conservée comme référence fonctionnelle et source des seeds, tant que la V1 n'est pas iso-fonctionnelle.

---

## Architecture

Le backend suit la modularisation `App\Modules\*` du monorepo. Tout le domaine métier est isolé dans le module **`Pilgrimage`** :

```
backend/app/Modules/Pilgrimage/
├── Models/            # 15 entités Eloquent (HasUuids, HasTranslations)
├── Enums/             # backed enums (Country, WaypointType, TripMemberRole, ...)
├── Http/
│   ├── Controllers/Api/   # endpoints REST (préfixe /api/pilgrimage)
│   └── Resources/         # API Resources (transformers JSON)
├── Filament/          # Resources + Widgets du panel admin
├── Policies/          # autorisation par rôle (organizer / participant / observer)
├── Observers/         # OccupancyObserver, GpxTraceObserver, AccommodationObserver
├── Services/          # GpxSimplificationService, JournalSync, PackWeight, ...
├── Jobs/              # RebuildOccupancy, SendTripInvitation, PurgePilgrimAssets
├── Support/           # DouglasPeucker, GpxXmlParser
├── Database/Seeders/  # données Belgique (idempotents)
└── Routes/            # api.php + web.php (callback SSO admin)
```

Les **15 entités** : `PilgrimageRoute`, `Stage`, `Waypoint`, `Accommodation`, `Meal`, `Pilgrim`, `Trip` (+ pivot `TripMember`), `Departure`, `Occupancy`, `PackScenario`, `PackItem`, `ItemAssignment`, `GpxTrace`, `JournalEntry`, `JournalPhoto`.

Le détail est documenté dans :

- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — ADRs, ERD, flux critiques, modèle d'auth
- [`docs/DATA_MODEL.md`](docs/DATA_MODEL.md) — les 15 entités champ par champ + règles de gestion
- [`docs/API.md`](docs/API.md) — référence des endpoints REST

---

## Démarrage développement

Toutes les commandes se lancent **depuis la racine du monorepo `SiteV26`**. L'orchestration Docker est centralisée dans `InfraDocker/compose.yaml`.

### 1. Créer la base de données (une seule fois)

La base `ultreiataku` doit exister explicitement avant les migrations (piège des volumes nommés — voir `bug_rule_shared_pgsql_db_password`) :

```bash
docker exec sitev26-site-pgsql-1 psql -U sail -c "CREATE DATABASE ultreiataku OWNER sail;"
```

### 2. Démarrer les services

```bash
docker compose -f InfraDocker/compose.yaml up -d \
  ultreiataku-app ultreiataku-queue ultreiataku-frontend
```

| Service | Rôle |
| --- | --- |
| `ultreiataku-app` | Backend Laravel + Filament (FPM), port host **8096** |
| `ultreiataku-queue` | Worker Redis (mails d'invitation, import GPX, purge RGPD) |
| `ultreiataku-frontend` | SPA React / Vite, port host **5181** |

### 3. Synchroniser `vendor/` dans le volume (première fois)

Le code et `vendor/` sont hors bind mount Windows pour la performance. Initialiser le volume :

```bash
bash InfraDocker/scripts/sync-vendor.sh ultreiataku
```

### 4. Activer le watch (sync code Windows → volume)

```bash
docker compose -f InfraDocker/compose.yaml watch ultreiataku-app
```

### 5. Migrations et seeds

```bash
docker exec sitev26-ultreiataku-app-1 php artisan migrate
docker exec sitev26-ultreiataku-app-1 php artisan db:seed
```

Le seed importe les traces GPX depuis `storage/seeds/gpx/` vers MinIO (bucket `ultreiataku-gpx`).

### Accès

| Cible | URL |
| --- | --- |
| Frontend PWA (dev) | `http://localhost:5181` |
| Admin Filament | `http://localhost:8096/admin` |
| API | `http://localhost:8096/api/pilgrimage/...` |

> **Vite sous Docker Windows** : `server.watch.usePolling: true` est obligatoire dans `vite.config.ts`, sinon Vite sert des modules périmés (voir `feedback_vite_polling_docker`).

---

## Authentification SSO

Ultreiataku n'a **aucun compte local**. L'authentification passe par le projet **Auth central** de SiteV26 (OAuth2 / Passport), et la session est portée par des **cookies de session** — pas de Bearer token en `localStorage`.

- Les routes API protégées utilisent le middleware `web` (démarre la session) + `auth` (résout le guard `web`). Le SPA React envoie les cookies via `credentials: 'include'`.
- Le panel Filament ne rend **pas** de formulaire de login natif : `/admin` redirige vers le frontend (`/login?app=ultreiataku&return=/admin/sso/callback`), et le callback backend hydrate la session Filament (voir `feedback_filament_sso_pattern`).
- Un profil `Pilgrim` est créé automatiquement au premier accès authentifié (`GET /api/pilgrimage/me`).
- Rôles : `super_admin`, `admin` (Filament) ; `organizer`, `participant`, `observer` (par Trip).

> La CSP du panel Filament exige `unsafe-inline` + `unsafe-eval` (Alpine/Livewire) — voir `bug_rule_filament_csp_unsafe_eval`.

Le contrat d'intégration SSO complet (CORS avec credentials, présence de l'app dans la table `apps`, permissions clés OAuth) est décrit dans `project_auth_sso_integration_contract`.

---

## Internationalisation

Trois locales obligatoires, traduites **simultanément** à chaque ajout : `fr` (référence), `nl`, `de`.

- **Backend** : champs traduisibles stockés en JSON `{"fr":..., "nl":..., "de":...}` via `HasTranslations` sur `PilgrimageRoute`, `Stage`, `Waypoint`, `Accommodation`, `Meal`.
- **Frontend** : namespace `pilgrimage.*` dans `fr.json` / `nl.json` / `de.json`.
- Slugs, codes d'étape (`BE-01`), coordonnées et valeurs numériques ne sont **pas** traduits.

---

## Tests et qualité

```bash
# Backend — tests PHPUnit
docker exec sitev26-ultreiataku-app-1 php artisan test

# Backend — analyse statique PHPStan (niveau 6 + baseline)
docker exec sitev26-ultreiataku-app-1 ./vendor/bin/phpstan analyse

# Backend — formatage Pint (preset laravel)
docker exec sitev26-ultreiataku-app-1 ./vendor/bin/pint

# Frontend — tests Vitest
docker exec sitev26-ultreiataku-frontend-1 npm run test

# Frontend — lint + typecheck
docker exec sitev26-ultreiataku-frontend-1 npm run lint
docker exec sitev26-ultreiataku-frontend-1 npm run typecheck
```

Fin de session : `composer audit` (backend) et `npm audit` (frontend) sont obligatoires (règle monorepo).

---

## Contenu source du pèlerinage

La matière des seeds provient du projet de préparation réel (branche source du submodule) : plans par pays, traces GPX quotidiennes, POI patrimoine, carnets d'hébergement/ravitaillement, spécialités régionales, inventaire du sac pesé. Elle reste consultable comme documentation vivante du Chemin :

### Voyage — Belgique (phase actuelle : Liège → Rocroi, ~204 km, 12 étapes)

- Voie Mosane (Liège · Amay · Huy · Andenne · Namur · Yvoir · Dinant · Hastière · Givet, ~130 km) — Meuse + patrimoine mosan + Vauban
- Voie Monastique (Givet · Doische · Olloy-sur-Viroin · Couvin · Rocroi, ~74 km) — Fagne + Ardennes + Rocroi étoilée

**Top POI** : Grotte Scladina (Sclayn), Rocroi étoilée (Vauban), Roche à Lomme (Dourbes), Dinant (Rochers Bayard + Citadelle), Passage d'eau de Waulsort (dernier passeur manuel de Wallonie).

### Phases suivantes (seeds V1.1)

- **France** — Rocroi → Saint-Jean-Pied-de-Port (Via Campaniensis / Vézelay, ~65 étapes)
- **Espagne** — SJPP → Santiago par le Camino del Norte (~46 étapes + variantes)

### Matériel, hébergement, préparation

Inventaire pesé Solo (~8,5 kg base) et Duo (~7,5 kg/pers), carnets de gîtes et bivouac légal par pays, ravitaillement et spécialités régionales, préparation physique/credencial/santé/budget — tout est présent dans l'arborescence source du projet et alimente les entités `PackScenario`, `PackItem`, `Accommodation`, `Meal`.

**Configuration retenue** : saison mai-juin · solo ou duo · hébergement mixte 50/50 bivouac légal + gîte pèlerin · budget ~549 € (Belgique solo) / ~4 000 € (pèlerinage complet solo).

---

## Documentation

| Document | Contenu |
| --- | --- |
| [`docs/API.md`](docs/API.md) | Référence des endpoints REST (Chemin public, Trips, Sac, Journal, RGPD) |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Synthèse des 6 ADRs, ERD, flux critiques, modèle d'auth cookies-session |
| [`docs/DATA_MODEL.md`](docs/DATA_MODEL.md) | Les 15 entités en détail + règles de gestion RG-01 à RG-08 + seeds |

Artefacts amont (hors ce dépôt) : cadrage, PRD et specs fonctionnelles dans `reports/product/ultreiataku/`, ADRs et ERD dans `reports/architecture/ultreiataku/`, backlog dans `reports/sprint/`.

---

## 🐚 Bon Chemin

_« Ultreïa e suseia »_
