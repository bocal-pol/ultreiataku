# Modèle de données — Ultreiataku

Description exhaustive des **15 entités** du module `Pilgrimage`, de leurs relations, énumérations, des **règles de gestion RG-01 à RG-08**, et des données de seed (Belgique).

Conventions communes :

- Clé primaire `id` en **UUID** (trait `HasUuids`), pas d'auto-increment.
- Champs marqués **(i18n)** stockés en JSON `{"fr":..., "nl":..., "de":...}` via `HasTranslations`. `fr` est la référence obligatoire.
- Poids toujours en **grammes** en base, distances en **km**, montants en **EUR**.
- Le modèle `PilgrimageRoute` porte la table `pilgrimage_routes` (évite la collision avec la façade `Route`).

---

## Sommaire

- [Le Chemin](#le-chemin) — Route, Stage, Waypoint, Accommodation, Meal
- [Les Pèlerins](#les-pèlerins) — Pilgrim, Trip (+ TripMember), Departure, Occupancy
- [Le Sac](#le-sac) — PackScenario, PackItem, ItemAssignment
- [Les Traces](#les-traces) — GpxTrace
- [Le Carnet](#le-carnet) — JournalEntry, JournalPhoto
- [Énumérations](#énumérations)
- [Règles de gestion](#règles-de-gestion-rg-01-à-rg-08)
- [Données de seed (Belgique)](#données-de-seed-belgique)

---

## Le Chemin

### 1. PilgrimageRoute (`pilgrimage_routes`)

Itinéraire nommé regroupant des étapes ordonnées — une voie de pèlerinage.

| Champ | Type | Notes |
| --- | --- | --- |
| `slug` | string(100) UK | kebab-case, non traduisible (`via-mosana-belgique`) |
| `name` | json (i18n) | requis |
| `description` | json (i18n) | nullable |
| `country` | enum `Country` | `BE`, `FR`, `ES` |
| `total_distance_km` | decimal(8,2) | > 0 |
| `total_elevation_gain_m` | integer | ≥ 0 |
| `is_active` | boolean | défaut `true` |
| `sort_order` | integer | unique par pays |

**Relations** : `hasMany Stage`, `hasMany Trip`.

### 2. Stage (`stages`)

Segment de marche entre deux waypoints — une journée type.

| Champ | Type | Notes |
| --- | --- | --- |
| `route_id` | UUID FK | cascade delete |
| `code` | string(10) UK | format `XX-NN` (`BE-01`) |
| `name` | json (i18n) | requis |
| `day_number` | integer | ≥ 1, unique par route |
| `start_waypoint_id` / `end_waypoint_id` | UUID FK | vers Waypoint |
| `distance_km` | decimal(6,2) | > 0 |
| `elevation_gain_m` / `elevation_loss_m` | integer | ≥ 0 |
| `estimated_duration_h` | decimal(4,1) | > 0 |
| `difficulty` | enum `StageDifficulty` | `easy`, `moderate`, `hard` |
| `accommodation_type_default` | enum `AccommodationType` | hébergement type par défaut |
| `notes` | json (i18n) | nullable |
| `sort_order` | integer | unique par route |

**Relations** : `belongsTo PilgrimageRoute`, `belongsTo Waypoint` (start/end), `belongsToMany Waypoint` (POI via pivot `stage_waypoint`), `hasMany Accommodation`, `hasMany Meal`, `hasMany GpxTrace`, `hasMany Departure`, `hasMany JournalEntry`.

**Index** : `(route_id, day_number)` UNIQUE ; `code` UNIQUE.

### 3. Waypoint (`waypoints`)

Point géographique : ville-étape, POI, halte, croisement, source d'eau, zone de bivouac.

| Champ | Type | Notes |
| --- | --- | --- |
| `slug` | string(150) UK | |
| `name` | json (i18n) | requis |
| `type` | enum `WaypointType` | `city`, `poi`, `water`, `rest`, `crossroads`, `bivouac_zone` |
| `poi_category` | enum `PoiCategory` | nullable — `archaeology`, `religious`, `fortress`, `nature`, `gastronomy`, `view` |
| `latitude` / `longitude` | decimal(10,7) | coordonnées WGS84 |
| `detour_type` | enum `DetourType` | nullable — `on_path`, `short`, `medium`, `long` |
| `detour_distance_km` | decimal(5,2) | nullable |
| `detour_duration_min` / `visit_duration_min` | integer | nullable |
| `entry_cost_eur` | decimal(6,2) | nullable |
| `booking_required` | boolean | défaut `false` |
| `booking_contact` | string(255) | nullable |
| `opening_notes` / `description` | json (i18n) | nullable |
| `is_active` | boolean | défaut `true` |
| `active_from` / `active_until` | date | nullable (saisonnalité) |
| `verified_at` | timestamp | nullable — voir RG-08 |

**Relations** : `hasMany Accommodation`, `hasMany Meal`, `belongsToMany Stage` (pivot), `hasMany GpxTrace` (détours).

### 4. Accommodation (`accommodations`)

Hébergement lié à une étape et/ou un waypoint.

| Champ | Type | Notes |
| --- | --- | --- |
| `stage_id` / `waypoint_id` | UUID FK | tous deux nullable |
| `name` | json (i18n) | requis |
| `type` | enum `AccommodationType` | `gite`, `camping`, `hostel`, `hotel`, `abbey`, `donativo`, `bivouac` |
| `address`, `phone`, `website`, `email` | string | nullable |
| `price_min_eur` / `price_max_eur` | decimal(6,2) | nullable |
| `is_donativo` | boolean | prix libre |
| `capacity` | integer | nullable |
| `has_shower`, `has_kitchen`, `has_wifi`, `stamps_credencial` | boolean | équipements ; `stamps_credencial` = tampon credencial |
| `pilgrim_friendly` | boolean | défaut `true` |
| `booking_required` | boolean | défaut `false` |
| `booking_notice_days` | integer | nullable |
| `bivouac_legal` | boolean | zone de bivouac tolérée |
| `bivouac_notes` | json (i18n) | nullable |
| `is_primary` | boolean | hébergement principal vs alternative |
| `sort_order` | integer | ordre d'affichage |
| `notes` | json (i18n) | nullable |
| `verified_at` | timestamp | nullable — RG-08 |

**Index** : `(stage_id, is_primary, sort_order)` composite ; `verified_at`.

### 5. Meal (`meals`)

Repas recommandé pour une étape — spécialité locale, restaurant ou cuisine bivouac.

| Champ | Type | Notes |
| --- | --- | --- |
| `stage_id` | UUID FK | requis |
| `waypoint_id` | UUID FK | nullable |
| `meal_type` | enum `MealType` | `breakfast`, `lunch`, `dinner`, `snack` |
| `name` | json (i18n) | requis |
| `description` | json (i18n) | nullable |
| `meal_context` | enum `MealContext` | `restaurant`, `bivouac_cooking`, `grocery`, `local_specialty` |
| `restaurant_name` / `restaurant_address` | string | nullable |
| `price_estimate_eur` | decimal(6,2) | nullable |
| `kcal_estimate` | integer | nullable |
| `weight_g` | integer | nullable — repas bivouac portés |
| `notes` | json (i18n) | nullable |

---

## Les Pèlerins

### 6. Pilgrim (`pilgrims`)

Profil pèlerin, lié à un utilisateur SSO (créé automatiquement au premier accès).

| Champ | Type | Notes |
| --- | --- | --- |
| `user_id` | integer FK UK | vers `users` (SSO), unique |
| `display_name` | string(100) | requis |
| `avatar_url` | string(500) | nullable, servi par proxy |
| `preferred_locale` | enum | `fr`, `nl`, `de` |
| `configuration` | enum `PilgrimConfiguration` | `solo`, `duo` |
| `target_base_weight_kg` | decimal(4,2) | nullable |
| `target_daily_kcal` | integer | nullable |

**Relations** : `hasMany Trip` (organizer), `belongsToMany Trip` (membres), `hasMany Departure`, `hasMany PackScenario`, `hasMany JournalEntry`.

### 7. Trip (`trips`) + pivot TripMember (`trip_members`)

Voyage partagé — un groupe qui parcourt une Route ensemble.

| Champ (Trip) | Type | Notes |
| --- | --- | --- |
| `route_id` | UUID FK | |
| `organizer_id` | UUID FK | vers Pilgrim |
| `name` | string(200) | requis |
| `description` | text | nullable |
| `status` | enum `TripStatus` | `planned`, `active`, `completed`, `cancelled` |
| `estimated_start_date` / `estimated_end_date` | date | nullable |
| `configuration` | enum `TripConfiguration` | `solo`, `duo`, `group` |
| `is_public` | boolean | défaut `false` — expose les entrées publiques hors Trip |
| `invite_token` | string(64) UK | nullable — révocable (RG-07) |

| Champ (pivot `trip_members`) | Type | Notes |
| --- | --- | --- |
| `trip_id` / `pilgrim_id` | UUID FK | index UNIQUE `(trip_id, pilgrim_id)` |
| `role` | enum `TripMemberRole` | `organizer`, `participant`, `observer` |
| `joined_at` | timestamp | |
| `invited_by` | UUID FK | nullable |

> Le pivot `trip_members` **n'a pas** de `created_at`/`updated_at` (décision schema). Certaines lectures utilisent `DB::table()` pour éviter la sélection implicite de timestamps.

### 8. Departure (`departures`)

Départ planifié d'un pèlerin sur un Trip — étapes couvertes et période.

| Champ | Type | Notes |
| --- | --- | --- |
| `trip_id` / `pilgrim_id` | UUID FK | |
| `start_stage_id` / `end_stage_id` | UUID FK | |
| `planned_start_date` | date | requis |
| `planned_end_date` | date | calculé ou saisi |
| `actual_start_date` | date | nullable — temps réel |
| `status` | enum `DepartureStatus` | `planned`, `active`, `paused`, `completed`, `abandoned` |
| `pack_scenario_id` | UUID FK | nullable |
| `notes` | text | nullable |

**Contrainte** : `end_stage.day_number >= start_stage.day_number` sur la même Route.

**Index** : `(trip_id, pilgrim_id, planned_start_date)`.

### 9. Occupancy (`occupancies`)

Occupation **dérivée** — calculée par Observer, jamais saisie (voir RG-02, ADR-U03). Lecture seule côté frontend.

| Champ | Type | Notes |
| --- | --- | --- |
| `accommodation_id` | UUID FK | |
| `date` | date | nuit du |
| `trip_id` | UUID FK | |
| `count` | integer | nombre de pèlerins du Trip prévus cette nuit |
| `last_recalculated_at` | timestamp | |

**Index** : `(accommodation_id, date, trip_id)` UNIQUE (upsert idempotent) ; `(trip_id, date)`. Une ligne à `count = 0` est supprimée.

---

## Le Sac

### 10. PackScenario (`pack_scenarios`)

Scénario de sac nommé, appartenant à un Pilgrim.

| Champ | Type | Notes |
| --- | --- | --- |
| `pilgrim_id` | UUID FK | |
| `name` | string(150) | requis (`Solo 8,5 kg Belgique`) |
| `description` | text | nullable |
| `target_base_weight_kg` | decimal(4,2) | objectif de poids à vide (voir RG-01) |
| `configuration` | enum | `solo`, `duo` |
| `season` | enum `PackSeason` | `spring`, `summer`, `autumn`, `winter` |

**Relations** : `belongsTo Pilgrim`, `hasMany PackItem`, `hasMany ItemAssignment`.

### 11. PackItem (`pack_items`)

Objet portable avec poids, appartenant à un PackScenario.

| Champ | Type | Notes |
| --- | --- | --- |
| `pack_scenario_id` | UUID FK | |
| `name` | string(200) | requis |
| `category` | enum `PackCategory` | `portage`, `sleeping`, `cooking`, `water`, `clothing`, `hygiene`, `health`, `navigation`, `misc` |
| `brand` / `model` | string(100) | nullable |
| `weight_g` | integer | requis, > 0 |
| `is_shared` | boolean | item mutualisé en duo |
| `is_consumable` | boolean | gaz, nourriture — exclu du poids de base |
| `replacement_km` | integer | nullable — km avant remplacement estimé |
| `notes` | text | nullable |
| `sort_order` | integer | ordre dans la catégorie |

### 12. ItemAssignment (`item_assignments`)

Assignation d'un item à un pèlerin pour un tronçon, dans un voyage duo.

| Champ | Type | Notes |
| --- | --- | --- |
| `pack_item_id` | UUID FK | |
| `departure_id` | UUID FK | |
| `assigned_to_pilgrim_id` | UUID FK | |
| `from_stage_id` / `to_stage_id` | UUID FK | nullable — tronçon d'application |
| `notes` | text | nullable |

**Règle** : un `PackItem` marqué `is_shared = true` peut avoir plusieurs assignations pour le même Departure (chaque porteur sur son tronçon).

---

## Les Traces

### 13. GpxTrace (`gpx_traces`)

Trace GPX importée, stockée sur MinIO, liée à une étape ou un détour.

| Champ | Type | Notes |
| --- | --- | --- |
| `stage_id` | UUID FK | nullable |
| `waypoint_id` | UUID FK | nullable — détour vers un POI |
| `trace_type` | enum `GpxTraceType` | `stage_main`, `detour`, `variant` |
| `name` | string(200) | requis |
| `minio_path` | string(500) | chemin relatif dans le bucket |
| `minio_disk` | string(50) | défaut `minio_gpx` — jamais `public` |
| `distance_km` | decimal(8,3) | calculé à l'import |
| `elevation_gain_m` / `elevation_loss_m` | integer | nullable |
| `track_points_count` | integer | nullable |
| `precision` | enum `GpxPrecision` | `exact`, `approximate` |
| `source` | string(200) | nullable — provenance de la trace |
| `imported_at` | timestamp | |

**Index** : `(stage_id, trace_type)`. Servie exclusivement par proxy `/api/pilgrimage/gpx/{id}` (RG-04).

---

## Le Carnet

### 14. JournalEntry (`journal_entries`)

Entrée de carnet — texte libre daté, lié à un Trip et optionnellement une étape.

| Champ | Type | Notes |
| --- | --- | --- |
| `trip_id` / `pilgrim_id` | UUID FK | `pilgrim_id` = auteur |
| `stage_id` | UUID FK | nullable |
| `title` | string(300) | nullable |
| `body` | text | nullable |
| `entry_date` | date | requis |
| `latitude` / `longitude` | decimal(10,7) | nullable — géoloc à l'écriture |
| `visibility` | enum `JournalVisibility` | `private`, `members`, `public` (RG-03) |
| `mood` | enum | nullable — `great`, `good`, `neutral`, `tired`, `difficult` |
| `km_walked_today` | decimal(6,2) | nullable |
| `is_synced` | boolean | défaut `true` ; `false` si créé offline |
| `local_id` | string(36) | nullable — UUID v4 client, idempotence sync (RG-05) |

**Relations** : `belongsTo Trip`, `belongsTo Pilgrim`, `belongsTo Stage`, `hasMany JournalPhoto`.

**Index** : `local_id` UNIQUE (partiel `WHERE NOT NULL`) ; `(trip_id, entry_date, visibility)`.

### 15. JournalPhoto (`journal_photos`)

Photo associée à une entrée, stockée sur MinIO `minio_journal`.

| Champ | Type | Notes |
| --- | --- | --- |
| `journal_entry_id` | UUID FK | cascade delete |
| `minio_path` | string(500) | requis |
| `minio_disk` | string(50) | défaut `minio_journal` |
| `alt_text` | string(500) | nullable — accessibilité WCAG |
| `caption` | string(500) | nullable |
| `taken_at` | timestamp | nullable — EXIF ou saisie |
| `latitude` / `longitude` | decimal(10,7) | nullable — EXIF ; révocable (RGPD-U05) |
| `file_size_bytes` | integer | nullable |
| `mime_type` | string(50) | nullable |
| `sort_order` | integer | ordre dans l'entrée |
| `is_synced` | boolean | défaut `true` |

**Règle** : servie uniquement via proxy `/api/pilgrimage/journal/photos/{id}` selon RG-03. Jamais d'URL MinIO directe.

---

## Énumérations

| Enum | Valeurs |
| --- | --- |
| `Country` | `BE`, `FR`, `ES` |
| `StageDifficulty` | `easy`, `moderate`, `hard` |
| `WaypointType` | `city`, `poi`, `water`, `rest`, `crossroads`, `bivouac_zone` |
| `PoiCategory` | `archaeology`, `religious`, `fortress`, `nature`, `gastronomy`, `view` |
| `DetourType` | `on_path`, `short`, `medium`, `long` |
| `AccommodationType` | `gite`, `camping`, `hostel`, `hotel`, `abbey`, `donativo`, `bivouac` |
| `MealType` | `breakfast`, `lunch`, `dinner`, `snack` |
| `MealContext` | `restaurant`, `bivouac_cooking`, `grocery`, `local_specialty` |
| `PilgrimConfiguration` | `solo`, `duo` |
| `TripStatus` | `planned`, `active`, `completed`, `cancelled` |
| `TripConfiguration` | `solo`, `duo`, `group` |
| `TripMemberRole` | `organizer`, `participant`, `observer` |
| `DepartureStatus` | `planned`, `active`, `paused`, `completed`, `abandoned` |
| `PackCategory` | `portage`, `sleeping`, `cooking`, `water`, `clothing`, `hygiene`, `health`, `navigation`, `misc` |
| `PackSeason` | `spring`, `summer`, `autumn`, `winter` |
| `GpxTraceType` | `stage_main`, `detour`, `variant` |
| `GpxPrecision` | `exact`, `approximate` |
| `JournalVisibility` | `private`, `members`, `public` |
| `JournalMood` | `great`, `good`, `neutral`, `tired`, `difficult` |

---

## Règles de gestion (RG-01 à RG-08)

### RG-01 — Poids du sac

```
base_weight_kg  = SUM(weight_g WHERE is_consumable = false) / 1000
total_weight_kg = base_weight_kg + eau (2,0 kg) + nourriture (0,75 kg)
```

Indicateur de jauge : **vert** si `base_weight ≤ target` ; **orange** si `base_weight ≤ target + 1 kg` ; **rouge** au-delà.

### RG-02 — Calcul de l'occupation

Pour chaque `Departure` en statut `planned` ou `active`, on incrémente `count` pour chaque nuit comprise entre `planned_start_date` et `planned_end_date` où l'hébergement principal (`is_primary = true`) de l'étape du jour est occupé. Déclenché par toute modification de `Departure` ou des membres du Trip (`OccupancyObserver`). Table matérialisée en lecture seule.

### RG-03 — Visibilité du journal

| Lecteur | `private` | `members` | `public` |
| --- | --- | --- | --- |
| Auteur | Oui | Oui | Oui |
| Participant du même Trip | Non | Oui | Oui |
| Observateur du même Trip | Non | Non | Oui |
| Extérieur au Trip | Non | Non | Oui (si `Trip.is_public`) |
| Non authentifié | Non | Non | Non (V1) |

### RG-04 — Proxy GPX et photos

Tout binaire (GPX, photo) passe par le backend : (1) session valide, (2) contrôle de droit (membre du Trip / visibilité), (3) stream depuis MinIO, (4) headers `Content-Disposition: inline` + `Cache-Control: private, max-age=3600`. Jamais d'URL MinIO exposée.

### RG-05 — Sync offline du journal

Création offline stockée en IndexedDB (`is_synced = false`, `local_id` = UUID v4). Au retour réseau : `POST /journal/entries` avec `local_id` ; si connu, l'entrée existante est renvoyée (idempotence) ; sur conflit, last-write-wins sur `updated_at` serveur. Réponse : `{ id, local_id, synced_at }`.

### RG-06 — Simplification GPX

Traces brutes (~150 points) simplifiées par Douglas-Peucker (tolérance ~0,0001° ≈ 10 m) avant envoi à Leaflet. L'original MinIO reste intact ; la version simplifiée est mise en cache Redis 24h (ADR-U05).

### RG-07 — Invitation à un Trip

L'organisateur génère un `invite_token` (UUID v4, unique). L'invité suit `/trips/join/{token}` ; s'il n'a pas de session, il passe par le SSO. Après authentification, un `TripMember` est créé avec le rôle défini. Le token est à usage multiple mais **révocable** (mis à `null`).

### RG-08 — Hébergements obsolètes

Tout hébergement dont `verified_at` est antérieur à `NOW() - 6 mois` apparaît dans le tableau de bord Filament avec un badge orange « À vérifier » (`AccommodationsToVerifyWidget`). Re-validation en un clic (`verified_at = NOW()`).

---

## Données de seed (Belgique)

Les seeds proviennent des carnets réels du projet (Voie Mosane + Voie Monastique, Liège → Rocroi). Ils sont **idempotents** (`updateOrCreate` sur `slug` / `code`).

**Ordre d'exécution** (dépendances) :

```
1. RouteSeeder         → 2 routes (Via Mosana + Via Monastique)
2. WaypointSeeder      → ~22 waypoints (villes + POI)
3. StageSeeder         → 12 étapes (BE-01 → BE-12)
4. StagePOISeeder      → pivot stage_waypoint (POI par étape)
5. AccommodationSeeder → ~24 hébergements
6. MealSeeder          → ~12 repas signatures
7. GpxTraceSeeder      → import GPX depuis storage/seeds/gpx/ vers MinIO
8. PackScenarioSeeder  → 2 scénarios (Solo 8,5 kg + Duo 7,5 kg/pers)
9. PackItemSeeder      → ~35 items pesés par scénario
```

### Routes

| slug | name.fr | pays | distance_km |
| --- | --- | --- | --- |
| `via-mosana-belgique` | Voie Mosane | BE | 130,00 |
| `via-monastique-belgique` | Voie Monastique | BE | 74,00 |

### Étapes (12)

| code | name.fr | jour | km | D+ | difficulté | hébergement défaut |
| --- | --- | --- | --- | --- | --- | --- |
| BE-01 | Liège → Amay | 1 | 22,00 | 250 | moderate | gite |
| BE-02 | Amay → Huy | 2 | 11,00 | 150 | easy | gite |
| BE-03 | Huy → Andenne | 3 | 18,00 | 200 | moderate | gite |
| BE-04 | Andenne → Namur | 4 | 22,00 | 250 | moderate | hostel |
| BE-05 | Namur → Yvoir | 5 | 18,00 | 200 | moderate | camping |
| BE-06 | Yvoir → Dinant | 6 | 14,00 | 150 | easy | gite |
| BE-07 | Dinant → Hastière | 7 | 14,00 | 100 | easy | abbey |
| BE-08 | Hastière → Givet | 8 | 11,00 | 100 | easy | camping |
| BE-09 | Givet → Doische | 9 | 18,00 | 300 | moderate | gite |
| BE-10 | Doische → Olloy-sur-Viroin | 10 | 19,00 | 400 | hard | gite |
| BE-11 | Olloy → Couvin | 11 | 17,00 | 300 | moderate | gite |
| BE-12 | Couvin → Rocroi | 12 | 20,00 | 350 | moderate | gite |

### Waypoints — POI notables

| slug | name.fr | type | catégorie | détour | coût |
| --- | --- | --- | --- | --- | --- |
| `grotte-scladina-sclayn` | Grotte Scladina | poi | archaeology | medium (1,5 km) | 8 € |
| `rocroi-etoilee` | Rocroi étoilée (Vauban) | poi | fortress | on_path | — |
| `roche-a-lomme-dourbes` | Roche à Lomme | poi | archaeology | medium (3 km) | — |
| `passage-eau-waulsort` | Passage d'eau de Waulsort | poi | nature | medium (2 km) | — |
| `citadelle-namur` | Citadelle de Namur | poi | fortress | on_path | — |
| `forteresse-poilvache` | Forteresse de Poilvache | poi | fortress | medium (3 km) | 3 € |
| `grotte-neptune-petigny` | Grotte de Neptune | poi | nature | medium (2,5 km) | 7 € |

### Repas signatures (extraits)

| étape | type | name.fr | contexte |
| --- | --- | --- | --- |
| BE-04 | dinner | Escavèche de Meuse | restaurant |
| BE-06 | dinner | Flamiche + couque de Dinant | restaurant |
| BE-07 | dinner | Fromage d'abbaye Hastière | local_specialty |
| BE-11 | dinner | Jambon d'Ardennes | local_specialty |
| BE-12 | dinner | Cacasse ardennaise | restaurant |

### Traces GPX

12 traces `stage_main` (une par étape, `J01`→`J12`) + 6 détours (Grotte Scladina, Forteresse Poilvache, Passage d'eau Waulsort, Roche à Lomme, Grotte de Neptune, Château de Freÿr), toutes en `precision = approximate`, importées vers le bucket `ultreiataku-gpx`.

### Scénario de sac — Solo (extrait)

| catégorie | objet | poids (g) |
| --- | --- | --- |
| portage | Sac à dos Osprey Exos 48 | 1 100 |
| portage | Housse pluie Osprey Ultralight M | 90 |
| sleeping | Tente MSR Hubba NX 1 | 1 050 |
| sleeping | Matelas Therm-a-Rest NeoAir XLite NXT | 350 |
| sleeping | Sac de couchage Sea to Summit Spark Sp III | 550 |
| cooking | Réchaud MSR PocketRocket 2 | 75 |
| cooking | Popote Toaks Titanium 750 mL | 105 |
| water | Filtre Sawyer Squeeze | 90 |
| navigation | Batterie externe 10 000 mAh | 220 |

Deux scénarios seedés : **Solo 8,5 kg** et **Duo 7,5 kg/pers** (~35 items pesés au total).

---

## Références

- Specs fonctionnelles complètes : `reports/product/ultreiataku/2026-07-24_functional-specs.md`
- ADRs (occupation, MinIO, sync offline) : `reports/architecture/ultreiataku/2026-07-24_adrs.md`
- Architecture (ERD, flux) : [`ARCHITECTURE.md`](ARCHITECTURE.md)
- Référence API : [`API.md`](API.md)
