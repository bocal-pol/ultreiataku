<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

/**
 * Crée les 5 étapes-variantes Belgique (Voie Mosane) et la branche Bruxelles.
 *
 * Sources :
 *   - etapes/etapes-belgique.md  (données réelles des carnets)
 *   - gpx/reel/jours/BE-J*-variante-*.gpx  (5 variantes)
 *   - gpx/reel/compagnon/bruxelles-namur.gpx  (branche Bruxelles)
 *
 * Chaque variante est :
 *   - is_variant = true
 *   - parent_stage_id = étape principale du jour (J3, J5, J7, J10, J11)
 *   - route_id = route de l'étape parente (Voie Mosane)
 *
 * Branche Bruxelles :
 *   - 1 étape-variante « Approche Bruxelles → Namur »
 *   - parent_stage_id = BE-01 (Liège → Amay, premier jour Voie Mosane)
 *   - route_id = Voie Mosane (pas une route séparée)
 *   - Rattachée par convention à BE-01 car Bruxelles→Namur aboutit au même
 *     bassin de départ de la Voie Mosane (Namur = J4 destination).
 */
class StageVariantBelgiqueSeeder extends Seeder
{
    public function run(): void
    {
        $mosana = PilgrimageRoute::where('slug', 'via-mosana-belgique')->first();
        $monastique = PilgrimageRoute::where('slug', 'via-monastique-belgique')->first();

        if ($mosana === null || $monastique === null) {
            $this->command->error('Routes non trouvées. Exécutez RouteSeeder d\'abord.');

            return;
        }

        // Charger les étapes parentes par code
        $parentStages = Stage::whereIn('code', ['BE-03', 'BE-05', 'BE-07', 'BE-10', 'BE-11', 'BE-01'])
            ->pluck('id', 'code');

        if ($parentStages->count() < 6) {
            $this->command->error('Étapes parentes manquantes. Exécutez StageSeeder d\'abord. Trouvé : ' . $parentStages->count() . '/6');

            return;
        }

        // Charger les waypoints nécessaires
        $wp = Waypoint::whereIn('slug', [
            'huy', 'andenne', 'namur', 'yvoir', 'dinant', 'hastiere', 'couvin',
            'doische', 'olloy-sur-viroin',
            'grotte-scladina-sclayn',
        ])->pluck('id', 'slug');

        // Waypoints variante-spécifiques — utiliser ceux disponibles ou l'étape cible
        // Pour Bruxelles, on utilise Namur comme destination (raccordement à la Voie Mosane)

        // ── 5 variantes Voie Mosane ───────────────────────────────────────────

        $variants = [

            // J3 — Variante Grotte Scladina (Huy → Andenne via Sclayn)
            [
                'route_id' => $mosana->id,
                'parent_stage_id' => $parentStages['BE-03'],
                'is_variant' => true,
                'code' => 'BE-03V-SCLADINA',
                'name' => [
                    'fr' => 'Huy → Andenne via Grotte Scladina',
                    'nl' => 'Hoei → Andenne via Grot Scladina',
                    'de' => 'Huy → Andenne via Höhle Scladina',
                ],
                'day_number' => 3,
                'start_waypoint_id' => $wp['huy'],
                'end_waypoint_id' => $wp['andenne'],
                'distance_km' => 19.50, // 18 km + ~3 km détour A/R Scladina
                'elevation_gain_m' => 220,
                'elevation_loss_m' => 210,
                'estimated_duration_h' => 6.0, // +1h30 visite guidée Scladina
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Variante avec crochet Grotte Scladina (Sclayn, +3 km A/R). Visite guidée 1er dimanche du mois à 14h — réservation OBLIGATOIRE (grotte@scladina.be · 081 58 29 58). Site archéologique : l\'Enfant de Scladina, Néandertalien de +100 000 ans. Quitter la Voie à Sclayn, bien indiqué depuis le RAVeL.',
                    'nl' => 'Variant met omweg Grot Scladina (Sclayn, +3 km heen-terug). Rondleiding 1e zondag van de maand om 14u — reservering VERPLICHT (grotte@scladina.be). Het Kind van Scladina, Neanderthaler van +100.000 jaar.',
                    'de' => 'Variante mit Umweg Höhle Scladina (Sclayn, +3 km Hin-Rück). Führung am 1. Sonntag des Monats um 14 Uhr — Reservierung PFLICHT (grotte@scladina.be). Das Kind von Scladina, Neandertaler von +100.000 Jahren.',
                ],
                'sort_order' => 31,
            ],

            // J5 — Variante Forteresse Poilvache (Namur → Yvoir via Houx)
            [
                'route_id' => $mosana->id,
                'parent_stage_id' => $parentStages['BE-05'],
                'is_variant' => true,
                'code' => 'BE-05V-POILVACHE',
                'name' => [
                    'fr' => 'Namur → Yvoir via Forteresse Poilvache',
                    'nl' => 'Namen → Yvoir via Vesting Poilvache',
                    'de' => 'Namur → Yvoir via Festung Poilvache',
                ],
                'day_number' => 5,
                'start_waypoint_id' => $wp['namur'],
                'end_waypoint_id' => $wp['yvoir'],
                'distance_km' => 21.00, // 18 km + ~3 km détour A/R + montée
                'elevation_gain_m' => 450, // 200 m standard + ~250 m montée Poilvache
                'elevation_loss_m' => 440,
                'estimated_duration_h' => 6.0,
                'difficulty' => 'hard',
                'accommodation_type_default' => 'camping',
                'notes' => [
                    'fr' => 'Variante avec montée Forteresse Poilvache (Houx, +3 km A/R + D+ 250 m supplémentaires). Ruines XIIIe sur éperon rocheux 150 m au-dessus de la Meuse. Panorama exceptionnel sur les méandres et les Rochers de Freÿr. Accès libre (~4 €). Sentier escarpé depuis Houx, 30-40 min. À arbitrer selon la forme du jour.',
                    'nl' => 'Variant met klim naar Vesting Poilvache (Houx, +3 km heen-terug + D+ 250 m extra). Ruïnes 13e eeuw op 150 m boven de Maas. Uitzonderlijk uitzicht op de meanders. Vrije toegang (~4 €). Steile weg vanuit Houx, 30-40 min.',
                    'de' => 'Variante mit Aufstieg zur Festung Poilvache (Houx, +3 km Hin-Rück + D+ 250 m extra). Ruinen 13. Jh. auf 150 m über der Maas. Außergewöhnliche Aussicht auf die Mäander. Freier Zutritt (~4 €). Steiler Weg von Houx, 30-40 Min.',
                ],
                'sort_order' => 51,
            ],

            // J7 — Variante Château-Thierry (Dinant → Hastière via Falmignoul)
            [
                'route_id' => $mosana->id,
                'parent_stage_id' => $parentStages['BE-07'],
                'is_variant' => true,
                'code' => 'BE-07V-CHATEAU-THIERRY',
                'name' => [
                    'fr' => 'Dinant → Hastière via Château-Thierry (vue)',
                    'nl' => 'Dinant → Hastière via Château-Thierry (uitzicht)',
                    'de' => 'Dinant → Hastière via Château-Thierry (Aussicht)',
                ],
                'day_number' => 7,
                'start_waypoint_id' => $wp['dinant'],
                'end_waypoint_id' => $wp['hastiere'],
                'distance_km' => 18.00, // 14 km + ~4 km détour A/R via passeur + montée
                'elevation_gain_m' => 300, // 100 m standard + ~200 m montée Château-Thierry
                'elevation_loss_m' => 290,
                'estimated_duration_h' => 5.5,
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'abbey',
                'notes' => [
                    'fr' => 'Variante via passage d\'eau de Waulsort (dernier passeur manuel de Wallonie depuis 1871, gratuit, 10 min) puis vue extérieure Château-Thierry (Falmignoul, ~4 km A/R + D+ 200 m). ⚠️ Site inaccessible au public (voûtes fragiles) — vue depuis les sentiers de randonnée uniquement (Point de vue du Drapeau, Cascatelles). À réserver aux marcheurs en pleine forme.',
                    'nl' => 'Variant via veerpont Waulsort (laatste handmatige veer van Wallonië, gratis, 10 min) en buitenzicht Château-Thierry (Falmignoul, ~4 km heen-terug + D+ 200 m). ⚠️ Site niet toegankelijk voor publiek — alleen buitenzicht via wandelwegen.',
                    'de' => 'Variante via Fähre Waulsort (letzte manuelle Fähre Walloniens, gratis, 10 Min.) und Außenansicht Château-Thierry (Falmignoul, ~4 km Hin-Rück + D+ 200 m). ⚠️ Nicht öffentlich zugänglich — nur Außenansicht von Wanderwegen.',
                ],
                'sort_order' => 71,
            ],

            // J10 — Variante Roche à Lomme (Doische → Olloy via Dourbes)
            [
                'route_id' => $monastique->id,
                'parent_stage_id' => $parentStages['BE-10'],
                'is_variant' => true,
                'code' => 'BE-10V-ROCHE-A-LOMME',
                'name' => [
                    'fr' => 'Doische → Olloy via Roche à Lomme (Dourbes)',
                    'nl' => 'Doische → Olloy via Roche à Lomme (Dourbes)',
                    'de' => 'Doische → Olloy via Roche à Lomme (Dourbes)',
                ],
                'day_number' => 10,
                'start_waypoint_id' => $wp['doische'],
                'end_waypoint_id' => $wp['olloy-sur-viroin'],
                'distance_km' => 21.00, // 19 km + ~2 km détour A/R
                'elevation_gain_m' => 450, // 400 m standard + montée Roche
                'elevation_loss_m' => 430,
                'estimated_duration_h' => 6.5,
                'difficulty' => 'hard',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Variante avec montée Roche à Lomme (Dourbes, +2 km A/R). Promontoire calcaire, Réserve naturelle classée patrimoine exceptionnel de Wallonie (2009). Grande croix 3 m au sommet, panorama vallée du Viroin. Archéologie : Néolithique, Romains, Gaulois, médiéval. Flore méditerranéenne unique en Belgique (buis, orchidées). Sentier balisé ~30-45 min depuis Dourbes, accès libre. ⚠️ Bivouac INTERDIT dans la Réserve Viroin-Hermeton.',
                    'nl' => 'Variant met klim Roche à Lomme (Dourbes, +2 km heen-terug). Kalksteenpromontoire, Natuurreservaat, uitzonderlijk erfgoed van Wallonië (2009). Groot kruis 3 m op de top, panorama op de Viroindal. Unieke mediterrane flora in België. Gemarkeerde weg ~30-45 min vanuit Dourbes, vrije toegang. ⚠️ Bivakkeren VERBODEN in Reservaat Viroin-Hermeton.',
                    'de' => 'Variante mit Aufstieg Roche à Lomme (Dourbes, +2 km Hin-Rück). Kalkstein-Promontoire, Naturschutzgebiet, außergewöhnliches Erbe Walloniens (2009). Großes Kreuz 3 m am Gipfel, Panorama Viroin-Tal. Einzigartige mediterrane Flora in Belgien. Markierter Weg ~30-45 Min von Dourbes, freier Zutritt. ⚠️ Biwak VERBOTEN im Naturschutzgebiet Viroin-Hermeton.',
                ],
                'sort_order' => 101,
            ],

