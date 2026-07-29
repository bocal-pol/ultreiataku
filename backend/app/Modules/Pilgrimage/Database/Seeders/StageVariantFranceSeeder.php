<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

/**
 * Crée la variante France : Faux de Verzy (J7 → J8, boucle matinale).
 *
 * Source : etapes/etapes-france.md (Segment B — POI Faux de Verzy)
 *          gpx/reel/jours/FR-J7-variante-Faux-de-Verzy.gpx
 *
 * La variante est rattachée à FR-04 (Reims → Verzy) comme étape parente :
 *   - is_variant = true
 *   - parent_stage_id = FR-04
 *   - Le pèlerin dort à Verzy J7, fait la boucle au matin J8 avant de descendre vers Châlons
 */
class StageVariantFranceSeeder extends Seeder
{
    public function run(): void
    {
        $campaniensis = PilgrimageRoute::where('slug', 'via-campaniensis-france')->first();

        if ($campaniensis === null) {
            $this->command->error('Route via-campaniensis-france non trouvée. Exécutez RouteSeederFrance d\'abord.');

            return;
        }

        $parentStage = Stage::where('code', 'FR-04')->first();

        if ($parentStage === null) {
            $this->command->error('Étape parente FR-04 non trouvée. Exécutez StageSeederFrance d\'abord.');

            return;
        }

        $wp = Waypoint::whereIn('slug', ['verzy', 'faux-de-verzy'])->pluck('id', 'slug');

        // La boucle des Faux démarre et termine à Verzy
        $verzyId = $wp['verzy'] ?? null;

        if ($verzyId === null) {
            $this->command->error('Waypoint verzy non trouvé. Exécutez WaypointSeederFrance d\'abord.');

            return;
        }

        $variant = [
            'route_id' => $campaniensis->id,
            'parent_stage_id' => $parentStage->id,
            'is_variant' => true,
            'code' => 'FR-04V-FAUX-VERZY',
            'name' => [
                'fr' => 'Boucle des Faux de Verzy (hêtres tortillards) — matin J8',
                'nl' => 'Lus van de Faux de Verzy (kronkelbeuken) — ochtend J8',
                'de' => 'Runde der Faux de Verzy (Tortillard-Buchen) — Morgen J8',
            ],
            'day_number' => 4, // Jour 4 du tronçon, boucle matinale avant descente vers Châlons
            'start_waypoint_id' => $verzyId,
            'end_waypoint_id' => $verzyId, // Boucle : retour à Verzy
            'distance_km' => 4.00,   // Boucle balisée ~4 km sur caillebotis
            'elevation_gain_m' => 60,
            'elevation_loss_m' => 60,
            'estimated_duration_h' => 1.5,
            'difficulty' => 'easy',
            'accommodation_type_default' => null, // Pas de nuit — boucle sur place depuis Verzy
            'notes' => [
                'fr' => 'La plus grande réserve mondiale de hêtres tortillards (~1 000 arbres mutants aux branches en parapluie, certains de 300+ ans). Réserve biologique ONF sur le plateau de la Montagne de Reims. Boucle balisée 4 km sur caillebotis autour des plus beaux spécimens + point de vue du Mont Sinaï sur le vignoble. Départ : 1,5 km au sud de Verzy village. ⭐ Plan conseillé : nuit à Verzy J7 (étape FR-04), boucle au lever du jour J8 (lumière rasante = féerique), puis descente vers Châlons (étape FR-05). Gratuit, libre, aucune réservation. GPX source : FR-J7-variante-Faux-de-Verzy.gpx.',
                'nl' => 'Grootste wereldreserve van kronkelbeuken (~1 000 gemuteerde bomen, sommige 300+ jaar oud). ONF biologisch reservaat op het plateau van de Montagne de Reims. Gemarkeerde lus 4 km op houten loopplanken + uitzicht Mont Sinaï. Start: 1,5 km ten zuiden van Verzy dorp. ⭐ Aanbevolen plan: nacht Verzy J7, lus bij zonsopgang J8 (laagstaand licht = betoverend), dan afdaling naar Châlons. Gratis, vrij toegankelijk. GPX: FR-J7-variante-Faux-de-Verzy.gpx.',
                'de' => 'Größtes Weltvorkommen der Tortillard-Buchen (~1 000 mutierte Bäume, einige 300+ Jahre alt). ONF-Naturschutzgebiet auf dem Plateau der Montagne de Reims. Markierte Rundtour 4 km auf Holzstegen + Aussichtspunkt Mont Sinaï. Start: 1,5 km südlich von Verzy. ⭐ Empfohlener Plan: Übernachtung Verzy J7, Runde im Morgengrauen J8 (Streifenlicht = zauberhaft), dann Abstieg nach Châlons. Kostenlos, frei zugänglich. GPX: FR-J7-variante-Faux-de-Verzy.gpx.',
            ],
            'sort_order' => 41,
        ];

        Stage::updateOrCreate(
            ['code' => $variant['code']],
            $variant,
        );

        $this->command->info('StageVariantFranceSeeder : 1 variante FR créée/mise à jour (FR-04V-FAUX-VERZY).');
    }
}
