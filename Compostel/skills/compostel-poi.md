---
name: compostel-poi
description: Points d'intérêt sur le Chemin de Compostelle — patrimoine religieux, patrimoine civil (Vauban, châteaux), paysages remarquables, curiosités naturelles. Aide à ne rien manquer entre deux étapes.
model: claude-sonnet-4-6
tools: Read, Write, AskUserQuestion
type: framework
priority: medium
scope: cross
dependencies: []
conflicts: []
---

# Compostel POI — Ce qu'il faut voir sur le Chemin

Recense les points remarquables le long du tracé, catégorisés et associés à leur étape.

## Directives — Non négociables

| Directive | Application |
|-----------|-------------|
| **Sur le chemin ou à ≤ 1 km** | Ne pas proposer de détour > 3 km sauf exception majeure |
| **Priorité UNESCO / classés** | Signaler les Patrimoines Mondiaux, monuments historiques, Vauban |
| **Ouverture vérifiable** | Mentionner horaires + jour de fermeture connus (à vérifier par utilisateur avant visite) |
| **Émerveillement > exhaustivité** | 1-3 POI par étape, pas 15 |
| **Pèlerin friendly** | Signaler les églises tamponnant la credencial, les fontaines miraculeuses, les vitraux jacquaires |

## Workflow

### Phase 1 — Paramètres

Collecter via AskUserQuestion si non fourni :
1. Tronçon (Belgique | France | Espagne)
2. Focus : religieux prioritaire | civil/histoire | nature/paysages | mixte

### Phase 2 — Catalogue par étape

Produire `poi/patrimoine-<tronçon>.md` avec pour chaque étape :

```
### JXX — Ville X

**Monument religieux**
- Nom + siècle
- Ce qu'il faut voir (rosace, tympan, crypte, relique jacquaire, coquille sculptée)
- Horaires + jour de fermeture connu
- Tampon credencial possible : ✅/❌

**Monument civil**
- Nom + intérêt (fortification, hôtel de ville, marché couvert)

**Paysage remarquable**
- Point de vue + orientation + moment idéal (matin/soir)

**Curiosité / secret local**
- Chose qu'un simple touriste rate (fresque cachée, fontaine, sentier de traverse, atelier d'artisan)
```

### Phase 3 — Fils thématiques

Produire aussi un rappel transversal (fils rouges) en tête du document :

- **Fil roman/gothique** : la Meuse regorge d'églises romanes remarquables (Amay, Hastière, Doische, Waulsort)
- **Fil Vauban** : Givet, Rocroi, Sedan (hors tronçon 1 mais utile)
- **Fil légendes** : Rochers Bayard (quatre fils Aymon), ourses d'Andenne, Sax à Dinant
- **Fil eau** : sources et fontaines miraculeuses jacquaires

## Base de données — Tronçon Belgique

### J1 · Liège
- **Cathédrale Saint-Paul** (XIIIe) — trésor + reliquaire, tampon possible sacristie
- **Perron liégeois** (place du Marché) — symbole des libertés
- **Coteaux de la Citadelle** — 400 marches Bueren, vue Meuse
- Curiosité : quartier Outremeuse, bar Le Vaudrée

### J1/2 · Amay
- **Collégiale Sainte-Ode et Saint-Georges** (XIe roman + châsse de Sainte-Ode)
- Musée archéologique + fouilles mérovingiennes

### J2 · Huy
- **Collégiale Notre-Dame** (XIVe gothique flamboyant) — la rosace **Li Rondia** (9 m de diamètre, 4e plus grande de Belgique)
- **Citadelle de Huy** — Fort militaire XIXe, mémorial WW2, vue Meuse
- Curiosité : les 4 merveilles de Huy — Li Rondia, Li Bassinia (fontaine), Li Pontia (pont), Li Tchestia (citadelle)

### J3 · Andenne
- **Collégiale Sainte-Begge** (fondée VIIe par sainte Begge, mère de Pépin de Herstal)
- Musée de la Céramique
- Curiosité : légende des Ours d'Andenne, fêtes carnavalesques

### J4 · Namur
- **Citadelle de Namur** — l'une des plus grandes d'Europe, panoramas confluent
- **TreM.a** — Musée des Arts Anciens du Namurois
- **Cathédrale Saint-Aubain** (XVIIIe classique) + trésor
- **Confluent Sambre-Meuse** — sculpture "Le Grognon"
- Curiosité : escargots de Namur, statuette du Namurois "à cheval"

### J5 · Vallée mosane (Profondeville / Yvoir)
- **Château de Poilvache** (ruines XIIIe) — sur les hauteurs
- **Rochers de Freÿr** — falaises calcaires, réserve naturelle
- **Château de Freÿr** (XVIe, jardins classés)
- Paysage : la Meuse encaissée, l'un des plus beaux panoramas de Wallonie

### J6 · Dinant
- **Collégiale Notre-Dame** (XIIIe) — bulbe caractéristique en plomb
- **Rochers Bayard** — pyramide rocheuse fendue, légende des quatre fils Aymon
- **Maison Sax** — musée Adolphe Sax (inventeur du saxophone)
- **Citadelle de Dinant** — téléphérique + escalier de 408 marches
- **Grotte la Merveilleuse** — cristallisations souterraines
- Curiosité : la couque de Dinant (biscuit)

### J7 · Hastière
- **Abbaye Notre-Dame d'Hastière** (fondation XIe, style roman mosan) — l'une des plus anciennes églises romanes de Belgique
- Crypte pré-romane, arts sacrés

### J8 · Givet (France)
- **Fort de Charlemont** (Vauban XVIIe) — panorama vallée Meuse
- **Église Saint-Hilaire** (XVIIe classique)
- **Tour Grégoire** (donjon médiéval)
- Curiosité : ville natale d'Étienne Nicolas Méhul (compositeur)

### J9 · Doische
- **Église romane** (village typique Fagne)
- **Chapelle Notre-Dame de Walcourt** (à croiser à proximité, célèbre Trinitaires)
- Paysage : entrée dans la Fagne — plateaux, tourbières, silence

### J10 · Olloy-sur-Viroin / Viroinval
- **Réserve naturelle Viroin-Hermeton** — l'une des plus riches de Wallonie (calcaires, pelouses sèches, orchidées)
- **Château de Vierves-sur-Viroin** (XVe)
- **Ruines du Château d'Hierges** (à proximité)
- Paysage : vallée sauvage, aigles bottés en été

### J11 · Couvin
- **Grotte de Neptune** (Petigny) — rivière souterraine navigable en barque
- **Nismes** (village voisin) — brasserie des Fagnes (visite + dégustation)
- **Église Saint-Germain** (XVIIIe)

### J12 · Rocroi (France)
- **Place d'armes en étoile** — chef-d'œuvre Vauban, ville étoilée à cinq branches
- **Remparts** — circuit complet praticable à pied
- **Musée de la Bataille de Rocroi** (1643, Grand Condé)
- Curiosité : première ville étoilée classée MH intégralement

## Fallback

Aucune dépendance externe — si les infos horaires évoluent, ajouter un disclaimer « à vérifier par l'utilisateur, base de données 2025 ».

## Entrée optionnelle

- Tronçon
- Focus (religieux | civil | nature | mixte)
- Niveau détail (compact 1 par étape | standard 3 par étape | exhaustif)
