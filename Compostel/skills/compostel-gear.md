---
name: compostel-gear
description: Sac à dos pesé pour pèlerinage Compostelle 2500 km avec bivouac. Deux variantes (Solo, Duo). Base weight ≤ 8 kg solo / ≤ 6,5 kg par personne en duo. Objectif durabilité 2500 km, réparabilité, remplacement consommables planifié. Délègue à christophe-agent pour la Règle des 3 et le calcul kcal.
model: claude-sonnet-4-6
tools: Read, Write, AskUserQuestion, Skill
type: framework
priority: high
scope: cross
dependencies:
  - christophe-agent
conflicts: []
---

# Compostel Gear — Sac à dos pèlerin ultra-long

Génère une liste de matériel **pesée**, **catégorisée**, **calibrée pour 2500 km**. Priorité : durabilité et poids. Un item ajouté doit être justifié par la Règle des 3 (Christophe) ou par la mission pèlerinage (marche 20 km/j 4 mois d'affilée).

## Directives — Non négociables

| Directive | Application |
|-----------|-------------|
| **Base weight ≤ 8 kg solo** | Hors eau + nourriture + consommables jour |
| **Durabilité 2500 km** | Chaussures haut de gamme rodées ; sac renforcé ; tente pas premier prix |
| **Réparabilité** | Kit couture, sparadrap toilé, Tenacious Tape, colle Aquaseal |
| **Remplacement planifié** | 2e paire de chaussures prévue en France ou Espagne, credenciales de rechange |
| **Mutualisation Duo** | Une tente à deux, un réchaud à deux, une popote à deux → 1,5 kg gagnés par personne |
| **Test terrain obligatoire** | Sortie 25 km avec sac chargé avant départ officiel |

## Workflow

### Phase 1 — Paramètres

Collecter via AskUserQuestion si non fourni :
1. Configuration : Solo | Duo (générer les deux si demandé)
2. Saison de départ : mai-juin | juillet-août | sept-oct | autre
3. Budget matériel : serré (< 1000 €) | moyen (1000-2000 €) | confort (> 2000 €)
4. Matériel déjà possédé (audit) : oui + liste | non, sac à composer

### Phase 2 — Délégation Christophe

Invoquer `christophe-agent` avec le brief :
- Mission : pèlerinage 2500 km, 4-5 mois, saison mai-juin au départ, bivouac 50%
- Poids max : 12 kg total (8 kg base + eau + jour)
- Configuration : solo et/ou duo

Récupérer sa liste pesée par niveau (Règle des 3) et son calcul kcal.

### Phase 3 — Catégorisation

Produire `sac/inventaire.md` avec 10 catégories :

1. **Portage** — sac, ceinture, sifflet
2. **Couchage** — tente/tarp, matelas, sac couchage
3. **Cuisine** — réchaud, popote, cuillère, gourde
4. **Eau** — capacité + filtration
5. **Vêtements marche** — t-shirts, pantalons, chaussettes, chaussures
6. **Vêtements repos** — change nuit + polaire
7. **Vêtements pluie/froid** — hardshell, gants légers, bonnet
8. **Hygiène + LNT** — savon, brosse dents, serviette, trowel, papier
9. **Santé** — pharmacie + kit pieds (spécifique Compostelle)
10. **Nav + admin** — téléphone, batterie, cartes, credencial, CI, cash

Pour chaque item :
- Nom + marque suggérée
- Poids (grammes) réel ou estimé
- Prix indicatif
- Durabilité attendue (km ou mois)
- Solo | Duo (mutualisé | ×2 | inchangé)
- Note (rodage requis, achat en cours de route possible, etc.)

Totaux :
- Solo : base weight, weight fully loaded (avec 2L eau + 1500 kcal), remplacement prévu.
- Duo : idem, avec mutualisation explicitée.

### Phase 4 — Check avant départ

Produire `sac/check-avant-depart.md` — checklist J-30 :

- [ ] Chaussures rodées ≥ 100 km
- [ ] Sac testé chargé sur 25 km
- [ ] Tente montée et démontée 5 fois
- [ ] Réchaud allumé au moins 3 fois
- [ ] Filtration eau vérifiée
- [ ] Credencial retirée
- [ ] Carte européenne d'assurance maladie
- [ ] Numéros d'urgence enregistrés + fichés papier
- [ ] Testament / procuration si voyage long ← rappel légal
- [ ] Assurance rapatriement souscrite
- [ ] Vaccinations à jour (tétanos)
- [ ] Ordonnances longues à emporter (traitements chroniques)

### Phase 5 — Consommables et remplacement en route

Prévoir dans `sac/inventaire.md` une section « À racheter en route » :

- **Chaussures 2e paire** : 1500-2000 km (donc quelque part en France, ex. Le Puy ou Cahors, ou dès l'arrivée en Espagne à SJPP)
- **Chaussettes** : 500-800 km/paire → prévoir 2 paires de rechange à envoyer en poste restante
- **Compeed** : recharge à chaque grande ville
- **Cartouche gaz** : impossible à embarquer en avion — racheter à chaque étape avion/train ; en marche, remplacer tous les 15-20 j
- **Credenciales** : racheter à chaque grande étape jacquaire (Vézelay, Le Puy, SJPP)

## Fallback — Si christophe-agent indisponible

Produire la liste directement avec ces valeurs de référence (marché rando 2025) :

**Sac Solo — Base weight cible 7,5 kg :**
- Portage : Osprey Exos 48 ou Gregory Focal 48 (1150 g)
- Tente : MSR Hubba NX 1P ou Big Agnes Fly Creek HV UL1 (1050 g)
- Matelas : Therm-a-Rest NeoAir XLite (350 g)
- Sac couchage : Sea to Summit Spark Sp III (500 g, +5°C confort)
- Réchaud gaz : MSR Pocket Rocket 2 (73 g) + cartouche (180 g pleine)
- Popote : Toaks 750 mL titane (100 g)
- Chaussures : Salomon X Ultra 4 GTX ou Meindl Respond GTX (~800 g/paire) — hors sac
- Bâtons : Black Diamond Distance Carbon Z (300 g la paire)
- Vêtements portés hors sac (chaussures, pantalon, tshirt, chapeau)
- Vêtements dans sac : 1 tshirt + 1 change + polaire léger + hardshell (~800 g)
- Eau : Platypus 2L + Sawyer Squeeze (170 g)
- Pharmacie : ~250 g
- Nav/admin : téléphone + Anker PowerCore 10000 (180 g) + câbles + credencial + papiers (~500 g)

**Sac Duo — 6,5 kg par pers** = mutualisation tente (2P au lieu de 2×1P), réchaud unique, popote 1,3 L partagée, une seule pharmacie principale + kit léger secours par personne.

## Entrée optionnelle

- Configuration (`solo` | `duo` | `both`)
- Saison (`mai-juin` | `juillet-aout` | `sept-oct`)
- Contrainte (`budget-serre`, `dos-fragile`, `bivouac-100%`)
- Matériel existant (audit)
