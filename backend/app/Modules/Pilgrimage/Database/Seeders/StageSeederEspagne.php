<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

/**
 * Étapes principales Espagne — Camino del Norte + Module Picos de Europa.
 *
 * 39 étapes ES (ES-01 → ES-39) alignées exactement sur les 39 fichiers GPX :
 *   gpx/reel/jours/ES-01-Bidarray.gpx … ES-39-Santiago.gpx
 * La destination de chaque étape = nom du fichier GPX.
 *
 * 9 étapes PC (PC-01 → PC-09) alignées sur :
 *   gpx/reel/jours/PC-01-Cades.gpx … PC-09-Ribadesella.gpx
 *
 * Les jours de repos (R1 Bilbao, R2 Santander, R3 Santillana, R4 Gijón,
 * R5 Luarca, R6-R7 Santiago) NE SONT PAS des étapes de marche.
 * Les GPX couvrent les 39 jours de marche effectifs.
 *
 * Idempotent — updateOrCreate sur code.
 */
class StageSeederEspagne extends Seeder
{
    public function run(): void
    {
        $norte = PilgrimageRoute::where('slug', 'camino-del-norte-es')->first();
        $picos = PilgrimageRoute::where('slug', 'module-picos-de-europa')->first();

        if ($norte === null) {
            $this->command->error('Route camino-del-norte-es non trouvée. Exécutez RouteSeederEspagne d\'abord.');

            return;
        }

        $wp = Waypoint::whereIn('slug', [
            'sjpp', 'bidarray', 'itxassou', 'ascain', 'irun',
            'san-sebastian', 'zarautz', 'deba', 'markina-xemein', 'gernika', 'bilbao',
            'portugalete', 'castro-urdiales', 'laredo', 'santona',
            'guemes', 'santander', 'requejada', 'santillana-del-mar',
            'comillas', 'san-vicente-de-la-barquera',
            'colombres', 'llanes', 'nueva-cuerres', 'ribadesella', 'villaviciosa',
            'gijon', 'aviles', 'soto-de-luina', 'luarca',
            'navia', 'ribadeo', 'lourenza', 'mondonedo',
            'vilalba', 'miraz', 'sobrado-dos-monxes',
            'arzua', 'o-pedrouzo', 'santiago-de-compostela',
            'potes', 'fuente-de', 'arenas-de-cabrales',
            'cades-liebana', 'cabanes-picos', 'sotres', 'arriondas-picos',
        ])->pluck('id', 'slug');

        // ═══════════════════════════════════════════════════════════════
        // CAMINO DEL NORTE — 39 étapes ES-01 → ES-39
        // ═══════════════════════════════════════════════════════════════

        $stagesNorte = [

            // SEGMENT P — Approche basque française (J1-J4)

            [
                'route_id' => $norte->id, 'code' => 'ES-01', 'day_number' => 1, 'sort_order' => 1,
                'name' => ['fr' => 'SJPP → Bidarray', 'nl' => 'SJPP → Bidarray', 'de' => 'SJPP → Bidarray'],
                'start_waypoint_id' => $wp['sjpp'] ?? null, 'end_waypoint_id' => $wp['bidarray'] ?? null,
                'distance_km' => 21.00, 'elevation_gain_m' => 350, 'elevation_loss_m' => 320,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Dernier dîner français — truite du gave. Vallée de la Nive via Ossès. Credencial n°3 à démarrer à SJPP.',
                    'nl' => 'Laatste Frans diner — forel van de bergstroom. Nive-vallei via Ossès. Credencial nr. 3 in SJPP.',
                    'de' => 'Letztes französisches Abendessen — Lachsforelle. Nive-Tal via Ossès. Credencial Nr. 3 in SJPP.',
                ],
            ],
            [
                'route_id' => $norte->id, 'code' => 'ES-02', 'day_number' => 2, 'sort_order' => 2,
                'name' => ['fr' => 'Bidarray → Itxassou (Pas de Roland)', 'nl' => 'Bidarray → Itxassou (Pas de Roland)', 'de' => 'Bidarray → Itxassou (Pas de Roland)'],
                'start_waypoint_id' => $wp['bidarray'] ?? null, 'end_waypoint_id' => $wp['itxassou'] ?? null,
                'distance_km' => 13.00, 'elevation_gain_m' => 250, 'elevation_loss_m' => 240,
                'estimated_duration_h' => 3.5, 'difficulty' => 'easy', 'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Journée courte. Cerises noires d\'Itxassou. Pas de Roland : gorges de la Nive, rocher percé légendaire.',
                    'nl' => 'Korte dag. Zwarte kersen van Itxassou. Pas de Roland: kloof van de Nive.',
                    'de' => 'Kurze Etappe. Schwarze Kirschen. Pas de Roland: Nive-Schlucht, legendärer Fels.',
                ],
            ],
            [
                'route_id' => $norte->id, 'code' => 'ES-03', 'day_number' => 3, 'sort_order' => 3,
                'name' => ['fr' => 'Itxassou → Ascain (via Espelette)', 'nl' => 'Itxassou → Ascain (via Espelette)', 'de' => 'Itxassou → Ascain (via Espelette)'],
                'start_waypoint_id' => $wp['itxassou'] ?? null, 'end_waypoint_id' => $wp['ascain'] ?? null,
                'distance_km' => 21.00, 'elevation_gain_m' => 400, 'elevation_loss_m' => 380,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Via Espelette (piment AOP, façades ornées) + Souraïde + Saint-Pée-sur-Nivelle. Axoa de veau à Espelette midi.',
                    'nl' => 'Via Espelette (AOP peper) + Souraïde + Saint-Pée-sur-Nivelle. Axoa de veau midi.',
                    'de' => 'Via Espelette (AOP-Paprika) + Souraïde + Saint-Pée. Axoa de veau mittags.',
                ],
            ],
            [
                'route_id' => $norte->id, 'code' => 'ES-04', 'day_number' => 4, 'sort_order' => 4,
                'name' => ['fr' => 'Ascain → Irun (Île des Faisans)', 'nl' => 'Ascain → Irun (Fazanteneiland)', 'de' => 'Ascain → Irun (Fasaneninsel)'],
                'start_waypoint_id' => $wp['ascain'] ?? null, 'end_waypoint_id' => $wp['irun'] ?? null,
                'distance_km' => 19.00, 'elevation_gain_m' => 300, 'elevation_loss_m' => 320,
                'estimated_duration_h' => 5.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Via Ciboure-Hendaye, passage de la Bidassoa. Île des Faisans visible depuis le pont. Premier albergue espagnol ! Premiers pintxos.',
                    'nl' => 'Via Ciboure-Hendaye, oversteek Bidassoa. Fazanteneiland zichtbaar. Eerste Spaans albergue!',
                    'de' => 'Via Ciboure-Hendaye, Bidassoa-Übergang. Fasaneninsel sichtbar. Erstes spanisches Albergue!',
                ],
            ],

            // SEGMENT Q — Irun → Deba (Gipuzkoa)

            [
                'route_id' => $norte->id, 'code' => 'ES-05', 'day_number' => 5, 'sort_order' => 5,
                'name' => ['fr' => 'Irun → San Sebastián (Jaizkibel)', 'nl' => 'Irun → San Sebastián (Jaizkibel)', 'de' => 'Irun → San Sebastián (Jaizkibel)'],
                'start_waypoint_id' => $wp['irun'] ?? null, 'end_waypoint_id' => $wp['san-sebastian'] ?? null,
                'distance_km' => 25.00, 'elevation_gain_m' => 500, 'elevation_loss_m' => 510,
                'estimated_duration_h' => 6.5, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Par le Jaizkibel. Pasajes/Pasaia (barque). La Concha — l\'une des plus belles baies du monde. Pintxos vieille ville.',
                    'nl' => 'Via Jaizkibel. Pasajes/Pasaia (bootoversteek). La Concha — een van de mooiste baaien. Pintxos oude stad.',
                    'de' => 'Via Jaizkibel. Pasajes/Pasaia (Bootsüberfahrt). La Concha. Pintxos Altstadt.',
                ],
            ],
            [
                'route_id' => $norte->id, 'code' => 'ES-06', 'day_number' => 6, 'sort_order' => 6,
                'name' => ['fr' => 'San Sebastián → Zarautz', 'nl' => 'San Sebastián → Zarautz', 'de' => 'San Sebastián → Zarautz'],
                'start_waypoint_id' => $wp['san-sebastian'] ?? null, 'end_waypoint_id' => $wp['zarautz'] ?? null,
                'distance_km' => 22.00, 'elevation_gain_m' => 350, 'elevation_loss_m' => 340,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Vignoble txakoli. Menú del día vue océan. Longue plage de Zarautz (800 m).',
                    'nl' => 'Txakoli-wijngaard. Menú del día met zeezicht. Lang strand van Zarautz.',
                    'de' => 'Txakoli-Weinberg. Menú del día mit Meeresblick. Langer Strand von Zarautz.',
                ],
            ],
            [
                'route_id' => $norte->id, 'code' => 'ES-07', 'day_number' => 7, 'sort_order' => 7,
                'name' => ['fr' => 'Zarautz → Deba (flysch de Zumaia)', 'nl' => 'Zarautz → Deba (flysch Zumaia)', 'de' => 'Zarautz → Deba (Flysch Zumaia)'],
                'start_waypoint_id' => $wp['zarautz'] ?? null, 'end_waypoint_id' => $wp['deba'] ?? null,
                'distance_km' => 22.00, 'elevation_gain_m' => 400, 'elevation_loss_m' => 390,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Flysch de Zumaia : falaises feuilletées (60 Ma), Game of Thrones. Géoparc Basque. Poisson grillé à Deba.',
                    'nl' => 'Flysch van Zumaia: gelaagde kliffen (60 Ma), Game of Thrones. Baskisch Geopark.',
                    'de' => 'Flysch von Zumaia: geschichtete Klippen (60 Ma), Game-of-Thrones-Kulisse. Baskischer Geopark.',
                ],
            ],

            // SEGMENT R — Deba → Bilbao (Bizkaia)

            [
                'route_id' => $norte->id, 'code' => 'ES-08', 'day_number' => 8, 'sort_order' => 8,
                'name' => ['fr' => 'Deba → Markina-Xemein', 'nl' => 'Deba → Markina-Xemein', 'de' => 'Deba → Markina-Xemein'],
                'start_waypoint_id' => $wp['deba'] ?? null, 'end_waypoint_id' => $wp['markina-xemein'] ?? null,
                'distance_km' => 24.00, 'elevation_gain_m' => 750, 'elevation_loss_m' => 730,
                'estimated_duration_h' => 7.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => '⚠️ Étape dure (D+ 750 m). Collines intérieures basques. Partir tôt (6h30). Menú campagnard à Markina.',
                    'nl' => '⚠️ Zware etappe (D+ 750 m). Baskische binnenhills. Vroeg vertrekken (6h30).',
                    'de' => '⚠️ Schwere Etappe (D+ 750 m). Baskisches Hügelland. Früh starten (6:30 Uhr).',
                ],
            ],
            [
                'route_id' => $norte->id, 'code' => 'ES-09', 'day_number' => 9, 'sort_order' => 9,
                'name' => ['fr' => 'Markina-Xemein → Gernika', 'nl' => 'Markina-Xemein → Gernika', 'de' => 'Markina-Xemein → Gernika'],
                'start_waypoint_id' => $wp['markina-xemein'] ?? null, 'end_waypoint_id' => $wp['gernika'] ?? null,
                'distance_km' => 25.00, 'elevation_gain_m' => 500, 'elevation_loss_m' => 490,
                'estimated_duration_h' => 6.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Gernika : arbre (libertés basques), musée de la Paix (bombardement 1937). Marché du lundi. Pimientos de Gernika.',
                    'nl' => 'Gernika: eik (baskische vrijheden), Vredesmuseum (1937). Maandagmarkt.',
                    'de' => 'Gernika: Eiche (baskische Freiheit), Friedensmuseum (1937). Montagsmarkt.',
                ],
            ],
            // ES-10 : Gernika → Bilbao (GPX ES-10-Bilbao.gpx — via Lezama)
            [
                'route_id' => $norte->id, 'code' => 'ES-10', 'day_number' => 10, 'sort_order' => 10,
                'name' => ['fr' => 'Gernika → Bilbao (via Lezama)', 'nl' => 'Gernika → Bilbao (via Lezama)', 'de' => 'Gernika → Bilbao (via Lezama)'],
                'start_waypoint_id' => $wp['gernika'] ?? null, 'end_waypoint_id' => $wp['bilbao'] ?? null,
                'distance_km' => 32.00, 'elevation_gain_m' => 600, 'elevation_loss_m' => 650,
                'estimated_duration_h' => 8.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => '⚠️ Longue étape (32 km, via Lezama). Option : couper à Lezama (21 km). Guggenheim Bilbao (~18 €). Pintxos Plaza Nueva. Bacalao al pil-pil. Repos R1 le lendemain.',
                    'nl' => '⚠️ Lange etappe (32 km, via Lezama). Optie: stoppen in Lezama (21 km). Guggenheim Bilbao (~18 €). Rustdag R1 morgen.',
                    'de' => '⚠️ Lange Etappe (32 km, via Lezama). Option: Halt in Lezama (21 km). Guggenheim Bilbao (~18 €). Ruhetag R1 morgen.',
                ],
            ],

            // SEGMENT S — Bilbao → Santoña

            // ES-11 : Bilbao → Portugalete (GPX ES-11-Portugalete.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-11', 'day_number' => 11, 'sort_order' => 11,
                'name' => ['fr' => 'Bilbao → Portugalete (pont transbordeur)', 'nl' => 'Bilbao → Portugalete (hangbrug)', 'de' => 'Bilbao → Portugalete (Hängebrücke)'],
                'start_waypoint_id' => $wp['bilbao'] ?? null, 'end_waypoint_id' => $wp['portugalete'] ?? null,
                'distance_km' => 19.00, 'elevation_gain_m' => 300, 'elevation_loss_m' => 310,
                'estimated_duration_h' => 5.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Traversée en nacelle du pont transbordeur de Vizcaya (UNESCO, 1893, 0,50 €). Menú ouvrier authentique.',
                    'nl' => 'Gondel oversteek Vizcaya Hängebrücke (UNESCO, 1893, 0,50 €). Authentieke arbeidersmenu.',
                    'de' => 'Gondel-Überfahrt Hängebrücke Vizcaya (UNESCO, 1893, 0,50 €). Authentische Arbeiter-Menú.',
                ],
            ],
            [
                'route_id' => $norte->id, 'code' => 'ES-12', 'day_number' => 12, 'sort_order' => 12,
                'name' => ['fr' => 'Portugalete → Castro Urdiales', 'nl' => 'Portugalete → Castro Urdiales', 'de' => 'Portugalete → Castro Urdiales'],
                'start_waypoint_id' => $wp['portugalete'] ?? null, 'end_waypoint_id' => $wp['castro-urdiales'] ?? null,
                'distance_km' => 25.00, 'elevation_gain_m' => 450, 'elevation_loss_m' => 440,
                'estimated_duration_h' => 6.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Castro Urdiales — Santa María de la Asunción (gothique face à l\'océan), château-phare templier. Premier menú cantabre.',
                    'nl' => 'Castro Urdiales — gotische kerk (aan de oceaan), tempeliers vuurtoren. Eerste Cantabrische menu.',
                    'de' => 'Castro Urdiales — gotische Kirche (vor dem Ozean), Tempelritter-Leuchtturmburg. Erste kantabrische Menú.',
                ],
            ],
            [
                'route_id' => $norte->id, 'code' => 'ES-13', 'day_number' => 13, 'sort_order' => 13,
                'name' => ['fr' => 'Castro Urdiales → Laredo (plage de la Salvé)', 'nl' => 'Castro Urdiales → Laredo (Salvé-strand)', 'de' => 'Castro Urdiales → Laredo (Salvé-Strand)'],
                'start_waypoint_id' => $wp['castro-urdiales'] ?? null, 'end_waypoint_id' => $wp['laredo'] ?? null,
                'distance_km' => 26.00, 'elevation_gain_m' => 500, 'elevation_loss_m' => 490,
                'estimated_duration_h' => 7.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Plage de la Salvé à Laredo (5 km de sable). Sardines grillées. Demain : courte étape + Faro del Caballo l\'après-midi.',
                    'nl' => 'Salvé-strand in Laredo (5 km zand). Gegrilde sardines.',
                    'de' => 'Salvé-Strand in Laredo (5 km Sand). Gegrillte Sardinen.',
                ],
            ],
            // ES-14 : Laredo → Santoña (GPX ES-14-Santona.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-14', 'day_number' => 14, 'sort_order' => 14,
                'name' => ['fr' => 'Laredo → Santoña (bac + Faro del Caballo)', 'nl' => 'Laredo → Santoña (veerboot + Faro del Caballo)', 'de' => 'Laredo → Santoña (Fähre + Faro del Caballo)'],
                'start_waypoint_id' => $wp['laredo'] ?? null, 'end_waypoint_id' => $wp['santona'] ?? null,
                'distance_km' => 5.00, 'elevation_gain_m' => 50, 'elevation_loss_m' => 50,
                'estimated_duration_h' => 1.5, 'difficulty' => 'easy', 'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => 'Étape courte (5 km + bac saisonnier). Après-midi COMPLET : Faro del Caballo (Monte Buciero, 763 marches, 7,5 km A/R). Anchois de Santoña le soir.',
                    'nl' => 'Korte etappe (5 km + seizoensveerboot). VOLLEDIGE namiddag: Faro del Caballo (763 treden, 7,5 km heen-terug). Santoña-ansjovis.',
                    'de' => 'Kurze Etappe (5 km + Fähre). VOLLSTÄNDIGER Nachmittag: Faro del Caballo (763 Stufen, 7,5 km hin-zurück). Santoña-Sardellen.',
                ],
            ],

            // SEGMENT T — Santoña → Santillana

            // ES-15 : Santoña → Güemes (GPX ES-15-Guemes.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-15', 'day_number' => 15, 'sort_order' => 15,
                'name' => ['fr' => 'Santoña → Güemes (albergue mythique)', 'nl' => 'Santoña → Güemes (mythisch albergue)', 'de' => 'Santoña → Güemes (mythisches Albergue)'],
                'start_waypoint_id' => $wp['santona'] ?? null, 'end_waypoint_id' => $wp['guemes'] ?? null,
                'distance_km' => 21.00, 'elevation_gain_m' => 300, 'elevation_loss_m' => 290,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'donativo',
                'notes' => [
                    'fr' => 'Albergue de Güemes — La Cabaña del Abuelo Peuto (Padre Ernesto). LE donativo mythique du Norte. Dîner communautaire inclus. Ne pas réserver — arriver suffit.',
                    'nl' => 'Albergue van Güemes — La Cabaña del Abuelo Peuto (Padre Ernesto). HET mythische donativo. Diner inbegrepen. Niet reserveren.',
                    'de' => 'Albergue von Güemes — La Cabaña del Abuelo Peuto (Padre Ernesto). DAS mythische Donativo. Gemeinsames Abendessen. Nicht reservieren.',
                ],
            ],
            // ES-16 : Güemes → Santander (GPX ES-16-Santander.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-16', 'day_number' => 16, 'sort_order' => 16,
                'name' => ['fr' => 'Güemes → Santander (bac Somo)', 'nl' => 'Güemes → Santander (Somo-veerboot)', 'de' => 'Güemes → Santander (Somo-Fähre)'],
                'start_waypoint_id' => $wp['guemes'] ?? null, 'end_waypoint_id' => $wp['santander'] ?? null,
                'distance_km' => 15.00, 'elevation_gain_m' => 200, 'elevation_loss_m' => 210,
                'estimated_duration_h' => 4.0, 'difficulty' => 'easy', 'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => 'Bac Somo → Santander. Rabas (calamars frits, apéro cantabre). Repos R2 : Palacio Magdalena, Sardinero, marché Esperanza.',
                    'nl' => 'Veerboot Somo → Santander. Rabas (gebakken inktvis). Rustdag R2 morgen.',
                    'de' => 'Fähre Somo → Santander. Rabas (gebratener Tintenfisch). Ruhetag R2 morgen.',
                ],
            ],
            // ES-17 : Santander → Requejada (GPX ES-17-Requejada.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-17', 'day_number' => 17, 'sort_order' => 17,
                'name' => ['fr' => 'Santander → Requejada', 'nl' => 'Santander → Requejada', 'de' => 'Santander → Requejada'],
                'start_waypoint_id' => $wp['santander'] ?? null, 'end_waypoint_id' => $wp['requejada'] ?? null,
                'distance_km' => 22.00, 'elevation_gain_m' => 350, 'elevation_loss_m' => 340,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Menú del día. Torrelavega à proximité. Demain : direct Santillana (9 km) ou variante Cohicillos.',
                    'nl' => 'Menú del día. Torrelavega in de buurt. Morgen: direct Santillana (9 km) of variant Cohicillos.',
                    'de' => 'Menú del día. Torrelavega in der Nähe. Morgen: direkt Santillana (9 km) oder Cohicillos-Variante.',
                ],
            ],
            // ES-18 : Requejada → Santillana del Mar (GPX ES-18-Santillana.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-18', 'day_number' => 18, 'sort_order' => 18,
                'name' => ['fr' => 'Requejada → Santillana del Mar', 'nl' => 'Requejada → Santillana del Mar', 'de' => 'Requejada → Santillana del Mar'],
                'start_waypoint_id' => $wp['requejada'] ?? null, 'end_waypoint_id' => $wp['santillana-del-mar'] ?? null,
                'distance_km' => 9.00, 'elevation_gain_m' => 100, 'elevation_loss_m' => 90,
                'estimated_duration_h' => 2.5, 'difficulty' => 'easy', 'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => 'Journée courte. Santillana del Mar — collégiale Santa Juliana (roman XIIe). Cocido montañés. Repos R3 : Altamira (réserver !). Variante Cohicillos recommandée.',
                    'nl' => 'Korte dag. Santillana — romaanse Colegiata. Cocido montañés. Rustdag R3: Altamira (reserveren!).',
                    'de' => 'Kurze Etappe. Santillana — romanische Stiftskirche. Cocido montañés. Ruhetag R3: Altamira (reservieren!).',
                ],
            ],

            // SEGMENT U — Santillana → Colombres

            // ES-19 : Santillana → Comillas (GPX ES-19-Comillas.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-19', 'day_number' => 19, 'sort_order' => 19,
                'name' => ['fr' => 'Santillana del Mar → Comillas (El Capricho)', 'nl' => 'Santillana del Mar → Comillas (El Capricho)', 'de' => 'Santillana del Mar → Comillas (El Capricho)'],
                'start_waypoint_id' => $wp['santillana-del-mar'] ?? null, 'end_waypoint_id' => $wp['comillas'] ?? null,
                'distance_km' => 22.00, 'elevation_gain_m' => 350, 'elevation_loss_m' => 340,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'El Capricho de Gaudí (~7 €) + Palais de Sobrellano + Université pontificale néo-gothique.',
                    'nl' => 'El Capricho de Gaudí (~7 €) + Paleis van Sobrellano.',
                    'de' => 'El Capricho von Gaudí (~7 €) + Palast von Sobrellano.',
                ],
            ],
            // ES-20 : Comillas → San Vicente de la Barquera (GPX ES-20-San-Vicente.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-20', 'day_number' => 20, 'sort_order' => 20,
                'name' => ['fr' => 'Comillas → San Vicente de la Barquera', 'nl' => 'Comillas → San Vicente de la Barquera', 'de' => 'Comillas → San Vicente de la Barquera'],
                'start_waypoint_id' => $wp['comillas'] ?? null, 'end_waypoint_id' => $wp['san-vicente-de-la-barquera'] ?? null,
                'distance_km' => 12.00, 'elevation_gain_m' => 200, 'elevation_loss_m' => 190,
                'estimated_duration_h' => 3.0, 'difficulty' => 'easy', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Journée courte. Pont de la Maza (28 arches), château Santa María. Sorropotún (marmite de thon). Module Picos Liébana optionnel depuis SVB.',
                    'nl' => 'Korte dag. Brug de la Maza (28 bogen). Sorropotún (tonijnsoep). Picos Liébana optioneel.',
                    'de' => 'Kurze Etappe. Maza-Brücke (28 Bögen). Sorropotún (Thunfischeintopf). Picos-Liébana optional.',
                ],
            ],
            // ES-21 : San Vicente → Colombres (GPX ES-21-Colombres.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-21', 'day_number' => 21, 'sort_order' => 21,
                'name' => ['fr' => 'San Vicente → Colombres (entrée Asturies)', 'nl' => 'San Vicente → Colombres (ingang Asturië)', 'de' => 'San Vicente → Colombres (Eingang Asturien)'],
                'start_waypoint_id' => $wp['san-vicente-de-la-barquera'] ?? null, 'end_waypoint_id' => $wp['colombres'] ?? null,
                'distance_km' => 17.00, 'elevation_gain_m' => 300, 'elevation_loss_m' => 290,
                'estimated_duration_h' => 4.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Passage frontière Cantabrie/Asturies. Colombres : Archivo de Indianos (musée émigration). Dernier menú cantabre.',
                    'nl' => 'Grens Cantabrië/Asturië. Colombres: Archivo de Indianos (emigratiemuseum).',
                    'de' => 'Grenze Kantabrien/Asturien. Colombres: Archivo de Indianos (Auswanderungsmuseum).',
                ],
            ],

            // SEGMENT V — Colombres → Gijón

            [
                'route_id' => $norte->id, 'code' => 'ES-22', 'day_number' => 22, 'sort_order' => 22,
                'name' => ['fr' => 'Colombres → Llanes (premier culín de sidra)', 'nl' => 'Colombres → Llanes (eerste culín sidra)', 'de' => 'Colombres → Llanes (erster culín Sidra)'],
                'start_waypoint_id' => $wp['colombres'] ?? null, 'end_waypoint_id' => $wp['llanes'] ?? null,
                'distance_km' => 23.00, 'elevation_gain_m' => 400, 'elevation_loss_m' => 390,
                'estimated_duration_h' => 6.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Premier culín de sidra à Llanes — versée à bout de bras, boire d\'un trait. Le rituel asturien commence.',
                    'nl' => 'Eerste culín sidra in Llanes — vanuit armhoogte inschenken, in één teug opdrinken.',
                    'de' => 'Erster Culín Sidra in Llanes — aus Armhöhe, in einem Zug trinken.',
                ],
            ],
            // ES-23 : Llanes → Nueva (GPX ES-23-Nueva.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-23', 'day_number' => 23, 'sort_order' => 23,
                'name' => ['fr' => 'Llanes → Nueva (Gulpiyuri 600 m)', 'nl' => 'Llanes → Nueva (Gulpiyuri 600 m)', 'de' => 'Llanes → Nueva (Gulpiyuri 600 m)'],
                'start_waypoint_id' => $wp['llanes'] ?? null, 'end_waypoint_id' => $wp['nueva-cuerres'] ?? null,
                'distance_km' => 20.00, 'elevation_gain_m' => 300, 'elevation_loss_m' => 290,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Détour OBLIGATOIRE : Playa de Gulpiyuri (+600 m). Plage intérieure 40 m (doline inondée), Monument Naturel. Bufones de Pría (geysers marins).',
                    'nl' => 'VERPLICHTE omweg: Playa de Gulpiyuri (+600 m). 40 m binnenlands strand (doline). Bufones de Pría (zeegejsers).',
                    'de' => 'PFLICHTUMWEG: Playa de Gulpiyuri (+600 m). 40 m Binnenstrand (Doline). Bufones de Pría (Meeresgeysire).',
                ],
            ],
            // ES-24 : Nueva → Ribadesella (GPX ES-24-Ribadesella.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-24', 'day_number' => 24, 'sort_order' => 24,
                'name' => ['fr' => 'Nueva → Ribadesella (fabada)', 'nl' => 'Nueva → Ribadesella (fabada)', 'de' => 'Nueva → Ribadesella (Fabada)'],
                'start_waypoint_id' => $wp['nueva-cuerres'] ?? null, 'end_waypoint_id' => $wp['ribadesella'] ?? null,
                'distance_km' => 12.00, 'elevation_gain_m' => 200, 'elevation_loss_m' => 210,
                'estimated_duration_h' => 3.0, 'difficulty' => 'easy', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Journée courte. Fabada asturiana — LE plat asturien. Grotte Tito Bustillo (UNESCO, réserver). Module Picos Covadonga optionnel.',
                    'nl' => 'Korte dag. Fabada asturiana. Grot Tito Bustillo (UNESCO, reserveren). Picos Covadonga module optioneel.',
                    'de' => 'Kurze Etappe. Fabada asturiana. Höhle Tito Bustillo (UNESCO, reservieren). Picos-Covadonga optional.',
                ],
            ],
            // ES-25 : Ribadesella → Villaviciosa (GPX ES-25-Villaviciosa.gpx — via Colunga)
            [
                'route_id' => $norte->id, 'code' => 'ES-25', 'day_number' => 25, 'sort_order' => 25,
                'name' => ['fr' => 'Ribadesella → Villaviciosa (via Colunga)', 'nl' => 'Ribadesella → Villaviciosa (via Colunga)', 'de' => 'Ribadesella → Villaviciosa (via Colunga)'],
                'start_waypoint_id' => $wp['ribadesella'] ?? null, 'end_waypoint_id' => $wp['villaviciosa'] ?? null,
                'distance_km' => 37.00, 'elevation_gain_m' => 650, 'elevation_loss_m' => 640,
                'estimated_duration_h' => 9.5, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => '⚠️ Longue étape (37 km, via Colunga). Partir très tôt (6h). Option : couper à Colunga (20 km). Villaviciosa — capitale de la sidra, llagar, Espicha si chance.',
                    'nl' => '⚠️ Lange etappe (37 km, via Colunga). Zeer vroeg (6u). Optie: Colunga (20 km). Villaviciosa — sidra-hoofdstad.',
                    'de' => '⚠️ Lange Etappe (37 km, via Colunga). Sehr früh (6 Uhr). Option: Colunga (20 km). Villaviciosa — Sidra-Hauptstadt.',
                ],
            ],

            // SEGMENT W — Gijón (Asturies ouest)

            // ES-26 : Villaviciosa → Gijón (GPX ES-26-Gijon.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-26', 'day_number' => 26, 'sort_order' => 26,
                'name' => ['fr' => 'Villaviciosa → Gijón', 'nl' => 'Villaviciosa → Gijón', 'de' => 'Villaviciosa → Gijón'],
                'start_waypoint_id' => $wp['villaviciosa'] ?? null, 'end_waypoint_id' => $wp['gijon'] ?? null,
                'distance_km' => 30.00, 'elevation_gain_m' => 550, 'elevation_loss_m' => 540,
                'estimated_duration_h' => 8.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => '⚠️ Étape longue (30 km). Gijón — Cimavilla, Elogio del Horizonte (Chillida), thermes romains. Repos R4 le lendemain.',
                    'nl' => '⚠️ Lange etappe (30 km). Gijón — Cimavilla, Elogio del Horizonte. Rustdag R4 morgen.',
                    'de' => '⚠️ Lange Etappe (30 km). Gijón — Cimavilla, Elogio del Horizonte. Ruhetag R4 morgen.',
                ],
            ],
            // ES-27 : Gijón → Avilés (GPX ES-27-Aviles.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-27', 'day_number' => 27, 'sort_order' => 27,
                'name' => ['fr' => 'Gijón → Avilés', 'nl' => 'Gijón → Avilés', 'de' => 'Gijón → Avilés'],
                'start_waypoint_id' => $wp['gijon'] ?? null, 'end_waypoint_id' => $wp['aviles'] ?? null,
                'distance_km' => 25.00, 'elevation_gain_m' => 400, 'elevation_loss_m' => 390,
                'estimated_duration_h' => 6.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Avilés — vieille ville médiévale méconnue + Centro Niemeyer. Cachopo (escalope farcie XXL — à partager). Marché lundi.',
                    'nl' => 'Avilés — middeleeuwse binnenstad + Centro Niemeyer. Cachopo (XXL gevuld schnitzel). Maandagmarkt.',
                    'de' => 'Avilés — mittelalterliche Altstadt + Centro Niemeyer. Cachopo (XXL-gefülltes Schnitzel). Montagsmarkt.',
                ],
            ],
            // ES-28 : Avilés → Soto de Luiña (GPX ES-28-Soto-de-Luina.gpx — via Muros de Nalón)
            [
                'route_id' => $norte->id, 'code' => 'ES-28', 'day_number' => 28, 'sort_order' => 28,
                'name' => ['fr' => 'Avilés → Soto de Luiña (Cudillero)', 'nl' => 'Avilés → Soto de Luiña (Cudillero)', 'de' => 'Avilés → Soto de Luiña (Cudillero)'],
                'start_waypoint_id' => $wp['aviles'] ?? null, 'end_waypoint_id' => $wp['soto-de-luina'] ?? null,
                'distance_km' => 38.00, 'elevation_gain_m' => 680, 'elevation_loss_m' => 660,
                'estimated_duration_h' => 9.5, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => '⚠️ Longue étape (38 km, via Muros de Nalón). Option : couper à Muros (23 km). Cudillero accessible en variante (+2 km) — village-amphithéâtre multicolore.',
                    'nl' => '⚠️ Lange etappe (38 km, via Muros de Nalón). Optie: Muros (23 km). Cudillero via variant (+2 km).',
                    'de' => '⚠️ Lange Etappe (38 km, via Muros de Nalón). Option: Muros (23 km). Cudillero via Variante (+2 km).',
                ],
            ],
            // ES-29 : Soto de Luiña → Luarca (GPX ES-29-Luarca.gpx — via Cadavedo)
            [
                'route_id' => $norte->id, 'code' => 'ES-29', 'day_number' => 29, 'sort_order' => 29,
                'name' => ['fr' => 'Soto de Luiña → Luarca (la villa blanche)', 'nl' => 'Soto de Luiña → Luarca (het witte dorp)', 'de' => 'Soto de Luiña → Luarca (das weiße Dorf)'],
                'start_waypoint_id' => $wp['soto-de-luina'] ?? null, 'end_waypoint_id' => $wp['luarca'] ?? null,
                'distance_km' => 34.00, 'elevation_gain_m' => 600, 'elevation_loss_m' => 630,
                'estimated_duration_h' => 9.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => '⚠️ Longue étape (34 km, via Cadavedo). Option : couper à Cadavedo (15 km). Luarca — port, phare, cimetière marin (Severo Ochoa). Merluza a la sidra. Repos R5.',
                    'nl' => '⚠️ Lange etappe (34 km, via Cadavedo). Optie: Cadavedo (15 km). Luarca — haven, vuurtoren. Rustdag R5 morgen.',
                    'de' => '⚠️ Lange Etappe (34 km, via Cadavedo). Option: Cadavedo (15 km). Luarca — Hafen, Leuchtturm. Ruhetag R5 morgen.',
                ],
            ],

            // SEGMENT X — Luarca → Mondoñedo

            // ES-30 : Luarca → Navia (GPX ES-30-Navia.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-30', 'day_number' => 30, 'sort_order' => 30,
                'name' => ['fr' => 'Luarca → Navia', 'nl' => 'Luarca → Navia', 'de' => 'Luarca → Navia'],
                'start_waypoint_id' => $wp['luarca'] ?? null, 'end_waypoint_id' => $wp['navia'] ?? null,
                'distance_km' => 20.00, 'elevation_gain_m' => 350, 'elevation_loss_m' => 340,
                'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Menú del día. Côte asturienne sauvage. La frontière galicienne approche.',
                    'nl' => 'Menú del día. Wilde Asturische kust. De Galicische grens nadert.',
                    'de' => 'Menú del día. Wilde asturische Küste. Die galicische Grenze naht.',
                ],
            ],
            // ES-31 : Navia → Ribadeo (GPX ES-31-Ribadeo.gpx — via La Caridad)
            [
                'route_id' => $norte->id, 'code' => 'ES-31', 'day_number' => 31, 'sort_order' => 31,
                'name' => ['fr' => 'Navia → Ribadeo (entrée Galice)', 'nl' => 'Navia → Ribadeo (ingang Galicië)', 'de' => 'Navia → Ribadeo (Eingang Galicien)'],
                'start_waypoint_id' => $wp['navia'] ?? null, 'end_waypoint_id' => $wp['ribadeo'] ?? null,
                'distance_km' => 37.00, 'elevation_gain_m' => 650, 'elevation_loss_m' => 660,
                'estimated_duration_h' => 9.5, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => '⚠️ Longue étape (37 km, via La Caridad). Option : couper à La Caridad (15 km). ENTRÉE EN GALICE ! Premier pulpo á feira (poulpe + paprika sur planche).',
                    'nl' => '⚠️ Lange etappe (37 km, via La Caridad). Optie: La Caridad (15 km). INGANG GALICIË! Eerste pulpo á feira.',
                    'de' => '⚠️ Lange Etappe (37 km, via La Caridad). Option: La Caridad (15 km). EINGANG GALICIEN! Erster Pulpo á feira.',
                ],
            ],
            // ES-32 : Ribadeo → Lourenzá (GPX ES-32-Lourenza.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-32', 'day_number' => 32, 'sort_order' => 32,
                'name' => ['fr' => 'Ribadeo → Lourenzá (faba IGP)', 'nl' => 'Ribadeo → Lourenzá (faba IGP)', 'de' => 'Ribadeo → Lourenzá (Faba IGP)'],
                'start_waypoint_id' => $wp['ribadeo'] ?? null, 'end_waypoint_id' => $wp['lourenza'] ?? null,
                'distance_km' => 28.00, 'elevation_gain_m' => 500, 'elevation_loss_m' => 480,
                'estimated_duration_h' => 7.5, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => '⚠️ Étape longue (28 km). Norte quitte la côte. Faba de Lourenzá IGP. Ravitailler à Ribadeo.',
                    'nl' => '⚠️ Lange etappe (28 km). Norte verlaat de kust. Faba de Lourenzá IGP. Ravitailleren in Ribadeo.',
                    'de' => '⚠️ Lange Etappe (28 km). Norte verlässt die Küste. Faba de Lourenzá IGP. In Ribadeo verproviantieren.',
                ],
            ],
            // ES-33 : Lourenzá → Mondoñedo (GPX ES-33-Mondonedo.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-33', 'day_number' => 33, 'sort_order' => 33,
                'name' => ['fr' => 'Lourenzá → Mondoñedo (cathédrale agenouillée)', 'nl' => 'Lourenzá → Mondoñedo (knielende kathedraal)', 'de' => 'Lourenzá → Mondoñedo (kniende Kathedrale)'],
                'start_waypoint_id' => $wp['lourenza'] ?? null, 'end_waypoint_id' => $wp['mondonedo'] ?? null,
                'distance_km' => 9.00, 'elevation_gain_m' => 150, 'elevation_loss_m' => 200,
                'estimated_duration_h' => 2.5, 'difficulty' => 'easy', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Journée courte — après-midi visite. Cathédrale de Mondoñedo (XIIe, rosace). Tarta de Mondoñedo (amande, cheveux d\'ange, fruits confits).',
                    'nl' => 'Korte dag, namiddag bezoek. Kathedraal Mondoñedo (12e eeuw, roosvenster). Tarta de Mondoñedo.',
                    'de' => 'Kurze Etappe, Nachmittag Besichtigung. Kathedrale Mondoñedo (12. Jh., Rosette). Tarta de Mondoñedo.',
                ],
            ],

            // SEGMENT Y — Mondoñedo → Santiago

            // ES-34 : Mondoñedo → Vilalba (GPX ES-34-Vilalba.gpx — via Abadín)
            [
                'route_id' => $norte->id, 'code' => 'ES-34', 'day_number' => 34, 'sort_order' => 34,
                'name' => ['fr' => 'Mondoñedo → Vilalba (queso San Simón)', 'nl' => 'Mondoñedo → Vilalba (queso San Simón)', 'de' => 'Mondoñedo → Vilalba (Queso San Simón)'],
                'start_waypoint_id' => $wp['mondonedo'] ?? null, 'end_waypoint_id' => $wp['vilalba'] ?? null,
                'distance_km' => 38.00, 'elevation_gain_m' => 750, 'elevation_loss_m' => 650,
                'estimated_duration_h' => 10.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => '⚠️ Très longue étape (38 km, via Abadín). Option : couper à Abadín (17 km). Vilalba — Queso San Simón da Costa (AOP, fumé bouleau, toupie). Ravitailler pour 48 h.',
                    'nl' => '⚠️ Zeer lange etappe (38 km, via Abadín). Optie: Abadín (17 km). Vilalba — Queso San Simón da Costa (AOP). Ravitailleren voor 48 u.',
                    'de' => '⚠️ Sehr lange Etappe (38 km, via Abadín). Option: Abadín (17 km). Vilalba — Queso San Simón da Costa (AOP). Für 48 Std. verproviantieren.',
                ],
            ],
            // ES-35 : Vilalba → Miraz (GPX ES-35-Miraz.gpx — via Baamonde)
            [
                'route_id' => $norte->id, 'code' => 'ES-35', 'day_number' => 35, 'sort_order' => 35,
                'name' => ['fr' => 'Vilalba → Miraz (hospitaliers britanniques)', 'nl' => 'Vilalba → Miraz (Britse hospitaliers)', 'de' => 'Vilalba → Miraz (britische Hospitalier)'],
                'start_waypoint_id' => $wp['vilalba'] ?? null, 'end_waypoint_id' => $wp['miraz'] ?? null,
                'distance_km' => 34.00, 'elevation_gain_m' => 500, 'elevation_loss_m' => 600,
                'estimated_duration_h' => 8.5, 'difficulty' => 'hard', 'accommodation_type_default' => 'donativo',
                'notes' => [
                    'fr' => '⚠️ Longue étape (34 km, via Baamonde). Option : couper à Baamonde (15 km). Albergue de Miraz — hospitaliers Confraternity of St James (Royaume-Uni). Thé + scones.',
                    'nl' => '⚠️ Lange etappe (34 km, via Baamonde). Optie: Baamonde (15 km). Albergue Miraz — Britse Confraternity of St James. Thee + scones.',
                    'de' => '⚠️ Lange Etappe (34 km, via Baamonde). Option: Baamonde (15 km). Albergue Miraz — britische Confraternity of St James. Tee + Scones.',
                ],
            ],
            // ES-36 : Miraz → Sobrado dos Monxes (GPX ES-36-Sobrado.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-36', 'day_number' => 36, 'sort_order' => 36,
                'name' => ['fr' => 'Miraz → Sobrado dos Monxes (nuit monastique)', 'nl' => 'Miraz → Sobrado dos Monxes (kloosternacht)', 'de' => 'Miraz → Sobrado dos Monxes (Klosternacht)'],
                'start_waypoint_id' => $wp['miraz'] ?? null, 'end_waypoint_id' => $wp['sobrado-dos-monxes'] ?? null,
                'distance_km' => 25.00, 'elevation_gain_m' => 400, 'elevation_loss_m' => 450,
                'estimated_duration_h' => 7.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'abbey',
                'notes' => [
                    'fr' => 'Monastère cistercien de Sobrado dos Monxes (1142). Albergue dans le monastère. Vêpres avec les moines possibles. La dernière grande nuit spirituelle.',
                    'nl' => 'Cisterciënzer klooster Sobrado dos Monxes (1142). Albergue in het klooster. Vespers met monniken.',
                    'de' => 'Zisterzienserkloster Sobrado dos Monxes (1142). Albergue im Kloster. Vesper mit Mönchen möglich.',
                ],
            ],
            // ES-37 : Sobrado → Arzúa (GPX ES-37-Arzua.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-37', 'day_number' => 37, 'sort_order' => 37,
                'name' => ['fr' => 'Sobrado → Arzúa (jonction Camino Francés)', 'nl' => 'Sobrado → Arzúa (aansluiting Camino Francés)', 'de' => 'Sobrado → Arzúa (Anschluss Camino Francés)'],
                'start_waypoint_id' => $wp['sobrado-dos-monxes'] ?? null, 'end_waypoint_id' => $wp['arzua'] ?? null,
                'distance_km' => 22.00, 'elevation_gain_m' => 350, 'elevation_loss_m' => 400,
                'estimated_duration_h' => 6.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Jonction Camino Francés à Arzúa — choc de la foule après le Norte sauvage. Queixo Arzúa-Ulloa (AOP, crémeux). Réserver albergues privés pour les 2 dernières étapes.',
                    'nl' => 'Aansluiting Camino Francés in Arzúa — schok van de menigte. Queixo Arzúa-Ulloa (AOP). Privéalbergues reserveren.',
                    'de' => 'Anschluss Camino Francés in Arzúa — Schock der Menge. Queixo Arzúa-Ulloa (AOP). Private Albergues reservieren.',
                ],
            ],
            // ES-38 : Arzúa → O Pedrouzo (GPX ES-38-O-Pedrouzo.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-38', 'day_number' => 38, 'sort_order' => 38,
                'name' => ['fr' => 'Arzúa → O Pedrouzo', 'nl' => 'Arzúa → O Pedrouzo', 'de' => 'Arzúa → O Pedrouzo'],
                'start_waypoint_id' => $wp['arzua'] ?? null, 'end_waypoint_id' => $wp['o-pedrouzo'] ?? null,
                'distance_km' => 19.00, 'elevation_gain_m' => 250, 'elevation_loss_m' => 300,
                'estimated_duration_h' => 5.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Avant-dernière étape — demain Santiago. Empanada gallega pour le chemin. Menú del peregrino (19h-20h).',
                    'nl' => 'Voorlaatste etappe — morgen Santiago. Empanada gallega. Menú del peregrino (19-20u).',
                    'de' => 'Vorletzte Etappe — morgen Santiago. Empanada gallega. Menú del peregrino (19-20 Uhr).',
                ],
            ],
            // ES-39 : O Pedrouzo → Santiago (GPX ES-39-Santiago.gpx)
            [
                'route_id' => $norte->id, 'code' => 'ES-39', 'day_number' => 39, 'sort_order' => 39,
                'name' => [
                    'fr' => 'O Pedrouzo → Santiago de Compostela',
                    'nl' => 'O Pedrouzo → Santiago de Compostela',
                    'de' => 'O Pedrouzo → Santiago de Compostela',
                ],
                'start_waypoint_id' => $wp['o-pedrouzo'] ?? null, 'end_waypoint_id' => $wp['santiago-de-compostela'] ?? null,
                'distance_km' => 20.00, 'elevation_gain_m' => 250, 'elevation_loss_m' => 300,
                'estimated_duration_h' => 5.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => 'Monte do Gozo (5 km avant) — première vue sur les tours de la cathédrale (le moment où beaucoup pleurent). Oficina del Peregrino : Compostela + certificat (~2 500 km depuis Liège, 4 credenciales). Mariscada de célébration ! Tarta de Santiago + albariño.',
                    'nl' => 'Monte do Gozo (5 km voor aankomst) — eerste blik op de torenspitsen. Oficina del Peregrino: Compostela. Mariscada feestmaaltijd!',
                    'de' => 'Monte do Gozo (5 km vor Ankunft) — erster Blick auf die Kathedralentürme. Oficina del Peregrino: Compostela. Mariscada-Festmahl!',
                ],
            ],
        ];

        // ═══════════════════════════════════════════════════════════════
        // MODULE PICOS DE EUROPA — 9 étapes PC-01 → PC-09
        // ═══════════════════════════════════════════════════════════════

        $stagesPicos = [];

        if ($picos !== null) {
            $stagesPicos = [
                // PC-01 : San Vicente → Cades (GPX PC-01-Cades.gpx)
                [
                    'route_id' => $picos->id, 'code' => 'PC-01', 'day_number' => 1, 'sort_order' => 1,
                    'name' => ['fr' => 'San Vicente → Cades (entrée Liébana)', 'nl' => 'San Vicente → Cades (ingang Liébana)', 'de' => 'San Vicente → Cades (Eingang Liébana)'],
                    'start_waypoint_id' => $wp['san-vicente-de-la-barquera'] ?? null, 'end_waypoint_id' => $wp['cades-liebana'] ?? null,
                    'distance_km' => 22.00, 'elevation_gain_m' => 500, 'elevation_loss_m' => 300,
                    'estimated_duration_h' => 6.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                    'notes' => [
                        'fr' => 'Début du module Picos de Europa depuis le Norte. Entrée dans la vallée de Liébana. Bocados de Liébana (miel, noix, fruits secs).',
                        'nl' => 'Begin Picos-module. Ingang Liébana-vallei. Bocados de Liébana (honing, noten).',
                        'de' => 'Beginn des Picos-Moduls. Eingang ins Liébana-Tal. Bocados de Liébana (Honig, Nüsse).',
                    ],
                ],
                // PC-02 : Cades → Cabanes (GPX PC-02-Cabanes.gpx)
                [
                    'route_id' => $picos->id, 'code' => 'PC-02', 'day_number' => 2, 'sort_order' => 2,
                    'name' => ['fr' => 'Cades → Cabanes', 'nl' => 'Cades → Cabanes', 'de' => 'Cades → Cabanes'],
                    'start_waypoint_id' => $wp['cades-liebana'] ?? null, 'end_waypoint_id' => $wp['cabanes-picos'] ?? null,
                    'distance_km' => 18.00, 'elevation_gain_m' => 600, 'elevation_loss_m' => 400,
                    'estimated_duration_h' => 5.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                    'notes' => [
                        'fr' => 'Montée progressive vers Potes. Vues sur les Picos en approche. Caldo lebaniés à l\'arrivée.',
                        'nl' => 'Geleidelijke klim naar Potes. Zicht op de Picos. Caldo lebaniés bij aankomst.',
                        'de' => 'Allmählicher Anstieg nach Potes. Blick auf die Picos. Caldo lebaniés bei Ankunft.',
                    ],
                ],
                // PC-03 : Cabanes → Potes (GPX PC-03-Potes.gpx)
                [
                    'route_id' => $picos->id, 'code' => 'PC-03', 'day_number' => 3, 'sort_order' => 3,
                    'name' => ['fr' => 'Cabanes → Potes (cœur du Liébana)', 'nl' => 'Cabanes → Potes (hart van Liébana)', 'de' => 'Cabanes → Potes (Herz des Liébana)'],
                    'start_waypoint_id' => $wp['cabanes-picos'] ?? null, 'end_waypoint_id' => $wp['potes'] ?? null,
                    'distance_km' => 15.00, 'elevation_gain_m' => 300, 'elevation_loss_m' => 500,
                    'estimated_duration_h' => 4.5, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hotel',
                    'notes' => [
                        'fr' => 'Potes — Torre del Infantado, distilleries Orujo. Monastère de Santo Toribio de Liébana (Lignum Crucis). Cocido lebaniés. Réserver téléférique pour J+2.',
                        'nl' => 'Potes — Torre del Infantado, Orujo-distilleerderijen. Klooster Santo Toribio (Lignum Crucis). Cocido lebaniés.',
                        'de' => 'Potes — Torre del Infantado, Orujo-Brennereien. Kloster Santo Toribio (Lignum Crucis). Cocido lebaniés.',
                    ],
                ],
                // PC-04 : Potes → Fuente Dé (GPX PC-04-Fuente-De.gpx)
                [
                    'route_id' => $picos->id, 'code' => 'PC-04', 'day_number' => 4, 'sort_order' => 4,
                    'name' => ['fr' => 'Potes → Fuente Dé (le cirque glaciaire)', 'nl' => 'Potes → Fuente Dé (glaciaal cirque)', 'de' => 'Potes → Fuente Dé (Gletscherkessel)'],
                    'start_waypoint_id' => $wp['potes'] ?? null, 'end_waypoint_id' => $wp['fuente-de'] ?? null,
                    'distance_km' => 25.00, 'elevation_gain_m' => 700, 'elevation_loss_m' => 200,
                    'estimated_duration_h' => 7.5, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                    'notes' => [
                        'fr' => 'Fuente Dé — cirque glaciaire terminus (Parador + téléférique). Réserver téléférique la veille (quota strict). Cabrales DOP en cave le soir.',
                        'nl' => 'Fuente Dé — glaciaal cirque (Parador + kabelbaan). Kabelbaan reserveren dag ervoor. Cabrales DOP \'s avonds.',
                        'de' => 'Fuente Dé — Gletscherkessel (Parador + Seilbahn). Seilbahn vorher reservieren. Cabrales DOP abends.',
                    ],
                ],
                // PC-05 : Fuente Dé → Sotres (GPX PC-05-Sotres.gpx)
                [
                    'route_id' => $picos->id, 'code' => 'PC-05', 'day_number' => 5, 'sort_order' => 5,
                    'name' => ['fr' => 'Fuente Dé → Sotres (haute route Picos)', 'nl' => 'Fuente Dé → Sotres (hoge Picos-route)', 'de' => 'Fuente Dé → Sotres (Hochroute Picos)'],
                    'start_waypoint_id' => $wp['fuente-de'] ?? null, 'end_waypoint_id' => $wp['sotres'] ?? null,
                    'distance_km' => 18.00, 'elevation_gain_m' => 400, 'elevation_loss_m' => 800,
                    'estimated_duration_h' => 6.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                    'notes' => [
                        'fr' => 'Téléférique Fuente Dé → Aliva. Haute route Picos vers Sotres. Variant Bulnes + Urriellu possible (voir StageVariantEspagne).',
                        'nl' => 'Kabelbaan Fuente Dé → Aliva. Hoge Picos-route naar Sotres. Variant Bulnes + Urriellu mogelijk.',
                        'de' => 'Seilbahn Fuente Dé → Aliva. Hochroute Picos nach Sotres. Variante Bulnes + Urriellu möglich.',
                    ],
                ],
                // PC-06 : Sotres → Arenas de Cabrales (GPX PC-06-Arenas-de-Cabrales.gpx)
                [
                    'route_id' => $picos->id, 'code' => 'PC-06', 'day_number' => 6, 'sort_order' => 6,
                    'name' => ['fr' => 'Sotres → Arenas de Cabrales (le fromage des Picos)', 'nl' => 'Sotres → Arenas de Cabrales (Picos-kaas)', 'de' => 'Sotres → Arenas de Cabrales (Picos-Käse)'],
                    'start_waypoint_id' => $wp['sotres'] ?? null, 'end_waypoint_id' => $wp['arenas-de-cabrales'] ?? null,
                    'distance_km' => 20.00, 'elevation_gain_m' => 300, 'elevation_loss_m' => 900,
                    'estimated_duration_h' => 6.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hostel',
                    'notes' => [
                        'fr' => 'Descente vers Arenas de Cabrales. Cabrales DOP (bleu affiné en grotte naturelle — odeur + saveur extrêmes). Dégustation en cave obligatoire.',
                        'nl' => 'Afdaling naar Arenas de Cabrales. Cabrales DOP (blauwe kaas gerijpt in grotten). Proeverij in grot verplicht.',
                        'de' => 'Abstieg nach Arenas de Cabrales. Cabrales DOP (Blauschimmelkäse, in natürlichen Höhlen gereift). Höhlenverkostung ein Muss.',
                    ],
                ],
                // PC-07 : Arenas → Covadonga (GPX PC-07-Covadonga.gpx)
                [
                    'route_id' => $picos->id, 'code' => 'PC-07', 'day_number' => 7, 'sort_order' => 7,
                    'name' => ['fr' => 'Arenas de Cabrales → Covadonga', 'nl' => 'Arenas de Cabrales → Covadonga', 'de' => 'Arenas de Cabrales → Covadonga'],
                    'start_waypoint_id' => $wp['arenas-de-cabrales'] ?? null, 'end_waypoint_id' => $wp['arenas-de-cabrales'] ?? null,
                    'distance_km' => 30.00, 'elevation_gain_m' => 800, 'elevation_loss_m' => 600,
                    'estimated_duration_h' => 9.0, 'difficulty' => 'hard', 'accommodation_type_default' => 'hotel',
                    'notes' => [
                        'fr' => 'Covadonga — sanctuaire marial dans la roche, musée trésor, lacs des Picos. Symbole de la Reconquista. Variant montagne via lacs Enol/Ercina recommandé.',
                        'nl' => 'Covadonga — Mariaheiligtdom in de rots, museum, Picos-meren. Symbool Reconquista. Bergvariant via meren Enol/Ercina aanbevolen.',
                        'de' => 'Covadonga — Marienheiligtum im Fels, Schatzmuseum, Picos-Seen. Symbol der Reconquista. Bergvariante via Enol/Ercina-Seen empfohlen.',
                    ],
                ],
                // PC-08 : Covadonga → Arriondas (GPX PC-08-Arriondas.gpx)
                [
                    'route_id' => $picos->id, 'code' => 'PC-08', 'day_number' => 8, 'sort_order' => 8,
                    'name' => ['fr' => 'Covadonga → Arriondas (descente vallée Sella)', 'nl' => 'Covadonga → Arriondas (afdaling Sella-vallei)', 'de' => 'Covadonga → Arriondas (Abstieg Sella-Tal)'],
                    'start_waypoint_id' => $wp['arenas-de-cabrales'] ?? null, 'end_waypoint_id' => $wp['arriondas-picos'] ?? null,
                    'distance_km' => 22.00, 'elevation_gain_m' => 200, 'elevation_loss_m' => 900,
                    'estimated_duration_h' => 6.0, 'difficulty' => 'moderate', 'accommodation_type_default' => 'hostel',
                    'notes' => [
                        'fr' => 'Descente vers la vallée du Sella. Arriondas — Descenso del Sella (course en canöe, août). Sidra locale + empanada asturiana.',
                        'nl' => 'Afdaling naar Sella-vallei. Arriondas — Descenso del Sella (augustus). Lokale sidra + empanada.',
                        'de' => 'Abstieg ins Sella-Tal. Arriondas — Descenso del Sella (August). Lokale Sidra + Empanada.',
                    ],
                ],
                // PC-09 : Arriondas → Ribadesella (GPX PC-09-Ribadesella.gpx) — retour Norte
                [
                    'route_id' => $picos->id, 'code' => 'PC-09', 'day_number' => 9, 'sort_order' => 9,
                    'name' => ['fr' => 'Arriondas → Ribadesella (retour Norte)', 'nl' => 'Arriondas → Ribadesella (terug Norte)', 'de' => 'Arriondas → Ribadesella (zurück Norte)'],
                    'start_waypoint_id' => $wp['arriondas-picos'] ?? null, 'end_waypoint_id' => $wp['ribadesella'] ?? null,
                    'distance_km' => 14.00, 'elevation_gain_m' => 150, 'elevation_loss_m' => 200,
                    'estimated_duration_h' => 3.5, 'difficulty' => 'easy', 'accommodation_type_default' => 'hostel',
                    'notes' => [
                        'fr' => 'Retour au Camino del Norte à Ribadesella (jonction ES-24). Fabada de célébration du retour des Picos.',
                        'nl' => 'Terugkeer naar Norte in Ribadesella (aansluiting ES-24). Fabada ter viering van de terugkeer.',
                        'de' => 'Rückkehr zum Norte in Ribadesella (Anschluss ES-24). Fabada zur Feier der Rückkehr.',
                    ],
                ],
            ];
        }

        $allStages = array_merge($stagesNorte, $stagesPicos);
        $count = 0;

        foreach ($allStages as $stage) {
            Stage::updateOrCreate(
                ['code' => $stage['code']],
                $stage,
            );
            $count++;
        }

        $this->command->info(sprintf(
            'StageSeederEspagne : %d étapes créées/mises à jour (%d Norte ES + %d Picos PC).',
            $count,
            count($stagesNorte),
            count($stagesPicos),
        ));
    }
}
