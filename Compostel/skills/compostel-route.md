---
name: compostel-route
description: Découpe des étapes du Chemin de Compostelle par tronçon (Belgique | France | Espagne). Produit distances, dénivelés, waypoints GPX, croisements de voies. En phase 1 : Voie Mosane + Voie Monastique de Liège à Rocroi.
model: claude-sonnet-4-6
tools: Read, Write, AskUserQuestion, Bash
type: framework
priority: high
scope: cross
dependencies: []
conflicts: []
---

# Compostel Route — Cartographie des étapes

Découpe le tracé du chemin en étapes journalières raisonnables, avec dénivelé estimé, waypoints principaux et références GPX. Chaque étape doit être adaptée à un marcheur qui a déjà 200-500 km dans les jambes (adaptation progressive en début de tronçon).

## Directives — Non négociables

| Directive | Application |
|-----------|-------------|
| **Journées 15-22 km** | 25 km max en pleine forme, jamais deux 25 km d'affilée |
| **Adaptation** | Premiers 3 jours d'un tronçon = étapes courtes (12-16 km) |
| **Dénivelé compte** | +500 m D+ = -3 km de distance équivalente |
| **Waypoints, pas traces** | Générer des GPX de waypoints (villes, points d'eau, gîtes) — traces continues via sources officielles |
| **Balisage officiel** | Coquille + flèche jaune ; renvoyer aux associations pour la fraîcheur du balisage |

## Workflow

### Phase 1 — Paramètres

Collecter via AskUserQuestion si non fourni :
1. Tronçon (Belgique | France | Espagne)
2. Voie précise si plusieurs options (ex. Voie de Vézelay vs Le Puy en France)
3. Rythme cible (relaxed 15-18 km/j | standard 18-22 km/j | rapide 22-27 km/j)
4. Jours de repos souhaités (0 | 1 par semaine | 2 par semaine)

### Phase 2 — Table maîtresse

Produire la table des étapes avec pour chaque jour :

| Colonne | Contenu |
|---------|---------|
| Jour | J01, J02… |
| De → À | Villes de départ et d'arrivée |
| Distance | km, arrondi au km |
| Dénivelé | +xxx m / -xxx m estimé |
| Voie | Nom de la voie/étape |
| Nuit prévisible | Type (gîte/bivouac) + suggestion |
| POI clé | 1 seul point majeur pour ne pas surcharger |

### Phase 3 — Fiches détaillées

Une fiche par étape dans `etapes/JXX_ville-ville.md` ou une seule `etapes/etapes-<tronçon>.md` compacte. Chaque fiche contient :

- Descriptif du parcours (paysages, difficultés, portions techniques)
- Balisage à suivre
- Points d'eau connus (fontaines, cimetières, cafés)
- Options de raccourci / rallongement si mauvais temps
- Où déjeuner (croisement avec `compostel-food`)
- Où dormir (croisement avec `compostel-lodging`)
- Ce qu'il faut voir (croisement avec `compostel-poi`)

### Phase 4 — GPX

Générer `gpx/waypoints-<tronçon>.gpx` — waypoints des villes-étapes + points remarquables au format GPX 1.1 (lisible dans Komoot, Locus, Gaia, Garmin).

Structure XML :
```xml
<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1" creator="compostel-route" xmlns="http://www.topografix.com/GPX/1/1">
  <wpt lat="LAT" lon="LON">
    <name>NOM</name>
    <desc>DESC + kilométrage cumulé</desc>
    <sym>Waypoint</sym>
  </wpt>
  ...
</gpx>
```

Produire aussi `gpx/README.md` avec :
- Explication : ces GPX sont des waypoints, PAS des traces
- Liens vers les sources officielles pour télécharger les traces continues :
  - Belgique : https://www.st-jacques.be/ (Association Belge des Amis de Saint-Jacques)
  - France : https://www.compostelle.asso.fr/ (ACIR) + traces FFRandonnée GR
  - Espagne : https://oficinadelperegrino.com/ + gronze.com

### Phase 5 — Croisements

Vérifier que les livrables sont cohérents avec les autres skills :
- Chaque étape a une suggestion de nuit → cf. `compostel-lodging`
- Chaque étape a une suggestion repas → cf. `compostel-food`
- Chaque étape a un POI → cf. `compostel-poi`

## Base de données — Tronçon Belgique (Liège → Rocroi)

Voies : **Voie Mosane** (Liège → Givet) + **Voie Monastique** (Givet → Rocroi).

Villes obligatoires (imposées par l'utilisateur) : Liège, Huy, Andenne, Namur, Dinant, Hastière, Givet, Doische, Olloy-sur-Viroin, Couvin, Rocroi.

Découpe recommandée (12 étapes, ~204 km) :

| J | De → À | Km | D+ | Nuit type | POI clé |
|---|--------|-----|-----|-----------|---------|
| 1 | Liège → Amay | 22 | 250 | Gîte/AJ | Collégiale Amay |
| 2 | Amay → Huy | 11 | 150 | Gîte paroissial | Collégiale + Li Rondia |
| 3 | Huy → Andenne | 18 | 200 | Bivouac RAVeL | Collégiale Sainte-Begge |
| 4 | Andenne → Namur | 22 | 250 | Gîte AJ Namur | Citadelle |
| 5 | Namur → Yvoir | 18 | 200 | Bivouac Meuse | Vallée mosane |
| 6 | Yvoir → Dinant | 14 | 150 | Gîte paroissial | Rochers Bayard + Sax |
| 7 | Dinant → Hastière | 14 | 100 | Gîte abbaye | Abbaye romane |
| 8 | Hastière → Givet | 11 | 100 | Camping/gîte | Fort de Charlemont |
| 9 | Givet → Doische | 18 | 300 | Bivouac ou gîte | Retour Wallonie, Fagne |
| 10 | Doische → Olloy-sur-Viroin | 19 | 400 | Bivouac Viroin | Réserve Viroin-Hermeton |
| 11 | Olloy → Couvin | 17 | 300 | Gîte/bivouac | Grotte de Neptune |
| 12 | Couvin → Rocroi | 20 | 350 | Gîte donativo Rocroi | Place Vauban étoilée |

## Fallback

Si l'utilisateur souhaite une adaptation (blessure, météo hostile, envie de traîner à Namur) : proposer un rebalancement (fusion d'étapes courtes, coupure d'étapes longues, jour de repos).

## Entrée optionnelle

- Tronçon (`belgique` | `france` | `espagne`)
- Voie précise (`voie-mosane` | `via-podiensis` | `camino-frances` | ...)
- Rythme (`relaxed` | `standard` | `rapide`)
- Contrainte (`avec-enfant`, `blessure-genou`, `weekend-only`)
