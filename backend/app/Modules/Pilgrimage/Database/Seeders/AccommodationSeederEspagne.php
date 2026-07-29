<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Hébergements Espagne (Camino del Norte + Module Picos).
 *
 * Sources :
 *   - hebergement/carnet-espagne.md
 *   - etapes/etapes-espagne.md
 *
 * Étapes emblématiques :
 *   - ES-15 (Güemes) : donativo Padre Ernesto — LE Norte mythique
 *   - ES-14 (Santoña) : pension pour Faro del Caballo
 *   - ES-35 (Miraz) : donativo Confraternity of St James (UK)
 *   - ES-36 (Sobrado) : nuit monastique cistercienne (1142)
 *   - PC-04 (Fuente Dé) : Parador de montagne + albergue
 *
 * Idempotent via (stage_id + name JSON fr).
 */
class AccommodationSeederEspagne extends Seeder
{
    public function run(): void
    {
        $this->command->info('AccommodationSeederEspagne — démarrage');

        $stagesByCode = Stage::pluck('id', 'code');

        $accommodations = [

            // ── ES-05 San Sebastián ──────────────────────────────────────────
            [
                'stage_code' => 'ES-05',
                'data' => [
                    'name' => ['fr' => 'Albergue Ondarreta (SS)', 'nl' => 'Albergue Ondarreta (SS)', 'de' => 'Albergue Ondarreta (SS)'],
                    'type' => 'hostel',
                    'price_min_eur' => 12.00,
                    'price_max_eur' => 18.00,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => false,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Plusieurs albergues en ville. Réserver en haute saison (juillet-août). San Sebastián — pintxos vieille ville, obligatoire le soir.',
                        'nl' => 'Meerdere albergues in de stad. Reserveren in hoogseizoen. Pintxos oude stad, verplicht \'s avonds.',
                        'de' => 'Mehrere Albergues in der Stadt. In der Hochsaison reservieren. Pintxos Altstadt, abends Pflicht.',
                    ],
                    'verified_at' => now()->subMonths(6),
                ],
            ],

            // ── ES-10 Bilbao (repos R1) ──────────────────────────────────────
            [
                'stage_code' => 'ES-10',
                'data' => [
                    'name' => ['fr' => 'Bilbao — pension Casco Viejo', 'nl' => 'Bilbao — pension Casco Viejo', 'de' => 'Bilbao — Pension Casco Viejo'],
                    'type' => 'hotel',
                    'price_min_eur' => 40.00,
                    'price_max_eur' => 70.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 3,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Repos R1 Bilbao. Pensión dans le Casco Viejo recommandée pour accès facile Guggenheim + marché Ribera. Réserver 3+ jours à l\'avance.',
                        'nl' => 'Rustdag R1 Bilbao. Pensión in Casco Viejo aanbevolen voor Guggenheim + Ribera-markt. Min. 3 dagen vooraf reserveren.',
                        'de' => 'Ruhetag R1 Bilbao. Pensión im Casco Viejo empfohlen für Guggenheim + Ribera-Markt. Min. 3 Tage vorher reservieren.',
                    ],
                    'verified_at' => now()->subMonths(4),
                ],
            ],

