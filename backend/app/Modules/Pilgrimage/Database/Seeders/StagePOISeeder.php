<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

/**
 * Attache les POI (highlights) à chaque étape via la table pivot stage_waypoint.
 * Les waypoints city (start/end) sont déjà liés via start_waypoint_id / end_waypoint_id.
 * Ce seeder ajoute les POI notables traversés.
 */
class StagePOISeeder extends Seeder
{
    public function run(): void
    {
        $stages = Stage::all()->keyBy('code');
        $wp = Waypoint::all()->keyBy('slug');

        if ($stages->isEmpty() || $wp->isEmpty()) {
            $this->command->error('Stages ou waypoints manquants. Exécutez StageSeeder + WaypointSeeder d\'abord.');

            return;
        }

        // Mapping : code étape → [slug waypoint → sort_order, is_highlight]
        $attachments = [
            'BE-02' => [
                ['slug' => 'li-rondia-huy', 'sort_order' => 1, 'is_highlight' => true],
                ['slug' => 'citadelle-namur', 'sort_order' => 2, 'is_highlight' => false],
            ],
            'BE-03' => [
                ['slug' => 'grotte-scladina-sclayn', 'sort_order' => 1, 'is_highlight' => true],
            ],
            'BE-05' => [
                ['slug' => 'forteresse-poilvache', 'sort_order' => 1, 'is_highlight' => true],
            ],
            'BE-06' => [
                ['slug' => 'citadelle-namur', 'sort_order' => 1, 'is_highlight' => false],
            ],
            'BE-07' => [
                ['slug' => 'passage-eau-waulsort', 'sort_order' => 1, 'is_highlight' => true],
            ],
            'BE-08' => [
                ['slug' => 'fort-charlemont-givet', 'sort_order' => 1, 'is_highlight' => true],
            ],
            'BE-10' => [
                ['slug' => 'roche-a-lomme-dourbes', 'sort_order' => 1, 'is_highlight' => true],
            ],
            'BE-11' => [
                ['slug' => 'grotte-neptune-petigny', 'sort_order' => 1, 'is_highlight' => true],
            ],
            'BE-12' => [
                ['slug' => 'rocroi-etoilee', 'sort_order' => 1, 'is_highlight' => true],
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

        $this->command->info("StagePOISeeder : {$attached} liaisons stage↔waypoint créées/mises à jour.");
    }
}
