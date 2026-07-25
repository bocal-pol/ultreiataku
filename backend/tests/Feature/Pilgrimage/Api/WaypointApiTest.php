<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature pour GET /api/pilgrimage/waypoints.
 * ULTREIA-15 + ULTREIA-1T.
 */
class WaypointApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── GET /api/pilgrimage/waypoints ───────────────────────────────────────

    public function test_index_returns_200_with_json_structure(): void
    {
        Waypoint::factory()->count(3)->create();

        $response = $this->getJson('/api/pilgrimage/waypoints');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'slug', 'name', 'type', 'latitude', 'longitude'],
                ],
            ]);
    }

    public function test_index_returns_only_active_waypoints_by_default(): void
    {
        Waypoint::factory()->count(3)->create(['is_active' => true]);
        Waypoint::factory()->inactive()->create();

        $response = $this->getJson('/api/pilgrimage/waypoints');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_index_filter_by_type_city(): void
    {
        Waypoint::factory()->city()->count(2)->create();
        Waypoint::factory()->poi()->create();

        $response = $this->getJson('/api/pilgrimage/waypoints?type=city');

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $wp) {
            $this->assertSame('city', $wp['type']);
        }
    }

    public function test_index_filter_by_type_poi(): void
    {
        Waypoint::factory()->city()->create();
        Waypoint::factory()->poi()->count(2)->create();

        $response = $this->getJson('/api/pilgrimage/waypoints?type=poi');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_index_is_paginated(): void
    {
        Waypoint::factory()->count(30)->create();

        $response = $this->getJson('/api/pilgrimage/waypoints');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['total', 'per_page', 'current_page']]);
    }

    // ─── GET /api/pilgrimage/waypoints/{slug} ────────────────────────────────

    public function test_show_returns_waypoint_by_slug(): void
    {
        Waypoint::factory()->create(['slug' => 'liege-cathedrale']);

        $response = $this->getJson('/api/pilgrimage/waypoints/liege-cathedrale');

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'liege-cathedrale');
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/pilgrimage/waypoints/inexistant')
            ->assertStatus(404);
    }

    public function test_show_contains_coordinates(): void
    {
        Waypoint::factory()->create([
            'slug' => 'test-coords',
            'latitude' => 50.6458,
            'longitude' => 5.5734,
        ]);

        $response = $this->getJson('/api/pilgrimage/waypoints/test-coords');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEqualsWithDelta(50.6458, (float) $data['latitude'], 0.0001);
        $this->assertEqualsWithDelta(5.5734, (float) $data['longitude'], 0.0001);
    }

    // ─── Localisation ────────────────────────────────────────────────────────

    public function test_name_localised_via_accept_language_nl(): void
    {
        Waypoint::factory()->create([
            'slug' => 'huy-test',
            'name' => ['fr' => 'Huy', 'nl' => 'Hoei', 'de' => 'Huy'],
        ]);

        $response = $this->getJson(
            '/api/pilgrimage/waypoints/huy-test',
            ['Accept-Language' => 'nl'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Hoei');
    }

    public function test_name_fallback_to_french_for_unknown_locale(): void
    {
        Waypoint::factory()->create([
            'slug' => 'namur-test',
            'name' => ['fr' => 'Namur', 'nl' => 'Namen', 'de' => 'Namur'],
        ]);

        $response = $this->getJson(
            '/api/pilgrimage/waypoints/namur-test',
            ['Accept-Language' => 'ja'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Namur');
    }
}
