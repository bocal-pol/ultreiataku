<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\GuideSection;
use Illuminate\Database\Seeder;

/**
 * Seeder des 6 sections Guide pèlerin.
 *
 * Contenu source importé depuis prep/*.md (contenu markdown FR).
 * NL et DE : version FR en attente de traduction (TODO).
 * Idempotent via updateOrCreate sur le slug.
 *
 * Catégories :
 *   - Le Corps   : forme-physique, sante-pieds-pharmacie
 *   - Pratique   : credencial-et-papiers, budget, meteo-saison, faune-dangereuse
 */
class GuideSectionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- GuideSectionSeeder démarrage ---');

        $sections = $this->getSections();

        foreach ($sections as $data) {
            GuideSection::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
            $this->command->line('  ✓ ' . $data['slug']);
        }

        $this->command->info('--- GuideSectionSeeder terminé — ' . count($sections) . ' sections ---');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSections(): array
    {
        return [
            // ─── Le Corps ────────────────────────────────────────────────────────
            [
                'slug' => 'forme-physique',
                'category' => 'Le Corps',
                'title' => [
                    'fr' => 'Forme physique',
                    'nl' => 'Forme physique', // TODO: traduction NL
                    'de' => 'Forme physique', // TODO: Übersetzung DE
                ],
                'icon' => 'heroicon-o-heart',
                'content' => [
                    'fr' => $this->getFormePhysiqueContent(),
                    'nl' => $this->getFormePhysiqueContent(), // TODO: traduction NL
                    'de' => $this->getFormePhysiqueContent(), // TODO: Übersetzung DE
                ],
                'sort_order' => 1,
                'is_published' => true,
            ],
            [
                'slug' => 'sante-pieds-pharmacie',
                'category' => 'Le Corps',
                'title' => [
                    'fr' => 'Santé, pieds & pharmacie',
                    'nl' => 'Santé, pieds & pharmacie', // TODO: traduction NL
                    'de' => 'Santé, pieds & pharmacie', // TODO: Übersetzung DE
                ],
                'icon' => 'heroicon-o-shield-check',
                'content' => [
                    'fr' => $this->getSantePiedsContent(),
                    'nl' => $this->getSantePiedsContent(), // TODO: traduction NL
                    'de' => $this->getSantePiedsContent(), // TODO: Übersetzung DE
                ],
                'sort_order' => 2,
                'is_published' => true,
            ],

            // ─── Pratique ────────────────────────────────────────────────────────
            [
                'slug' => 'credencial-et-papiers',
                'category' => 'Pratique',
                'title' => [
                    'fr' => 'Credencial & papiers',
                    'nl' => 'Credencial & papiers', // TODO: traduction NL
                    'de' => 'Credencial & papiers', // TODO: Übersetzung DE
                ],
                'icon' => 'heroicon-o-identification',
                'content' => [
                    'fr' => $this->getCredencialContent(),
                    'nl' => $this->getCredencialContent(), // TODO: traduction NL
                    'de' => $this->getCredencialContent(), // TODO: Übersetzung DE
                ],
                'sort_order' => 3,
                'is_published' => true,
            ],
            [
                'slug' => 'budget',
                'category' => 'Pratique',
                'title' => [
                    'fr' => 'Budget du pèlerin',
                    'nl' => 'Budget du pèlerin', // TODO: traduction NL
                    'de' => 'Budget du pèlerin', // TODO: Übersetzung DE
                ],
                'icon' => 'heroicon-o-banknotes',
                'content' => [
                    'fr' => $this->getBudgetContent(),
                    'nl' => $this->getBudgetContent(), // TODO: traduction NL
                    'de' => $this->getBudgetContent(), // TODO: Übersetzung DE
                ],
                'sort_order' => 4,
                'is_published' => true,
            ],
            [
                'slug' => 'meteo-saison',
                'category' => 'Pratique',
                'title' => [
                    'fr' => 'Météo & saison',
                    'nl' => 'Météo & saison', // TODO: traduction NL
                    'de' => 'Météo & saison', // TODO: Übersetzung DE
                ],
                'icon' => 'heroicon-o-sun',
                'content' => [
                    'fr' => $this->getMeteoContent(),
                    'nl' => $this->getMeteoContent(), // TODO: traduction NL
                    'de' => $this->getMeteoContent(), // TODO: Übersetzung DE
                ],
                'sort_order' => 5,
                'is_published' => true,
            ],
            [
                'slug' => 'faune-dangereuse',
                'category' => 'Pratique',
                'title' => [
                    'fr' => 'Faune dangereuse',
                    'nl' => 'Faune dangereuse', // TODO: traduction NL
                    'de' => 'Faune dangereuse', // TODO: Übersetzung DE
                ],
                'icon' => 'heroicon-o-exclamation-triangle',
                'content' => [
                    'fr' => $this->getFauneContent(),
                    'nl' => $this->getFauneContent(), // TODO: traduction NL
                    'de' => $this->getFauneContent(), // TODO: Übersetzung DE
                ],
                'sort_order' => 6,
                'is_published' => true,
            ],
        ];
    }

    private function getFormePhysiqueContent(): string
    {
        return <<<'MARKDOWN'
# Préparation physique — 12 semaines avant le départ

> **Objectif** : encaisser 20 km/jour pendant 3 mois consécutifs avec 10-12 kg sur le dos. Ce n'est pas une course, c'est une endurance de fond.

## Principe général

Ton corps doit apprendre trois choses distinctes :
1. **Marcher longtemps** (endurance cardio-vasculaire)
2. **Porter du poids sans se blesser** (charpente + dos + gainage)
3. **Récupérer entre étapes** (sommeil + nutrition + mobilité)

Une prépa de **12 semaines minimum** est idéale. En dessous de 8 semaines, tu partiras mais tu risques la casse (tendinite, périostite, ampoules chroniques). 12 semaines te laissent une marge pour intégrer les 3 objectifs.

## Programme 12 semaines

### Semaines 1-3 · Fondations (marche + gainage)

**3 sorties/semaine, 5-8 km, sac vide ou 3 kg**

- Objectif : caler la régularité, prendre plaisir à marcher
- Chaussures : celles que tu emporteras au Compostelle (rodage démarre ici)
- Terrain : varié (route + sentier + herbe)

**+ Gainage 3×/semaine** — planches (face, latérales), superman, pont — 3 séries × 30 s chaque

**+ Étirements chevilles + mollets** post-marche — 5 min

### Semaines 4-6 · Montée en distance

**3 sorties/semaine, 10-15 km, sac 5-7 kg**

- Charger progressivement le sac (petit à petit, jamais brutalement)
- Une sortie longue le weekend (15 km) + deux courtes en semaine (8-10 km)
- Alterner allure : marche cool + segments d'accélération 10 min

**+ Renforcement bas du corps 2×/semaine** — squats, fentes, mollets, hip thrusts — 3×10-15 reps

**+ Renforcement dos + trapèzes 2×/semaine** — tirage horizontal, rowing élastique, superman — 3×10-15 reps

### Semaines 7-9 · Endurance longue et dénivelé

**3 sorties/semaine, 15-20 km, sac 8-10 kg**

- Une sortie longue par weekend : 20 km avec sac plein
- Chercher du dénivelé : coteaux, escaliers, forêts (Ardenne belge = simulation parfaite)
- **Test dos + hanches** : peux-tu porter 10 kg 4h sans douleur ?

**+ Cardio 1×/semaine** — vélo ou natation 45 min — pour préserver les articulations

### Semaines 10-11 · Simulation grande étape

**3 sorties/semaine, dont 1 grande sortie 25-30 km avec sac plein (11-12 kg)**

- Simuler une étape complète : partir tôt matin, pique-nique en route, arriver fin d'après-midi
- Tester TOUT : chaussures, sac, tente, réchaud, ravitaillement
- Repérer inconforts, ajuster matériel

**+ Test bivouac** — au moins une nuit sous tente dans le jardin ou en pleine nature (légal !)

### Semaine 12 · Tapering (allègement)

**2 sorties, 10-15 km avec sac 8 kg**

- Réduire volume de 50% — le corps doit récupérer avant départ
- Pas de nouvelle sortie longue
- Sommeil ++, nutrition ++, hydratation ++
- Ongles coupés, chaussettes propres, pharmacie vérifiée

## Renforcement musculaire prioritaire

Trois zones qui font la différence sur un pèlerinage :

### 1. Dos + trapèzes
- **Tirage vertical avec élastique** — 3×15 reps 3×/sem
- **Rowing horizontal** (avec bande ou haltères) — 3×12 reps 3×/sem
- **Superman** — 3×15 s 3×/sem
- **Étirement grand rond + trapèzes** — quotidien

### 2. Fessiers + hanches
- **Squats à charge modérée** — 3×15 reps 3×/sem
- **Fentes bulgares** — 3×10 reps par jambe 2×/sem
- **Hip thrusts / pont** — 3×15 reps 3×/sem
- **Étirement psoas** — quotidien

### 3. Mollets + chevilles + pieds
- **Élévations mollets sur marche** — 3×20 reps quotidien
- **Élévations mollets à une jambe** (progression) — 3×10 reps 2×/sem
- **Mobilité cheville** — cercles + flexions dorsales — 5 min quotidien
- **Marche pieds nus 15 min/jour** — renforce voûte plantaire

## Signaux d'alerte à écouter

Arrête l'entraînement 3-5 jours si :
- Douleur point localisé (tendinite naissante)
- Fatigue générale non récupérée après 48h
- Insomnie durable
- Blessure ampoule mal soignée
- Douleur genou latérale (syndrome bandelette ilio-tibiale)

## Test-flash 3 semaines avant

Peux-tu enchaîner **2 jours de suite** 22 km avec sac 10 kg + tente montage/démontage ?

- OUI → prêt à partir, tapering
- NON → 2 semaines de plus de charge, décaler départ si possible
MARKDOWN;
    }

    private function getSantePiedsContent(): string
    {
        return <<<'MARKDOWN'
# Santé, pieds et pharmacie — Le kit vital du pèlerin 2500 km

> **Le poste critique du pèlerinage.** Une ampoule mal soignée = arrêt. Une tendinite ignorée = un mois d'arrêt. Ce guide est écrit pour ne PAS avoir à renoncer.

## Les pieds — l'obsession numéro 1

### Rodage des chaussures — sacré, non négociable

**100 km MINIMUM avant le départ officiel**, avec le même sac chargé que tu emporteras.

- Chaussures neuves = ampoules garanties
- Le pied s'adapte aux points de pression, la chaussure se moule autour
- Après 100 km : tu sais si le modèle te convient

### Prévention active des ampoules

**Chaque matin avant de mettre les chaussures** :
1. Pieds propres et **secs** (essentiel — le mouillé = frottement = ampoule)
2. **Crème anti-frottement Nok / SquirrelNut Butter** sur zones à risque : talons, orteils, coup de pied, entre-orteils
3. **Talc** léger si tu tends à transpirer beaucoup
4. **Sous-chaussettes fines soie/synth** + **chaussettes rando laine mérinos** = combo révolutionnaire

**Pendant la marche** :
- **Aérer les pieds à chaque pause > 15 min** — enlever chaussures + chaussettes
- **Changer de chaussettes à mi-journée** si transpiration importante

### Traitement des ampoules

**Ampoule fermée non éclatée :**
1. Désinfecter avec teinture d'iode ou Biseptine
2. Percer proprement avec **aiguille stérilisée** à la flamme
3. Faire sortir le liquide en appuyant depuis les bords
4. **Ne PAS enlever la peau** de l'ampoule (protection naturelle)
5. Coller **Compeed** ou **hydrocolloïde équivalent** — laisser 3-5 jours

## Kit pharmacie détaillé — pesé

| Item | Quantité | Poids |
|------|----------|-------|
| Compeed ampoules S | 8 pièces | 15 g |
| Compeed ampoules M | 8 pièces | 15 g |
| Nok Akiléïne / SquirrelNut | 1 tube 60 mL | 65 g |
| Teinture d'iode 20 mL | 1 flacon | 30 g |
| Biseptine 20 mL | 1 flacon | 30 g |
| Paracétamol 500 mg | 20 comprimés | 20 g |
| Ibuprofène 400 mg | 20 comprimés | 20 g |
| Loperamide (Imodium) | 6 gélules | 5 g |
| Sels de réhydratation OMS | 5 sachets | 50 g |
| Antihistaminique (cétirizine) | 10 comprimés | 10 g |
| Répulsif DEET 30% 30 mL | 1 flacon | 40 g |
| Crème solaire SPF 50 | 50 mL | 60 g |

**Total kit santé : ~580 g**

## Articulations : genoux, chevilles, dos

### Genoux — l'ennemi #2

Le genou souffre à la **descente**, sur les pavés/asphalte, et si tu portes trop lourd.

**Prévention** :
- **Bâtons de marche** obligatoires en descente — répartition de 20-25% de la charge
- Chaussures avec bonne semelle amortissante
- Descentes lentes, petits pas

### Dos — le silencieux

Douleurs lombaires souvent = **sac mal réglé** (poids sur épaules au lieu des hanches). Corrections :
1. **Ceinture ventrale ferme** — 80% du poids doit reposer sur les hanches
2. Bretelles pas trop serrées

## Une pensée pour finir

**Le pèlerinage n'est pas une performance sportive.** C'est un voyage lent. Chaque jour où tu écoutes ton corps est un jour gagné sur le long terme.

**Tes pieds, tes genoux, tes hanches doivent te conduire jusqu'à Santiago.** Prends soin d'eux.
MARKDOWN;
    }

    private function getCredencialContent(): string
    {
        return <<<'MARKDOWN'
# Credencial et papiers administratifs — Pèlerinage 2500 km

## La Credencial de Pèlerin

**Qu'est-ce que c'est ?** Un carnet de route officiel du pèlerin, format A6 replié. Chaque étape, un gîte / paroisse / mairie / office tourisme y appose son tampon. C'est ta preuve d'avoir marché — indispensable pour obtenir la **Compostela** à Santiago.

**Elle donne accès aux gîtes donativos + albergues** à prix modique tout au long du chemin.

### Où la retirer à Liège

1. **Cathédrale Saint-Paul de Liège** — sacristie (ouverture heures culte + matinées)
   - Adresse : Place de la Cathédrale, 4000 Liège
   - Contact : +32 4 232 61 30

2. **Association Belge des Amis de Saint-Jacques**
   - Site : https://www.st-jacques.be/
   - Envoi possible avant départ (contre timbre + formulaire)
   - Tarif indicatif : 5-10 € donation

### Prévoir des credenciales de rechange

Sur 2500 km, une seule credencial ne suffit pas. **Prévoir 3 à 4 credenciales au total** :

| Étape voyage | Credencial | Où racheter |
|--------------|-----------|-------------|
| Belgique + début France | Credencial 1 (Liège) | — |
| Milieu France | Credencial 2 | Association ACIR Compostelle, Vézelay ou Le Puy |
| Fin France / bascule Norte | Credencial 3 | Saint-Jean-Pied-de-Port ou Irun |
| Espagne — Norte | Credencial 4 | Bilbao, Santander, Gijón |

**Densité de tampons** : viser 2-3 tampons par jour (départ + arrivée + pause déjeuner ou POI).

### La Compostela (le certificat final)

- Décernée à l'**Oficina del Peregrino** à Santiago
- **Adresse** : Rúa Carretas 33, 15705 Santiago de Compostela
- Ouvert 7j/7, horaires étendus
- Conditions : avoir marché **au moins 100 km**, motivation religieuse ou spirituelle, credencial(es) avec tampons continus
- **Gratuit** (donation possible)

## Papiers administratifs — Check-list complète

### Documents d'identité
- Carte d'identité belge (obligatoire) — valide au-delà du retour
- Passeport (optionnel mais recommandé)
- Photocopies dans un ziplock, séparées du sac principal
- Scan de tout dans ton drive cloud

### Santé
- **Carte européenne d'assurance maladie (CEAM)** — gratuite, à demander ~3 semaines avant départ
- **Attestation assurance rapatriement** — ~50-80 €/an
- Carnet de vaccination — tétanos à jour (rappel tous les 20 ans)

### Financier
- Cash 300-400 € en euros, répartis en 3 poches
- 2 cartes bancaires différentes (Visa + MasterCard idéalement)
- Numéros d'opposition CB notés à part

### Contacts d'urgence
- Urgence Europe : **112**
- Ambassade Belgique France : +33 1 44 09 39 39
- Ambassade Belgique Espagne : +34 91 577 63 00
MARKDOWN;
    }

    private function getBudgetContent(): string
    {
        return <<<'MARKDOWN'
# Budget — Pèlerinage complet Liège → Santiago

## Budget global (aperçu 2500 km · 100-125 jours)

**Estimation moyenne, un pèlerin solo, mode mixte bivouac 50% / gîte 50%.**

| Poste | Belgique (~14 j) | France (~50 j) | Espagne (~40 j) | Total 2500 km |
|-------|-----------------|----------------|-----------------|---------------|
| Hébergement | 160 € | 750 € | 400 € | **1 310 €** |
| Nourriture | 200 € | 800 € | 700 € | **1 700 €** |
| Boissons cafés | 55 € | 200 € | 150 € | **405 €** |
| Extras / restaurants | 100 € | 350 € | 200 € | **650 €** |
| Transports internes | 20 € | 50 € | 100 € | **170 €** |
| Matériel remplacement | 0 € | 250 € | 100 € | **350 €** |
| Imprévus (10%) | 65 € | 270 € | 190 € | **525 €** |
| **TOTAL PAR TRONÇON** | **~710 €** | **~2 960 €** | **~2 090 €** | **~5 760 €** |

## Configurations budget

| Configuration | Total pèlerinage 2500 km |
|---------------|--------------------------|
| **Ultra-serré** (bivouac 80%, cuisine 90%, zéro restau) | **~2 800-3 500 €** |
| **Économique** (bivouac 50%, cuisine 70%, 1 restau/semaine) | **~3 500-4 500 €** |
| **Confort** (gîte 70%, restaurant 3-4×/semaine) | **~5 500-7 000 €** |

**Budget cible réaliste : 3 500 à 4 500 € pour le voyage complet solo, mixte bivouac/gîte 50/50.**

## Stratégies d'économie

### Sur l'hébergement
- **Bivouac légal** = 0 €. Aires DNF Wallonie, forêts communales, terrains agricoles avec autorisation
- **Donativos** = souvent 5-10 € au lieu de 25-30 € en AJ. Payer honnêtement !
- **Camping municipal** = 8-15 €/nuit
- **Warmshowers / Couchsurfing** = gratuit

### Sur la nourriture
- **Petit-déjeuner cuisine soi-même** : 2 €/jour vs 8-12 € café
- **Marchés hebdomadaires** : -30% sur légumes/fruits/fromages
- **Cuisine bivouac** = 4-6 €/repas vs 12-25 € restaurant

## Répartition de cash / cartes

**Sur 400 € cash initial** :
- **200 €** dans ceinture-portefeuille caché sous vêtement
- **100 €** dans poche haute pantalon (accès quotidien)
- **100 €** au fond du sac, poche interne fermée

## Ce qu'il faut retenir

- **Budget solo 4 000 € réaliste, confort correct** pour 2500 km
- **Budget duo ~7 000 € couple** (économies mutualisation)
- **Prépare 500 € de marge imprévue** — blessure, matériel cassé, changement de plan
- **Autour de 30-40 €/jour** en Belgique/France, **20-25 €/jour** en Espagne
MARKDOWN;
    }

    private function getMeteoContent(): string
    {
        return <<<'MARKDOWN'
# Météo et saison — Pèlerinage Liège → Santiago

## Fenêtre optimale globale

### Départ recommandé : **mai-juin**

**Pourquoi** :
- **Températures agréables** en Belgique et France : 10-22°C
- **Nuits fraîches mais praticables** : 5-10°C (sac de couchage +5°C confort)
- **Jours longs** : 15-16h de lumière au solstice = étapes matinales tranquilles
- **Gîtes ouverts** : haute saison jacquaire, tous les gîtes donativos actifs
- **Arrivée Santiago août-septembre** : chaleur d'été, festivité de la Saint-Jacques (25 juillet)

### Alternatives

| Saison départ | Avantages | Inconvénients | Verdict |
|---------------|-----------|---------------|---------|
| **Avril** | Fleurs, peu de monde | Nuits froides Belgique (0-5°C), boue Fagne | ⚠️ Trop tôt |
| **Mai-juin** | Cf. ci-dessus | Beaucoup de monde après mi-juin en Espagne | ⭐ **Optimal** |
| **Juillet-août** | Été franc | Chaleur écrasante Espagne (35-45°C), gîtes complets | ❌ À éviter |
| **Octobre** | Automne coloré | Nuits froides, pluies, hôtels/gîtes ferment | ❌ Trop tard pour BE |

## Phase 3 · Espagne — CAMINO DEL NORTE

**⚠️ Pas de Meseta, pas de canicule : ici l'ennemi c'est la PLUIE, pas la chaleur.** Le Norte longe la côte atlantique — c'est vert *parce qu'*il y pleut, même en plein été.

**Stratégie Norte (anti-pluie, pas anti-chaleur)** :
- **Poncho ou veste + sur-pantalon toujours accessibles** en haut du sac
- **Housse de sac** systématique + sacs étanches intérieurs
- **Chaussettes de rechange sèches** = priorité au moral
- **Protection solaire quand même** : le soleil basque/asturien tape fort entre deux averses

## Gestion de l'imprévu

### Journée pluvieuse
- **Sur-pantalon pluie** + **hardshell** + **housse de sac**
- **Étape raccourcie** si averse continue > 4h — chercher gîte plutôt que bivouac
- **Priorité SÉCURITÉ** — pas de traversée gué crue, pas de crête exposée si orage

### Journée caniculaire
- **Départ ultra-matinal** (5h30-6h)
- **Chapeau + buff mouillé cou** = -3°C ressentis
- **Pause obligatoire 12h-16h** — sieste ombre
- **Hydratation constante** : petites gorgées toutes les 15 min

## Recommandation finale

**Départ recommandé : entre le 15 mai et le 5 juin.**

Ça te donne :
- Nuits belges praticables (7-10°C moyenne)
- Traversée France mi-juin à mi-août — bien géré
- **Cantabrie début septembre** : la meilleure fenêtre du Norte (foule d'août partie, mer encore tiède)
- Galice mi-fin septembre : pluvieux mais atmosphérique, albergues calmes
- Arrivée Santiago **fin septembre** : temps doux, avant les grandes pluies d'octobre
MARKDOWN;
    }

    private function getFauneContent(): string
    {
        return <<<'MARKDOWN'
# Faune potentiellement dangereuse — précautions par zone

> Rassurant d'abord : sur ce chemin, **aucun de ces animaux ne cherche l'humain**. Les accidents graves sont rarissimes. Le vrai risque quotidien reste les **tiques** et les **chiens de troupeau**, pas les grands fauves.

## Vue d'ensemble par tronçon

| Zone | Présences possibles | Risque réel |
|------|---------------------|-------------|
| Ardennes belges + Fagne | Vipère péliade, sanglier, tiques ; loup (farouche) | Tiques ++, vipère (bas) |
| France rurale / Massif | Vipères aspic & péliade, sanglier, tiques ; patous en estive | Tiques ++, patous |
| Pyrénées basques | Quelques ours (réintroduits), patous, vipères | Patous, vipère |
| **Picos de Europa / Asturies-Cantabrie** | **Ours brun cantabrique**, **loup**, **mastines**, vipère de Seoane | **Mastines ++**, ours/loup (très rare) |
| Côte du Norte | Vipères en talus rocheux, chiens | Faible |

## Chiens de troupeau — le risque le plus fréquent en montagne

**Mastines** (Cantabrie/Asturies/Picos) et **patous** (Pyrénées) font leur travail ; ils aboient, chargent pour intimider, mordent parfois.

**Précautions** :
- **Ne pas courir, ne pas crier, ne pas lever le bâton** (perçu comme agression)
- S'**arrêter**, rester de côté (pas de face), **éviter le contact visuel** direct, parler calmement
- **Contourner le troupeau largement**, laisser le chien te « raccompagner » hors de sa zone
- Garder les bâtons **bas**

## Vipères / serpents (tout le parcours)

**Danger réel** : faible mais concret. Morsure douloureuse, rarement mortelle chez l'adulte en bonne santé, mais **urgence médicale** toujours.

**Précautions** :
- **Regarder où tu poses pieds et mains** (surtout en franchissant murets, rochers, bois morts)
- Le matin, les vipères lézardent sur les pierres chaudes : attention aux passages rocheux ensoleillés

**En cas de morsure** :
1. **Rester calme**, s'asseoir, **immobiliser le membre**
2. Enlever bagues/montre/chaussure avant le gonflement
3. **NE PAS** : inciser, aspirer, poser un garrot
4. **Appeler le 112** immédiatement

## Ours brun (Picos / Asturies + Pyrénées)

**Danger réel** : très faible. L'ours cantabrique est **farouche** ; les rencontres sont exceptionnelles.

**Précautions** :
- **Faire du bruit** en terrain fermé/broussailleux (parler, bâtons)
- Ne **jamais s'approcher d'un ourson** (la mère n'est pas loin)
- Si rencontre : **ne pas courir, ne pas crier**, reculer lentement en parlant d'une voix calme

## Tiques — le vrai risque quotidien

Zones à risque : Ardennes, Fagne, forêts françaises, nord humide de l'Espagne, avril-octobre.

**Prévention** :
- Vêtements longs en herbes hautes, répulsif DEET
- **Inspection quotidienne** complète (aisselles, aines, nuque, plis genou)
- **Tire-tique** dans la pharmacie — extraction perpendiculaire lente
- Surveiller l'**érythème migrant** 30 jours après morsure — consulter au moindre doute (Lyme se soigne bien si traitée précoce)
MARKDOWN;
    }
}