            // J11 — Variante Grotte de Neptune (Olloy → Couvin via Petigny)
            [
                'route_id' => $monastique->id,
                'parent_stage_id' => $parentStages['BE-11'],
                'is_variant' => true,
                'code' => 'BE-11V-GROTTE-NEPTUNE',
                'name' => [
                    'fr' => 'Olloy → Couvin via Grotte de Neptune (Petigny)',
                    'nl' => 'Olloy → Couvin via Grot van Neptunus (Petigny)',
                    'de' => 'Olloy → Couvin via Neptun-Höhle (Petigny)',
                ],
                'day_number' => 11,
                'start_waypoint_id' => $wp['olloy-sur-viroin'],
                'end_waypoint_id' => $wp['couvin'],
                'distance_km' => 19.00, // 17 km + ~2 km crochet Petigny
                'elevation_gain_m' => 330,
                'elevation_loss_m' => 310,
                'estimated_duration_h' => 6.0, // +45 min visite grotte
                'difficulty' => 'moderate',
                'accommodation_type_default' => 'gite',
                'notes' => [
                    'fr' => 'Variante via Grotte de Neptune (Petigny, ~2 km A/R depuis Couvin ou intégrée si passage Petigny). Rivière souterraine navigable en barque, concrétions calcaires, visite guidée 45 min. Rue de l\'Adujoir 24, 5660 Petigny. Ouv. avr-oct tous les jours. Tarif ~12 €. Tél. 060 31 19 54. Jambon d\'Ardennes au dîner à Couvin.',
                    'nl' => 'Variant via Grot van Neptunus (Petigny, ~2 km heen-terug). Ondergrondse rivier bevaarbaar per boot, kalksteenformaties, rondleiding 45 min. Rue de l\'Adujoir 24, 5660 Petigny. Open apr-okt dagelijks. Tarief ~12 €. Tel. 060 31 19 54.',
                    'de' => 'Variante via Neptun-Höhle (Petigny, ~2 km Hin-Rück). Unterirdischer Fluss per Boot befahrbar, Kalksteinformationen, geführte Tour 45 Min. Rue de l\'Adujoir 24, 5660 Petigny. Offen Apr-Okt täglich. Preis ~12 €. Tel. 060 31 19 54.',
                ],
                'sort_order' => 111,
            ],
        ];

        // ── Branche Bruxelles → Namur (raccordement Voie Mosane) ─────────────
        // Rattachée à BE-01 (premier jour Voie Mosane = Liège → Amay).
        // Décision produit : variante de raccordement sur la Voie Mosane.
        // Les pèlerins venant de Bruxelles rejoignent la Voie Mosane à Namur
        // (J4 destination) en empruntant ce raccordement de ~95 km sur 5 jours.
        // GPX source : gpx/reel/compagnon/bruxelles-namur.gpx

        $bruxellesVariant = [
            'route_id' => $mosana->id,
            'parent_stage_id' => $parentStages['BE-01'],
            'is_variant' => true,
            'code' => 'BE-BXL-NAMUR',
            'name' => [
                'fr' => 'Approche Bruxelles → Namur (raccordement Voie Mosane)',
                'nl' => 'Nadering Brussel → Namen (aansluiting Maasweg)',
                'de' => 'Zubringer Brüssel → Namur (Anschluss Maasweg)',
            ],
            'day_number' => 0, // Étape de raccordement pré-J1 (numérotation spéciale)
            'start_waypoint_id' => $wp['namur'], // Point de jonction — Namur = arrivée de la branche BXL
            'end_waypoint_id' => $wp['namur'],   // et départ J4 de la Voie Mosane
            'distance_km' => 95.00,              // Bruxelles → Namur ~95 km (GPX compagnon)
            'elevation_gain_m' => 600,
            'elevation_loss_m' => 600,
            'estimated_duration_h' => 23.0, // ~5 jours de marche à 18-20 km/j
            'difficulty' => 'moderate',
            'accommodation_type_default' => 'gite',
            'notes' => [
                'fr' => 'Branche de raccordement pour les pèlerins partant de Bruxelles. Rejoint la Voie Mosane à Namur (J4). Tracé : Bruxelles → Wavre → Gembloux → Namur via la vallée de la Dyle et les plateaux brabançons. ~95 km, 5 jours de marche. GPX source : compagnon/bruxelles-namur.gpx. Ce n\'est PAS une route séparée — variante de raccordement sur la Voie Mosane.',
                'nl' => 'Aansluitingstak voor pelgrims vertrekkend vanuit Brussel. Sluit aan op de Maasweg in Namen (J4). Route: Brussel → Waver → Gembloux → Namen via de Dijlevallei en de Brabantse plateaus. ~95 km, 5 loopsdagen. GPX bron: compagnon/bruxelles-namur.gpx.',
                'de' => 'Zubringerast für Pilger, die in Brüssel starten. Anschluss an den Maasweg in Namur (J4). Strecke: Brüssel → Wavre → Gembloux → Namur über das Dijle-Tal und die brabantischen Hochebenen. ~95 km, 5 Wandertage. GPX-Quelle: compagnon/bruxelles-namur.gpx.',
            ],
            'sort_order' => 0,
        ];

        // Séeder toutes les variantes
        $allVariants = array_merge($variants, [$bruxellesVariant]);
        $count = 0;

        foreach ($allVariants as $variant) {
            Stage::updateOrCreate(
                ['code' => $variant['code']],
                $variant,
            );
            $count++;
        }

        $this->command->info(sprintf(
            'StageVariantBelgiqueSeeder : %d variantes créées/mises à jour (%d BE + 1 Bruxelles).',
            $count,
            $count - 1,
        ));
    }
}
