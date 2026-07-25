<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orchestrateur de tous les seeders Pilgrimage.
 * Ordre de dépendance strict :
 *   1. RouteSeeder         → crée les routes (parents)
 *   2. WaypointSeeder      → crée tous les waypoints (villes + POI)
 *   3. StageSeeder         → crée les étapes (lie route + waypoints start/end)
 *   4. StagePOISeeder      → attache les POI aux étapes via pivot stage_waypoint
 *   5. GpxTraceSeeder      → importe les traces GPX et crée les GpxTrace
 *   6. AccommodationSeeder → hébergements réels Belgique (vague 1b)
 *   7. MealSeeder          → repas signatures Belgique (vague 1b)
 */
class PilgrimageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Pilgrimage Seeder — démarrage ===');

        $this->call([
            RouteSeeder::class,
            WaypointSeeder::class,
            StageSeeder::class,
            StagePOISeeder::class,
            GpxTraceSeeder::class,
            AccommodationSeeder::class,
            MealSeeder::class,
        ]);

        $this->command->info('=== Pilgrimage Seeder — terminé ===');
    }
}
