<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Models\User;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ULTREIA-4T — Tests Feature API PackScenario / PackItem / ItemAssignment.
 */
class PackScenarioApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pilgrim $pilgrim;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->pilgrim = Pilgrim::factory()->create(['user_id' => $this->user->id]);
    }

    // ─── GET /api/pilgrimage/pilgrims/{id}/pack-scenarios ─────────────────────

    public function test_index_for_pilgrim_returns_own_scenarios(): void
    {
        PackScenario::factory()->count(2)->create(['pilgrim_id' => $this->pilgrim->id]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/pilgrimage/pilgrims/{$this->pilgrim->id}/pack-scenarios");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [
                '*' => ['id', 'pilgrim_id', 'name', 'configuration', 'season', 'base_weight_g', 'weight_indicator'],
            ]]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_for_pilgrim_returns_403_for_other_pilgrim(): void
    {
        $otherPilgrim = Pilgrim::factory()->create();

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/pilgrimage/pilgrims/{$otherPilgrim->id}/pack-scenarios");

        $response->assertStatus(403);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson("/api/pilgrimage/pilgrims/{$this->pilgrim->id}/pack-scenarios");

        $response->assertStatus(401);
    }

    // ─── GET /api/pilgrimage/pack-scenarios/{id} ──────────────────────────────

    public function test_show_returns_scenario_with_items(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);
        PackItem::factory()->count(3)->create(['pack_scenario_id' => $scenario->id]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/pilgrimage/pack-scenarios/{$scenario->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $scenario->id)
            ->assertJsonPath('data.pilgrim_id', $this->pilgrim->id)
            ->assertJsonStructure(['data' => ['id', 'name', 'base_weight_g', 'total_weight_g', 'weight_indicator', 'items']]);
    }

    public function test_show_returns_403_for_other_pilgrim_scenario(): void
    {
        $otherPilgrim = Pilgrim::factory()->create();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $otherPilgrim->id]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/pilgrimage/pack-scenarios/{$scenario->id}");

        $response->assertStatus(403);
    }

    public function test_show_returns_404_for_unknown_scenario(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/pilgrimage/pack-scenarios/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    // ─── POST /api/pilgrimage/pack-scenarios ──────────────────────────────────

    public function test_store_creates_scenario_for_current_pilgrim(): void
    {
        $payload = [
            'name' => 'Solo printemps',
            'target_base_weight_kg' => 8.5,
            'configuration' => 'solo',
            'season' => 'spring',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/pack-scenarios', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Solo printemps')
            ->assertJsonPath('data.pilgrim_id', $this->pilgrim->id)
            ->assertJsonPath('data.weight_indicator', 'green');

        $this->assertDatabaseHas('pack_scenarios', [
            'pilgrim_id' => $this->pilgrim->id,
            'name' => 'Solo printemps',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/pack-scenarios', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_configuration_enum(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/pack-scenarios', [
                'name' => 'Test',
                'configuration' => 'trio',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['configuration']);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/pilgrimage/pack-scenarios', ['name' => 'Test']);

        $response->assertStatus(401);
    }

    // ─── PUT /api/pilgrimage/pack-scenarios/{id} ──────────────────────────────

    public function test_update_modifies_own_scenario(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/pilgrimage/pack-scenarios/{$scenario->id}", [
                'name' => 'Nom mis à jour',
                'target_base_weight_kg' => 9.0,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Nom mis à jour');

        $this->assertDatabaseHas('pack_scenarios', ['id' => $scenario->id, 'name' => 'Nom mis à jour']);
    }

    public function test_update_returns_403_for_other_pilgrim_scenario(): void
    {
        $otherPilgrim = Pilgrim::factory()->create();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $otherPilgrim->id]);

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/pilgrimage/pack-scenarios/{$scenario->id}", [
                'name' => 'Tentative',
            ]);

        $response->assertStatus(403);
    }

    // ─── POST /api/pilgrimage/pack-scenarios/{id}/items ───────────────────────

    public function test_add_item_creates_pack_item(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        $payload = [
            'name' => 'Tente MSR Hubba NX 1',
            'category' => 'sleeping',
            'brand' => 'MSR',
            'model' => 'Hubba NX 1',
            'weight_g' => 1050,
            'is_consumable' => false,
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/pack-scenarios/{$scenario->id}/items", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Tente MSR Hubba NX 1')
            ->assertJsonPath('data.weight_g', 1050)
            ->assertJsonPath('data.category', 'sleeping');

        $this->assertDatabaseHas('pack_items', [
            'pack_scenario_id' => $scenario->id,
            'name' => 'Tente MSR Hubba NX 1',
            'weight_g' => 1050,
        ]);
    }

    public function test_add_item_validates_weight_greater_than_zero(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/pack-scenarios/{$scenario->id}/items", [
                'name' => 'Item invalide',
                'category' => 'misc',
                'weight_g' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['weight_g']);
    }

    public function test_add_item_validates_category_enum(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/pack-scenarios/{$scenario->id}/items", [
                'name' => 'Item invalide',
                'category' => 'invalid_category',
                'weight_g' => 100,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_add_item_returns_403_for_other_pilgrim_scenario(): void
    {
        $otherPilgrim = Pilgrim::factory()->create();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $otherPilgrim->id]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/pack-scenarios/{$scenario->id}/items", [
                'name' => 'Test',
                'category' => 'misc',
                'weight_g' => 100,
            ]);

        $response->assertStatus(403);
    }

    // ─── POST /api/pilgrimage/departures/{id}/assignments ─────────────────────

    private function makeDeparture(): Departure
    {
        $wp = Waypoint::factory()->create();
        $route = PilgrimageRoute::factory()->create();
        $trip = Trip::factory()->create(['organizer_id' => $this->pilgrim->id, 'route_id' => $route->id]);
        $trip->members()->attach($this->pilgrim->id, ['role' => TripMemberRole::Organizer->value, 'joined_at' => now()]);

        $stage = Stage::factory()->create([
            'route_id' => $route->id,
            'start_waypoint_id' => $wp->id,
            'end_waypoint_id' => $wp->id,
        ]);

        return Departure::factory()->create([
            'trip_id' => $trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'start_stage_id' => $stage->id,
            'end_stage_id' => $stage->id,
        ]);
    }

    public function test_add_assignment_creates_item_assignment(): void
    {
        $departure = $this->makeDeparture();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);
        $item = PackItem::factory()->create(['pack_scenario_id' => $scenario->id]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/departures/{$departure->id}/assignments", [
                'pack_item_id' => $item->id,
                'assigned_to_pilgrim_id' => $this->pilgrim->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.pack_item_id', $item->id)
            ->assertJsonPath('data.departure_id', $departure->id)
            ->assertJsonPath('data.assigned_to_pilgrim_id', $this->pilgrim->id);

        $this->assertDatabaseHas('item_assignments', [
            'departure_id' => $departure->id,
            'pack_item_id' => $item->id,
        ]);
    }

    public function test_add_assignment_validates_pack_item_exists(): void
    {
        $departure = $this->makeDeparture();

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/departures/{$departure->id}/assignments", [
                'pack_item_id' => '00000000-0000-0000-0000-000000000000',
                'assigned_to_pilgrim_id' => $this->pilgrim->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pack_item_id']);
    }

    // ─── RG-01 via API ────────────────────────────────────────────────────────

    public function test_scenario_returns_correct_weight_indicator_green(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);

        // 8000 g = 8 kg <= 8.5 kg → green
        PackItem::factory()->create([
            'pack_scenario_id' => $scenario->id,
            'weight_g' => 8000,
            'is_consumable' => false,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/pilgrimage/pack-scenarios/{$scenario->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.weight_indicator', 'green')
            ->assertJsonPath('data.base_weight_g', 8000);
    }

    public function test_scenario_returns_correct_weight_indicator_red(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);

        // 10000 g = 10 kg > 8.5 + 1.0 = 9.5 → red
        PackItem::factory()->create([
            'pack_scenario_id' => $scenario->id,
            'weight_g' => 10000,
            'is_consumable' => false,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson("/api/pilgrimage/pack-scenarios/{$scenario->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.weight_indicator', 'red');
    }
}