            // ── ES-14 Santoña (pour Faro del Caballo) ───────────────────────
            [
                'stage_code' => 'ES-14',
                'data' => [
                    'name' => ['fr' => 'Santoña — Pensión Anchoïta (recommandée Faro)', 'nl' => 'Santoña — Pensión Anchoïta (aanbevolen Faro)', 'de' => 'Santoña — Pensión Anchoïta (empfohlen Faro)'],
                    'type' => 'hotel',
                    'price_min_eur' => 35.00,
                    'price_max_eur' => 55.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 2,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Pension recommandée pour liberté d\'horaire (départ libre pour Monte Buciero). Anchois de Santoña au dîner : El Capricho de Santoña ou La Bodega de Santoña.',
                        'nl' => 'Aanbevolen pension voor vrije timing (Monte Buciero). Santoña-ansjovis \'s avonds.',
                        'de' => 'Empfohlene Pension für freie Zeiteinteilung (Monte Buciero). Santoña-Sardellen zum Abendessen.',
                    ],
                    'verified_at' => now()->subMonths(5),
                ],
            ],
            [
                'stage_code' => 'ES-14',
                'data' => [
                    'name' => ['fr' => 'Albergue municipal de Santoña', 'nl' => 'Gemeentelijk albergue Santoña', 'de' => 'Kommunales Albergue Santoña'],
                    'type' => 'hostel',
                    'price_min_eur' => 8.00,
                    'price_max_eur' => 12.00,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => false,
                    'is_primary' => false,
                    'sort_order' => 2,
                    'notes' => [
                        'fr' => 'Alternative économique. Moins adapté pour Faro del Caballo (horaires rigides). Prefer la pension pour liberté totale.',
                        'nl' => 'Economisch alternatief. Minder geschikt voor Faro del Caballo (starre uren). Geef de voorkeur aan pension.',
                        'de' => 'Wirtschaftliche Alternative. Weniger geeignet für Faro del Caballo (starre Zeiten).',
                    ],
                    'verified_at' => now()->subMonths(5),
                ],
            ],

            // ── ES-15 Güemes — LE mythique donativo ─────────────────────────
            [
                'stage_code' => 'ES-15',
                'data' => [
                    'name' => ['fr' => 'La Cabaña del Abuelo Peuto (Güemes)', 'nl' => 'La Cabaña del Abuelo Peuto (Güemes)', 'de' => 'La Cabaña del Abuelo Peuto (Güemes)'],
                    'type' => 'donativo',
                    'address' => 'Güemes, Cantabria, España',
                    'website' => 'https://padreernesto.com',
                    'price_min_eur' => 0.00,
                    'price_max_eur' => 0.00,
                    'is_donativo' => true,
                    'capacity' => 80,
                    'has_shower' => true,
                    'has_kitchen' => false,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => false,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'LE donativo mythique du Norte. Fondé par Padre Ernesto Bustio (prêtre). Capacité 80 personnes. Dîner communautaire inclus dans le donativo. Veillée commune chaque soir. AUCUNE réservation possible ni nécessaire — arriver, c\'est tout. Contribuer selon ses moyens. Un des moments les plus forts du Norte. Ne jamais manquer cet albergue si le timing le permet.',
                        'nl' => 'HET mythische donativo van de Norte. Opgericht door Padre Ernesto Bustio. Capaciteit 80 personen. Gemeenschappelijk diner inbegrepen. GEEN reservering mogelijk of nodig — gewoon aankomen. Bijdragen naar eigen vermogen.',
                        'de' => 'DAS mythische Donativo des Norte. Gegründet von Padre Ernesto Bustio. Kapazität 80 Personen. Gemeinsames Abendessen inklusive. KEINE Reservierung möglich oder nötig — einfach ankommen. Nach eigenen Mitteln beitragen.',
                    ],
                    'bivouac_legal' => false,
                    'verified_at' => now()->subMonths(3),
                ],
            ],

            // ── ES-16 Santander (repos R2) ───────────────────────────────────
            [
                'stage_code' => 'ES-16',
                'data' => [
                    'name' => ['fr' => 'Santander — pension centre-ville', 'nl' => 'Santander — pension stadscentrum', 'de' => 'Santander — Pension Stadtzentrum'],
                    'type' => 'hotel',
                    'price_min_eur' => 35.00,
                    'price_max_eur' => 60.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 2,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Repos R2 Santander. Palacio de la Magdalena (promenade), Sardinero, marché de la Esperanza. Rabas + menú. Réserver à l\'avance.',
                        'nl' => 'Rustdag R2 Santander. Paleis Magdalena, Sardinero, Esperanza-markt. Rabas + menú.',
                        'de' => 'Ruhetag R2 Santander. Palacio Magdalena, Sardinero, Esperanza-Markt. Rabas + Menú.',
                    ],
                    'verified_at' => now()->subMonths(4),
                ],
            ],

            // ── ES-18 Santillana del Mar (repos R3) ─────────────────────────
            [
                'stage_code' => 'ES-18',
                'data' => [
                    'name' => ['fr' => 'Santillana del Mar — Casa del Marqués (histórico)', 'nl' => 'Santillana del Mar — Casa del Marqués', 'de' => 'Santillana del Mar — Casa del Marqués'],
                    'type' => 'hotel',
                    'price_min_eur' => 50.00,
                    'price_max_eur' => 80.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 5,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Repos R3 Santillana. Ville médiévale — nuit dans un bâtiment historique recommandée. Altamira à réserver IMPÉRATIVEMENT ce soir via museodealtamira.mcu.es. Cocido montañés le soir.',
                        'nl' => 'Rustdag R3 Santillana. Middeleeuwse stad — historisch gebouw aanbevolen. Altamira VERPLICHT vanavond reserveren. Cocido montañés.',
                        'de' => 'Ruhetag R3 Santillana. Mittelalterliche Stadt. Altamira UNBEDINGT heute Abend reservieren. Cocido montañés.',
                    ],
                    'verified_at' => now()->subMonths(4),
                ],
            ],

            // ── ES-26 Gijón (repos R4) ───────────────────────────────────────
            [
                'stage_code' => 'ES-26',
                'data' => [
                    'name' => ['fr' => 'Gijón — Albergue Peregrinante (Cimavilla)', 'nl' => 'Gijón — Albergue Peregrinante (Cimavilla)', 'de' => 'Gijón — Albergue Peregrinante (Cimavilla)'],
                    'type' => 'hostel',
                    'price_min_eur' => 12.00,
                    'price_max_eur' => 18.00,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => false,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Repos R4 Gijón. Cimavilla : quartier historique, Elogio del Horizonte (Chillida). Thermes romains. Sidrerías voisines.',
                        'nl' => 'Rustdag R4 Gijón. Cimavilla: historische wijk. Thermes romains. Zijwaartse sidrerías.',
                        'de' => 'Ruhetag R4 Gijón. Cimavilla: historisches Viertel. Römische Thermen. Sidrerías in der Nähe.',
                    ],
                    'verified_at' => now()->subMonths(4),
                ],
            ],

            // ── ES-29 Luarca (repos R5) ──────────────────────────────────────
            [
                'stage_code' => 'ES-29',
                'data' => [
                    'name' => ['fr' => 'Luarca — Hostal Villa Blanca', 'nl' => 'Luarca — Hostal Villa Blanca', 'de' => 'Luarca — Hostal Villa Blanca'],
                    'type' => 'hotel',
                    'price_min_eur' => 30.00,
                    'price_max_eur' => 50.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Repos R5 Luarca. La villa blanche de la Côte Verte. Port, phare, cimetière marin. Merluza a la sidra. Option excursion Obona depuis Luarca (taxi 40 min vers Tineo).',
                        'nl' => 'Rustdag R5 Luarca. Het witte dorp. Haven, vuurtoren. Merluza a la sidra.',
                        'de' => 'Ruhetag R5 Luarca. Das weiße Dorf. Hafen, Leuchtturm. Merluza a la sidra.',
                    ],
                    'verified_at' => now()->subMonths(5),
                ],
            ],

            // ── ES-35 Miraz — donativo Confraternity of St James ────────────
            [
                'stage_code' => 'ES-35',
                'data' => [
                    'name' => ['fr' => 'Albergue de Miraz (Confraternity of St James UK)', 'nl' => 'Albergue van Miraz (Confraternity of St James VK)', 'de' => 'Albergue von Miraz (Confraternity of St James UK)'],
                    'type' => 'donativo',
                    'website' => 'https://www.csj.org.uk/miraz',
                    'price_min_eur' => 0.00,
                    'price_max_eur' => 0.00,
                    'is_donativo' => true,
                    'capacity' => 20,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => false,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Tenu par des hospitaliers bénévoles britanniques de la Confraternity of St James. Thé + scones à l\'arrivée (tradition britannique improbable en Galice !). Ambiance internationale unique. Donativo. Rural profond. AUCUNE réservation — premier arrivé, premier servi.',
                        'nl' => 'Beheerd door Britse vrijwillige hospitaliers van de Confraternity of St James. Thee + scones bij aankomst. Unieke internationale sfeer. Donativo. Geen reservering — wie eerst komt, eerst maalt.',
                        'de' => 'Von britischen Freiwilligen der Confraternity of St James geführt. Tee + Scones bei Ankunft. Einzigartige internationale Atmosphäre. Donativo. Keine Reservierung.',
                    ],
                    'verified_at' => now()->subMonths(3),
                ],
            ],

            // ── ES-36 Sobrado dos Monxes — nuit monastique ──────────────────
            [
                'stage_code' => 'ES-36',
                'data' => [
                    'name' => ['fr' => 'Albergue du Monastère de Sobrado dos Monxes', 'nl' => 'Albergue van het Klooster Sobrado dos Monxes', 'de' => 'Albergue des Klosters Sobrado dos Monxes'],
                    'type' => 'abbey',
                    'address' => 'Monasterio de Santa María de Sobrado, 15813 Sobrado, A Coruña',
                    'phone' => '+34 981 78 75 09',
                    'price_min_eur' => 10.00,
                    'price_max_eur' => 15.00,
                    'capacity' => 100,
                    'has_shower' => true,
                    'has_kitchen' => false,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => false,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Monastère cistercien (1142). Albergue DANS le monastère, géré par les moines. Vêpres avec les moines (19h) possible. Dîner monastique au réfectoire. La dernière grande nuit spirituelle avant la jonction avec le Camino Francés à Arzúa. Cellules spartiate mais chargées d\'histoire. Réserver Santiago et albergues des 3 dernières étapes depuis ici (Arzúa → Pedrouzo → Santiago, forte affluence).',
                        'nl' => 'Cisterciënzer klooster (1142). Albergue IN het klooster, beheerd door monniken. Vespers met monniken (19u) mogelijk. Monastiek diner in het refectorium. Reserveer Santiago en albergues voor de laatste 3 etappes vanuit hier.',
                        'de' => 'Zisterzienserkloster (1142). Albergue IM Kloster, von Mönchen verwaltet. Vesper mit Mönchen (19 Uhr) möglich. Klösterliches Abendessen. Die letzte große spirituelle Nacht. Santiago und Albergues für die letzten 3 Etappen von hier aus reservieren.',
                    ],
                    'verified_at' => now()->subMonths(2),
                ],
            ],

            // ── ES-39 Santiago (R6-R7) ───────────────────────────────────────
            [
                'stage_code' => 'ES-39',
                'data' => [
                    'name' => ['fr' => 'Santiago — Albergue de Peregrinos (Seminario Menor)', 'nl' => 'Santiago — Albergue de Peregrinos (Seminario Menor)', 'de' => 'Santiago — Albergue de Peregrinos (Seminario Menor)'],
                    'type' => 'hostel',
                    'address' => 'Belvis, 15704 Santiago de Compostela',
                    'website' => 'https://www.pilgrimwelcomesantiago.org',
                    'price_min_eur' => 12.00,
                    'price_max_eur' => 18.00,
                    'capacity' => 177,
                    'has_shower' => true,
                    'has_kitchen' => false,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => false,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Le plus grand albergue de Santiago (177 places). 2 nuits maximum. Offices de pèlerins à la cathédrale (12h). Oficina del Peregrino (Rúa Carretas 33) : Compostela + Certificat de Distance (~2 500 km depuis Liège). Messe du pèlerin quotidienne. Repos R6-R7.',
                        'nl' => 'Grootste albergue van Santiago (177 plaatsen). Max 2 nachten. Pèlerinsmis elke dag. Oficina del Peregrino: Compostela + afstandscertificaat. Rustdagen R6-R7.',
                        'de' => 'Größtes Albergue Santiagos (177 Plätze). Max. 2 Nächte. Pilgermesse täglich. Oficina del Peregrino: Compostela + Distanzzertifikat. Ruhetage R6-R7.',
                    ],
                    'verified_at' => now()->subMonths(2),
                ],
            ],
            [
                'stage_code' => 'ES-39',
                'data' => [
                    'name' => ['fr' => 'Santiago — Parador de Turismo (Hostal Reyes Católicos)', 'nl' => 'Santiago — Parador de Turismo (Hostal Reyes Católicos)', 'de' => 'Santiago — Parador de Turismo (Hostal Reyes Católicos)'],
                    'type' => 'hotel',
                    'address' => 'Plaza do Obradoiro 1, 15704 Santiago de Compostela',
                    'website' => 'https://parador.es/santiago',
                    'phone' => '+34 981 58 22 00',
                    'price_min_eur' => 200.00,
                    'price_max_eur' => 400.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 30,
                    'is_primary' => false,
                    'sort_order' => 2,
                    'notes' => [
                        'fr' => 'Le Parador historique — ancienne auberge royale des pèlerins (XVe), directement sur la Plaza del Obradoiro. Tradition : les pèlerins présentant leur Compostela peuvent manger gratuitement au restaurant (0 à 10 repas gratuits par jour selon disponibilité — se renseigner). Expérience unique si budget le permet.',
                        'nl' => 'De historische Parador — voormalige koninklijke pelgrimsherberg (15e eeuw), direct op de Plaza del Obradoiro. Traditie: pelgrims die hun Compostela tonen kunnen gratis eten (0-10 gratis maaltijden/dag naar beschikbaarheid).',
                        'de' => 'Die historische Parador — ehemalige königliche Pilgerherberge (15. Jh.), direkt am Plaza del Obradoiro. Tradition: Pilger mit Compostela können kostenlos essen (0-10 Gratis-Mahlzeiten/Tag je nach Verfügbarkeit).',
                    ],
                    'verified_at' => now()->subMonths(2),
                ],
            ],

            // ── PC-04 Fuente Dé — Picos de Europa ───────────────────────────
            [
                'stage_code' => 'PC-04',
                'data' => [
                    'name' => ['fr' => 'Fuente Dé — Albergue El Redil', 'nl' => 'Fuente Dé — Albergue El Redil', 'de' => 'Fuente Dé — Albergue El Redil'],
                    'type' => 'hostel',
                    'price_min_eur' => 15.00,
                    'price_max_eur' => 20.00,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 7,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Réserver IMPÉRATIVEMENT à l\'avance (seul albergue au fond du cirque). Télécabine Fuente Dé à réserver aussi la veille (quota strict). Cabrales et cocido lebaniés le soir. Température fraîche même en été (1 070 m d\'altitude).',
                        'nl' => 'VERPLICHT vooraf reserveren (enig albergue in het cirque). Kabelbaan Fuente Dé ook dag ervoor reserveren. Cabrales en cocido lebaniés \'s avonds.',
                        'de' => 'UNBEDINGT vorher reservieren (einziges Albergue im Kessel). Seilbahn Fuente Dé ebenfalls vorher reservieren. Cabrales und Cocido lebaniés abends.',
                    ],
                    'verified_at' => now()->subMonths(6),
                ],
            ],
            [
                'stage_code' => 'PC-04',
                'data' => [
                    'name' => ['fr' => 'Fuente Dé — Parador de Fuente Dé (montagne)', 'nl' => 'Fuente Dé — Parador de Fuente Dé (bergen)', 'de' => 'Fuente Dé — Parador de Fuente Dé (Gebirge)'],
                    'type' => 'hotel',
                    'website' => 'https://parador.es/fuente-de',
                    'price_min_eur' => 100.00,
                    'price_max_eur' => 180.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => false,
                    'booking_required' => true,
                    'booking_notice_days' => 14,
                    'is_primary' => false,
                    'sort_order' => 2,
                    'notes' => [
                        'fr' => 'Alternative luxe au fond du cirque. Vue spectaculaire sur le massif. Réserver 2+ semaines à l\'avance en haute saison.',
                        'nl' => 'Luxe alternatief aan het einde van het cirque. Spectaculair uitzicht. Min. 2 weken vooraf reserveren in hoogseizoen.',
                        'de' => 'Luxus-Alternative am Ende des Kessels. Spektakuläre Aussicht. Min. 2 Wochen vorher reservieren in der Hochsaison.',
                    ],
                    'verified_at' => now()->subMonths(6),
                ],
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($accommodations as $item) {
            $stageId = $stagesByCode[$item['stage_code']] ?? null;

            if ($stageId === null) {
                $this->command->warn("AccommodationSeederEspagne : stage {$item['stage_code']} introuvable — skip.");

                continue;
            }

            $nameFr = $item['data']['name']['fr'];

            $existing = Accommodation::where('stage_id', $stageId)
                ->whereJsonContains('name->fr', $nameFr)
                ->first();

            if ($existing !== null) {
                $existing->update(array_merge(['stage_id' => $stageId], $item['data']));
                $updated++;
            } else {
                Accommodation::create(array_merge(['stage_id' => $stageId], $item['data']));
                $created++;
            }
        }

        Log::info('AccommodationSeederEspagne terminé', ['created' => $created, 'updated' => $updated]);
        $this->command->info("AccommodationSeederEspagne : {$created} créés, {$updated} mis à jour.");
    }
}
