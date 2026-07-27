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
 * Tests — Corrections revue Themis (B-01, B-02, B-03, I-07) + sécurité P1-02.
 *
 * B-01 : GET /api/pilgrimage/trips retourne les trips du pèlerin courant
 * B-02 : invite_token exposé uniquement à l'organisateur
 * B-03 : removeMember interdit d'éjecter l'organisateur (422)
 * I-07 : addDeparture refuse un pilgrim_id hors du Trip (403)
 * P1-02 : addDeparture refuse qu'un participant crée une departure pour un autre membre
 */
class TripThemisFixTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pilgrim $pilgrim;

    private PilgrimageRoute $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'themis@example.com']);
        $this->pilgrim = Pilgrim::factory()->create(['user_id' => $this->user->id]);
        $this->route = PilgrimageRoute::factory()->create();
    }

    // ─── B-01 — GET /api/pilgrimage/trips ────────────────────────────────────

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/pilgrimage/trips')
            ->assertStatus(401);
    }

    public function test_index_returns_trips_where_pilgrim_is_organizer(): void
    {
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/pilgrimage/trips');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $trip->id);
    }

    public function test_index_returns_trips_where_pilgrim_is_participant(): void
    {
        $otherPilgrim = Pilgrim::factory()->create();
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $otherPilgrim->id,
        ]);
        $trip->members()->attach($otherPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/pilgrimage/trips');

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertContains($trip->id, $ids->toArray());
    }

    public function test_index_does_not_return_trips_where_pilgrim_is_not_member(): void
    {
        $otherPilgrim = Pilgrim::factory()->create();
        $foreignTrip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $otherPilgrim->id,
        ]);
        $foreignTrip->members()->attach($otherPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/pilgrimage/trips');

        $response->assertStatus(200);
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertNotContains($foreignTrip->id, $ids->toArray());
    }

    public function test_index_returns_empty_array_for_pilgrim_with_no_trips(): void
    {
        $this->actingAs($this->user, 'web')
            ->getJson('/api/pilgrimage/trips')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    // ─── B-02 — invite_token visible uniquement pour l'organisateur ───────────

    public function test_show_exposes_invite_token_to_organizer(): void
    {
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
            'invite_token' => 'secret-token-abc',
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson("/api/pilgrimage/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.invite_token', 'secret-token-abc')
            ->assertJsonPath('data.has_invite_token', true);
    }

    public function test_show_hides_invite_token_from_participant(): void
    {
        $organizerPilgrim = Pilgrim::factory()->create();
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $organizerPilgrim->id,
            'invite_token' => 'secret-token-def',
        ]);
        $trip->members()->attach($organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson("/api/pilgrimage/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.invite_token', null)
            ->assertJsonPath('data.has_invite_token', true);
    }

    public function test_show_hides_invite_token_from_observer(): void
    {
        $organizerPilgrim = Pilgrim::factory()->create();
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $organizerPilgrim->id,
            'invite_token' => 'secret-token-ghi',
        ]);
        $trip->members()->attach($organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'observer', 'joined_at' => now()]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson("/api/pilgrimage/trips/{$trip->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.invite_token', null)
            ->assertJsonPath('data.has_invite_token', true);
    }

    // ─── B-03 — removeMember interdit d'éjecter l'organisateur ───────────────

    public function test_remove_member_returns_422_when_targeting_organizer(): void
    {
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        // L'organisateur tente de s'auto-éjecter
        $response = $this->actingAs($this->user, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$trip->id}/members/{$this->pilgrim->id}");

        $response->assertStatus(422)
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'organisateur'));
    }

    public function test_remove_member_succeeds_for_participant(): void
    {
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $participantPilgrim = Pilgrim::factory()->create();
        $trip->members()->attach($participantPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        $this->actingAs($this->user, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$trip->id}/members/{$participantPilgrim->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('trip_members', [
            'trip_id' => $trip->id,
            'pilgrim_id' => $participantPilgrim->id,
        ]);
    }

    // ─── I-07 — addDeparture refuse un pilgrim_id non-membre ─────────────────

    public function test_add_departure_rejects_pilgrim_not_member_of_trip(): void
    {
        $startWp = Waypoint::factory()->create();
        $endWp = Waypoint::factory()->create();

        $stage1 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'I07-01',
            'day_number' => 1,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);
        $stage5 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'I07-05',
            'day_number' => 5,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);

        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        // Pèlerin totalement étranger au Trip
        $outsiderPilgrim = Pilgrim::factory()->create();

        $response = $this->actingAs($this->user, 'web')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/departures", [
                'pilgrim_id' => $outsiderPilgrim->id,
                'start_stage_id' => $stage1->id,
                'end_stage_id' => $stage5->id,
                'planned_start_date' => '2027-06-01',
            ]);

        $response->assertStatus(403);
    }

    public function test_add_departure_accepts_pilgrim_who_is_member(): void
    {
        $startWp = Waypoint::factory()->create();
        $endWp = Waypoint::factory()->create();

        $stage1 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'I07-M01',
            'day_number' => 1,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);
        $stage5 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'I07-M05',
            'day_number' => 5,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);

        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $memberPilgrim = Pilgrim::factory()->create();
        $trip->members()->attach($memberPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        $response = $this->actingAs($this->user, 'web')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/departures", [
                'pilgrim_id' => $memberPilgrim->id,
                'start_stage_id' => $stage1->id,
                'end_stage_id' => $stage5->id,
                'planned_start_date' => '2027-06-01',
            ]);

        $response->assertStatus(201);
    }

    // ─── P1-02 — IDOR : participant ne peut créer departure que pour lui-même ──

    public function test_participant_cannot_create_departure_for_another_member(): void
    {
        $startWp = Waypoint::factory()->create();
        $endWp = Waypoint::factory()->create();

        $stage1 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'P102-A01',
            'day_number' => 1,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);
        $stage3 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'P102-A03',
            'day_number' => 3,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);

        // Organisateur (autre utilisateur)
        $organizerPilgrim = Pilgrim::factory()->create();

        // Pèlerin courant = participant
        $participantUser = User::factory()->create(['email' => 'participant-p102@example.com']);
        $participantPilgrim = Pilgrim::factory()->create(['user_id' => $participantUser->id]);

        // Autre participant victime de l'IDOR
        $victimPilgrim = Pilgrim::factory()->create();

        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $organizerPilgrim->id,
        ]);
        $trip->members()->attach($organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $trip->members()->attach($participantPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);
        $trip->members()->attach($victimPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        // Le participant tente de créer une departure pour la victime → 403
        $response = $this->actingAs($participantUser, 'web')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/departures", [
                'pilgrim_id' => $victimPilgrim->id,
                'start_stage_id' => $stage1->id,
                'end_stage_id' => $stage3->id,
                'planned_start_date' => '2027-07-01',
            ]);

        $response->assertStatus(403);
    }

    public function test_participant_can_create_departure_for_themselves(): void
    {
        $startWp = Waypoint::factory()->create();
        $endWp = Waypoint::factory()->create();

        $stage1 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'P102-B01',
            'day_number' => 1,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);
        $stage3 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'P102-B03',
            'day_number' => 3,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);

        $organizerPilgrim = Pilgrim::factory()->create();

        $participantUser = User::factory()->create(['email' => 'participant-p102b@example.com']);
        $participantPilgrim = Pilgrim::factory()->create(['user_id' => $participantUser->id]);

        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $organizerPilgrim->id,
        ]);
        $trip->members()->attach($organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $trip->members()->attach($participantPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        // Le participant crée une departure pour lui-même → 201
        $response = $this->actingAs($participantUser, 'web')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/departures", [
                'pilgrim_id' => $participantPilgrim->id,
                'start_stage_id' => $stage1->id,
                'end_stage_id' => $stage3->id,
                'planned_start_date' => '2027-07-01',
            ]);

        $response->assertStatus(201);
    }

    public function test_organizer_can_create_departure_for_any_member(): void
    {
        $startWp = Waypoint::factory()->create();
        $endWp = Waypoint::factory()->create();

        $stage1 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'P102-C01',
            'day_number' => 1,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);
        $stage2 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'P102-C02',
            'day_number' => 2,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);

        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $memberPilgrim = Pilgrim::factory()->create();
        $trip->members()->attach($memberPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        // L'organisateur crée une departure pour le membre → 201
        $response = $this->actingAs($this->user, 'web')
            ->postJson("/api/pilgrimage/trips/{$trip->id}/departures", [
                'pilgrim_id' => $memberPilgrim->id,
                'start_stage_id' => $stage1->id,
                'end_stage_id' => $stage2->id,
                'planned_start_date' => '2027-07-01',
            ]);

        $response->assertStatus(201);
    }
}
