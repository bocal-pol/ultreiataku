<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

/**
 * Crée les étapes-variantes Espagne (is_variant=true).
 *
 * Variantes Norte (ES) :
 *   ES-15V-FARO-CABALLO  → parent ES-14 (Laredo→Santoña, après-midi Faro del Caballo)
 *   ES-18V-ALTAMIRA      → parent ES-18 (repos R3 Santillana — excursion Altamira)
 *   ES-18V-COHICILLOS    → parent ES-18 (Requejada→Santillana via Cohicillos)
 *   ES-20V-PICOS-LIEBANA → parent ES-20 (San Vicente : embranchement module Picos)
 *   ES-23V-GULPIYURI     → parent ES-23 (Llanes→Nueva : détour Gulpiyuri 600 m)
 *   ES-24V-PICOS-COVADONGA → parent ES-24 (Nueva→Ribadesella : embranchement Covadonga)
 *   ES-28V-CUDILLERO     → parent ES-28 (Avilés→Soto : détour Cudillero +2 km)
 *
 * Variantes Picos (PC) :
 *   PC-05V-BULNES-URRIELLU → parent PC-05 (Fuente Dé→Sotres : variante Bulnes)
 *   PC-07V-MONTAGNE        → parent PC-07 (Arenas→Covadonga : variante lacs)
 *
 * Idempotent — updateOrCreate sur code.
 */
class StageVariantEspagneSeeder extends Seeder
{
    public function run(): void
    {
        $norte = PilgrimageRoute::where('slug', 'camino-del-norte-es')->first();
        $picos = PilgrimageRoute::where('slug', 'module-picos-de-europa')->first();

        if ($norte === null) {
            $this->command->error('Route camino-del-norte-es non trouvée. Exécutez RouteSeederEspagne d\'abord.');

            return;
        }

        // Charger les étapes parentes par code
        $parentStages = Stage::whereIn('code', [
            'ES-14', 'ES-18', 'ES-20', 'ES-23', 'ES-24', 'ES-28', // Norte
            'PC-05', 'PC-07',                                        // Picos
        ])->pluck('id', 'code');

        if ($parentStages->count() < 6) {
            $this->command->error(sprintf(
                'Étapes parentes manquantes. Exécutez StageSeederEspagne d\'abord. Trouvé : %d/8',
                $parentStages->count(),
            ));

            return;
        }

        // Charger les waypoints nécessaires
        $wp = Waypoint::whereIn('slug', [
            'santona', 'guemes', 'requejada', 'santillana-del-mar',
            'san-vicente-de-la-barquera', 'llanes', 'nueva-cuerres',
            'ribadesella', 'aviles', 'soto-de-luina',
            'fuente-de', 'sotres', 'arenas-de-cabrales',
            'grottes-altamira', 'cohicillos-cartes', 'playa-de-gulpiyuri',
        ])->pluck('id', 'slug');

        $variants = [

            // ── Variantes Norte (ES) ────────────────────────────────────────────

            // ES-14V-FARO-CABALLO — après-midi Faro del Caballo depuis Santoña
            [
                'route_id' => $norte->id,
                'parent_stage_id' => $parentStages['ES-14'] ?? null,
                'is_variant' => true,
                'code' => 'ES-15V-FARO-CABALLO',
                'name' => [
                    'fr' => 'Santoña : Faro del Caballo (Monte Buciero, 763 marches)',
                    'nl' => 'Santoña: Faro del Caballo (Monte Buciero, 763 treden)',
                    'de' => 'Santoña: Faro del Caballo (Monte Buciero, 763 Stufen)',
                ],
                'day_number' => 14,
                'start_waypoint_id' => $wp['santona'] ?? null,
                'end_waypoint_id' => $wp['santona'] ?? null,
                'distance_km' => 7.50,
                'elevation_gain_m' => 430,
                'elevation_loss_m' => 430,
                'estimated_duration_h' => 3.0,
                'difficulty' => 'hard',
                'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => 'Excursion depuis l\'albergue de Santoña (après-midi J14). Sentier Monte Buciero → 763 marches taillées dans la roche → phare abandonné. SANS eau ni ravitaillement sur le sentier (emporter 1,5 L). Vue panoramique sur la ría, les plages et les Picos au fond par temps clair. Durée A/R : ~3 h depuis le port. Ne pas tenter si vent fort (> Beaufort 5). La « montée mystique » du Norte après les anchois de Santoña.',
                    'nl' => 'Uitstap vanuit het albergue van Santoña (namiddag J14). Monte Buciero → 763 in rots gekapte treden → verlaten vuurtoren. ZONDER water op het pad (1,5 L meenemen). Panoramisch uitzicht over de ría en de Picos. Duur heen-terug: ~3 u.',
                    'de' => 'Ausflug ab dem Albergue von Santoña (Nachmittag J14). Monte Buciero → 763 in den Fels gehauene Stufen → verlassener Leuchtturm. OHNE Wasser unterwegs (1,5 L mitnehmen). Panoramablick über die Ría und die Picos. Dauer hin-zurück: ~3 Std.',
                ],
                'sort_order' => 141,
            ],

            // ES-18V-ALTAMIRA — repos R3 Santillana : excursion Altamira
            [
                'route_id' => $norte->id,
                'parent_stage_id' => $parentStages['ES-18'] ?? null,
                'is_variant' => true,
                'code' => 'ES-18V-ALTAMIRA',
                'name' => [
                    'fr' => 'Repos R3 Santillana : excursion Grottes d\'Altamira',
                    'nl' => 'Rustdag R3 Santillana: uitstap Grotten van Altamira',
                    'de' => 'Ruhetag R3 Santillana: Ausflug Altamira-Höhlen',
                ],
                'day_number' => 18,
                'start_waypoint_id' => $wp['santillana-del-mar'] ?? null,
                'end_waypoint_id' => $wp['santillana-del-mar'] ?? null,
                'distance_km' => 4.00,
                'elevation_gain_m' => 50,
                'elevation_loss_m' => 50,
                'estimated_duration_h' => 4.0,
                'difficulty' => 'easy',
                'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => 'Excursion pédestre depuis Santillana (2 km à pied). Réservation OBLIGATOIRE : museodealtamira.mcu.es · quota journalier strict · billet ~3 €. Musée avec néo-grotte : réplique exacte des peintures rupestres (bisons polychromes ~15 000 ans). La grotte originale est fermée au public depuis 2002 (préservation). Écho avec la visite de la Grotte Scladina (Belgique, J3) : ton pèlerinage traverse 100 000 ans d\'art humain.',
                    'nl' => 'Wandeluitstap vanuit Santillana (2 km te voet). Reservering VERPLICHT: museodealtamira.mcu.es · strikt dagsquotum · ticket ~3 €. Museum met neogrot: exacte replica van de grotschilderingen (polychrome bizons ~15 000 jaar).',
                    'de' => 'Wanderausflug ab Santillana (2 km zu Fuß). Reservierung PFLICHT: museodealtamira.mcu.es · strenge Tagesquoten · Ticket ~3 €. Museum mit Neo-Höhle: exakte Replika der Höhlenmalereien (polychrome Bisons ~15 000 Jahre).',
                ],
                'sort_order' => 182,
            ],

