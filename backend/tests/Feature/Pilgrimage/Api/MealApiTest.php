<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Modules\Pilgrimage\Models\Meal;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature pour GET /api/pilgrimage/meals.
 * ULTREIA-23 + ULTREIA-2T.
 */
class MealApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeStage(): Stage
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();

        return Stage::factory()->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);
    }

    // ─── GET /api/pilgrimage/meals ───────────────────────────────────────────

    public function test_index_returns_200_with_json_structure(): void
    {
        $stage = $this->makeStage();
        Meal::factory()->forStage($stage)->create();

        $response = $this->getJson('/api/pilgrimage/meals');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'stage_id', 'meal_type', 'name',
                        'meal_context', 'price_estimate_eur',
                    ],
                ],
            ]);
    }

    public function test_index_filter_by_stage_id(): void
    {
        $stage1 = $this->makeStage();
        $stage2 = $this->makeStage();

        Meal::factory()->forStage($stage1)->create();
        Meal::factory()->forStage($stage2)->create();

        $response = $this->getJson('/api/pilgrimage/meals?stage_id=' . $stage1->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($stage1->id, $response->json('data.0.stage_id'));
    }

    public function test_index_filter_by_meal_type(): void
    {
        $stage = $this->makeStage();
        Meal::factory()->forStage($stage)->create(['meal_type' => 'dinner']);
        Meal::factory()->forStage($stage)->create(['meal_type' => 'lunch']);

        $response = $this->getJson('/api/pilgrimage/meals?meal_type=dinner');

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $meal) {
            $this->assertSame('dinner', $meal['meal_type']);
        }
    }

    // ─── GET /api/pilgrimage/meals/{id} ─────────────────────────────────────

    public function test_show_returns_meal(): void
    {
        $stage = $this->makeStage();
        $meal = Meal::factory()->forStage($stage)->create([
            'name' => ['fr' => 'Flamiche dinantaise', 'nl' => 'Dinantse flamiche', 'de' => 'Dinantser Flamiche'],
        ]);

        $response = $this->getJson('/api/pilgrimage/meals/' . $meal->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $meal->id)
            ->assertJsonPath('data.name', 'Flamiche dinantaise');
    }

    public function test_show_returns_404_for_unknown_id(): void
    {
        $this->getJson('/api/pilgrimage/meals/00000000-0000-0000-0000-000000000000')
            ->assertStatus(404);
    }

    // ─── Extension /stages/{code}?include=meals ──────────────────────────────

    public function test_stage_show_includes_meals(): void
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();
        $stage = Stage::factory()->create([
            'code' => 'MEAL-01',
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);
        Meal::factory()->forStage($stage)->create(['meal_type' => 'dinner']);
        Meal::factory()->forStage($stage)->create(['meal_type' => 'lunch']);

        $response = $this->getJson('/api/pilgrimage/stages/MEAL-01?include=meals');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['meals']])
            ->assertJsonCount(2, 'data.meals');
    }

    // ─── i18n ────────────────────────────────────────────────────────────────

    public function test_name_localised_via_accept_language(): void
    {
        $stage = $this->makeStage();
        $meal = Meal::factory()->forStage($stage)->create([
            'name' => ['fr' => 'Cacasse française', 'nl' => 'Cacasse Nederlands', 'de' => 'Cacasse Deutsch'],
        ]);

        $response = $this->getJson(
            '/api/pilgrimage/meals/' . $meal->id,
            ['Accept-Language' => 'de'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Cacasse Deutsch');
    }

    // ─── Meal type label ─────────────────────────────────────────────────────

    public function test_meal_type_label_is_returned(): void
    {
        $stage = $this->makeStage();
        $meal = Meal::factory()->forStage($stage)->dinner()->create();

        $response = $this->getJson('/api/pilgrimage/meals/' . $meal->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.meal_type', 'dinner')
            ->assertJsonPath('data.meal_type_label', 'Dîner');
    }
}
