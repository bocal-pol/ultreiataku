# Référence API — Ultreiataku

Référence des endpoints REST du module `Pilgrimage`, tels qu'exposés par `backend/app/Modules/Pilgrimage/Routes/api.php`.

- **Préfixe commun** : `/api/pilgrimage`
- **Format** : JSON UTF-8
- **Authentification** : cookies de session (SSO). Les routes protégées passent par les middlewares `web` (démarre la session) + `auth` (guard `web`). Le SPA doit envoyer les cookies via `credentials: 'include'`. **Aucun Bearer token** n'est utilisé.
- **Erreurs** : réponses JSON avec `message` (et `errors` sur validation 422).

> Convention de la colonne **Auth** : « Public » = lecture sans session ; « Session » = cookie de session valide requis ; les contrôles fins (membre du Trip, auteur, visibilité) sont appliqués par les Policies côté contrôleur.

---

## Sommaire

- [1. Chemin — lecture publique](#1-chemin--lecture-publique)
- [2. Traces GPX](#2-traces-gpx)
- [3. Pèlerin courant et RGPD](#3-pèlerin-courant-et-rgpd)
- [4. Trips, membres, départs, occupation](#4-trips-membres-départs-occupation)
- [5. Le Sac](#5-le-sac)
- [6. Carnet de voyage](#6-carnet-de-voyage)
- [7. Endpoints RGPD (récapitulatif)](#7-endpoints-rgpd-récapitulatif)

---

## 1. Chemin — lecture publique

Ces endpoints ne requièrent pas de session : ils servent le contenu du Chemin (routes, étapes, waypoints, hébergements, repas), consommé y compris hors connexion via le cache du Service Worker.

| Méthode | Route | Auth | Description |
| --- | --- | --- | --- |
| GET | `/api/pilgrimage/routes` | Public | Liste des routes (itinéraires). Filtrable par pays. |
| GET | `/api/pilgrimage/routes/{slug}` | Public | Détail d'une route + étapes ordonnées. |
| GET | `/api/pilgrimage/stages` | Public | Liste des étapes. Filtrable par route / pays. |
| GET | `/api/pilgrimage/stages/{code}` | Public | Détail d'une étape (`BE-01`) avec waypoints, hébergements, repas, traces GPX — payload conçu pour l'offline. |
| GET | `/api/pilgrimage/waypoints` | Public | Liste des waypoints / POI. Filtrable par type / catégorie. |
| GET | `/api/pilgrimage/waypoints/{slug}` | Public | Détail d'un waypoint. |
| GET | `/api/pilgrimage/accommodations` | Public | Liste des hébergements. Filtrable par étape / type / bivouac légal. |
| GET | `/api/pilgrimage/accommodations/{id}` | Public | Détail d'un hébergement. |
| GET | `/api/pilgrimage/meals` | Public | Liste des repas. Filtrable par étape / type de repas. |
| GET | `/api/pilgrimage/meals/{id}` | Public | Détail d'un repas. |

**Paramètres de requête usuels** : `?filter[country]=BE`, `?filter[route_id]={uuid}`, `?include=waypoints,accommodations,meals,gpx_traces`.

### Exemple — `GET /api/pilgrimage/stages/BE-07`

```json
{
  "data": {
    "id": "9b1f...c3",
    "code": "BE-07",
    "name": { "fr": "Dinant → Hastière" },
    "day_number": 7,
    "distance_km": 14.0,
    "elevation_gain_m": 100,
    "difficulty": "easy",
    "accommodations": [
      {
        "id": "...",
        "name": { "fr": "Gîte Abbaye Hastière" },
        "type": "abbey",
        "is_primary": true,
        "price_min_eur": 15.0,
        "stamps_credencial": true
      }
    ],
    "meals": [
      { "meal_type": "dinner", "name": { "fr": "Fromage d'abbaye Hastière" }, "meal_context": "local_specialty" }
    ],
    "gpx_traces": [
      { "id": "...", "trace_type": "stage_main", "distance_km": 14.0, "precision": "approximate" }
    ]
  }
}
```

---

## 2. Traces GPX

Servies par proxy backend (RG-04) : jamais d'URL MinIO directe. La version simplifiée (Douglas-Peucker, RG-06) est mise en cache Redis 24h.

| Méthode | Route | Auth | Description |
| --- | --- | --- | --- |
| GET | `/api/pilgrimage/gpx/{id}` | Session | Stream binaire de la trace GPX (`application/gpx+xml`), depuis MinIO `minio_gpx`. |
| GET | `/api/pilgrimage/gpx/{id}/simplified` | Session | GeoJSON simplifié pour Leaflet (cache Redis, `Cache-Control: public, max-age=3600`). |

### Exemple — `GET /api/pilgrimage/gpx/{id}/simplified`

```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": { "type": "LineString", "coordinates": [[5.5718, 50.6458], [5.32, 50.55]] },
      "properties": { "distance_km": 22.0, "elevation_gain_m": 252, "points_original": 160, "points_simplified": 24 }
    }
  ]
}
```

---

## 3. Pèlerin courant et RGPD

| Méthode | Route | Auth | Description |
| --- | --- | --- | --- |
| GET | `/api/pilgrimage/me` | Session | Utilisateur SSO courant + son profil `Pilgrim` (créé automatiquement au premier accès). |
| GET | `/api/pilgrimage/me/export` | Session | **RGPD Art. 20** — export JSON de portabilité (profil, trips, départs, scénarios de sac, entrées de journal + métadonnées photos, sans binaire). |
| DELETE | `/api/pilgrimage/me` | Session | **RGPD Art. 17** — droit à l'oubli : supprime le profil et cascade (journal, photos, sac, départs, memberships) + purge MinIO asynchrone. |

**Garde métier sur la suppression** : refusée (`422`) si le pèlerin est organisateur d'au moins un Trip en statut `planned` ou `active`. Il doit d'abord transférer l'organisation ou annuler ces Trips.

### Exemple — `GET /api/pilgrimage/me`

```json
{
  "user": { "id": 42, "name": "Pascal D.", "email": "pascal@example.be" },
  "pilgrim": {
    "id": "...",
    "display_name": "Pascal D.",
    "preferred_locale": "fr",
    "configuration": "solo",
    "target_base_weight_kg": 8.5
  }
}
```

### Exemple — `GET /api/pilgrimage/me/export` (extrait)

```json
{
  "export_date": "2027-05-16T09:30:00Z",
  "pilgrim": { "id": "...", "display_name": "Pascal D.", "configuration": "solo" },
  "trips": [{ "id": "...", "name": "Belgique Mai 2027", "role": "organizer", "is_organizer": true }],
  "departures": [{ "id": "...", "planned_start_date": "2027-05-10", "status": "planned" }],
  "pack_scenarios": [{ "id": "...", "name": "Solo 8,5 kg Belgique" }],
  "journal_entries": [{ "id": "...", "entry_date": "2027-05-16", "visibility": "public", "photos": [] }]
}
```

---

## 4. Trips, membres, départs, occupation

Toutes ces routes exigent une session. Les Policies distinguent organizer / participant / observer.

| Méthode | Route | Auth | Description |
| --- | --- | --- | --- |
| GET | `/api/pilgrimage/trips` | Session | Trips dont le pèlerin courant est organisateur ou membre. |
| POST | `/api/pilgrimage/trips` | Session | Créer un Trip. |
| GET | `/api/pilgrimage/trips/{id}` | Session (membre) | Détail d'un Trip. |
| POST | `/api/pilgrimage/trips/join/{token}` | Session | Rejoindre un Trip via un token d'invitation (RG-07). Redirige vers le SSO si non authentifié. |
| POST | `/api/pilgrimage/trips/{id}/members` | Session (organizer) | Ajouter un membre. |
| DELETE | `/api/pilgrimage/trips/{id}/members/{pilgrimId}` | Session (organizer) | Retirer un membre. Body `journal_action={keep\|remove}` (RGPD-U03) décide du sort de ses entrées. |
| POST | `/api/pilgrimage/trips/{id}/departures` | Session (membre) | Créer un départ planifié. |
| GET | `/api/pilgrimage/trips/{id}/occupancy` | Session (membre) | Occupation prévue des hébergements du Trip (dérivée, RG-02 / ADR-U03). |
| POST | `/api/pilgrimage/trips/{id}/invite-token` | Session (organizer) | Générer / régénérer le token d'invitation. |
| DELETE | `/api/pilgrimage/trips/{id}/invite-token` | Session (organizer) | Révoquer le token (le met à `null`). |
| POST | `/api/pilgrimage/trips/{id}/invite-email` | Session (organizer) | Envoyer l'invitation par email (mailable fr/nl/de). |

### Exemple — `POST /api/pilgrimage/trips`

Requête :

```json
{ "name": "Belgique Mai 2027", "route_id": "...", "configuration": "duo", "estimated_start_date": "2027-05-10" }
```

Réponse `201` :

```json
{ "data": { "id": "...", "name": "Belgique Mai 2027", "status": "planned", "organizer_id": "..." } }
```

### Exemple — `GET /api/pilgrimage/trips/{id}/occupancy`

```json
{
  "data": [
    { "accommodation_id": "...", "date": "2027-05-14", "count": 2 },
    { "accommodation_id": "...", "date": "2027-05-15", "count": 2 }
  ]
}
```

---

## 5. Le Sac

| Méthode | Route | Auth | Description |
| --- | --- | --- | --- |
| GET | `/api/pilgrimage/pilgrims/{pilgrimId}/pack-scenarios` | Session | Scénarios de sac d'un pèlerin. |
| POST | `/api/pilgrimage/pack-scenarios` | Session | Créer un scénario. |
| GET | `/api/pilgrimage/pack-scenarios/{id}` | Session (owner ou trip) | Détail : items groupés par catégorie + poids calculés (RG-01). |
| PUT | `/api/pilgrimage/pack-scenarios/{id}` | Session (owner) | Mettre à jour un scénario. |
| POST | `/api/pilgrimage/pack-scenarios/{id}/items` | Session (owner) | Ajouter un objet au scénario. |
| POST | `/api/pilgrimage/departures/{id}/assignments` | Session (organizer ou owner) | Assigner un objet à un pèlerin sur un tronçon (duo). |

### Exemple — `GET /api/pilgrimage/pack-scenarios/{id}`

```json
{
  "data": {
    "id": "...",
    "name": "Solo 8,5 kg Belgique",
    "target_base_weight_kg": 8.5,
    "base_weight_kg": 8.34,
    "total_weight_kg": 11.09,
    "weight_status": "green",
    "items_by_category": {
      "portage": [{ "name": "Sac à dos Osprey Exos 48", "weight_g": 1100 }],
      "sleeping": [{ "name": "Tente MSR Hubba NX 1", "weight_g": 1050 }]
    }
  }
}
```

---

## 6. Carnet de voyage

Sync offline (ADR-U04 / RG-05) : la création est **idempotente** sur `local_id` (UUID v4 généré côté client). Un `local_id` déjà connu renvoie l'entrée existante (`200`) au lieu d'en créer une nouvelle (`201`). En cas de conflit, last-write-wins sur `updated_at`.

| Méthode | Route | Auth | Description |
| --- | --- | --- | --- |
| GET | `/api/pilgrimage/trips/{id}/journal` | Session (membre) | Entrées du Trip filtrées par la visibilité du lecteur (RG-03), pagination curseur. |
| POST | `/api/pilgrimage/journal/entries` | Session | Créer une entrée (idempotent sur `local_id`). |
| GET | `/api/pilgrimage/journal/entries/{entryId}` | Session (selon RG-03) | Détail d'une entrée. |
| PUT | `/api/pilgrimage/journal/entries/{entryId}` | Session (auteur) | Modifier une entrée. |
| DELETE | `/api/pilgrimage/journal/entries/{entryId}` | Session (auteur) | Supprimer une entrée. |
| POST | `/api/pilgrimage/journal/entries/{entryId}/photos` | Session (auteur) | Upload d'une photo (multipart). EXIF strippé, stockage MinIO `minio_journal`. |
| GET | `/api/pilgrimage/journal/photos/{id}` | Session (selon RG-03) | Stream binaire de la photo par proxy MinIO. Jamais d'URL directe. |
| DELETE | `/api/pilgrimage/journal/photos/{id}` | Session (auteur) | Supprimer une photo (DB + MinIO). |
| PATCH | `/api/pilgrimage/journal/photos/{id}/revoke-location` | Session (auteur) | **RGPD-U05** — révoquer les coordonnées GPS d'une photo (Art. 17 partiel). |

### Exemple — `POST /api/pilgrimage/journal/entries`

Requête :

```json
{
  "local_id": "3f2504e0-4f89-41d3-9a0c-0305e82c3301",
  "trip_id": "...",
  "stage_id": "...",
  "title": "Le bac de Waulsort",
  "body": "Ce matin, à Waulsort, nous avons traversé la Meuse…",
  "entry_date": "2027-05-16",
  "visibility": "public",
  "mood": "great",
  "km_walked_today": 14.0
}
```

Réponse `201` (ou `200` si `local_id` déjà synchronisé) :

```json
{ "id": "9b1f...c3", "local_id": "3f2504e0-4f89-41d3-9a0c-0305e82c3301", "synced_at": "2027-05-16T18:02:11Z" }
```

---

## 7. Endpoints RGPD (récapitulatif)

| Droit | Article | Endpoint | Effet |
| --- | --- | --- | --- |
| Portabilité | Art. 20 | `GET /api/pilgrimage/me/export` | Export JSON complet des données du pèlerin courant. |
| Effacement | Art. 17 | `DELETE /api/pilgrimage/me` | Suppression du profil + cascade + purge MinIO async (bloquée si organisateur d'un Trip actif). |
| Effacement partiel | Art. 17 | `PATCH /api/pilgrimage/journal/photos/{id}/revoke-location` | Suppression des coordonnées GPS d'une photo. |
| Retrait d'un membre | — | `DELETE /api/pilgrimage/trips/{id}/members/{pilgrimId}` | `journal_action={keep\|remove}` décide du sort de ses entrées de journal. |
