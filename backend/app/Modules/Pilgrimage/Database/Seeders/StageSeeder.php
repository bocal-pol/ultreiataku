<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        $mosana = PilgrimageRoute::where('slug', 'via-mosana-belgique')->first();
        $monastique = PilgrimageRoute::where('slug', 'via-monastique-belgique')->first();

        if ($mosana === null || $monastique === null) {
            $this->command->error('Routes non trouvées. Exécutez RouteSeeder d\'abord.');

            return;
        }

        // Charger les waypoints par slug
        $wp = Waypoint::whereIn('slug', [
            'liege-cathedrale', 'amay', 'huy', 'andenne', 'namur',
            'yvoir', 'dinant', 'hastiere', 'givet', 'doische',
            'olloy-sur-viroin', 'couvin', 'rocroi',
        ])->pluck('id', 'slug');

        if ($wp->count() < 13) {
            $this->command->error('Waypoints manquants. Exécutez WaypointSeeder d\'abord.');

            return;
        }

        // ── Voie Mosane (BE-01 → BE-08) ─────────────────────────────────────
        $mosanaStages = [
            [
                'route_id' => $mosana->id,
                'code' => 'BE-01',
                'name' => [
                    'fr' => 'Liège → Amay',
                    'nl' => 'Luik → Amay',
                    'de' => 'Lüttich → Amay',
                ],
                'day_number' => 1,
                'start_waypoint_id' => $wp['liege-cathedrale'],
                'end_waypoint_id' => $wp['amay'],
                'distance_km' => 22.00,
                'elevation_gain_m' => 250,
                'elevation_loss_m' => 230,
                'estimated_duration_h' => 5.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'donativo',
                'notes' => [
                    'fr' => 'Première étape modérée pour se caler. Ne pas partir de Liège avant 8h. Prévoir 2 L d\'eau — pas de fontaine jusqu\'à Amay.',
                    'nl' => 'Eerste matige etappe om in het ritme te komen. Niet voor 8u vertrekken. 2 L water voorzien.',
                    'de' => 'Erste moderate Etappe zum Eingewöhnen. Nicht vor 8 Uhr starten. 2 L Wasser vorsehen.',
                ],
                'sort_order' => 1,
            ],
            [
                'route_id' => $mosana->id,
                'code' => 'BE-02',
                'name' => ['fr' => 'Amay → Huy', 'nl' => 'Amay → Hoei', 'de' => 'Amay → Huy'],
                'day_number' => 2,
                'start_waypoint_id' => $wp['amay'],
                'end_waypoint_id' => $wp['huy'],
                'distance_km' => 11.00,
                'elevation_gain_m' => 150,
                'elevation_loss_m' => 140,
                'estimated_duration_h' => 2.5,
                'difficulty' => 'easy',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Journée courte — 11 km le matin, après-midi de visite Huy (Li Rondia, Citadelle). L\'un des meilleurs après-midis culturels du tronçon.',
                    'nl' => 'Korte dag — 11 km \'s morgens, namiddag bezoek Hoei (Li Rondia, Citadel).',
                    'de' => 'Kurze Etappe — 11 km morgens, nachmittags Besichtigung Huy (Li Rondia, Zitadelle).',
                ],
                'sort_order' => 2,
            ],
            [
                'route_id' => $mosana->id,
                'code' => 'BE-03',
                'name' => ['fr' => 'Huy → Andenne', 'nl' => 'Hoei → Andenne', 'de' => 'Huy → Andenne'],
                'day_number' => 3,
                'start_waypoint_id' => $wp['huy'],
                'end_waypoint_id' => $wp['andenne'],
                'distance_km' => 18.00,
                'elevation_gain_m' => 200,
                'elevation_loss_m' => 190,
                'estimated_duration_h' => 4.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Grotte Scladina accessible 1er dimanche du mois à 14h — planifier le passage en conséquence. Fontaine à Bas-Oha.',
                    'nl' => 'Grot Scladina toegankelijk 1e zondag van de maand om 14u.',
                    'de' => 'Höhle Scladina zugänglich am 1. Sonntag des Monats um 14 Uhr.',
                ],
                'sort_order' => 3,
            ],
            [
                'route_id' => $mosana->id,
                'code' => 'BE-04',
                'name' => ['fr' => 'Andenne → Namur', 'nl' => 'Andenne → Namen', 'de' => 'Andenne → Namur'],
                'day_number' => 4,
                'start_waypoint_id' => $wp['andenne'],
                'end_waypoint_id' => $wp['namur'],
                'distance_km' => 22.00,
                'elevation_gain_m' => 250,
                'elevation_loss_m' => 240,
                'estimated_duration_h' => 5.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Namur mérite une journée de repos : Citadelle, confluent Sambre-Meuse, TreM.a, ruelles. Escavèche de Meuse au dîner.',
                    'nl' => 'Namen verdient een rustdag: Citadel, Sambre-Maas samenvloeiing, TreM.a.',
                    'de' => 'Namur verdient einen Ruhetag: Zitadelle, Sambre-Maas-Mündung, TreM.a.',
                ],
                'sort_order' => 4,
            ],
            [
                'route_id' => $mosana->id,
                'code' => 'BE-05',
                'name' => ['fr' => 'Namur → Yvoir', 'nl' => 'Namen → Yvoir', 'de' => 'Namur → Yvoir'],
                'day_number' => 5,
                'start_waypoint_id' => $wp['namur'],
                'end_waypoint_id' => $wp['yvoir'],
                'distance_km' => 18.00,
                'elevation_gain_m' => 200,
                'elevation_loss_m' => 190,
                'estimated_duration_h' => 4.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'camping',
                'notes' => [
                    'fr' => 'L\'une des plus belles portions — falaises calcaires, méandres Meuse. Forteresse Poilvache : +250m D+ supplémentaires mais vue exceptionnelle.',
                    'nl' => 'Een van de mooiste stukken — kalksteenkliffen, Maas-meanders. Vesting Poilvache: +250m D+ extra maar uitzonderlijk uitzicht.',
                    'de' => 'Eine der schönsten Strecken — Kalksteinklippen, Maas-Mäander. Festung Poilvache: +250m D+ extra, aber außergewöhnliche Aussicht.',
                ],
                'sort_order' => 5,
            ],
            [
                'route_id' => $mosana->id,
                'code' => 'BE-06',
                'name' => ['fr' => 'Yvoir → Dinant', 'nl' => 'Yvoir → Dinant', 'de' => 'Yvoir → Dinant'],
                'day_number' => 6,
                'start_waypoint_id' => $wp['yvoir'],
                'end_waypoint_id' => $wp['dinant'],
                'distance_km' => 14.00,
                'elevation_gain_m' => 150,
                'elevation_loss_m' => 140,
                'estimated_duration_h' => 3.5,
                'difficulty' => 'easy',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Journée de repos possible à Dinant — 5 POI dans 1 km. Flamiche dinantaise + couque de Dinant au dîner.',
                    'nl' => 'Rustdag mogelijk in Dinant — 5 POI binnen 1 km. Flamiche dinantaise + couque de Dinant.',
                    'de' => 'Ruhetag in Dinant möglich — 5 POI in 1 km. Flamiche dinantaise + couque de Dinant.',
                ],
                'sort_order' => 6,
            ],
            [
                'route_id' => $mosana->id,
                'code' => 'BE-07',
                'name' => ['fr' => 'Dinant → Hastière', 'nl' => 'Dinant → Hastière', 'de' => 'Dinant → Hastière'],
                'day_number' => 7,
                'start_waypoint_id' => $wp['dinant'],
                'end_waypoint_id' => $wp['hastiere'],
                'distance_km' => 14.00,
                'elevation_gain_m' => 100,
                'elevation_loss_m' => 95,
                'estimated_duration_h' => 3.5,
                'difficulty' => 'easy',
                'accommodation_type_default' => 'abbey',
                'notes' => [
                    'fr' => 'Passage d\'eau de Waulsort — dernier passeur manuel de Wallonie depuis 1871. Gîte de l\'Abbaye Notre-Dame d\'Hastière (expérience monastique).',
                    'nl' => 'Veerpont van Waulsort — laatste handmatige veer van Wallonië sinds 1871. Abdijverblijf Notre-Dame d\'Hastière.',
                    'de' => 'Fähre von Waulsort — letzte manuelle Fähre Walloniens seit 1871. Abteiherberge Notre-Dame d\'Hastière.',
                ],
                'sort_order' => 7,
            ],
            [
                'route_id' => $mosana->id,
                'code' => 'BE-08',
                'name' => ['fr' => 'Hastière → Givet', 'nl' => 'Hastière → Givet', 'de' => 'Hastière → Givet'],
                'day_number' => 8,
                'start_waypoint_id' => $wp['hastiere'],
                'end_waypoint_id' => $wp['givet'],
                'distance_km' => 11.00,
                'elevation_gain_m' => 100,
                'elevation_loss_m' => 95,
                'estimated_duration_h' => 2.5,
                'difficulty' => 'easy',
                'accommodation_type_default' => 'camping',
                'notes' => [
                    'fr' => 'Passage frontière BE → FR (Schengen, rien à faire). Fort Charlemont + Tour Grégoire à Givet. Journée de repos à Givet conseillée.',
                    'nl' => 'Grensovergang BE → FR (Schengen). Fort Charlemont + Tour Grégoire te Givet. Rustdag aanbevolen.',
                    'de' => 'Grenzübergang BE → FR (Schengen). Fort Charlemont + Tour Grégoire in Givet. Ruhetag empfohlen.',
                ],
                'sort_order' => 8,
            ],
        ];

        // ── Voie Monastique (BE-09 → BE-12) ─────────────────────────────────
        $monastiqueStages = [
            [
                'route_id' => $monastique->id,
                'code' => 'BE-09',
                'name' => ['fr' => 'Givet → Doische', 'nl' => 'Givet → Doische', 'de' => 'Givet → Doische'],
                'day_number' => 1,
                'start_waypoint_id' => $wp['givet'],
                'end_waypoint_id' => $wp['doische'],
                'distance_km' => 18.00,
                'elevation_gain_m' => 300,
                'elevation_loss_m' => 260,
                'estimated_duration_h' => 4.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Retour en Belgique. Entrée dans la Fagne. Peu de café — remplir 2 L avant Foisches. Cacasse à cul nu au dîner.',
                    'nl' => 'Terug naar België. Binnenkomst in de Fagne. Weinig cafés — 2 L water vullen voor Foisches.',
                    'de' => 'Rückkehr nach Belgien. Eintritt in die Fagne. Wenig Cafés — 2 L Wasser in Foisches auffüllen.',
                ],
                'sort_order' => 1,
            ],
            [
                'route_id' => $monastique->id,
                'code' => 'BE-10',
                'name' => ['fr' => 'Doische → Olloy-sur-Viroin', 'nl' => 'Doische → Olloy-sur-Viroin', 'de' => 'Doische → Olloy-sur-Viroin'],
                'day_number' => 2,
                'start_waypoint_id' => $wp['doische'],
                'end_waypoint_id' => $wp['olloy-sur-viroin'],
                'distance_km' => 19.00,
                'elevation_gain_m' => 400,
                'elevation_loss_m' => 380,
                'estimated_duration_h' => 5.5,
                'difficulty' => 'hard',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Roche à Lomme : si un seul détour du tronçon, celui-ci ou Scladina. Bivouac INTERDIT dans la Réserve Viroin-Hermeton.',
                    'nl' => 'Roche à Lomme: als maar één omweg, dan deze of Scladina. Bivakkeren VERBODEN in Reservaat Viroin-Hermeton.',
                    'de' => 'Roche à Lomme: wenn nur ein Umweg, dann dieser oder Scladina. Bivakkieren VERBOTEN im Naturschutzgebiet.',
                ],
                'sort_order' => 2,
            ],
            [
                'route_id' => $monastique->id,
                'code' => 'BE-11',
                'name' => ['fr' => 'Olloy → Couvin', 'nl' => 'Olloy → Couvin', 'de' => 'Olloy → Couvin'],
                'day_number' => 3,
                'start_waypoint_id' => $wp['olloy-sur-viroin'],
                'end_waypoint_id' => $wp['couvin'],
                'distance_km' => 17.00,
                'elevation_gain_m' => 300,
                'elevation_loss_m' => 280,
                'estimated_duration_h' => 4.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Grotte de Neptune à Petigny : barque dans la rivière souterraine, visite guidée 45 min. Jambon d\'Ardennes au dîner.',
                    'nl' => 'Grot van Neptunus te Petigny: boot in de ondergrondse rivier, rondleiding 45 min. Ardens Hammetje bij het diner.',
                    'de' => 'Neptun-Höhle in Petigny: Boot in unterirdischem Fluss, geführte Tour 45 Min. Ardenner Schinken zum Abendessen.',
                ],
                'sort_order' => 3,
            ],
            [
                'route_id' => $monastique->id,
                'code' => 'BE-12',
                'name' => ['fr' => 'Couvin → Rocroi', 'nl' => 'Couvin → Rocroi', 'de' => 'Couvin → Rocroi'],
                'day_number' => 4,
                'start_waypoint_id' => $wp['couvin'],
                'end_waypoint_id' => $wp['rocroi'],
                'distance_km' => 20.00,
                'elevation_gain_m' => 350,
                'elevation_loss_m' => 340,
                'estimated_duration_h' => 5.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Fin du tronçon Belgique (204 km, 12 jours de marche). Brûly-de-Pesche (bunker Hitler 1940) sur le parcours. Rocroi étoilée : remparts complets, Musée Bataille 1643.',
                    'nl' => 'Einde van het Belgische traject (204 km, 12 loopsdagen). Brûly-de-Pesche (Hitlers bunker 1940). Rocroi: volledige vestingwallen, Museum Slag 1643.',
                    'de' => 'Ende des belgischen Abschnitts (204 km, 12 Wandertage). Brûly-de-Pesche (Hitlers Bunker 1940). Rocroi: vollständige Festungsmauern, Museum der Schlacht 1643.',
                ],
                'sort_order' => 4,
            ],
        ];

        $allStages = array_merge($mosanaStages, $monastiqueStages);

        foreach ($allStages as $stage) {
            Stage::updateOrCreate(
                ['code' => $stage['code']],
                $stage
            );
        }

        $this->command->info(sprintf('StageSeeder : %d étapes créées/mises à jour.', count($allStages)));
    }
}
