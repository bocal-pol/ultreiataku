<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use Illuminate\Database\Seeder;

/**
 * Crée les 2 routes France :
 *   - Via Campaniensis (Rocroi → Vézelay, ~540 km)
 *   - Voie de Vézelay / Via Lemovicensis GR654 (Vézelay → SJPP, ~610 km)
 *
 * Sources : etapes/etapes-france.md + plan-voyage-france.md
 */
class RouteSeederFrance extends Seeder
{
    public function run(): void
    {
        $routes = [
            [
                'slug' => 'via-campaniensis-france',
                'name' => [
                    'fr' => 'Via Campaniensis — Rocroi → Vézelay',
                    'nl' => 'Via Campaniensis — Rocroi → Vézelay',
                    'de' => 'Via Campaniensis — Rocroi → Vézelay',
                ],
                'description' => [
                    'fr' => 'Rocroi → Vézelay à travers Champagne-Ardenne et Bourgogne. ~540 km, 23 étapes de marche (+ variante Faux de Verzy). Voie moins fréquentée, forêts ardennaises, vignobles champenois, Lac du Der, Chablis. La Via Campaniensis rejoint à Vézelay la voie historique de pèlerinage vers Compostelle.',
                    'nl' => 'Rocroi → Vézelay door Champagne-Ardennen en Bourgondië. ~540 km, 23 etappes. Minder drukke pelgrimsroute, Ardense wouden, Champagnewijnstokken, Lac du Der, Chablis.',
                    'de' => 'Rocroi → Vézelay durch Champagne-Ardenne und Burgund. ~540 km, 23 Etappen. Weniger frequentierter Weg, Ardenner Wälder, Champagner-Weinberge, Lac du Der, Chablis.',
                ],
                'country' => 'FR',
                'total_distance_km' => 540.00,
                'total_elevation_gain_m' => 6200,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'voie-vezelay-gr654-france',
                'name' => [
                    'fr' => 'Voie de Vézelay / GR654 — Vézelay → Saint-Jean-Pied-de-Port',
                    'nl' => 'Voie de Vézelay / GR654 — Vézelay → Saint-Jean-Pied-de-Port',
                    'de' => 'Voie de Vézelay / GR654 — Vézelay → Saint-Jean-Pied-de-Port',
                ],
                'description' => [
                    'fr' => 'Vézelay → Saint-Jean-Pied-de-Port via La Charité-sur-Loire, Bourges, Limoges, Oradour-sur-Glane, Périgueux, les Landes et le Béarn. ~610 km, 42 étapes. Variante ouest par Oradour intégrée. 7 sites UNESCO. Réseau des refuges Vézelay Compostelle tous les 25-30 km.',
                    'nl' => 'Vézelay → Saint-Jean-Pied-de-Port via La Charité-sur-Loire, Bourges, Limoges, Oradour-sur-Glane, Périgueux, de Landes en het Béarn. ~610 km, 42 etappes. Westelijke variant via Oradour. 7 UNESCO-sites.',
                    'de' => 'Vézelay → Saint-Jean-Pied-de-Port über La Charité-sur-Loire, Bourges, Limoges, Oradour-sur-Glane, Périgueux, die Landes und das Béarn. ~610 km, 42 Etappen. Westvariante über Oradour. 7 UNESCO-Stätten.',
                ],
                'country' => 'FR',
                'total_distance_km' => 610.00,
                'total_elevation_gain_m' => 8400,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($routes as $route) {
            PilgrimageRoute::updateOrCreate(
                ['slug' => $route['slug']],
                $route
            );
        }

        $this->command->info('RouteSeederFrance : 2 routes FR créées/mises à jour.');
    }
}
