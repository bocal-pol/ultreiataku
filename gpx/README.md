# GPX — Chemin de Compostelle · Belgique + France

## Ce que contient ce dossier

### Tronçon Belgique (Liège → Rocroi)

| Fichier | Contenu | Précision |
|---------|---------|-----------|
| `waypoints-belgique.gpx` | 22 waypoints (villes-étapes + POI majeurs) | Exact (coordonnées publiques vérifiées) |
| `trace-belgique.gpx` ⭐ | **Trace continue reconstituée** — 12 étapes + 6 détours + waypoints | **Approximative — voir avertissement ci-dessous** |
| `liens-traces-officielles-belgique.md` | Sources fiables pour la trace exacte mètre par mètre | — |

### Tronçon France (Rocroi → Saint-Jean-Pied-de-Port)

| Fichier | Contenu | Précision |
|---------|---------|-----------|
| `waypoints-france.gpx` | ~45 waypoints villes/POI + **boucle Faux de Verzy** (4 km) + liaison Verzy | Exact (villes) / bon (boucle Verzy) |
| `trace-france.gpx` | **Trace reconstituée** — 16 segments (65 étapes) par villes-étapes, variante Oradour incluse | **Approximative** — vue d'ensemble seulement (résolution ~5-15 km entre points) |
| `liens-traces-officielles-france.md` | Trace Peregrinos France (id=890940) + GR654 FFRandonnée + Via Campaniensis | — |

### Tronçon Espagne (SJPP → Santiago, Camino del Norte)

| Fichier | Contenu | Précision |
|---------|---------|-----------|
| `waypoints-espagne.gpx` | ~40 waypoints villes/POI (Île des Faisans, Faro del Caballo, Altamira, Obona…) | Exact (villes) |
| `trace-espagne.gpx` | **Trace reconstituée** — 10 segments (46 étapes) + détour Faro del Caballo | **Approximative** — vue d'ensemble |
| `liens-traces-officielles-espagne.md` | Gronze (référence Norte) + apps + **réservations obligatoires** (Altamira, Tito Bustillo, As Catedrais) | — |

**Le même avertissement s'applique aux trois traces reconstituées** : elles servent à visualiser le parcours et l'ordre des villes, PAS à naviguer en forêt. La navigation précise = traces officielles (Peregrinos pour BE/FR, GR654 balisé blanc-rouge, flèches jaunes + GPX Gronze pour le Norte).

## ⚠️ Avertissement honnête sur `trace-belgique.gpx`

Cette trace est une **reconstitution basée sur** :
- Le tracé du **RAVeL 1** le long de la Meuse (Liège → Givet), très proche de la Voie Mosane sur toute cette portion
- Les **villes-étapes obligatoires** que tu m'as données (Liège, Huy, Andenne, Namur, Dinant, Hastière, Givet, Doische, Olloy-sur-Viroin, Couvin, Rocroi)
- Ma connaissance des **points intermédiaires** (Ombret, Ampsin, Bas-Oha, Namêche, Sclayn, Marche-les-Dames, Wépion, Anseremme, Foisches, Vaucelles, Matagnes, Dourbes, Nismes, Frasnes, Brûly-de-Pesche, Regniowez…)
- Les **6 détours** vers les POI majeurs (Scladina, Poilvache, Château-Thierry vue, Roche à Lomme, Grotte de Neptune, Freÿr)

**Précision** :
- **Étapes principales** : ~1 km de résolution entre points (10-16 track points par jour)
- **Erreur latérale** : quelques centaines de mètres à quelques km selon les zones (bonne le long du RAVeL, moins bonne sur la Voie Monastique en Fagne)
- **Usage** : navigation d'ensemble, aperçu du parcours, ordre des villes. **Pas suffisant pour ne pas te perdre** dans une forêt dense sans balisage.

**Ce que la trace ne remplace pas** :
- Le tracé **officiel Peregrinos** sur calculitineraires.fr — 232 km, tracé mètre par mètre par un pèlerin réel qui a fait le chemin
- Le tracé **Vlaams Compostelagenootschap** (association flamande, créatrice de la Via Monastica)
- Le tracé **verscompostelle.be** (association wallonne)

## Comment obtenir la trace exacte (recommandation)

1. **Aller sur** https://www.calculitineraires.fr/?id=890923
2. **Cliquer sur** « Télécharger l'itinéraire » (menu latéral)
3. **Choisir le format** :
   - **GPX Trace** : trace continue, points denses — le meilleur pour GPS de rando
   - **GPX Route** : version simplifiée avec waypoints, plus légère
   - **KML** : pour Google Earth
   - **TCX** : pour montres Garmin/Coros
