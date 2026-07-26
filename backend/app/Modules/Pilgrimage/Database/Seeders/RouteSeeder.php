<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            [
                'slug' => 'via-mosana-belgique',
                'name' => ['fr' => 'Voie Mosane', 'nl' => 'Moezelweg', 'de' => 'Maasweg'],
                'description' => [
                    'fr' => 'Liège → Givet le long de la Meuse. 8 étapes, ~111 km, voie historique de pèlerinage mosane.',
                    'nl' => 'Luik → Givet langs de Maas. 8 etappes, ~111 km, historische pelgrimsroute.',
                    'de' => 'Lüttich → Givet entlang der Maas. 8 Etappen, ~111 km, historischer Pilgerweg.',
                ],
                'country' => 'BE',
                'total_distance_km' => 111.00,
                'total_elevation_gain_m' => 1150,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'via-monastique-belgique',
                'name' => ['fr' => 'Voie Monastique', 'nl' => 'Kloosterweg', 'de' => 'Klosterweg'],
                'description' => [
                    'fr' => 'Givet → Rocroi à travers la Fagne et les Ardennes. 4 étapes, ~74 km, prolongement de la Voie Mosane.',
                    'nl' => 'Givet → Rocroi door de Fagne en de Ardennen. 4 etappes, ~74 km.',
                    'de' => 'Givet → Rocroi durch Fagne und Ardennen. 4 Etappen, ~74 km.',
                ],
                'country' => 'BE',
                'total_distance_km' => 74.00,
                'total_elevation_gain_m' => 1350,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($routes as $route) {
            PilgrimageRoute::updateOrCreate(
                ['slug' => $route['slug']],
                $route
            );
        }

        $this->command->info('RouteSeeder : 2 routes créées/mises à jour.');
    }
}
