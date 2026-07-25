<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature pour GET /api/pilgrimage/accommodations.
 * ULTREIA-23 + ULTREIA-2T.
 */
class AccommodationApiTest extends TestCase
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

    // ─── GET /api/pilgrimage/accommodations ──────────────────────────────────

    public function test_index_returns_200_with_json_structure(): void
    {
        $stage = $this->makeStage();
        Accommodation::factory()->forStage($stage)->create();

        $response = $this->getJson('/api/pilgrimage/accommodations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'stage_id', 'name', 'type', 'is_primary',
                        'has_shower', 'has_kitchen', 'has_wifi', 'stamps_credencial',
                        'bivouac_legal', 'verified_at', 'is_obsolete',
                    ],
                ],
            ]);
    }

    public function test_index_filter_by_stage_id(): void
    {
        $stage1 = $this->makeStage();
        $stage2 = $this->makeStage();

        Accommodation::factory()->forStage($stage1)->create();
        Accommodation::factory()->forStage($stage2)->create();

        $response = $this->getJson('/api/pilgrimage/accommodations?stage_id=' . $stage1->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($stage1->id, $response->json('data.0.stage_id'));
    }

    public function test_index_filter_by_type(): void
    {
        $stage = $this->makeStage();
        Accommodation::factory()->forStage($stage)->create(['type' => 'camping']);
        Accommodation::factory()->forStage($stage)->create(['type' => 'gite']);

        $response = $this->getJson('/api/pilgrimage/accommodations?type=camping');

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $accom) {
            $this->assertSame('camping', $accom['type']);
        }
    }

    public function test_index_filter_by_bivouac_legal(): void
    {
        $stage = $this->makeStage();
        Accommodation::factory()->forStage($stage)->create(['bivouac_legal' => true]);
        Accommodation::factory()->forStage($stage)->create(['bivouac_legal' => false]);

        $response = $this->getJson('/api/pilgrimage/accommodations?bivouac_legal=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $accom) {
            $this->assertTrue($accom['bivouac_legal']);
        }
    }

    public function test_index_primary_sorted_first(): void
    {
        $stage = $this->makeStage();
        Accommodation::factory()->forStage($stage)->secondary()->create();
        Accommodation::factory()->forStage($stage)->primary()->create();

        $response = $this->getJson('/api/pilgrimage/accommodations?stage_id=' . $stage->id);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertTrue($data[0]['is_primary']);
    }

    // ─── GET /api/pilgrimage/accommodations/{id} ─────────────────────────────

    public function test_show_returns_accommodation(): void
    {
        $stage = $this->makeStage();
        $accommodation = Accommodation::factory()->forStage($stage)->create([
            'name' => ['fr' => 'Gîte Test', 'nl' => 'Test Gîte', 'de' => 'Test Gîte'],
        ]);

        $response = $this->getJson('/api/pilgrimage/accommodations/' . $accommodation->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $accommodation->id)
            ->assertJsonPath('data.name', 'Gîte Test');
    }

    public function test_show_returns_404_for_unknown_id(): void
    {
        $this->getJson('/api/pilgrimage/accommodations/00000000-0000-0000-0000-000000000000')
            ->assertStatus(404);
    }

    // ─── RG-08 — is_obsolete ─────────────────────────────────────────────────

    public function test_is_obsolete_true_when_verified_at_null(): void
    {
        $stage = $this->makeStage();
        $accommodation = Accommodation::factory()->forStage($stage)->neverVerified()->create();

        $response = $this->getJson('/api/pilgrimage/accommodations/' . $accommodation->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_obsolete', true);
    }

    public function test_is_obsolete_true_when_verified_at_over_six_months(): void
    {
        $stage = $this->makeStage();
        $accommodation = Accommodation::factory()->forStage($stage)->obsolete()->create();

        $response = $this->getJson('/api/pilgrimage/accommodations/' . $accommodation->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_obsolete', true);
    }

    public function test_is_obsolete_false_when_recently_verified(): void
    {
        $stage = $this->makeStage();
        $accommodation = Accommodation::factory()->forStage($stage)->create([
            'verified_at' => now()->subMonths(2),
        ]);

        $response = $this->getJson('/api/pilgrimage/accommodations/' . $accommodation->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_obsolete', false);
    }

    // ─── Extension /stages/{code}?include=accommodations ────────────────────

    public function test_stage_show_includes_accommodations(): void
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();
        $stage = Stage::factory()->create([
            'code' => 'TEST-01',
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);
        Accommodation::factory()->forStage($stage)->create();
        Accommodation::factory()->forStage($stage)->secondary()->create();

        $response = $this->getJson('/api/pilgrimage/stages/TEST-01?include=accommodations');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['accommodations']])
            ->assertJsonCount(2, 'data.accommodations');
    }

    // ─── i18n ────────────────────────────────────────────────────────────────

    public function test_name_localised_via_accept_language(): void
    {
        $stage = $this->makeStage();
        $accommodation = Accommodation::factory()->forStage($stage)->create([
            'name' => ['fr' => 'Gîte Français', 'nl' => 'Nederlands Gîte', 'de' => 'Deutsch Gîte'],
        ]);

        $response = $this->getJson(
            '/api/pilgrimage/accommodations/' . $accommodation->id,
            ['Accept-Language' => 'nl'],
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nederlands Gîte');
    }
}