4. **Sauvegarder** le fichier sur ton téléphone
5. **Ouvrir avec Locus Map / OsmAnd / Gaia GPS** — importation automatique

## Utilisation combinée

**Ordre d'importation recommandé** dans ton application GPS :

1. D'abord importer le **`trace-belgique.gpx`** de ce dossier — te donne une **vue d'ensemble immédiate** avec les 12 étapes visibles distinctement et les détours en surcouche
2. Puis importer la **trace officielle Peregrinos** téléchargée sur calculitineraires.fr — te donne la précision exacte pour la marche
3. Superposer les deux dans ton app — tu vois si des divergences existent (parfois oui, la trace Peregrinos peut passer côté opposé de la Meuse à certains endroits)

## Structure du fichier `trace-belgique.gpx`

Le fichier contient **plusieurs `<trk>` (tracks)** distincts :

**Étapes principales** :
- J01 - Liège → Amay (22 km) — 16 points
- J02 - Amay → Huy (11 km) — 10 points
- J03 - Huy → Andenne (18 km) — 14 points
- J04 - Andenne → Namur (22 km) — 13 points
- J05 - Namur → Yvoir (18 km) — 10 points
- J06 - Yvoir → Dinant (14 km) — 9 points
- J07 - Dinant → Hastière (14 km) — 8 points
- J08 - Hastière → Givet (11 km) — 8 points
- J09 - Givet → Doische (18 km) — 9 points
- J10 - Doische → Olloy-sur-Viroin (19 km) — 12 points
- J11 - Olloy → Couvin (17 km) — 9 points
- J12 - Couvin → Rocroi (20 km) — 11 points

**Détours** (chacun avec point de départ = point du track principal correspondant, point d'arrivée = POI) :
- DÉTOUR — Grotte Scladina (Sclayn) — J03
- DÉTOUR — Forteresse Poilvache — J05
- DÉTOUR — Château-Thierry vue extérieure (Falmignoul) — J07
- DÉTOUR — Roche à Lomme (Dourbes) — J10
- DÉTOUR — Grotte de Neptune (Petigny) — J11
- DÉTOUR OPTIONNEL — Château de Freÿr (rive gauche) — J06 ou J07

**Waypoints** : 13 points d'intérêt = villes-étapes + départ + arrivée.

## Apps GPS recommandées

| App | Force | Offline | Prix |
|-----|-------|---------|------|
| **Locus Map Pro** (Android) | Import GPX facile, cartes IGN/OSM, layers | ✅ | 8 €/an |
| **OsmAnd+** | Open source, cartes détaillées | ✅ | Gratuit/donation |
| **Komoot** | UI moderne, communauté | ✅ (pack payant) | Pack région ~4 € |
| **Gaia GPS** (iOS) | Topo précises, superpositions | ✅ (premium) | 40 €/an |

## Notes fraîcheur balisage

Le balisage du sentier (coquille jaune + flèche) est entretenu par des bénévoles. Il peut manquer sur certaines portions (travaux, vandalisme, chablis). Le GPS reste le filet de sécurité.

**Fais confiance au balisage quand il est présent, au GPS quand il ne l'est plus.**

## À faire avant le départ

- [ ] Télécharger la trace officielle Peregrinos + sauvegarde locale
- [ ] Cross-checker avec une seconde source (verscompostelle.be OU compostelagenootschap.be)
- [ ] Télécharger cartes offline (Wallonie + département Ardennes FR) dans l'app choisie
- [ ] Importer `trace-belgique.gpx` (repère d'ensemble) + trace officielle (précision)
- [ ] Test grandeur nature : import du GPX + navigation sur 5 km avant départ
- [ ] Prévoir batterie externe (fond de sac) pour rechargement GPS/téléphone

## Sources consultées

- [Peregrinos - Via Mosana + Monastica sur Calcul Itinéraires](https://www.calculitineraires.fr/?id=890923)
- [Vers Compostelle - Guy Coppieters](https://verscompostelle.be/coguibel.htm)
- [Vlaams Compostelagenootschap](https://www.compostelagenootschap.be/)
- [FFRandonnée - Article Via Monastica](https://www.ffrandonnee.fr/s-informer/actualites/la-via-monastica-le-chemin-vers-compostelle-en-belgique)
- [RAVeL Wallonie](https://ravel.wallonie.be/)
