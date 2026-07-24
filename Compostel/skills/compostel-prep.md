---
name: compostel-prep
description: Compléments transversaux préparation pèlerinage Compostelle 2500 km — forme physique, credencial, santé pieds, météo/saison, budget global. Valide pour l'intégralité du voyage jusqu'à Santiago.
model: claude-haiku-4-5-20251001
tools: Read, Write
type: utilitaire
priority: medium
scope: cross
dependencies: []
conflicts: []
---

# Compostel Prep — Préparation transversale 2500 km

Skill mécanique produisant cinq fiches courtes et actionnables. Contenu stable, mise à jour peu fréquente, d'où le choix Haiku.

## Directives — Non négociables

| Directive | Application |
|-----------|-------------|
| **Vision 2500 km** | Chaque conseil vise la tenue sur 4-5 mois de marche, pas juste la Belgique |
| **Actionnable** | Chaque fiche se termine par une checklist datée (J-90, J-60, J-30, J-7) |
| **Chiffres concrets** | Kcal, coûts, dates, kilométrages — pas de vague |

## Workflow

### Phase 1 — Contexte

Lire le contexte du projet si présent (`plan-voyage-<tronçon>.md`, `vue-globale-santiago.md`) pour aligner les dates et les paramètres.

### Phase 2 — Générer les 5 fiches

Créer le dossier `prep/` et écrire cinq fichiers :

1. **`prep/forme-physique.md`** — plan d'entraînement 12 semaines avant départ, sorties progressives 5→25 km avec sac, renforcement dos + gainage, mobilité chevilles, échelle RPE
2. **`prep/credencial-et-papiers.md`** — credencial pèlerin, où la retirer à Liège, credenciales de rechange pour 2500 km, Compostela à Santiago, carte européenne d'assurance maladie, contacts urgence, procuration, testament (voyage long)
3. **`prep/sante-pieds-pharmacie.md`** — kit pharmacie pesé et détaillé, pieds (rodage chaussures, ampoules, ongles, chaussettes techniques), articulations (genou/cheville), thermorégulation (canicule/hypothermie), plantes locales à connaître
4. **`prep/meteo-saison.md`** — fenêtre optimale mai-juin au départ, projections températures par tronçon (Belgique/France/Espagne meseta), risques saisonniers, apps météo utiles offline
5. **`prep/budget.md`** — budget par tronçon + budget global, sources de dépenses (nuit dur, épicerie, restau, transports internes, matériel remplacement), astuces économies, budget cash vs CB

### Phase 3 — Checklist synthétique

À la fin, produire une checklist consolidée `prep/checklist-j-x.md` :

```markdown
# Checklist J-x avant départ

## J-90 (3 mois)
- [ ] Décision date de départ ferme
- [ ] Achat sac + tente + chaussures
- [ ] Début entraînement 3 sorties/sem
- [ ] Retirer credencial (Cathédrale Saint-Paul Liège ou Association)

## J-60 (2 mois)
- [ ] Sorties 15 km avec sac chargé
- [ ] Test réchaud + tente
- [ ] Vérifier vaccination tétanos
- [ ] Souscrire assurance rapatriement

## J-30 (1 mois)
- [ ] Sortie test 25 km avec sac plein
- [ ] Chaussures rodées ≥ 100 km
- [ ] Numéros urgence dans téléphone + papier
- [ ] Ordonnances traitements chroniques

## J-7
- [ ] Météo J-7 vérifiée
- [ ] Ravitaillement J1-2 acheté
- [ ] Confirmation gîte J1 (si réservé)
- [ ] Contact famille — plan de communication

## J-1
- [ ] Sac pesé une dernière fois (≤ 12 kg)
- [ ] Douche + ongles + chaussettes propres
- [ ] Repas riche en glucides (pâtes)
- [ ] Coucher tôt
```

## Fallback

Aucune dépendance externe. Contenu autonome basé sur les recommandations FFRandonnée + associations jacquaires.

## Entrée optionnelle

- Date de départ prévue (pour calibrer les J-x)
- Niveau physique de départ (sédentaire | actif occasionnel | sportif régulier)
- Contraintes médicales connues
