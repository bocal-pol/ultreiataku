<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature pour GET /api/pilgrimage/stages.
 * ULTREIA-15 + ULTREIA-1T.
 */
class StageApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeStage(array $overrides = []): Stage
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();

        return Stage::factory()->create(array_merge([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ], $overrides));
    }

    // ─── GET /api/pilgrimage/stages ──────────────────────────────────────────

    public function test_index_returns_200_with_json_structure(): void
    {
        $this->makeStage();
        $this->makeStage();

        $response = $this->getJson('/api/pilgrimage/stages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'day_number', 'distance_km', 'difficulty'],
                ],
            ]);
    }

    public function test_index_filter_by_difficulty(): void
    {
        $this->makeStage(['difficulty' => 'easy']);
        $this->makeStage(['difficulty' => 'hard']);

        $response = $this->getJson('/api/pilgrimage/stages?difficulty=easy');

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $stage) {
            $this->assertSame('easy', $stage['difficulty']);
        }
    }

    public function test_index_filter_by_route_id(): void
    {
        $route = PilgrimageRoute::factory()->create();
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();

        Stage::factory()->forRoute($route)->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);
        $this->makeStage(); // autre route

        $response = $this->getJson('/api/pilgrimage/stages?route_id=' . $route->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_is_paginated(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->makeStage();
        }

        $response = $this->getJson('/api/pilgrimage/stages');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['total', 'per_page', 'current_page']]);
    }

    /**
     * BUG-P1-001 — Vérification du tri groupé par route_id puis sort_order.
     * Deux routes avec sort_order 1..2 ne doivent pas s'entremêler.
     */
    public function test_index_ordered_by_route_then_sort_order(): void
    {
        $routeA = PilgrimageRoute::factory()->create(['country' => 'BE']);
        $routeB = PilgrimageRoute::factory()->create(['country' => 'BE']);

        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();

        // Route A : sort_order 1, 2
        $stageA1 = Stage::factory()->forRoute($routeA)->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
            'sort_order' => 1,
        ]);
        $stageA2 = Stage::factory()->forRoute($routeA)->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
            'sort_order' => 2,
        ]);

        // Route B : sort_order 1, 2 — mêmes valeurs, doivent rester après A
        $stageB1 = Stage::factory()->forRoute($routeB)->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
            'sort_order' => 1,
        ]);
        $stageB2 = Stage::factory()->forRoute($routeB)->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/pilgrimage/stages?per_page=100');
        $response->assertStatus(200);

        $data = $response->json('data');
        $returnedIds = array_column($data, 'id');

        // Toutes les étapes de routeA doivent apparaître avant toutes celles de routeB
        // (ou inversement selon l'ordre UUID), mais jamais entremêlées.
        $posA1 = array_search($stageA1->id, $returnedIds, true);
        $posA2 = array_search($stageA2->id, $returnedIds, true);
        $posB1 = array_search($stageB1->id, $returnedIds, true);
        $posB2 = array_search($stageB2->id, $returnedIds, true);

        // A1 doit être avant A2 (sort_order dans la même route)
        $this->assertLessThan($posA2, $posA1, 'A1 doit précéder A2 dans la même route');
        // B1 doit être avant B2
        $this->assertLessThan($posB2, $posB1, 'B1 doit précéder B2 dans la même route');

        // Les deux routes ne doivent pas s'entremêler :
        // soit tous les A avant tous les B, soit tous les B avant tous les A.
        $allABeforeB = ($posA1 < $posB1) && ($posA1 < $posB2) && ($posA2 < $posB1) && ($posA2 < $posB2);
        $allBBeforeA = ($posB1 < $posA1) && ($posB1 < $posA2) && ($posB2 < $posA1) && ($posB2 < $posA2);

        $this->assertTrue(
            $allABeforeB || $allBBeforeA,
            'Les étapes des deux routes ne doivent pas s\'entremêler — chaque route doit former un bloc contigu.',
        );
    }

    // ─── GET /api/pilgrimage/stages/{code} ───────────────────────────────────

    public function test_show_returns_stage_by_code(): void
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();
        Stage::factory()->create([
            'code' => 'BE-01',
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);

        $response = $this->getJson('/api/pilgrimage/stages/BE-01');

        $response->assertStatus(200)
            ->assertJsonPath('data.code', 'BE-01');
    }

    public function test_show_returns_404_for_unknown_code(): void
    {
        $this->getJson('/api/pilgrimage/stages/XX-99')
            ->assertStatus(404);
    }

    public function test_show_includes_start_and_end_waypoints(): void
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();
        $stage = Stage::factory()->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);

        $response = $this->getJson('/api/pilgrimage/stages/' . $stage->code . '?include=waypoints');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['start_waypoint', 'end_waypoint']]);
    }

    // ─── Localisation ────────────────────────────────────────────────────────

    public function test_name_localised_via_accept_language(): void
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();
        Stage::factory()->create([
            'code' => 'BE-I18N',
            'name' => ['fr' => 'Liège → Amay', 'nl' => 'Luik → Amay', 'de' => 'Lüttich → Amay'],
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);

        $response = $this->getJson(
            '/api/pilgrimage/stages/BE-I18N',
            ['Accept-Language' => 'de'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Lüttich → Amay');
    }
}
