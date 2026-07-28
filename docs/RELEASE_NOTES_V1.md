# Notes de version — Ultreiataku V1.0.0

**Date de release** : 2026-07-28
**Version** : 1.0.0
**Environnement cible** : staging (validation pre-production)

---

## Resume executif

Ultreiataku V1 est la transformation complete de la PWA de pelerinage HTML vers une application full-stack moderne : backend Laravel 13 + Filament 4, frontend React 19 PWA offline-first. Le perimetre livre couvre la Belgique dans son integralite (24 etapes, 2 routes : Via Mosana et Via Monastique), avec les cinq grandes fonctionnalites : consultation du chemin avec carte hors ligne, hebergements et repas, voyages partages entre pelerins, gestion du sac, carnet de voyage synchronise.

L'application est fonctionnellement complete pour un deploiement en Belgique. Elle est architecturalement pays-agnostique : l'ajout de la France ou de l'Espagne ne necessite que des seeds supplementaires, zero nouvelle fonctionnalite.

---

## Perimetre livre — V1 (Belgique)

### Fonctionnel

| Module | Tickets | SP livres |
|--------|---------|-----------|
| Scaffolding, infra, CI, SSO, i18n | ULTREIA-00 a 04 | 19 |
| Chemin — Route, Stage, Waypoint, GPX, carte offline | ULTREIA-10 a 1T | 59 |
| Vivre — Accommodation, Meal | ULTREIA-20 a 2T | 21 |
| Pelerins — Trip, Pilgrim, Departure, Occupancy | ULTREIA-30 a 3T | 42 |
| Sac — PackScenario, PackItem, ItemAssignment | ULTREIA-40 a 4T | 23 |
| Carnet — JournalEntry, JournalPhoto | ULTREIA-50 a 5T | 46 |
| **Total** | **49 tickets** | **210 SP** |

### Donnees Belgique seedees

- 2 routes : Via Mosana (12 etapes, Liege-Namur-Santiago) et Via Monastique (12 etapes)
- 22 villes et points d'interet belges avec coordonnees GPS reelles
- ~24 hebergements avec equipements, capacites et tarifs
- ~12 repas signatures par etape (specialites regionales reelles)
- 2 scenarios de sac (Solo 8,5 kg / Duo 7,5 kg) avec 35 items peses
- Traces GPX : 12 traces principales + 6 detours via MinIO `minio_gpx`

### Infrastructure

- CI : lint (Pint, oxlint), PHPUnit, Playwright, PHPStan niveau 6 + baseline
- PHPStan niveau 6 avec baseline generee
- MinIO : 2 disks dedies (`minio_gpx`, `minio_journal`)
- SSO Passport OAuth2 avec Auth central SiteV26
- Internationalisation : francais, neerlandais, allemand

---

## Reporte en V1.1

| Sujet | Tickets | Raison du report |
|-------|---------|-----------------|
| Seeds France (Via Campaniensis / Vezelay, 65 etapes) | ULTREIA-60 | Contenu uniquement ; modele de donnees pret |
| Seeds Espagne (Camino del Norte, 46 etapes + 9 PC) | ULTREIA-61 | Contenu uniquement ; modele de donnees pret |
| Endpoint `GET /departures/{id}/assignments` | ULTREIA-45b | Vue « qui porte quoi » degradee sans lui |
| Drag-and-drop assignation duo (sac) | ULTREIA-45 Should | Select simple livre ; drag-and-drop reporte |
| Trace GPX BE-01 (Liege→Amay) | — | Source absente ; fallback sans minio_path |
| Points de decision contextuels (V2) | Hors backlog V1 | Fonctionnalite V2 — IA contextuelle |

