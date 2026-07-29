<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use Illuminate\Database\Seeder;

/**
 * Crée les routes Espagne (Camino del Norte).
 *
 * Sources :
 *   - etapes/etapes-espagne.md   (10 segments · 46 étapes + 7 repos · ~905 km)
 *   - plan-voyage-espagne.md
 *
 * Architecture de routes :
 *   - camino-del-norte-es     : route principale ES (country='ES', 39 étapes de marche)
 *   - module-picos-de-europa  : module optionnel Picos (9 étapes PC-01→PC-09, country='ES',
 *                               rattaché comme sous-route complémentaire — les variantes PC
 *                               sont des étapes is_variant=true dans StageSeederEspagne)
 *
 * Idempotent — updateOrCreate sur slug.
 */
class RouteSeederEspagne extends Seeder
{
    public function run(): void
    {
        $routes = [
            [
                'slug' => 'camino-del-norte-es',
                'name' => [
                    'fr' => 'Camino del Norte — Espagne',
                    'nl' => 'Camino del Norte — Spanje',
                    'de' => 'Camino del Norte — Spanien',
                ],
                'description' => [
                    'fr' => 'SJPP → Santiago de Compostela par le Camino del Norte. Approche basque française (4 étapes) puis Norte intégral (35 étapes). ~905 km, 46 jours de marche + 7 jours de repos. La côte cantabrique — Euskadi, Cantabrie, Asturies, Galice.',
                    'nl' => 'SJPP → Santiago de Compostela via de Camino del Norte. Baskische Franse aanloop (4 etappes) dan volledig Norte (35 etappes). ~905 km, 46 loopsdagen + 7 rustdagen. De Kantabrische kust.',
                    'de' => 'SJPP → Santiago de Compostela über den Camino del Norte. Französisch-baskischer Zubringer (4 Etappen) dann vollständiger Norte (35 Etappen). ~905 km, 46 Wandertage + 7 Ruhetage. Die kantabrische Küste.',
                ],
                'country' => 'ES',
                'total_distance_km' => 905.00,
                'total_elevation_gain_m' => 14500,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'module-picos-de-europa',
                'name' => [
                    'fr' => 'Module Picos de Europa (optionnel)',
                    'nl' => 'Module Picos de Europa (optioneel)',
                    'de' => 'Modul Picos de Europa (optional)',
                ],
                'description' => [
                    'fr' => 'Détour optionnel depuis le Norte : Camino Lebaniego (San Vicente → Potes, 72 km) + Fuente Dé + Covadonga. 9 étapes PC-01→PC-09, +3-4 jours. Les Picos de Europa en verticale — la Cantabrie et les Asturies des bergers.',
                    'nl' => 'Optionele omweg van de Norte: Camino Lebaniego (San Vicente → Potes, 72 km) + Fuente Dé + Covadonga. 9 etappes PC-01→PC-09, +3-4 dagen. De Picos de Europa verticaal.',
                    'de' => 'Optionaler Umweg vom Norte: Camino Lebaniego (San Vicente → Potes, 72 km) + Fuente Dé + Covadonga. 9 Etappen PC-01→PC-09, +3-4 Tage. Die Picos de Europa vertikal.',
                ],
                'country' => 'ES',
                'total_distance_km' => 180.00,
                'total_elevation_gain_m' => 5800,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($routes as $route) {
            PilgrimageRoute::updateOrCreate(
                ['slug' => $route['slug']],
                $route,
            );
        }

        $this->command->info('RouteSeederEspagne : 2 routes ES créées/mises à jour.');
    }
}
