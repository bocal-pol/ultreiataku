<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\ItemAssignment;
use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ULTREIA-42 — Seeds scénarios de sac bocal.
 *
 * RÈGLE UTILISATEUR : les scénarios appartiennent au Pilgrim `bocal` (user_id=1).
 * Idempotent : updateOrCreate sur (pilgrim_id, name).
 *
 * Sources de poids :
 *   - Solo : inventaire.md § RÉCAPITULATIF SOLO (~9,06 kg total)
 *   - Duo  : inventaire.md § RÉCAPITULATIF DUO (~7,90 kg/pers)
 *
 * Items sélectionnés : ~35 items réels pesés (poids exacts tirés de l'inventaire).
 * is_consumable : gaz + nourriture quotidienne.
 * is_shared (Duo) : tente 2P, réchaud, filtre à eau, pharmacie principale, chargeur, batterie.
 *
 * ItemAssignment d'exemple : assignation de la tente et du réchaud sur le Departure bocal.
 */
class PackScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== PackScenarioSeeder ===');

        /** @var Pilgrim|null $bocal */
        $bocal = Pilgrim::query()->where('user_id', 1)->first();

        if ($bocal === null) {
            $this->command->warn('Pilgrim bocal (user_id=1) introuvable — PersonalTripSeeder à exécuter d\'abord.');
            Log::warning('PackScenarioSeeder: pilgrim bocal not found.');

            return;
        }

        DB::transaction(function () use ($bocal): void {
            $this->seedSoloScenario($bocal);
            $this->seedDuoScenario($bocal);
            $this->seedItemAssignmentExamples($bocal);
        });

        $this->command->info('=== PackScenarioSeeder — terminé ===');
    }

    // ─── Scénario SOLO ────────────────────────────────────────────────────────

    private function seedSoloScenario(Pilgrim $bocal): void
    {
        /** @var PackScenario $solo */
        $solo = PackScenario::query()->updateOrCreate(
            [
                'pilgrim_id' => $bocal->id,
                'name' => 'Solo 8,5 kg Belgique',
            ],
            [
                'description' => 'Configuration mai-juin, Via Mosana Belgique. Objectif 8,5 kg. Base weight réel ~9,06 kg avec liste complète.',
                'target_base_weight_kg' => 8.50,
                'configuration' => 'solo',
                'season' => 'spring',
            ],
        );

        $this->command->info("Scénario Solo : {$solo->id}");

        // Items Solo — poids exacts tirés de inventaire.md
        $soloItems = [
            // 1 · PORTAGE (~1300 g)
            ['category' => 'portage', 'name' => 'Sac à dos Osprey Exos 48', 'brand' => 'Osprey', 'model' => 'Exos 48', 'weight_g' => 1100, 'notes' => 'Ceinture ventrale ferme obligatoire, filet dorsal ventilé', 'replacement_km' => 5000, 'sort_order' => 1],
            ['category' => 'portage', 'name' => 'Housse pluie Osprey Ultralight M', 'brand' => 'Osprey', 'model' => 'Ultralight Raincover M', 'weight_g' => 90, 'sort_order' => 2],
            ['category' => 'portage', 'name' => 'Sacoche ceinture Ferrino Sky Bag', 'brand' => 'Ferrino', 'model' => 'Sky Bag', 'weight_g' => 80, 'notes' => 'Portefeuille + téléphone accessible', 'sort_order' => 3],
            ['category' => 'portage', 'name' => 'Sac étanche couchage Sea to Summit Ultra-Sil 20L', 'brand' => 'Sea to Summit', 'model' => 'Ultra-Sil Dry 20L', 'weight_g' => 60, 'notes' => 'Protège duvet même en cas de pluie', 'sort_order' => 4],

            // 2 · COUCHAGE SOLO (~2050 g)
            ['category' => 'sleeping', 'name' => 'Tente MSR Hubba NX 1', 'brand' => 'MSR', 'model' => 'Hubba NX 1', 'weight_g' => 1050, 'notes' => 'Solo uniquement. 1-person, 3-saisons', 'replacement_km' => 3000, 'sort_order' => 1],
            ['category' => 'sleeping', 'name' => 'Matelas Therm-a-Rest NeoAir XLite NXT', 'brand' => 'Therm-a-Rest', 'model' => 'NeoAir XLite NXT', 'weight_g' => 350, 'notes' => 'R-value 4.5, confort', 'replacement_km' => 3000, 'sort_order' => 2],
            ['category' => 'sleeping', 'name' => 'Sac de couchage Sea to Summit Spark Sp III', 'brand' => 'Sea to Summit', 'model' => 'Spark Sp III', 'weight_g' => 550, 'notes' => 'Confort +5°C pour mai-juin', 'replacement_km' => 3000, 'sort_order' => 3],
            ['category' => 'sleeping', 'name' => 'Sac à viande soie Cocoon MummyLiner', 'brand' => 'Cocoon', 'model' => 'MummyLiner Silk', 'weight_g' => 100, 'notes' => '+3°C au sac + hygiène auberges', 'sort_order' => 4],

            // 3 · CUISINE SOLO (~570 g avec gaz)
            ['category' => 'cooking', 'name' => 'Réchaud MSR PocketRocket 2', 'brand' => 'MSR', 'model' => 'PocketRocket 2', 'weight_g' => 75, 'notes' => 'Fiable, universel', 'sort_order' => 1],
            ['category' => 'cooking', 'name' => 'Cartouche gaz 100 g Coleman C100', 'brand' => 'Coleman', 'model' => 'C100', 'weight_g' => 190, 'is_consumable' => true, 'notes' => 'À remplacer tous les 15-20 jours', 'sort_order' => 2],
            ['category' => 'cooking', 'name' => 'Popote Toaks Titanium 750 mL', 'brand' => 'Toaks', 'model' => 'Titanium Pot 750 mL', 'weight_g' => 105, 'sort_order' => 3],
            ['category' => 'cooking', 'name' => 'Cuillère titane Toaks Long Spoon', 'brand' => 'Toaks', 'model' => 'Titanium Long Spoon', 'weight_g' => 15, 'sort_order' => 4],
            ['category' => 'cooking', 'name' => 'Opinel N°8 carbone', 'brand' => 'Opinel', 'model' => 'N°8 lame carbone', 'weight_g' => 42, 'notes' => 'Légal Belgique (lame < 20 cm ouverte)', 'sort_order' => 5],
            ['category' => 'cooking', 'name' => 'Briquet Bic Mini + ferro rod', 'weight_g' => 45, 'notes' => 'Double redondance feu', 'sort_order' => 6],

            // 4 · EAU (~320 g)
            ['category' => 'water', 'name' => 'Poche à eau Platypus Big Zip LP 2L', 'brand' => 'Platypus', 'model' => 'Big Zip LP 2L', 'weight_g' => 150, 'sort_order' => 1],
            ['category' => 'water', 'name' => 'Nalgene Wide-Mouth 1L', 'brand' => 'Nalgene', 'model' => 'Wide-Mouth 1L', 'weight_g' => 180, 'notes' => 'Robuste, gradation, filtre Sawyer compatible', 'sort_order' => 2],
            ['category' => 'water', 'name' => 'Filtre Sawyer Squeeze', 'brand' => 'Sawyer', 'model' => 'Squeeze', 'weight_g' => 90, 'notes' => 'Filtre 4000 L, essentiel sources Fagnes/Ardennes', 'replacement_km' => 5000, 'sort_order' => 3],
            ['category' => 'water', 'name' => 'Pastilles Micropur Forte 100', 'brand' => 'Micropur', 'model' => 'Forte 100', 'weight_g' => 15, 'is_consumable' => true, 'notes' => 'Backup filtre', 'sort_order' => 4],

            // 5+6 · VÊTEMENTS (~1430 g dans sac)
            ['category' => 'clothing', 'name' => 'T-shirt mérinos nuit Woolpower 200', 'brand' => 'Woolpower', 'model' => '200', 'weight_g' => 130, 'sort_order' => 1],
            ['category' => 'clothing', 'name' => 'Legging Icebreaker 200 Oasis', 'brand' => 'Icebreaker', 'model' => '200 Oasis Legging', 'weight_g' => 200, 'sort_order' => 2],
            ['category' => 'clothing', 'name' => 'Chaussons camp Xero Shoes Aqua Cloud', 'brand' => 'Xero Shoes', 'model' => 'Aqua Cloud', 'weight_g' => 220, 'notes' => 'Nuit gîte + douches auberges', 'sort_order' => 3],
            ['category' => 'clothing', 'name' => 'Veste hardshell Rab Kinetic Alpine', 'brand' => 'Rab', 'model' => 'Kinetic Alpine', 'weight_g' => 350, 'notes' => 'Membrane 3L, capuche, coupe rando', 'sort_order' => 4],
            ['category' => 'clothing', 'name' => 'Sur-pantalon pluie Montane Pac Plus XT', 'brand' => 'Montane', 'model' => 'Pac Plus XT', 'weight_g' => 250, 'sort_order' => 5],
            ['category' => 'clothing', 'name' => 'Polaire Patagonia R1 Pullover', 'brand' => 'Patagonia', 'model' => 'R1 Pullover', 'weight_g' => 300, 'sort_order' => 6],
            ['category' => 'clothing', 'name' => 'Doudoune Uniqlo Ultra Light Down', 'brand' => 'Uniqlo', 'model' => 'Ultra Light Down', 'weight_g' => 250, 'sort_order' => 7],
            ['category' => 'clothing', 'name' => 'Gants Patagonia Capilene Midweight', 'brand' => 'Patagonia', 'model' => 'Capilene Midweight', 'weight_g' => 40, 'sort_order' => 8],
            ['category' => 'clothing', 'name' => 'Bonnet mérinos Icebreaker Cotswold', 'brand' => 'Icebreaker', 'model' => 'Cotswold Beanie', 'weight_g' => 30, 'sort_order' => 9],

            // 8 · HYGIÈNE (~440 g)
            ['category' => 'hygiene', 'name' => 'Savon Dr. Bronner Baby Mild 60 mL', 'brand' => 'Dr. Bronner', 'model' => 'Baby Mild', 'weight_g' => 65, 'is_consumable' => true, 'notes' => 'Corps + linge + vaisselle, biodégradable', 'sort_order' => 1],
            ['category' => 'hygiene', 'name' => 'Serviette microfibre Sea to Summit DryLite M', 'brand' => 'Sea to Summit', 'model' => 'DryLite Towel M', 'weight_g' => 130, 'sort_order' => 2],
            ['category' => 'hygiene', 'name' => 'Dentifrice pastilles Georganics 60 pcs', 'brand' => 'Georganics', 'weight_g' => 40, 'is_consumable' => true, 'sort_order' => 3],
            ['category' => 'hygiene', 'name' => 'Trowel The Deuce #2', 'brand' => 'The Deuce', 'model' => '#2', 'weight_g' => 17, 'notes' => '20 cm profondeur, obligatoire LNT', 'sort_order' => 4],

            // 9 · SANTÉ (~580 g)
            ['category' => 'health', 'name' => 'Kit pieds complet (Compeed + sparadrap + antiseptique)', 'weight_g' => 220, 'notes' => 'Compeed S+M 12 pcs + Elastoplast 5cm + Biseptine 20mL + aiguille', 'sort_order' => 1],
            ['category' => 'health', 'name' => 'Pharmacie de base (analgésiques + anti-diarrhéique + antihistaminique)', 'weight_g' => 150, 'is_consumable' => true, 'notes' => 'Paracétamol + Ibuprofène + Lopéramide + antihistaminique + pansements', 'sort_order' => 2],
            ['category' => 'health', 'name' => 'Crème anti-frottement Nok 60 mL', 'brand' => 'Nok', 'weight_g' => 65, 'is_consumable' => true, 'notes' => 'Prévention ampoules, appliquer matin zones à risque', 'sort_order' => 3],

            // 10 · NAVIGATION (~1030 g)
            ['category' => 'navigation', 'name' => 'Batterie externe Anker PowerCore 10000', 'brand' => 'Anker', 'model' => 'PowerCore 10000', 'weight_g' => 180, 'notes' => '3-4 recharges téléphone', 'sort_order' => 1],
            ['category' => 'navigation', 'name' => 'Frontale Petzl Tikka Core rechargeable USB', 'brand' => 'Petzl', 'model' => 'Tikka Core', 'weight_g' => 80, 'sort_order' => 2],
            ['category' => 'navigation', 'name' => 'Chargeur double USB-C 20W Anker Nano II', 'brand' => 'Anker', 'model' => 'Nano II 20W', 'weight_g' => 60, 'sort_order' => 3],
            ['category' => 'navigation', 'name' => 'Boussole Silva Ranger SL', 'brand' => 'Silva', 'model' => 'Ranger SL', 'weight_g' => 30, 'notes' => 'Backup GPS mort', 'sort_order' => 4],
            ['category' => 'navigation', 'name' => 'Cartes papier bandeau (1 pays à la fois)', 'weight_g' => 80, 'is_consumable' => true, 'notes' => 'Extraits GPX 2-3 km couloir, recto-verso 2-up, pochette étanche', 'sort_order' => 5],
            ['category' => 'navigation', 'name' => 'Credencial + rechanges (4 total)', 'weight_g' => 100, 'notes' => 'Retirer à Liège Cathédrale ; rechanges pour 2500 km', 'sort_order' => 6],
        ];

        foreach ($soloItems as $itemData) {
            PackItem::query()->updateOrCreate(
                [
                    'pack_scenario_id' => $solo->id,
                    'name' => $itemData['name'],
                ],
                array_merge([
                    'category' => $itemData['category'],
                    'brand' => $itemData['brand'] ?? null,
                    'model' => $itemData['model'] ?? null,
                    'weight_g' => $itemData['weight_g'],
                    'is_shared' => $itemData['is_shared'] ?? false,
                    'is_consumable' => $itemData['is_consumable'] ?? false,
                    'replacement_km' => $itemData['replacement_km'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                    'sort_order' => $itemData['sort_order'] ?? 0,
                ], []),
            );
        }

        $count = $solo->items()->count();
        $this->command->info("Scénario Solo — {$count} items créés.");
    }

    // ─── Scénario DUO ─────────────────────────────────────────────────────────

    private function seedDuoScenario(Pilgrim $bocal): void
    {
        /** @var PackScenario $duo */
        $duo = PackScenario::query()->updateOrCreate(
            [
                'pilgrim_id' => $bocal->id,
                'name' => 'Duo 7,5 kg Belgique',
            ],
            [
                'description' => 'Configuration duo mai-juin, Via Mosana Belgique. Objectif 7,5 kg/pers. Mutualisation tente 2P, réchaud, filtre, pharmacie principale.',
                'target_base_weight_kg' => 7.50,
                'configuration' => 'duo',
                'season' => 'spring',
            ],
        );

        $this->command->info("Scénario Duo : {$duo->id}");

        // Items Duo — items mutualisés marqués is_shared=true
        $duoItems = [
            // 1 · PORTAGE (×2 non mutualisé)
            ['category' => 'portage', 'name' => 'Sac à dos Osprey Exos 48', 'brand' => 'Osprey', 'model' => 'Exos 48', 'weight_g' => 1100, 'sort_order' => 1],
            ['category' => 'portage', 'name' => 'Housse pluie Osprey Ultralight M', 'brand' => 'Osprey', 'model' => 'Ultralight Raincover M', 'weight_g' => 90, 'sort_order' => 2],
            ['category' => 'portage', 'name' => 'Sacoche ceinture Ferrino Sky Bag', 'brand' => 'Ferrino', 'model' => 'Sky Bag', 'weight_g' => 80, 'sort_order' => 3],
            ['category' => 'portage', 'name' => 'Sac étanche couchage Sea to Summit Ultra-Sil 20L', 'brand' => 'Sea to Summit', 'model' => 'Ultra-Sil Dry 20L', 'weight_g' => 60, 'sort_order' => 4],

            // 2 · COUCHAGE DUO (~1750 g/pers — tente 2P mutualisée)
            ['category' => 'sleeping', 'name' => 'Tente MSR Hubba Hubba NX 2 (mutualisée)', 'brand' => 'MSR', 'model' => 'Hubba Hubba NX 2', 'weight_g' => 1500, 'is_shared' => true, 'notes' => 'Tente 2P mutualisée — portée à tour de rôle', 'replacement_km' => 3000, 'sort_order' => 1],
            ['category' => 'sleeping', 'name' => 'Matelas Therm-a-Rest NeoAir XLite NXT', 'brand' => 'Therm-a-Rest', 'model' => 'NeoAir XLite NXT', 'weight_g' => 350, 'sort_order' => 2],
            ['category' => 'sleeping', 'name' => 'Sac de couchage Sea to Summit Spark Sp III', 'brand' => 'Sea to Summit', 'model' => 'Spark Sp III', 'weight_g' => 550, 'notes' => 'Confort +5°C', 'replacement_km' => 3000, 'sort_order' => 3],
            ['category' => 'sleeping', 'name' => 'Sac à viande soie Cocoon MummyLiner', 'brand' => 'Cocoon', 'model' => 'MummyLiner Silk', 'weight_g' => 100, 'sort_order' => 4],

            // 3 · CUISINE DUO (mutualisée, ~230 g + gaz / pers)
            ['category' => 'cooking', 'name' => 'Réchaud MSR PocketRocket 2 (mutualisé)', 'brand' => 'MSR', 'model' => 'PocketRocket 2', 'weight_g' => 75, 'is_shared' => true, 'sort_order' => 1],
            ['category' => 'cooking', 'name' => 'Cartouche gaz 100 g (mutualisée)', 'brand' => 'Coleman', 'model' => 'C100', 'weight_g' => 190, 'is_shared' => true, 'is_consumable' => true, 'notes' => 'À remplacer tous les 15-20 jours', 'sort_order' => 2],
            ['category' => 'cooking', 'name' => 'Popote Toaks Titanium 1300 mL (mutualisée)', 'brand' => 'Toaks', 'model' => 'Titanium Pot 1300 mL', 'weight_g' => 155, 'is_shared' => true, 'notes' => 'Duo — un plat pour deux', 'sort_order' => 3],
            ['category' => 'cooking', 'name' => 'Cuillère titane', 'brand' => 'Toaks', 'model' => 'Titanium Long Spoon', 'weight_g' => 15, 'sort_order' => 4],
            ['category' => 'cooking', 'name' => 'Opinel N°8 carbone', 'brand' => 'Opinel', 'model' => 'N°8 lame carbone', 'weight_g' => 42, 'sort_order' => 5],

            // 4 · EAU (~275 g / pers — filtre mutualisé)
            ['category' => 'water', 'name' => 'Poche à eau Platypus Big Zip LP 2L', 'brand' => 'Platypus', 'model' => 'Big Zip LP 2L', 'weight_g' => 150, 'sort_order' => 1],
            ['category' => 'water', 'name' => 'Nalgene Wide-Mouth 1L', 'brand' => 'Nalgene', 'model' => 'Wide-Mouth 1L', 'weight_g' => 180, 'sort_order' => 2],
            ['category' => 'water', 'name' => 'Filtre Sawyer Squeeze (mutualisé)', 'brand' => 'Sawyer', 'model' => 'Squeeze', 'weight_g' => 90, 'is_shared' => true, 'replacement_km' => 5000, 'sort_order' => 3],

            // 7 · PLUIE/FROID
            ['category' => 'clothing', 'name' => 'Veste hardshell Rab Kinetic Alpine', 'brand' => 'Rab', 'model' => 'Kinetic Alpine', 'weight_g' => 350, 'sort_order' => 1],
            ['category' => 'clothing', 'name' => 'Sur-pantalon pluie Montane Pac Plus XT', 'brand' => 'Montane', 'model' => 'Pac Plus XT', 'weight_g' => 250, 'sort_order' => 2],
            ['category' => 'clothing', 'name' => 'Polaire Patagonia R1 Pullover', 'brand' => 'Patagonia', 'model' => 'R1 Pullover', 'weight_g' => 300, 'sort_order' => 3],
            ['category' => 'clothing', 'name' => 'Doudoune Uniqlo Ultra Light Down', 'brand' => 'Uniqlo', 'model' => 'Ultra Light Down', 'weight_g' => 250, 'sort_order' => 4],
            ['category' => 'clothing', 'name' => 'T-shirt mérinos nuit', 'brand' => 'Woolpower', 'model' => '200', 'weight_g' => 130, 'sort_order' => 5],
            ['category' => 'clothing', 'name' => 'Legging Icebreaker 200 Oasis', 'brand' => 'Icebreaker', 'model' => '200 Oasis Legging', 'weight_g' => 200, 'sort_order' => 6],

            // 8 · HYGIÈNE (savon + trowel + papier mutualisés)
            ['category' => 'hygiene', 'name' => 'Savon Dr. Bronner Baby Mild (mutualisé)', 'brand' => 'Dr. Bronner', 'weight_g' => 65, 'is_shared' => true, 'is_consumable' => true, 'sort_order' => 1],
            ['category' => 'hygiene', 'name' => 'Serviette microfibre Sea to Summit DryLite M', 'brand' => 'Sea to Summit', 'model' => 'DryLite Towel M', 'weight_g' => 130, 'sort_order' => 2],
            ['category' => 'hygiene', 'name' => 'Trowel The Deuce #2 (mutualisé)', 'brand' => 'The Deuce', 'model' => '#2', 'weight_g' => 17, 'is_shared' => true, 'sort_order' => 3],

            // 9 · SANTÉ (kit principal mutualisé + kit perso)
            ['category' => 'health', 'name' => 'Pharmacie principale mutualisée', 'weight_g' => 300, 'is_shared' => true, 'notes' => 'Kit pieds Compeed + antiseptiques + bandages', 'sort_order' => 1],
            ['category' => 'health', 'name' => 'Mini-pharmacie personnelle', 'weight_g' => 130, 'is_consumable' => true, 'notes' => 'Paracétamol + ampoules perso + gel hydro', 'sort_order' => 2],
            ['category' => 'health', 'name' => 'Crème anti-frottement Nok 60 mL', 'brand' => 'Nok', 'weight_g' => 65, 'is_consumable' => true, 'sort_order' => 3],

            // 10 · NAVIGATION (chargeur + batterie mutualisés)
            ['category' => 'navigation', 'name' => 'Batterie externe Anker PowerCore 10000 (mutualisée)', 'brand' => 'Anker', 'model' => 'PowerCore 10000', 'weight_g' => 180, 'is_shared' => true, 'sort_order' => 1],
            ['category' => 'navigation', 'name' => 'Chargeur double USB-C 20W Anker (mutualisé)', 'brand' => 'Anker', 'model' => 'Nano II 20W', 'weight_g' => 60, 'is_shared' => true, 'sort_order' => 2],
            ['category' => 'navigation', 'name' => 'Frontale Petzl Tikka Core', 'brand' => 'Petzl', 'model' => 'Tikka Core', 'weight_g' => 80, 'sort_order' => 3],
            ['category' => 'navigation', 'name' => 'Boussole Silva Ranger SL (mutualisée)', 'brand' => 'Silva', 'model' => 'Ranger SL', 'weight_g' => 30, 'is_shared' => true, 'sort_order' => 4],
            ['category' => 'navigation', 'name' => 'Credencial + rechanges', 'weight_g' => 100, 'sort_order' => 5],
        ];

        foreach ($duoItems as $itemData) {
            PackItem::query()->updateOrCreate(
                [
                    'pack_scenario_id' => $duo->id,
                    'name' => $itemData['name'],
                ],
                [
                    'category' => $itemData['category'],
                    'brand' => $itemData['brand'] ?? null,
                    'model' => $itemData['model'] ?? null,
                    'weight_g' => $itemData['weight_g'],
                    'is_shared' => $itemData['is_shared'] ?? false,
                    'is_consumable' => $itemData['is_consumable'] ?? false,
                    'replacement_km' => $itemData['replacement_km'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                    'sort_order' => $itemData['sort_order'] ?? 0,
                ],
            );
        }

        $count = $duo->items()->count();
        $this->command->info("Scénario Duo — {$count} items créés.");
    }

    // ─── ItemAssignment d'exemple ─────────────────────────────────────────────

    private function seedItemAssignmentExamples(Pilgrim $bocal): void
    {
        // Chercher le Departure bocal (BE-01 → BE-12)
        $startStage = Stage::query()->where('code', 'BE-01')->first();
        $endStage = Stage::query()->where('code', 'BE-12')->first();

        if ($startStage === null || $endStage === null) {
            $this->command->warn('Stages BE-01/BE-12 introuvables — ItemAssignment examples non créés.');

            return;
        }

        $departure = Departure::query()
            ->where('pilgrim_id', $bocal->id)
            ->where('start_stage_id', $startStage->id)
            ->where('end_stage_id', $endStage->id)
            ->first();

        if ($departure === null) {
            $this->command->warn('Departure bocal BE-01→BE-12 introuvable — ItemAssignment examples non créés.');

            return;
        }

        // Scénario Solo
        /** @var PackScenario|null $solo */
        $solo = PackScenario::query()
            ->where('pilgrim_id', $bocal->id)
            ->where('name', 'Solo 8,5 kg Belgique')
            ->first();

        if ($solo === null) {
            return;
        }

        // Lier le scénario Solo au Departure
        $departure->update(['pack_scenario_id' => $solo->id]);

        // Quelques ItemAssignments d'exemple (tente, sac de couchage, frontale)
        $exampleItems = [
            'Tente MSR Hubba NX 1',
            'Sac de couchage Sea to Summit Spark Sp III',
            'Frontale Petzl Tikka Core rechargeable USB',
            'Filtre Sawyer Squeeze',
        ];

        foreach ($exampleItems as $itemName) {
            $item = $solo->items()->where('name', $itemName)->first();

            if ($item === null) {
                continue;
            }

            ItemAssignment::query()->updateOrCreate(
                [
                    'pack_item_id' => $item->id,
                    'departure_id' => $departure->id,
                    'assigned_to_pilgrim_id' => $bocal->id,
                ],
                [
                    'from_stage_id' => null,
                    'to_stage_id' => null,
                    'notes' => null,
                ],
            );
        }

        $count = ItemAssignment::query()->where('departure_id', $departure->id)->count();
        $this->command->info("ItemAssignments d'exemple — {$count} créés sur le Departure bocal.");
    }
}
