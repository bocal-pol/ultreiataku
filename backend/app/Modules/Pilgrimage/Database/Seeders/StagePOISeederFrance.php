<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

/**
 * Attache les POI France aux étapes via la table pivot stage_waypoint.
 *
 * Sources : poi/patrimoine-france.md (POI vérifiés distance au chemin)
 */
class StagePOISeederFrance extends Seeder
{
    public function run(): void
    {
        $stages = Stage::all()->keyBy('code');
        $wp = Waypoint::all()->keyBy('slug');

        if ($stages->isEmpty() || $wp->isEmpty()) {
            $this->command->error('Stages ou waypoints manquants. Exécutez StageSeederFrance + WaypointSeederFrance d\'abord.');

            return;
        }

        // Mapping : code étape → [slug waypoint → sort_order, is_highlight]
        // Uniquement les POI vérifiés "sur le chemin" ou "< 2 km A/R" du tracé.
        $attachments = [
            // FR-03 — Reims (Cathédrale UNESCO + Basilique Saint-Remi)
            'FR-03' => [
                ['slug' => 'cathedrale-reims', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-04 — Verzy : les Faux de Verzy (1,5 km du village)
            'FR-04' => [
                ['slug' => 'faux-de-verzy', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-05 — Châlons : collégiale Notre-Dame-en-Vaux (UNESCO) sur le chemin
            // Utilise le waypoint ville chalons-en-champagne
            'FR-05' => [
                // Pas de POI waypoint dédié — intégré aux notes d'étape
            ],

            // FR-15 — Vézelay (Basilique UNESCO)
            'FR-15' => [
                ['slug' => 'basilique-vezelay', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-17 — La Charité-sur-Loire (Prieuré UNESCO)
            'FR-17' => [
                ['slug' => 'prieures-la-charite-loire', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-18 — Bourges (Cathédrale UNESCO)
            'FR-18' => [
                ['slug' => 'cathedrale-bourges', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-25 — Oradour-sur-Glane (sur le tracé variante ouest)
            'FR-25' => [
                ['slug' => 'village-martyr-oradour', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-29 — Périgueux (Cathédrale Saint-Front UNESCO)
            'FR-29' => [
                ['slug' => 'cathedrale-saint-front-perigueux', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-32 — Bazas (Cathédrale UNESCO)
            'FR-32' => [
                ['slug' => 'cathedrale-bazas', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-35 — Saint-Sever (Abbatiale UNESCO)
            'FR-35' => [
                ['slug' => 'abbatiale-saint-sever', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-39 — Saint-Palais : Stèle de Gibraltar (convergence des 3 voies)
            'FR-39' => [
                ['slug' => 'stele-gibraltar', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-40 — SJPP : Porte Saint-Jacques (UNESCO)
            'FR-40' => [
                ['slug' => 'porte-saint-jacques-sjpp', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // FR-04V-FAUX-VERZY — variante : les Faux de Verzy sont le POI de la boucle
            'FR-04V-FAUX-VERZY' => [
                ['slug' => 'faux-de-verzy', 'sort_order' => 1, 'is_highlight' => true],
            ],
        ];

        $attached = 0;

        foreach ($attachments as $stageCode => $pois) {
            $stage = $stages->get($stageCode);

            if ($stage === null) {
                $this->command->warn("Étape {$stageCode} non trouvée, skip.");

                continue;
            }

            foreach ($pois as $poiData) {
                $waypoint = $wp->get($poiData['slug']);

                if ($waypoint === null) {
                    $this->command->warn("Waypoint {$poiData['slug']} non trouvé, skip.");

                    continue;
                }

                $stage->waypoints()->syncWithoutDetaching([
                    $waypoint->id => [
                        'sort_order' => $poiData['sort_order'],
                        'is_highlight' => $poiData['is_highlight'],
                    ],
                ]);

                $attached++;
            }
        }

        $this->command->info("StagePOISeederFrance : {$attached} liaisons stage↔waypoint FR créées/mises à jour.");
    }
}
