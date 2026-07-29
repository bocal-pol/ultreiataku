<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Attache les POI (highlights) ES aux étapes via la table pivot stage_waypoint.
 *
 * Sources :
 *   - ravitaillement/carnet-espagne.md
 *   - WaypointSeederEspagne.php (slugs de référence)
 *
 * Idempotent via syncWithoutDetaching.
 * start_waypoint_id / end_waypoint_id sont déjà liés par StageSeederEspagne.
 * Ce seeder ajoute les POI notables traversés ou proches des étapes.
 */
class StagePOISeederEspagne extends Seeder
{
    public function run(): void
    {
        $stages = Stage::whereIn('code', array_keys($this->getAttachments()))
            ->get()
            ->keyBy('code');

        $wp = Waypoint::all()->keyBy('slug');

        if ($stages->isEmpty() || $wp->isEmpty()) {
            $this->command->error('Stages ou waypoints ES manquants. Exécutez StageSeederEspagne + WaypointSeederEspagne d\'abord.');

            return;
        }

        $attached = 0;
        $skipped = 0;

        foreach ($this->getAttachments() as $stageCode => $pois) {
            $stage = $stages->get($stageCode);

            if ($stage === null) {
                $this->command->warn("StagePOISeederEspagne : étape {$stageCode} non trouvée, skip.");
                $skipped++;

                continue;
            }

            foreach ($pois as $poiData) {
                $waypoint = $wp->get($poiData['slug']);

                if ($waypoint === null) {
                    $this->command->warn("StagePOISeederEspagne : waypoint {$poiData['slug']} non trouvé pour {$stageCode}, skip.");
                    $skipped++;

                    continue;
                }

                $stage->waypoints()->syncWithoutDetaching([
                    $waypoint->id => [
                        'sort_order' => $poiData['sort_order'],
                        'is_highlight' => $poiData['is_highlight'],
                    ],
                ]);

                $attached++;
            }
        }

        Log::info('StagePOISeederEspagne terminé', ['attached' => $attached, 'skipped' => $skipped]);
        $this->command->info("StagePOISeederEspagne : {$attached} liaisons stage↔waypoint créées/mises à jour, {$skipped} ignorées.");
    }

    /**
     * Mapping code étape ES/PC → POI à attacher.
     * Slugs alignés sur WaypointSeederEspagne (slugs exacts).
     *
     * @return array<string, list<array{slug: string, sort_order: int, is_highlight: bool}>>
     */
    private function getAttachments(): array
    {
        return [

            // ── ES-07 Deba — falaises Flysch de Zumaia ───────────────────────
            'ES-07' => [
                ['slug' => 'flysch-zumaia', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-09 Gernika — Pont transbordeur Vizcaya (UNESCO) ───────────
            // Slug réel : pont-transbordeur-portugalete (traversé ES-09/ES-11)
            'ES-09' => [
                ['slug' => 'pont-transbordeur-portugalete', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-11 Portuguesa — côté arrivée du pont transbordeur ─────────
            'ES-11' => [
                ['slug' => 'pont-transbordeur-portugalete', 'sort_order' => 1, 'is_highlight' => false],
            ],

            // ── ES-14 Santoña — Faro del Caballo (763 marches) ──────────────
            // Slug réel : faro-del-caballo-santona
            'ES-14' => [
                ['slug' => 'faro-del-caballo-santona', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-15 Güemes — Albergue La Cabaña del Abuelo Peuto ──────────
            // Slug réel : albergue-guemes
            'ES-15' => [
                ['slug' => 'albergue-guemes', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-18 Santillana — Grottes d'Altamira ───────────────────────
            // Slug réel : grottes-altamira
            'ES-18' => [
                ['slug' => 'grottes-altamira', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-19 Comillas — El Capricho de Gaudí ───────────────────────
            // Slug réel : el-capricho-gaudi-comillas
            'ES-19' => [
                ['slug' => 'el-capricho-gaudi-comillas', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-20 San Vicente de la Barquera — porto Asturies + Picos ───
            // Santo Toribio en détour depuis San Vicente (slug réel : santo-toribio-liebana)
            'ES-20' => [
                ['slug' => 'santo-toribio-liebana', 'sort_order' => 1, 'is_highlight' => false],
            ],

            // ── ES-23 Nueva — Playa de Gulpiyuri ────────────────────────────
            // Slug réel : playa-de-gulpiyuri
            'ES-23' => [
                ['slug' => 'playa-de-gulpiyuri', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-33 Mondoñedo — Cathédrale de Mondoñedo ──────────────────
            // Slug réel : catedral-mondonedo
            'ES-33' => [
                ['slug' => 'catedral-mondonedo', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-36 Sobrado — Monastère de Sobrado dos Monxes ─────────────
            // Slug réel : monastere-sobrado
            'ES-36' => [
                ['slug' => 'monastere-sobrado', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── ES-39 Santiago de Compostela — Cathédrale ───────────────────
            // Slug réel : catedral-santiago
            'ES-39' => [
                ['slug' => 'catedral-santiago', 'sort_order' => 1, 'is_highlight' => true],
            ],

            // ── PC-03 Potes — Santo Toribio de Liébana ──────────────────────
            'PC-03' => [
                ['slug' => 'santo-toribio-liebana', 'sort_order' => 1, 'is_highlight' => true],
            ],
        ];
    }
}
