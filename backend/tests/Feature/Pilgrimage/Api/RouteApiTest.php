<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature pour GET /api/pilgrimage/routes.
 * ULTREIA-15 + ULTREIA-1T.
 */
class RouteApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── GET /api/pilgrimage/routes ──────────────────────────────────────────

    public function test_index_returns_200_with_json_structure(): void
    {
        PilgrimageRoute::factory()->count(3)->create();

        $response = $this->getJson('/api/pilgrimage/routes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'slug', 'name', 'country', 'total_distance_km', 'is_active'],
                ],
            ]);
    }

    public function test_index_returns_only_active_routes_by_default(): void
    {
        PilgrimageRoute::factory()->count(2)->create(['is_active' => true]);
        PilgrimageRoute::factory()->inactive()->create();

        $response = $this->getJson('/api/pilgrimage/routes');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_filter_by_country(): void
    {
        PilgrimageRoute::factory()->belgian()->count(2)->create();
        PilgrimageRoute::factory()->create(['country' => 'FR']);

        $response = $this->getJson('/api/pilgrimage/routes?country=BE');

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $route) {
            $this->assertSame('BE', $route['country']);
        }
    }

    public function test_index_is_paginated(): void
    {
        PilgrimageRoute::factory()->count(25)->create();

        $response = $this->getJson('/api/pilgrimage/routes');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['total', 'per_page', 'current_page']]);
    }

    // ─── GET /api/pilgrimage/routes/{slug} ───────────────────────────────────

    public function test_show_returns_route_by_slug(): void
    {
        $route = PilgrimageRoute::factory()->create(['slug' => 'via-mosana-test']);

        $response = $this->getJson('/api/pilgrimage/routes/via-mosana-test');

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'via-mosana-test')
            ->assertJsonPath('data.id', $route->id);
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/pilgrimage/routes/slug-inexistant')
            ->assertStatus(404);
    }

    public function test_show_includes_stages_when_requested(): void
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();
        $route = PilgrimageRoute::factory()->create();
        Stage::factory()->forRoute($route)->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);

        $response = $this->getJson('/api/pilgrimage/routes/' . $route->slug . '?include=stages');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['stages']]);
    }

    // ─── Localisation ────────────────────────────────────────────────────────

    public function test_name_returned_in_french_by_default(): void
    {
        PilgrimageRoute::factory()->create([
            'slug' => 'route-i18n-test',
            'name' => ['fr' => 'Voie Mosane', 'nl' => 'Moezelweg', 'de' => 'Maasweg'],
        ]);

        $response = $this->getJson('/api/pilgrimage/routes/route-i18n-test');

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Voie Mosane');
    }

    public function test_name_returned_in_dutch_via_accept_language(): void
    {
        PilgrimageRoute::factory()->create([
            'slug' => 'route-nl-test',
            'name' => ['fr' => 'Voie Mosane', 'nl' => 'Moezelweg', 'de' => 'Maasweg'],
        ]);

        $response = $this->getJson(
            '/api/pilgrimage/routes/route-nl-test',
            ['Accept-Language' => 'nl'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Moezelweg');
    }
}