> **Note** : les droits RGPD (export Art. 20, suppression Art. 17, choix de depart d'un membre keep/remove, revocation geoloc photo) sont **livres en V1** (iteration 2 de la boucle qualite), pas reportes. La retention est illimitee par decision produit (suppression manuelle uniquement). Restent hors code : les prealables juridiques (mentions legales, ROPA, DPO, DPA) listes dans les Known issues.

---

## Audit dependances — Resultats reels (2026-07-28)

### Backend PHP — composer audit (container sitev26-ultreiataku-app-1)

```
No security vulnerability advisories found.
```

**Resultat : PROPRE.** Aucune vulnerabilite connue dans les 110 packages PHP de l'Ultreiataku (Laravel 13.21, Filament 4.12.3, spatie/laravel-translatable 6.14, larastan/larastan ^3.0).

Note : le `composer audit` execute sur le container SiteV26 principal (non-Ultreiataku) retourne 21 avis sur 9 packages (filament, laravel/framework, spatie/laravel-medialibrary, mtdowling/jmespath.php). Ces avis ne concernent pas Ultreiataku dont le `composer.lock` est independant et propre.

### Frontend npm — npm audit (Ultreiataku/frontend)

```
2 high severity vulnerabilities
Package : react-router / react-router-dom (>=6.0.0 <=7.17.0)
CVE visibles : CVE-2025-43864, CVE-2025-43865 et 12 autres references GHSA
```

**Verdict : NON EXPLOITABLES dans cette architecture.**

L'audit de securite cerbere (rapport `reports/security/ultreiataku/2026-07-27_findings-summary.md`) confirme que l'ensemble des CVE react-router actifs ciblent exclusivement :
- les scenarios SSR (`renderToString`) — Ultreiataku est une SPA pure (build Vite client uniquement, aucun `renderToString` dans `vite.config.ts` ni dans les entrypoints)
- les React Server Components — non utilises, architecture React client uniquement

**Action recommandee :** resserrer la contrainte de version de `>=7.0.0 <7.12.0` vers `^7.6.0` dans `package.json` (1 ligne, zero impact fonctionnel) pour fermer le vecteur preventif si SSR/RSC est introduit ulterieurement. La contrainte actuelle `<7.12.0` empeche npm audit fix automatique (`7.18.1` est disponible) — la mise a jour manuelle vers `^7.6.0` ou `^7.18.0` est a effectuer avant le deploiement en production.

---

## Prerequis de deploiement

### Variables d'environnement obligatoires

Les variables suivantes doivent etre definies avant le premier demarrage en staging/production. Se referer au `.env.example` du backend.

| Variable | Valeur attendue | Remarque |
|----------|-----------------|----------|
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | **Obligatoire** — stack traces desactivees |
| `APP_KEY` | genere (`php artisan key:generate`) | |
| `DB_HOST` | `site-pgsql` | hostname Docker du cluster partage |
| `DB_PASSWORD` | `password` | **litteral** — voir bug_rule_shared_pgsql |
| `REDIS_PASSWORD` | `password` | **litteral** — voir BUG-ULTREIA-P0-001 |
| `AUTH_VERIFY_URL` | URL centrale Auth | ex. `http://localhost:8082/api/auth/verify` |
| `AUTH_API_URL` | URL API Auth | |
| `AUTH_LOGIN_URL` | URL login Auth | |
| `AUTH_APP_ID` | `ultreiataku` | |
| `MINIO_ENDPOINT` | URL MinIO interne | |
| `MINIO_ACCESS_KEY_ID` / `MINIO_SECRET_ACCESS_KEY` | credentials MinIO | |
| `SESSION_ENCRYPT` | `true` | |
| `VAULT_ADDR` / `VAULT_TOKEN` | Vault interne | remplacer le dev-root-token par un AppRole en prod |

### Etapes de deploiement

1. Creer la base de donnees Ultreiataku sur `site-pgsql` si inexistante (`CREATE DATABASE ultreiataku`).
2. Copier `.env.example` vers `.env`, renseigner toutes les variables ci-dessus.
3. Executer `php artisan migrate --force` dans le container.
4. Executer `php artisan db:seed --class=Database\Seeders\DatabaseSeeder` pour charger les donnees Belgique.
5. Verifier que les buckets MinIO `minio_gpx` et `minio_journal` sont crees et accessibles.
6. Relancer le container (`docker compose restart ultreiataku-app`).
7. Valider le SSO depuis le frontend : `http://<host>:5181` doit rediriger vers Auth central et creer un Pilgrim au premier login.

---

## Known issues

> État au 2026-07-28. Tous les bloquants identifiés pendant la boucle qualité (themis, cerbere, RGPD, E2E argus) ont été **corrigés et re-vérifiés** avant cette release. Les issues ci-dessous sont soit résolues (rappelées pour traçabilité), soit non bloquantes.

### Résolu pendant la boucle qualité (vérifié)

| ID | Titre | Résolution |
| --- | --- | --- |
| BUG-P0-001 / P0-002 | `.env` : `REDIS_PASSWORD=null` + `DB_HOST` invalide | Corrigés et **persistés dans `.env.example`** (`site-pgsql` + `password`) |
| B-01 (themis) | Route `GET /api/pilgrimage/trips` absente | Route + `TripController::index` ajoutés |
| B-02 (themis) | Contrat `invite_token` | `TripResource` expose le token à l'organizer uniquement |
| B-03 (themis) | `removeMember` pouvait éjecter l'organisateur | Garde 422 en place |
| BUG-P1-001 | Étapes des 2 voies belges mélangées | Groupé par route (Via Mosana puis Via Monastique) — vérifié sur l'API réelle |
| BUG-P1-002 | Spécialité locale masquée par le restaurant | `meal.name` en titre, restaurant en sous-titre |
| BUG-P2-001 | Gîte des Compagnons seedé en donativo à tort | Corrigé (10 €), re-seed vérifié |
| Sécu P0/P1 cerbere (7) | Guard API, CSRF state, IDOR, EXIF, seeder GPX | Tous corrigés (cf. CHANGELOG Security) |
| RGPD P1 (3) + P2 (2) | Export/suppression, rétention, départ membre | Endpoints livrés, conformité code V1 atteinte |

### Ouvert — non bloquant pour un staging

| ID | Titre | Nature |
| --- | --- | --- |
| Scénarios E2E Auth (16) | Parcours authentifiés non exécutés en headless | Nécessite un compte de test SSO sur l'Auth central — les parcours publics (8) passent, le code auth est testé unitairement (254 tests) |
| compose watch sync | Le container peut servir du code périmé sous Windows | Infra dev à investiguer (contournement : `docker compose cp` + `route:clear`) |
| GPX BE-01 | Trace Liège→Amay absente des sources | Fallback sans minio_path ; trace à créer |
| P2-02/04/05 (sécu) | Vault dev-token, CSP admin, TTL invite token | Durcissement à planifier V1.0.x |

### Reporté V1.1 (fonctionnel)

- Seeds France (Via Campaniensis/Vézelay) + Espagne (Camino del Norte) — données pures
- ULTREIA-45b : endpoint `GET /departures/{id}/assignments` (vue « qui porte quoi » complète)
- Points de décision contextuels aux POI (feature différenciante V2)

### Hors code — préalables juridiques à la mise en production (ressort métier)

Mentions légales + politique de confidentialité (Art. 13), registre des traitements ROPA (Art. 30), désignation DPO (secteur public), DPA avec MinIO et le projet Auth central (Art. 28).

---

## Conditions de GO en production

Le code V1 (périmètre Belgique) est **prêt pour un déploiement en staging** : 254 tests backend + 121 frontend verts, boucle qualité complète (sécurité + RGPD + revue de code + E2E) close, bugs E2E corrigés et vérifiés.

Avant le GO **production** :

1. Valider les 16 scénarios E2E authentifiés avec un compte de test SSO headless.
2. Traiter les préalables juridiques RGPD (hors code, ci-dessus).
3. Durcissement sécu P2 (Vault token de prod, CSP admin, TTL invite token).
