<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Trip;

use App\Models\User;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests ULTREIA-32 — Invitation flow (génération, révocation, join).
 * RG-07 — token UUID v4 unique, révocable, à usage multiple.
 */
class TripInvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $organizerUser;

    private Pilgrim $organizerPilgrim;

    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizerUser = User::factory()->create();
        $this->organizerPilgrim = Pilgrim::factory()->create(['user_id' => $this->organizerUser->id]);
        $route = PilgrimageRoute::factory()->create();
        $this->trip = Trip::factory()->create([
            'route_id' => $route->id,
            'organizer_id' => $this->organizerPilgrim->id,
        ]);
        $this->trip->members()->attach($this->organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
    }

    // ─── Génération de token ──────────────────────────────────────────────────

    public function test_organizer_can_generate_invite_token(): void
    {
        $response = $this->actingAs($this->organizerUser, 'api')
            ->postJson("/api/pilgrimage/trips/{$this->trip->id}/invite-token");

        $response->assertStatus(200)
            ->assertJsonStructure(['invite_token']);

        $token = $response->json('invite_token');
        $this->assertNotEmpty($token);

        $this->assertDatabaseHas('trips', [
            'id' => $this->trip->id,
            'invite_token' => $token,
        ]);
    }

    public function test_participant_cannot_generate_invite_token(): void
    {
        $participantUser = User::factory()->create();
        $participantPilgrim = Pilgrim::factory()->create(['user_id' => $participantUser->id]);
        $this->trip->members()->attach($participantPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        $this->actingAs($participantUser, 'api')
            ->postJson("/api/pilgrimage/trips/{$this->trip->id}/invite-token")
            ->assertStatus(403);
    }

    // ─── Révocation ───────────────────────────────────────────────────────────

    public function test_organizer_can_revoke_invite_token(): void
    {
        $this->trip->update(['invite_token' => Str::uuid()->toString()]);

        $this->actingAs($this->organizerUser, 'api')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/invite-token")
            ->assertStatus(200);

        $this->assertDatabaseHas('trips', [
            'id' => $this->trip->id,
            'invite_token' => null,
        ]);
    }

    // ─── Join via token ───────────────────────────────────────────────────────

    public function test_pilgrim_can_join_trip_via_valid_token(): void
    {
        $token = Str::uuid()->toString();
        $this->trip->update(['invite_token' => $token]);

        $newUser = User::factory()->create();
        $newPilgrim = Pilgrim::factory()->create(['user_id' => $newUser->id]);

        $this->actingAs($newUser, 'api')
            ->postJson("/api/pilgrimage/trips/join/{$token}")
            ->assertStatus(200);

        $this->assertDatabaseHas('trip_members', [
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $newPilgrim->id,
            'role' => 'participant',
        ]);
    }

    public function test_join_with_invalid_token_returns_404(): void
    {
        $newUser = User::factory()->create();
        Pilgrim::factory()->create(['user_id' => $newUser->id]);

        $this->actingAs($newUser, 'api')
            ->postJson('/api/pilgrimage/trips/join/invalid-or-revoked-token')
            ->assertStatus(404);
    }

    public function test_join_revoked_token_returns_404(): void
    {
        // Token révoqué (null) → le Trip ne matche pas sur WHERE invite_token = null
        $this->trip->update(['invite_token' => null]);

        $newUser = User::factory()->create();
        Pilgrim::factory()->create(['user_id' => $newUser->id]);

        $this->actingAs($newUser, 'api')
            ->postJson('/api/pilgrimage/trips/join/revoked-token-xyz')
            ->assertStatus(404);
    }

    public function test_join_already_member_returns_409(): void
    {
        $token = Str::uuid()->toString();
        $this->trip->update(['invite_token' => $token]);

        // L'organisateur essaie de rejoindre son propre Trip
        $this->actingAs($this->organizerUser, 'api')
            ->postJson("/api/pilgrimage/trips/join/{$token}")
            ->assertStatus(409);
    }

    // ─── Token idempotent (usage multiple) ───────────────────────────────────

    public function test_same_token_can_be_used_by_multiple_pilgrims(): void
    {
        $token = Str::uuid()->toString();
        $this->trip->update(['invite_token' => $token]);

        $user1 = User::factory()->create();
        $pilgrim1 = Pilgrim::factory()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create();
        $pilgrim2 = Pilgrim::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1, 'api')
            ->postJson("/api/pilgrimage/trips/join/{$token}")
            ->assertStatus(200);

        $this->actingAs($user2, 'api')
            ->postJson("/api/pilgrimage/trips/join/{$token}")
            ->assertStatus(200);

        $this->assertDatabaseHas('trip_members', ['trip_id' => $this->trip->id, 'pilgrim_id' => $pilgrim1->id]);
        $this->assertDatabaseHas('trip_members', ['trip_id' => $this->trip->id, 'pilgrim_id' => $pilgrim2->id]);
    }
}
