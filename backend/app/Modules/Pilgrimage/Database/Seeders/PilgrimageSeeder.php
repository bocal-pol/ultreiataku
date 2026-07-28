<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orchestrateur de tous les seeders Pilgrimage.
 * Ordre de dépendance strict :
 *   1. RouteSeeder                    → crée les routes (parents)
 *   2. WaypointSeeder                 → crée tous les waypoints (villes + POI)
 *   3. StageSeeder                    → crée les étapes principales
 *   4. StageVariantBelgiqueSeeder     → crée les 5 variantes BE + branche Bruxelles
 *   5. StagePOISeeder                 → attache les POI aux étapes via pivot stage_waypoint
 *   6. GpxTraceSeeder                 → importe les traces GPX principales
 *   7. GpxTraceVariantBelgiqueSeeder  → importe les GPX des variantes BE
 *   8. AccommodationSeeder            → hébergements réels Belgique (vague 1b)
 *   9. MealSeeder                     → repas signatures Belgique (vague 1b)
 *  10. PersonalTripSeeder             → seeds bocal (RÈGLE UTILISATEUR — vague 1c)
 *  11. PackScenarioSeeder             → scénarios de sac bocal (ULTREIA-42 — vague 1d)
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
            StageVariantBelgiqueSeeder::class,
            StagePOISeeder::class,
            GpxTraceSeeder::class,
            GpxTraceVariantBelgiqueSeeder::class,
            AccommodationSeeder::class,
            MealSeeder::class,
            PersonalTripSeeder::class,
            PackScenarioSeeder::class,
        ]);

        $this->command->info('=== Pilgrimage Seeder — terminé ===');
    }
}