            // ES-18V-COHICILLOS — variante familiale Requejada → Santillana via Cohicillos
            [
                'route_id' => $norte->id,
                'parent_stage_id' => $parentStages['ES-18'] ?? null,
                'is_variant' => true,
                'code' => 'ES-18V-COHICILLOS',
                'name' => [
                    'fr' => 'Requejada → Santillana via Cohicillos (variante familiale)',
                    'nl' => 'Requejada → Santillana via Cohicillos (familievariant)',
                    'de' => 'Requejada → Santillana via Cohicillos (Familienvariante)',
                ],
                'day_number' => 18,
                'start_waypoint_id' => $wp['requejada'] ?? null,
                'end_waypoint_id' => $wp['santillana-del-mar'] ?? null,
                'distance_km' => 20.00,
                'elevation_gain_m' => 250,
                'elevation_loss_m' => 240,
                'estimated_duration_h' => 5.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => 'Variante familiale recommendée (+11 km vs 9 km directs). Via Torrelavega → Cartes → Cohicillos → Santillana. Cohicillos (commune de Cartes, Cantabrie) : Ermita de San Cipriano sur sa colline, village d\'origine du grand-père. Romería de San Cipriano le 16 septembre (pèlerinage local le plus ancien de Cantabrie). L\'étape où le voyage personnel et le chemin se rencontrent.',
                    'nl' => 'Aanbevolen familievariant (+11 km vs 9 km direct). Via Torrelavega → Cartes → Cohicillos → Santillana. Cohicillos (gemeente Cartes): Ermita de San Cipriano, het geboortedorp van grootvader. Romería de San Cipriano op 16 september.',
                    'de' => 'Empfohlene Familienvariante (+11 km vs 9 km direkt). Via Torrelavega → Cartes → Cohicillos → Santillana. Cohicillos (Gemeinde Cartes): Ermita de San Cipriano auf seinem Hügel, Herkunftsdorf des Großvaters. Romería de San Cipriano am 16. September.',
                ],
                'sort_order' => 183,
            ],

            // ES-20V-PICOS-LIEBANA — embranchement module Picos depuis San Vicente
            [
                'route_id' => $norte->id,
                'parent_stage_id' => $parentStages['ES-20'] ?? null,
                'is_variant' => true,
                'code' => 'ES-20V-PICOS-LIEBANA',
                'name' => [
                    'fr' => 'San Vicente → Module Picos Liébana (Camino Lebaniego)',
                    'nl' => 'San Vicente → Module Picos Liébana (Camino Lebaniego)',
                    'de' => 'San Vicente → Modul Picos Liébana (Camino Lebaniego)',
                ],
                'day_number' => 20,
                'start_waypoint_id' => $wp['san-vicente-de-la-barquera'] ?? null,
                'end_waypoint_id' => $wp['ribadesella'] ?? null,
                'distance_km' => 252.00, // 9 étapes PC (~180 km) + retour Norte ES-24 à Ribadesella
                'elevation_gain_m' => 5800,
                'elevation_loss_m' => 5800,
                'estimated_duration_h' => 0.0, // Non applicable — 9-12 jours selon rythme
                'difficulty' => 'hard',
                'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Embranchement optionnel du Norte vers le Module Picos de Europa. Depuis San Vicente, suivre le Camino Lebaniego (72 km, UNESCO) vers Potes. Puis haute route Picos → Covadonga → retour au Norte à Ribadesella (jonction ES-24). Voir les 9 étapes PC-01→PC-09. Durée totale : +9-12 jours. Réservation téléférique Fuente Dé indispensable (quota strict).',
                    'nl' => 'Optionele aftakking van de Norte naar het Module Picos de Europa. Vanuit San Vicente, de Camino Lebaniego (72 km, UNESCO) volgen naar Potes. Dan hoge Picos-route → Covadonga → terugkeer Norte in Ribadesella (aansluiting ES-24). Zie 9 etappes PC-01→PC-09.',
                    'de' => 'Optionale Abzweigung des Norte zum Modul Picos de Europa. Von San Vicente dem Camino Lebaniego (72 km, UNESCO) nach Potes folgen. Dann Hochroute Picos → Covadonga → Rückkehr Norte in Ribadesella (Anschluss ES-24). Siehe 9 Etappen PC-01→PC-09.',
                ],
                'sort_order' => 201,
            ],

            // ES-23V-GULPIYURI — détour Playa de Gulpiyuri (~600 m depuis le Norte à Naves)
            [
                'route_id' => $norte->id,
                'parent_stage_id' => $parentStages['ES-23'] ?? null,
                'is_variant' => true,
                'code' => 'ES-23V-GULPIYURI',
                'name' => [
                    'fr' => 'Détour Playa de Gulpiyuri (300 m A/R)',
                    'nl' => 'Omweg Playa de Gulpiyuri (300 m heen-terug)',
                    'de' => 'Umweg Playa de Gulpiyuri (300 m hin-zurück)',
                ],
                'day_number' => 23,
                'start_waypoint_id' => $wp['llanes'] ?? null,
                'end_waypoint_id' => $wp['nueva-cuerres'] ?? null,
                'distance_km' => 20.60, // 20 km + 600 m détour
                'elevation_gain_m' => 310,
                'elevation_loss_m' => 300,
                'estimated_duration_h' => 5.8,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Variante courte OBLIGATOIRE intégrée à l\'étape ES-23. À Naves (village sur le Norte), 300 m hors-chemin vers la Playa de Gulpiyuri. Plage intérieure de 40 m au milieu des prés : doline inondée via tunnel sous-marin, Monument Naturel. Viser marée montante/haute — à marée basse la plage est sans eau. Bufones de Pría (geysers marins) à observer aussi sur le chemin.',
                    'nl' => 'Korte VERPLICHTE omweg geïntegreerd in etappe ES-23. In Naves, 300 m buiten het pad naar Playa de Gulpiyuri. 40 m binnenlandse doline-strand. Bij hoog-/vloed te bezoeken. Bufones de Pría (zeegejsers) ook te zien.',
                    'de' => 'Kurzer PFLICHT-Umweg integriert in Etappe ES-23. In Naves, 300 m vom Weg zur Playa de Gulpiyuri. 40 m innenliegende Doline. Bei Flut/Hochwasser besuchen. Bufones de Pría (Meeresgeysire) ebenfalls sichtbar.',
                ],
                'sort_order' => 231,
            ],

            // ES-24V-PICOS-COVADONGA — embranchement module Picos depuis Ribadesella
            [
                'route_id' => $norte->id,
                'parent_stage_id' => $parentStages['ES-24'] ?? null,
                'is_variant' => true,
                'code' => 'ES-24V-PICOS-COVADONGA',
                'name' => [
                    'fr' => 'Ribadesella → Module Picos Covadonga (via Arriondas)',
                    'nl' => 'Ribadesella → Module Picos Covadonga (via Arriondas)',
                    'de' => 'Ribadesella → Modul Picos Covadonga (via Arriondas)',
                ],
                'day_number' => 24,
                'start_waypoint_id' => $wp['ribadesella'] ?? null,
                'end_waypoint_id' => $wp['ribadesella'] ?? null,
                'distance_km' => 36.00, // PC-07→PC-09 retour Ribadesella
                'elevation_gain_m' => 1200,
                'elevation_loss_m' => 1200,
                'estimated_duration_h' => 0.0, // 3-4 jours selon entrée Arriondas/Covadonga
                'difficulty' => 'hard',
                'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Deuxième embranchement pour le Module Picos depuis Ribadesella. Via Arriondas → Covadonga (entrée par la vallée — évite Fuente Dé et téléférique). Rejoint le Norte à Ribadesella (ES-24). Voir les étapes PC-07→PC-09. Alternative plus courte (+3 jours) au module complet (9 jours).',
                    'nl' => 'Tweede vertakking voor het Module Picos vanuit Ribadesella. Via Arriondas → Covadonga. Sluit aan op Norte in Ribadesella (ES-24). Zie etappes PC-07→PC-09. Kortere alternatief (+3 dagen) voor de volledige module (9 dagen).',
                    'de' => 'Zweite Abzweigung für das Picos-Modul von Ribadesella. Via Arriondas → Covadonga. Anschluss Norte in Ribadesella (ES-24). Siehe Etappen PC-07→PC-09. Kürzere Alternative (+3 Tage) zur vollen Module (9 Tage).',
                ],
                'sort_order' => 241,
            ],

            // ES-28V-CUDILLERO — détour Cudillero depuis le Norte (+2 km)
            [
                'route_id' => $norte->id,
                'parent_stage_id' => $parentStages['ES-28'] ?? null,
                'is_variant' => true,
                'code' => 'ES-28V-CUDILLERO',
                'name' => [
                    'fr' => 'Avilés → Soto via Cudillero (village-amphithéâtre)',
                    'nl' => 'Avilés → Soto via Cudillero (amfitheater-dorp)',
                    'de' => 'Avilés → Soto via Cudillero (Amphitheater-Dorf)',
                ],
                'day_number' => 28,
                'start_waypoint_id' => $wp['aviles'] ?? null,
                'end_waypoint_id' => $wp['soto-de-luina'] ?? null,
                'distance_km' => 40.00, // 38 km + 2 km détour
                'elevation_gain_m' => 710,
                'elevation_loss_m' => 690,
                'estimated_duration_h' => 10.0,
                'difficulty' => 'hard',
                'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Variante qui intègre Cudillero dans l\'étape (+2 km). Village de pêcheurs multicolore en amphithéâtre au-dessus du port — l\'un des plus beaux villages d\'Espagne. Pixín (lotte) et mariscos des ports. Chorizo a la sidra. Nécessite une étape longue (38+2 km) : partir à 6h minimum, envisager de couper à Muros de Nalón (23 km).',
                    'nl' => 'Variant die Cudillero in de etappe integreert (+2 km). Multicolore amfitheater-vissersdorp — een van de mooiste dorpen van Spanje. Pixín (zeeduivel) en mariscos. Chorizo a la sidra.',
                    'de' => 'Variante, die Cudillero in die Etappe integriert (+2 km). Buntes Amphitheater-Fischerdorf — eines der schönsten Dörfer Spaniens. Pixín (Seeteufel) und Mariscos. Chorizo a la sidra.',
                ],
                'sort_order' => 281,
            ],

            // ── Variantes Module Picos (PC) ─────────────────────────────────────

            // PC-05V-BULNES-URRIELLU — variante depuis Sotres vers Bulnes + Naranjo
            [
                'route_id' => $picos !== null ? $picos->id : $norte->id,
                'parent_stage_id' => $parentStages['PC-05'] ?? null,
                'is_variant' => true,
                'code' => 'PC-05V-BULNES-URRIELLU',
                'name' => [
                    'fr' => 'Variante PC-05 — Bulnes + Naranjo de Bulnes (Urriellu)',
                    'nl' => 'Variant PC-05 — Bulnes + Naranjo de Bulnes (Urriellu)',
                    'de' => 'Variante PC-05 — Bulnes + Naranjo de Bulnes (Urriellu)',
                ],
                'day_number' => 5,
                'start_waypoint_id' => $wp['fuente-de'] ?? null,
                'end_waypoint_id' => $wp['sotres'] ?? null,
                'distance_km' => 22.00,
                'elevation_gain_m' => 800,
                'elevation_loss_m' => 600,
                'estimated_duration_h' => 8.0,
                'difficulty' => 'hard',
                'accommodation_type_default' => 'hostel',
                'notes' => [
                    'fr' => 'Variante alpine haute depuis les Picos. Via Bulnes (village enclavé, accessible uniquement à pied ou funiculaire depuis Arenas — 15 min, ~5 €). Vue sur le Naranjo de Bulnes/Urriellu (2 519 m, monolithe emblématique). Excellente forme physique requise. Cabane gardée à Vega de Urriello possible si journée trop longue.',
                    'nl' => 'Hoge alpenvariant vanuit de Picos. Via Bulnes (enclave, alleen te voet of funiculair vanuit Arenas — 15 min, ~5 €). Uitzicht op Naranjo de Bulnes/Urriellu (2 519 m). Uitstekende fysieke conditie vereist.',
                    'de' => 'Hochalpine Variante aus den Picos. Via Bulnes (Enklave, nur zu Fuß oder Standseilbahn ab Arenas — 15 Min., ~5 €). Blick auf Naranjo de Bulnes/Urriellu (2 519 m). Sehr gute körperliche Verfassung erforderlich.',
                ],
                'sort_order' => 51,
            ],

            // PC-07V-MONTAGNE — haute route lacs Enol/Ercina depuis Covadonga
            [
                'route_id' => $picos !== null ? $picos->id : $norte->id,
                'parent_stage_id' => $parentStages['PC-07'] ?? null,
                'is_variant' => true,
                'code' => 'PC-07V-MONTAGNE',
                'name' => [
                    'fr' => 'Variante PC-07 — Lacs Enol/Ercina (haute route Covadonga)',
                    'nl' => 'Variant PC-07 — Meren Enol/Ercina (hoge Covadonga-route)',
                    'de' => 'Variante PC-07 — Seen Enol/Ercina (Hochroute Covadonga)',
                ],
                'day_number' => 7,
                'start_waypoint_id' => $wp['arenas-de-cabrales'] ?? null,
                'end_waypoint_id' => $wp['arenas-de-cabrales'] ?? null,
                'distance_km' => 35.00,
                'elevation_gain_m' => 1200,
                'elevation_loss_m' => 1100,
                'estimated_duration_h' => 10.0,
                'difficulty' => 'hard',
                'accommodation_type_default' => 'hotel',
                'notes' => [
                    'fr' => 'Haute route par les lacs glaciaires d\'Enol et d\'Ercina (1 000 m d\'altitude, vue spectaculaire sur les Picos enneigés). Recommandée si météo dégagée. Accès à Covadonga depuis les lacs : descente 12 km. Attention : route des lacs fermée aux voitures en saison haute — bus officiel ou pédestres uniquement.',
                    'nl' => 'Hoge route via de gletsjermeren Enol en Ercina (1 000 m hoogte, spectaculair uitzicht op besneeuwde Picos). Aanbevolen bij helder weer. Toegang Covadonga via afdaling 12 km. Weg naar de meren afgesloten voor auto\'s in hoogseizoen — officiële bus of voetgangers.',
                    'de' => 'Hochroute via die Gletscherseen Enol und Ercina (1 000 m Höhe, spektakulärer Blick auf verschneite Picos). Bei klarem Wetter empfohlen. Zugang Covadonga via Abstieg 12 km. Seenstraße in der Hochsaison für Autos gesperrt — offizieller Bus oder Fußgänger.',
                ],
                'sort_order' => 71,
            ],
        ];

        $count = 0;
        $skipped = 0;

        foreach ($variants as $variant) {
            if ($variant['parent_stage_id'] === null) {
                $this->command->warn("parent_stage_id null pour {$variant['code']} — étape parente manquante. Skip.");
                $skipped++;

                continue;
            }

            Stage::updateOrCreate(
                ['code' => $variant['code']],
                $variant,
            );
            $count++;
        }

        $this->command->info(sprintf(
            'StageVariantEspagneSeeder : %d variantes créées/mises à jour, %d ignorées.',
            $count,
            $skipped,
        ));
    }
}
