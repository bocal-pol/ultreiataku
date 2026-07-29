<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

/**
 * Villes-étapes et POI Espagne (Camino del Norte + Module Picos).
 *
 * Sources :
 *   - etapes/etapes-espagne.md
 *   - poi/patrimoine-espagne.md
 *   - plan-voyage-espagne.md
 *
 * Coordonnées réelles (GPS) — toutes vérifiées.
 * Idempotent — updateOrCreate sur slug.
 */
class WaypointSeederEspagne extends Seeder
{
    public function run(): void
    {
        $waypoints = [
            // ── Segment P — Approche basque française (J1-J4) ──────────────────
            [
                'slug' => 'sjpp',
                'name' => ['fr' => 'Saint-Jean-Pied-de-Port', 'nl' => 'Saint-Jean-Pied-de-Port', 'de' => 'Saint-Jean-Pied-de-Port'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.1631,
                'longitude' => -1.2386,
                'is_active' => true,
            ],
            [
                'slug' => 'bidarray',
                'name' => ['fr' => 'Bidarray', 'nl' => 'Bidarray', 'de' => 'Bidarray'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2661,
                'longitude' => -1.3361,
                'is_active' => true,
            ],
            [
                'slug' => 'itxassou',
                'name' => ['fr' => 'Itxassou', 'nl' => 'Itxassou', 'de' => 'Itxassou'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3214,
                'longitude' => -1.4078,
                'is_active' => true,
            ],
            [
                'slug' => 'ascain',
                'name' => ['fr' => 'Ascain', 'nl' => 'Ascain', 'de' => 'Ascain'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3753,
                'longitude' => -1.6169,
                'is_active' => true,
            ],

            // ── Segment Q — Irun → Deba (Gipuzkoa) ───────────────────────────
            [
                'slug' => 'irun',
                'name' => ['fr' => 'Irun', 'nl' => 'Irun', 'de' => 'Irun'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3392,
                'longitude' => -1.7886,
                'is_active' => true,
            ],
            [
                'slug' => 'san-sebastian',
                'name' => ['fr' => 'San Sebastián (Donostia)', 'nl' => 'San Sebastián', 'de' => 'San Sebastián'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3183,
                'longitude' => -1.9812,
                'is_active' => true,
            ],
            [
                'slug' => 'zarautz',
                'name' => ['fr' => 'Zarautz', 'nl' => 'Zarautz', 'de' => 'Zarautz'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2836,
                'longitude' => -2.1706,
                'is_active' => true,
            ],
            [
                'slug' => 'deba',
                'name' => ['fr' => 'Deba', 'nl' => 'Deba', 'de' => 'Deba'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2950,
                'longitude' => -2.3514,
                'is_active' => true,
            ],

            // ── Segment R — Deba → Bilbao (Bizkaia) ──────────────────────────
            [
                'slug' => 'markina-xemein',
                'name' => ['fr' => 'Markina-Xemein', 'nl' => 'Markina-Xemein', 'de' => 'Markina-Xemein'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2656,
                'longitude' => -2.4975,
                'is_active' => true,
            ],
            [
                'slug' => 'gernika',
                'name' => ['fr' => 'Gernika-Lumo', 'nl' => 'Gernika', 'de' => 'Gernika'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3161,
                'longitude' => -2.6822,
                'is_active' => true,
            ],
            [
                'slug' => 'lezama',
                'name' => ['fr' => 'Lezama', 'nl' => 'Lezama', 'de' => 'Lezama'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2997,
                'longitude' => -2.8469,
                'is_active' => true,
            ],
            [
                'slug' => 'bilbao',
                'name' => ['fr' => 'Bilbao', 'nl' => 'Bilbao', 'de' => 'Bilbao'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2627,
                'longitude' => -2.9253,
                'is_active' => true,
            ],

            // ── Segment S — Bilbao → Santoña (entrée Cantabrie) ──────────────
            [
                'slug' => 'portugalete',
                'name' => ['fr' => 'Portugalete', 'nl' => 'Portugalete', 'de' => 'Portugalete'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3211,
                'longitude' => -3.0206,
                'is_active' => true,
            ],
            [
                'slug' => 'castro-urdiales',
                'name' => ['fr' => 'Castro Urdiales', 'nl' => 'Castro Urdiales', 'de' => 'Castro Urdiales'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3833,
                'longitude' => -3.2175,
                'is_active' => true,
            ],
            [
                'slug' => 'laredo',
                'name' => ['fr' => 'Laredo', 'nl' => 'Laredo', 'de' => 'Laredo'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3969,
                'longitude' => -3.4108,
                'is_active' => true,
            ],
            [
                'slug' => 'santona',
                'name' => ['fr' => 'Santoña', 'nl' => 'Santoña', 'de' => 'Santoña'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4497,
                'longitude' => -3.4614,
                'is_active' => true,
            ],

            // ── Segment T — Santoña → Santillana (Cantabrie centrale) ─────────
            [
                'slug' => 'guemes',
                'name' => ['fr' => 'Güemes', 'nl' => 'Güemes', 'de' => 'Güemes'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4789,
                'longitude' => -3.5453,
                'is_active' => true,
            ],
            [
                'slug' => 'santander',
                'name' => ['fr' => 'Santander', 'nl' => 'Santander', 'de' => 'Santander'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4623,
                'longitude' => -3.8099,
                'is_active' => true,
            ],
            [
                'slug' => 'requejada',
                'name' => ['fr' => 'Requejada', 'nl' => 'Requejada', 'de' => 'Requejada'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3589,
                'longitude' => -4.0292,
                'is_active' => true,
            ],
            [
                'slug' => 'santillana-del-mar',
                'name' => ['fr' => 'Santillana del Mar', 'nl' => 'Santillana del Mar', 'de' => 'Santillana del Mar'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3914,
                'longitude' => -4.1039,
                'is_active' => true,
            ],

            // ── Segment U — Santillana → Colombres (Cantabrie ouest) ──────────
            [
                'slug' => 'comillas',
                'name' => ['fr' => 'Comillas', 'nl' => 'Comillas', 'de' => 'Comillas'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3858,
                'longitude' => -4.2894,
                'is_active' => true,
            ],
            [
                'slug' => 'san-vicente-de-la-barquera',
                'name' => ['fr' => 'San Vicente de la Barquera', 'nl' => 'San Vicente de la Barquera', 'de' => 'San Vicente de la Barquera'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3800,
                'longitude' => -4.3939,
                'is_active' => true,
            ],
            [
                'slug' => 'colombres',
                'name' => ['fr' => 'Colombres', 'nl' => 'Colombres', 'de' => 'Colombres'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3761,
                'longitude' => -4.5381,
                'is_active' => true,
            ],

            // ── Segment V — Colombres → Gijón (Asturies est) ─────────────────
            [
                'slug' => 'llanes',
                'name' => ['fr' => 'Llanes', 'nl' => 'Llanes', 'de' => 'Llanes'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4200,
                'longitude' => -4.7556,
                'is_active' => true,
            ],
            [
                'slug' => 'nueva-cuerres',
                'name' => ['fr' => 'Nueva / Cuerres', 'nl' => 'Nueva / Cuerres', 'de' => 'Nueva / Cuerres'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4183,
                'longitude' => -4.9181,
                'is_active' => true,
            ],
            [
                'slug' => 'ribadesella',
                'name' => ['fr' => 'Ribadesella', 'nl' => 'Ribadesella', 'de' => 'Ribadesella'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4622,
                'longitude' => -5.0594,
                'is_active' => true,
            ],
            [
                'slug' => 'colunga',
                'name' => ['fr' => 'Colunga', 'nl' => 'Colunga', 'de' => 'Colunga'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4861,
                'longitude' => -5.2728,
                'is_active' => true,
            ],
            [
                'slug' => 'villaviciosa',
                'name' => ['fr' => 'Villaviciosa', 'nl' => 'Villaviciosa', 'de' => 'Villaviciosa'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4822,
                'longitude' => -5.4358,
                'is_active' => true,
            ],
            [
                'slug' => 'gijon',
                'name' => ['fr' => 'Gijón', 'nl' => 'Gijón', 'de' => 'Gijón'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5322,
                'longitude' => -5.6611,
                'is_active' => true,
            ],

            // ── Segment W — Gijón → Luarca (Asturies ouest) ──────────────────
            [
                'slug' => 'aviles',
                'name' => ['fr' => 'Avilés', 'nl' => 'Avilés', 'de' => 'Avilés'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5578,
                'longitude' => -5.9247,
                'is_active' => true,
            ],
            [
                'slug' => 'muros-de-nalon',
                'name' => ['fr' => 'Muros de Nalón', 'nl' => 'Muros de Nalón', 'de' => 'Muros de Nalón'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5617,
                'longitude' => -6.0808,
                'is_active' => true,
            ],
            [
                'slug' => 'soto-de-luina',
                'name' => ['fr' => 'Soto de Luiña', 'nl' => 'Soto de Luiña', 'de' => 'Soto de Luiña'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5483,
                'longitude' => -6.2156,
                'is_active' => true,
            ],
            [
                'slug' => 'cadavedo',
                'name' => ['fr' => 'Cadavedo', 'nl' => 'Cadavedo', 'de' => 'Cadavedo'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5744,
                'longitude' => -6.3444,
                'is_active' => true,
            ],
            [
                'slug' => 'luarca',
                'name' => ['fr' => 'Luarca', 'nl' => 'Luarca', 'de' => 'Luarca'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5461,
                'longitude' => -6.5350,
                'is_active' => true,
            ],

            // ── Segment X — Luarca → Mondoñedo (entrée Galice) ───────────────
            [
                'slug' => 'navia',
                'name' => ['fr' => 'Navia', 'nl' => 'Navia', 'de' => 'Navia'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5453,
                'longitude' => -6.7272,
                'is_active' => true,
            ],
            [
                'slug' => 'la-caridad',
                'name' => ['fr' => 'La Caridad', 'nl' => 'La Caridad', 'de' => 'La Caridad'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5461,
                'longitude' => -6.8922,
                'is_active' => true,
            ],
            [
                'slug' => 'ribadeo',
                'name' => ['fr' => 'Ribadeo (entrée Galice)', 'nl' => 'Ribadeo (ingang Galicië)', 'de' => 'Ribadeo (Eingang Galicien)'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.5361,
                'longitude' => -7.0406,
                'is_active' => true,
            ],
            [
                'slug' => 'lourenza',
                'name' => ['fr' => 'Lourenzá', 'nl' => 'Lourenzá', 'de' => 'Lourenzá'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4722,
                'longitude' => -7.1583,
                'is_active' => true,
            ],
            [
                'slug' => 'mondonedo',
                'name' => ['fr' => 'Mondoñedo', 'nl' => 'Mondoñedo', 'de' => 'Mondoñedo'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.4289,
                'longitude' => -7.3644,
                'is_active' => true,
            ],

            // ── Segment Y — Mondoñedo → Santiago (Galice intérieure) ──────────
            [
                'slug' => 'abadin',
                'name' => ['fr' => 'Abadín', 'nl' => 'Abadín', 'de' => 'Abadín'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3650,
                'longitude' => -7.4814,
                'is_active' => true,
            ],
            [
                'slug' => 'vilalba',
                'name' => ['fr' => 'Vilalba', 'nl' => 'Vilalba', 'de' => 'Vilalba'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2967,
                'longitude' => -7.6803,
                'is_active' => true,
            ],
            [
                'slug' => 'baamonde',
                'name' => ['fr' => 'Baamonde', 'nl' => 'Baamonde', 'de' => 'Baamonde'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2139,
                'longitude' => -7.7119,
                'is_active' => true,
            ],
            [
                'slug' => 'miraz',
                'name' => ['fr' => 'Miraz', 'nl' => 'Miraz', 'de' => 'Miraz'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.1747,
                'longitude' => -7.8253,
                'is_active' => true,
            ],
            [
                'slug' => 'sobrado-dos-monxes',
                'name' => ['fr' => 'Sobrado dos Monxes', 'nl' => 'Sobrado dos Monxes', 'de' => 'Sobrado dos Monxes'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.0361,
                'longitude' => -8.0200,
                'is_active' => true,
            ],
            [
                'slug' => 'arzua',
                'name' => ['fr' => 'Arzúa', 'nl' => 'Arzúa', 'de' => 'Arzúa'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 42.9289,
                'longitude' => -8.1633,
                'is_active' => true,
            ],
            [
                'slug' => 'o-pedrouzo',
                'name' => ['fr' => 'O Pedrouzo', 'nl' => 'O Pedrouzo', 'de' => 'O Pedrouzo'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 42.8931,
                'longitude' => -8.3467,
                'is_active' => true,
            ],
            [
                'slug' => 'santiago-de-compostela',
                'name' => [
                    'fr' => 'Santiago de Compostela',
                    'nl' => 'Santiago de Compostela',
                    'de' => 'Santiago de Compostela',
                ],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 42.8805,
                'longitude' => -8.5459,
                'is_active' => true,
            ],

            // ── Module Picos de Europa ────────────────────────────────────────
            [
                'slug' => 'potes',
                'name' => ['fr' => 'Potes (Liébana)', 'nl' => 'Potes', 'de' => 'Potes'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.1536,
                'longitude' => -4.6183,
                'is_active' => true,
            ],
            [
                'slug' => 'fuente-de',
                'name' => ['fr' => 'Fuente Dé', 'nl' => 'Fuente Dé', 'de' => 'Fuente Dé'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.1258,
                'longitude' => -4.8103,
                'is_active' => true,
            ],
            [
                'slug' => 'arenas-de-cabrales',
                'name' => ['fr' => 'Arenas de Cabrales', 'nl' => 'Arenas de Cabrales', 'de' => 'Arenas de Cabrales'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2981,
                'longitude' => -4.8467,
                'is_active' => true,
            ],

            // ── POI remarquables Espagne ──────────────────────────────────────

            // Île des Faisans (J4, sur la Bidassoa)
            [
                'slug' => 'ile-des-faisans-bidassoa',
                'name' => [
                    'fr' => 'Île des Faisans (Bidassoa)',
                    'nl' => 'Fazanteneiland (Bidassoa)',
                    'de' => 'Fasaneninsel (Bidassoa)',
                ],
                'type' => 'poi',
                'poi_category' => 'archaeology',
                'latitude' => 43.3519,
                'longitude' => -1.7736,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Le plus petit condominium du monde (6 820 m²) dans la Bidassoa : espagnol du 1er fév. au 31 juil., français du 1er août au 31 jan. Depuis le traité des Pyrénées (1659). Accès interdit — se contemple depuis le pont Santiago (Irun) ou les berges d\'Hendaye. Ton passage août-sept = île sous drapeau français vue de la rive espagnole.',
                    'nl' => 'Het kleinste condominium ter wereld (6 820 m²) in de Bidassoa: Spaans van 1 feb. t/m 31 jul., Frans van 1 aug. t/m 31 jan. Toegang verboden — te bezichtigen vanaf de Santiago-brug (Irun) of de oevers van Hendaye.',
                    'de' => 'Das kleinste Kondominium der Welt (6 820 m²) in der Bidassoa. Zugang verboten — vom Santiago-Brücke (Irun) oder Hendaye-Ufer betrachtbar.',
                ],
                'is_active' => true,
            ],

            // Flysch de Zumaia
            [
                'slug' => 'flysch-zumaia',
                'name' => [
                    'fr' => 'Flysch de Zumaia',
                    'nl' => 'Flysch van Zumaia',
                    'de' => 'Flysch von Zumaia',
                ],
                'type' => 'poi',
                'poi_category' => 'nature',
                'latitude' => 43.2972,
                'longitude' => -2.2522,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Falaises feuilletées (60 millions d\'années lisibles dans la roche) — décor de Game of Thrones. Géoparc Basque. Ermitage San Telmo visible depuis le chemin.',
                    'nl' => 'Gelaagde kliffen (60 miljoen jaar leesbaar in de rots) — decor van Game of Thrones. Baskisch Geopark.',
                    'de' => 'Geschichtete Klippen (60 Millionen Jahre in Gestein lesbar). Kulisse von Game of Thrones. Baskischer Geopark.',
                ],
                'is_active' => true,
            ],

            // Pont transbordeur de Portugalete
            [
                'slug' => 'pont-transbordeur-portugalete',
                'name' => [
                    'fr' => 'Pont transbordeur de Vizcaya',
                    'nl' => 'Hängebrücke Bizkaia (Vizcaya)',
                    'de' => 'Hängebrücke Vizcaya',
                ],
                'type' => 'poi',
                'poi_category' => 'archaeology',
                'latitude' => 43.3231,
                'longitude' => -3.0167,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 20,
                'entry_cost_eur' => 0.50,
                'booking_required' => false,
                'description' => [
                    'fr' => 'UNESCO, 1893. Le plus vieux pont transbordeur du monde. Traversée en nacelle suspendue (0,50 €, 2 min). Pont passé dans les deux sens sur le chemin.',
                    'nl' => 'UNESCO, 1893. Oudste hangbrug ter wereld. Oversteek in gondel (0,50 €, 2 min).',
                    'de' => 'UNESCO, 1893. Älteste Hängebrücke der Welt. Überfahrt in Gondel (0,50 €, 2 Min.).',
                ],
                'is_active' => true,
            ],

            // Faro del Caballo (Santoña)
            [
                'slug' => 'faro-del-caballo-santona',
                'name' => [
                    'fr' => 'Faro del Caballo (Santoña)',
                    'nl' => 'Faro del Caballo (Santoña)',
                    'de' => 'Faro del Caballo (Santoña)',
                ],
                'type' => 'poi',
                'poi_category' => 'nature',
                'latitude' => 43.4550,
                'longitude' => -3.4267,
                'detour_type' => 'medium',
                'detour_distance_km' => 7.50,
                'detour_duration_min' => 180,
                'visit_duration_min' => 60,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Sentier du Monte Buciero (Fuerte de San Martín, Santoña) puis descente de 763 marches taillées dans la falaise jusqu\'au phare au ras de l\'eau turquoise. 7,5 km A/R, ~3 h. Ni eau ni service sur le sentier — partir avec 1,5 L + casquette. Baignade possible par mer calme. Étape J14 (Laredo→Santoña) volontairement courte (5 km + bac) pour consacrer l\'après-midi à ce POI.',
                    'nl' => 'Pad van Monte Buciero (Fort San Martín, Santoña) dan afdaling van 763 in de klif uitgehakte treden tot aan de vuurtoren aan de turkooizen zee. 7,5 km heen-terug, ~3 u. Geen water of diensten op het pad — vertrekken met 1,5 L + pet.',
                    'de' => 'Weg des Monte Buciero (Fort San Martín, Santoña) dann Abstieg 763 in den Fels gehauener Stufen bis zum Leuchtturm auf Meereshöhe. 7,5 km hin-zurück, ~3 Std. Kein Wasser auf dem Weg — mit 1,5 L starten.',
                ],
                'is_active' => true,
            ],

            // Albergue de Güemes (POI humain)
            [
                'slug' => 'albergue-guemes',
                'name' => [
                    'fr' => 'Albergue de Güemes — La Cabaña del Abuelo Peuto',
                    'nl' => 'Albergue de Güemes — La Cabaña del Abuelo Peuto',
                    'de' => 'Albergue de Güemes — La Cabaña del Abuelo Peuto',
                ],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 43.4789,
                'longitude' => -3.5453,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Le plus célèbre albergue du Norte. Padre Ernesto — prêtre-voyageur, bibliothèque-musée de ses voyages au monde entier. Donativo. Veillée commune où chacun se présente. Ne pas réserver — arriver c\'est tout.',
                    'nl' => 'Beroemdste albergue van de Norte. Padre Ernesto — priester-reiziger, bibliotheek-museum van zijn wereldreizen. Donativo. Gemeenschappelijke avond waar iedereen zich voorstelt.',
                    'de' => 'Berühmtestes Albergue des Norte. Padre Ernesto — Priester-Weltreisender, Bibliothek-Museum. Donativo. Gemeinsamer Abend wo sich jeder vorstellt.',
                ],
                'is_active' => true,
            ],

            // Altamira (Santillana del Mar)
            [
                'slug' => 'grottes-altamira',
                'name' => [
                    'fr' => 'Grottes d\'Altamira (Musée + néo-grotte)',
                    'nl' => 'Grotten van Altamira (Museum + neogrot)',
                    'de' => 'Höhlen von Altamira (Museum + Neo-Höhle)',
                ],
                'type' => 'poi',
                'poi_category' => 'archaeology',
                'latitude' => 43.3783,
                'longitude' => -4.1200,
                'detour_type' => 'short',
                'detour_distance_km' => 2.00,
                'detour_duration_min' => 60,
                'visit_duration_min' => 90,
                'entry_cost_eur' => 3.00,
                'booking_required' => true,
                'booking_contact' => 'www.culturaydeporte.gob.es/mnaltamira',
                'opening_notes' => [
                    'fr' => 'Réserver en ligne obligatoire — quotas stricts. Ouv. mar-dim.',
                    'nl' => 'Online reserveren verplicht — strikt quotum. Di-zo open.',
                    'de' => 'Online-Reservierung Pflicht — strikte Quoten. Di-So geöffnet.',
                ],
                'description' => [
                    'fr' => 'La « chapelle Sixtine de l\'art préhistorique » (~15 000 ans, bisons polychromes). Grotte originale fermée (préservation) — néo-grotte du musée = réplique exacte remarquable. À 2 km de Santillana. ~3 €. Écho de la Grotte Scladina (Belgique, Néandertal) : ton pèlerinage traverse 100 000 ans d\'humanité.',
                    'nl' => 'De « Sixtijnse Kapel van de prehistorische kunst » (~15 000 jaar, polychrome bizons). Originele grot gesloten — neogrotte van het museum = exacte replica. 2 km van Santillana. ~3 €.',
                    'de' => 'Die « Sixtinische Kapelle der Höhlenkunst » (~15 000 Jahre, polychrome Bisons). Originalhöhle geschlossen — Neo-Höhle des Museums = exakte Replika. 2 km von Santillana. ~3 €.',
                ],
                'is_active' => true,
            ],

            // El Capricho de Gaudí (Comillas)
            [
                'slug' => 'el-capricho-gaudi-comillas',
                'name' => [
                    'fr' => 'El Capricho de Gaudí (Comillas)',
                    'nl' => 'El Capricho de Gaudí (Comillas)',
                    'de' => 'El Capricho von Gaudí (Comillas)',
                ],
                'type' => 'poi',
                'poi_category' => 'archaeology',
                'latitude' => 43.3853,
                'longitude' => -4.2919,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 60,
                'entry_cost_eur' => 7.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Villa orientaliste de jeunesse de Gaudí (1883-1885), l\'un des 3 seuls bâtiments Gaudí hors Catalogne. Céramiques tournesol, minaret, fantaisie totale. ~7 €.',
                    'nl' => 'Oriëntalistisch jeugdwerk van Gaudí (1883-1885), een van de 3 enige Gaudí-gebouwen buiten Catalonië. Zonnebloem-keramiek, minaret. ~7 €.',
                    'de' => 'Orientalistische Jugendarbeit Gaudís (1883-1885), eines der 3 einzigen Gaudí-Gebäude außerhalb Kataloniens. ~7 €.',
                ],
                'is_active' => true,
            ],

            // Playa de Gulpiyuri (J23, détour 600 m)
            [
                'slug' => 'playa-de-gulpiyuri',
                'name' => [
                    'fr' => 'Playa de Gulpiyuri (plage intérieure)',
                    'nl' => 'Playa de Gulpiyuri (binnenlandse kust)',
                    'de' => 'Playa de Gulpiyuri (innenliegender Strand)',
                ],
                'type' => 'poi',
                'poi_category' => 'nature',
                'latitude' => 43.4308,
                'longitude' => -4.8644,
                'detour_type' => 'short',
                'detour_distance_km' => 0.60,
                'detour_duration_min' => 15,
                'visit_duration_min' => 30,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Plage intérieure de 40 m au milieu des prés (doline inondée par un tunnel sous la falaise), Monument Naturel. 600 m de détour depuis le Norte à Naves. Viser marée montante/haute — à marée basse il n\'y a pas d\'eau. Une des plages les plus étranges du monde.',
                    'nl' => 'Binnenlandse 40 m-strand midden in de weiden (ondergelopen doline via tunnel onder de klif), Natuurmonument. 600 m omweg vanuit de Norte bij Naves. Bezoeken bij hoog-/vloed — bij laagwater geen water.',
                    'de' => 'Innenliegender 40 m-Strand mitten in Wiesen (durch Tunnel unter der Klippe überflutete Doline), Naturdenkmal. 600 m Umweg vom Norte bei Naves. Bei Flut/Hochwasser besuchen.',
                ],
                'is_active' => true,
            ],

            // Monastère de Sobrado dos Monxes (POI ET ville-étape)
            [
                'slug' => 'monastere-sobrado',
                'name' => [
                    'fr' => 'Monastère cistercien de Sobrado dos Monxes',
                    'nl' => 'Cisterciënzer Klooster Sobrado dos Monxes',
                    'de' => 'Zisterzienserkloster Sobrado dos Monxes',
                ],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 43.0361,
                'longitude' => -8.0200,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 60,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Cistercien (1142), église baroque monumentale, cloîtres. Albergue DANS le monastère — vêpres avec les moines possibles. La dernière grande nuit spirituelle avant la jonction avec le Camino Francés à Arzúa.',
                    'nl' => 'Cistercënzers (1142), monumentale barokkerk, kloostergangen. Albergue IN het klooster — vespers met monniken mogelijk.',
                    'de' => 'Zisterzienser (1142), monumentale Barockkirche, Kreuzgänge. Albergue IM Kloster — Vesper mit Mönchen möglich.',
                ],
                'is_active' => true,
            ],

            // Cohicillos — village du grand-père (variante J19b)
            [
                'slug' => 'cohicillos-cartes',
                'name' => [
                    'fr' => 'Cohicillos — vallée de Cartes (village du grand-père)',
                    'nl' => 'Cohicillos — vallei van Cartes (dorp van grootvader)',
                    'de' => 'Cohicillos — Tal von Cartes (Dorf des Großvaters)',
                ],
                'type' => 'poi',
                'poi_category' => 'archaeology',
                'latitude' => 43.3258,
                'longitude' => -4.0811,
                'detour_type' => 'medium',
                'detour_distance_km' => 5.00,
                'detour_duration_min' => 90,
                'booking_required' => false,
                'description' => [
                    'fr' => 'La vallée familiale (commune de Cartes, Cantabrie). Ermita de San Cipriano sur sa colline, église, cimetière — le village d\'origine du grand-père. Romería de San Cipriano le 16 septembre : l\'un des plus anciens pèlerinages de Cantabrie (Fête d\'Intérêt Touristique Régional). Intégré via variante J19b « familiale » : Requejada → Torrelavega → Cohicillos → Cartes → Santillana (~20 km au lieu de 9 km directs).',
                    'nl' => 'De familiavallei (gemeente Cartes, Cantabrië). Ermita de San Cipriano op zijn heuvel, kerk, kerkhof — het dorp van herkomst van grootvader. Romería de San Cipriano op 16 september: een van de oudste pelgrimstochten van Cantabrië.',
                    'de' => 'Das Familienval (Gemeinde Cartes, Kantabrien). Ermita de San Cipriano auf seinem Hügel, Kirche, Friedhof — Herkunftsdorf des Großvaters. Romería de San Cipriano am 16. September: einer der ältesten Wallfahrten Kantabriens.',
                ],
                'is_active' => true,
            ],

            // Catedral de Mondoñedo
            [
                'slug' => 'catedral-mondonedo',
                'name' => [
                    'fr' => 'Cathédrale de Mondoñedo',
                    'nl' => 'Kathedraal van Mondoñedo',
                    'de' => 'Kathedrale von Mondoñedo',
                ],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 43.4289,
                'longitude' => -7.3644,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 45,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'La « cathédrale agenouillée » — basse et large, rosace, fresques médiévales. L\'une des plus petites villes cathédrales d\'Espagne. Étape J33 volontairement courte (9 km) pour en profiter l\'après-midi.',
                    'nl' => 'De « knielende kathedraal » — laag en breed, roosvenster, middeleeuwse fresco\'s. Een van de kleinste kathedraalsteden van Spanje.',
                    'de' => 'Die « kniende Kathedrale » — niedrig und breit, Rosette, mittelalterliche Fresken. Eine der kleinsten Kathedralstädte Spaniens.',
                ],
                'is_active' => true,
            ],

            // Catedral de Santiago de Compostela
            [
                'slug' => 'catedral-santiago',
                'name' => [
                    'fr' => 'Cathédrale de Santiago de Compostela',
                    'nl' => 'Kathedraal van Santiago de Compostela',
                    'de' => 'Kathedrale von Santiago de Compostela',
                ],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 42.8806,
                'longitude' => -8.5459,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 120,
                'entry_cost_eur' => 0.00,
                'booking_required' => true,
                'booking_contact' => 'www.catedraldesantiago.es',
                'description' => [
                    'fr' => 'Portique de la Gloire (réserver la visite guidée), abrazo à l\'Apôtre, crypte du tombeau. Botafumeiro (53 kg) lors des messes solennelles. Oficina del Peregrino (Rúa Carretas 33) : Compostela + certificat de distance (~2 500 km depuis Liège, 4 credenciales).',
                    'nl' => 'Portaal van de Glorie (begeleide rondleiding reserveren), omhelzing van de Apostel, crypte van het graf. Botafumeiro (53 kg) bij plechtige missen. Oficina del Peregrino: Compostela + afstandscertificaat (~2 500 km vanuit Luik).',
                    'de' => 'Portico de la Gloria (Führung reservieren), Abrazo zum Apostel, Gruft des Grabes. Botafumeiro (53 kg) bei feierlichen Messen. Oficina del Peregrino: Compostela + Distanzzertifikat (~2 500 km ab Lüttich).',
                ],
                'is_active' => true,
            ],

            // Picos de Europa — Santo Toribio de Liébana
            [
                'slug' => 'santo-toribio-liebana',
                'name' => [
                    'fr' => 'Santo Toribio de Liébana',
                    'nl' => 'Santo Toribio de Liébana',
                    'de' => 'Santo Toribio de Liébana',
                ],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 43.1742,
                'longitude' => -4.6469,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 60,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Monastère du Lignum Crucis — l\'un des 5 lieux saints de la chrétienté (Rome, Jérusalem, Santiago, Caravaca, Santo Toribio). Délivre sa propre « Lebaniega ». Sur le Camino Lebaniego (San Vicente → Potes, 72 km, UNESCO).',
                    'nl' => 'Klooster van het Lignum Crucis — een van de 5 heilige plaatsen van de christenheid. Geeft zijn eigen « Lebaniega » af. Op de Camino Lebaniego (San Vicente → Potes, 72 km, UNESCO).',
                    'de' => 'Kloster des Lignum Crucis — einer der 5 heiligen Orte der Christenheit. Verleiht eigene « Lebaniega ». Auf dem Camino Lebaniego (San Vicente → Potes, 72 km, UNESCO).',
                ],
                'is_active' => true,
            ],

            // ÔöÇÔöÇ Picos de Europa ÔÇö villes-etapes du module PC ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ

            // Cades (Liebana) ÔÇö PC-01 destination
            [
                'slug' => 'cades-liebana',
                'name' => ['fr' => 'Cades (Liebana)', 'nl' => 'Cades (Liebana)', 'de' => 'Cades (Liebana)'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2042,
                'longitude' => -4.4411,
                'is_active' => true,
            ],

            // Cabanes (Picos) ÔÇö PC-02 destination
            [
                'slug' => 'cabanes-picos',
                'name' => ['fr' => 'Cabanes (Liebana)', 'nl' => 'Cabanes (Liebana)', 'de' => 'Cabanes (Liebana)'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.1708,
                'longitude' => -4.6144,
                'is_active' => true,
            ],

            // Sotres (Picos de Europa) ÔÇö PC-05 destination
            [
                'slug' => 'sotres',
                'name' => ['fr' => 'Sotres (Picos de Europa)', 'nl' => 'Sotres (Picos de Europa)', 'de' => 'Sotres (Picos de Europa)'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.2236,
                'longitude' => -4.7297,
                'is_active' => true,
            ],

            // Arriondas (Picos) ÔÇö PC-08 destination (vallee du Sella)
            [
                'slug' => 'arriondas-picos',
                'name' => ['fr' => 'Arriondas (vallee du Sella)', 'nl' => 'Arriondas (Sella-vallei)', 'de' => 'Arriondas (Sella-Tal)'],
                'type' => 'city',
                'poi_category' => null,
                'latitude' => 43.3939,
                'longitude' => -5.2497,
                'is_active' => true,
            ],
        ];

        foreach ($waypoints as $waypoint) {
            Waypoint::updateOrCreate(
                ['slug' => $waypoint['slug']],
                $waypoint,
            );
        }

        $this->command->info(sprintf('WaypointSeederEspagne : %d waypoints créés/mis à jour.', count($waypoints)));
    }
}
