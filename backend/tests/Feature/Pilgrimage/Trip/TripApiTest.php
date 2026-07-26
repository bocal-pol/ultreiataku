<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Trip;

use App\Models\User;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature ULTREIA-35 — API REST Trips.
 */
class TripApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pilgrim $pilgrim;

    private PilgrimageRoute $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'test@example.com']);
        $this->pilgrim = Pilgrim::factory()->create(['user_id' => $this->user->id]);
        $this->route = PilgrimageRoute::factory()->create();
    }

    // ─── POST /api/pilgrimage/trips ──────────────────────────────────────────

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/pilgrimage/trips', ['name' => 'Test', 'route_id' => $this->route->id])
            ->assertStatus(401);
    }

    public function test_store_creates_trip_and_adds_organizer(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/trips', [
                'route_id' => $this->route->id,
                'name' => 'Belgique Mai 2027',
                'configuration' => 'solo',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Belgique Mai 2027')
            ->assertJsonPath('data.organizer_id', $this->pilgrim->id);

        // L'organisateur est ajouté automatiquement comme membre
        $tripId = $response->json('data.id');
        $this->assertDatabaseHas('trip_members', [
            'trip_id' => $tripId,
            'pilgrim_id' => $this->pilgrim->id,
            'role' => 'organizer',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/trips', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['route_id', 'name']);
    }

    public function test_store_validates_route_exists(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/trips', [
                'route_id' => '00000000-0000-0000-0000-000000000000',
                'name' => 'Test',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['route_id']);
    }

    // ─── GET /api/pilgrimage/trips/{id} ─────────────────────────────────────

    public function test_show_returns_trip_for_member(): void
    {
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $this->actingAs($this->user, 'api')
            ->getJson("/api/pilgrimage/trips/{$trip->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $trip->id);
    }

    public function test_show_denies_non_member(): void
    {
        $otherPilgrim = Pilgrim::factory()->create();
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $otherPilgrim->id,
        ]);
        $trip->members()->attach($otherPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $this->actingAs($this->user, 'api')
            ->getJson("/api/pilgrimage/trips/{$trip->id}")
            ->assertStatus(403);
    }

    // ─── POST /api/pilgrimage/trips/{id}/members ─────────────────────────────

    public function test_add_member_by_organizer(): void
    {
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $newPilgrim = Pilgrim::factory()->create();

        $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/members", [
                'pilgrim_id' => $newPilgrim->id,
                'role' => 'participant',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('trip_members', [
            'trip_id' => $trip->id,
            'pilgrim_id' => $newPilgrim->id,
            'role' => 'participant',
        ]);
    }

    public function test_add_member_denied_for_participant(): void
    {
        $organizerPilgrim = Pilgrim::factory()->create();
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $organizerPilgrim->id,
        ]);
        $trip->members()->attach($organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        $newPilgrim = Pilgrim::factory()->create();

        $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/members", [
                'pilgrim_id' => $newPilgrim->id,
                'role' => 'participant',
            ])
            ->assertStatus(403);
    }

    public function test_add_member_conflict_if_already_member(): void
    {
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/members", [
                'pilgrim_id' => $this->pilgrim->id,
                'role' => 'participant',
            ])
            ->assertStatus(409);
    }

    // ─── POST /api/pilgrimage/trips/{id}/departures ──────────────────────────

    public function test_add_departure_by_participant(): void
    {
        $startWp = Waypoint::factory()->create();
        $endWp = Waypoint::factory()->create();

        $stage1 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'TS-01',
            'day_number' => 1,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);
        $stage12 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'TS-12',
            'day_number' => 12,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);

        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/departures", [
                'pilgrim_id' => $this->pilgrim->id,
                'start_stage_id' => $stage1->id,
                'end_stage_id' => $stage12->id,
                'planned_start_date' => '2027-05-10',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('departures', [
            'trip_id' => $trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'start_stage_id' => $stage1->id,
        ]);
    }

    public function test_add_departure_denied_for_observer(): void
    {
        $startWp = Waypoint::factory()->create();
        $endWp = Waypoint::factory()->create();
        $stage1 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'TS-01B',
            'day_number' => 1,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);
        $stage12 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'TS-12B',
            'day_number' => 12,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);

        $organizerPilgrim = Pilgrim::factory()->create();
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $organizerPilgrim->id,
        ]);
        $trip->members()->attach($organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'observer', 'joined_at' => now()]);

        $this->actingAs($this->user, 'api')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/departures", [
                'pilgrim_id' => $this->pilgrim->id,
                'start_stage_id' => $stage1->id,
                'end_stage_id' => $stage12->id,
                'planned_start_date' => '2027-05-10',
            ])
            ->assertStatus(403);
    }
}
