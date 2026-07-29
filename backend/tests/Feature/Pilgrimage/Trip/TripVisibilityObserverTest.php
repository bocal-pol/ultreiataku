<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Trip;

use App\Models\User;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use App\Modules\Pilgrimage\Models\Waypoint;
use App\Modules\Pilgrimage\Policies\JournalEntryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * ULTREIA-VIS-01 — Visibilité stricte du Trip + rôle observer + lien de partage.
 *
 * Matrice testée :
 *   - Non-membre → 403 sur GET /trips/{id} (privacy par défaut)
 *   - Observer → 200 sur GET /trips/{id} (lecture seule)
 *   - Observer → 403 sur POST /trips/{id}/departures (écriture interdite)
 *   - join-observer/{token} → attache avec rôle observer
 *   - JournalEntryPolicy::viewAny(User) → signature 1 arg (fix admin P0)
 */
class TripVisibilityObserverTest extends TestCase
{
    use RefreshDatabase;

    private User $organizerUser;

    private Pilgrim $organizerPilgrim;

    private PilgrimageRoute $route;

    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizerUser = User::factory()->create();
        $this->organizerPilgrim = Pilgrim::factory()->create(['user_id' => $this->organizerUser->id]);
        $this->route = PilgrimageRoute::factory()->create();

        $this->trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->organizerPilgrim->id,
            'is_public' => false,
        ]);
        $this->trip->members()->attach($this->organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
    }

    // ─── Visibilité : non-membre → 403 ────────────────────────────────────────

    public function test_non_member_cannot_view_trip(): void
    {
        $nonMemberUser = User::factory()->create();
        Pilgrim::factory()->create(['user_id' => $nonMemberUser->id]);

        $this->actingAs($nonMemberUser, 'web')
            ->getJson("/api/pilgrimage/trips/{$this->trip->id}")
            ->assertStatus(403);
    }

    public function test_unauthenticated_cannot_view_trip(): void
    {
        $this->getJson("/api/pilgrimage/trips/{$this->trip->id}")
            ->assertStatus(401);
    }

    // ─── Observer : lecture seule autorisée ───────────────────────────────────

    public function test_observer_can_view_trip(): void
    {
        $observerUser = User::factory()->create();
        $observerPilgrim = Pilgrim::factory()->create(['user_id' => $observerUser->id]);
        $this->trip->members()->attach($observerPilgrim->id, ['role' => 'observer', 'joined_at' => now()]);

        $this->actingAs($observerUser, 'web')
            ->getJson("/api/pilgrimage/trips/{$this->trip->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $this->trip->id);
    }

    // ─── Observer : écriture interdite ────────────────────────────────────────

    public function test_observer_cannot_create_departure(): void
    {
        $startWp = Waypoint::factory()->create();
        $endWp = Waypoint::factory()->create();
        $stage1 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'VIS-OBS-01',
            'day_number' => 1,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);
        $stage2 = Stage::factory()->forRoute($this->route)->create([
            'code' => 'VIS-OBS-02',
            'day_number' => 2,
            'start_waypoint_id' => $startWp->id,
            'end_waypoint_id' => $endWp->id,
        ]);

        $observerUser = User::factory()->create();
        $observerPilgrim = Pilgrim::factory()->create(['user_id' => $observerUser->id]);
        $this->trip->members()->attach($observerPilgrim->id, ['role' => 'observer', 'joined_at' => now()]);

        $this->actingAs($observerUser, 'web')
            ->postJson("/api/pilgrimage/trips/{$this->trip->id}/departures", [
                'pilgrim_id' => $observerPilgrim->id,
                'start_stage_id' => $stage1->id,
                'end_stage_id' => $stage2->id,
                'planned_start_date' => '2027-06-01',
            ])
            ->assertStatus(403);
    }

    public function test_observer_cannot_create_journal_entry(): void
    {
        $observerUser = User::factory()->create();
        $observerPilgrim = Pilgrim::factory()->create(['user_id' => $observerUser->id]);
        $this->trip->members()->attach($observerPilgrim->id, ['role' => 'observer', 'joined_at' => now()]);

        $this->actingAs($observerUser, 'web')
            ->postJson('/api/pilgrimage/journal/entries', [
                'trip_id' => $this->trip->id,
                'entry_date' => '2027-06-01',
                'visibility' => 'public',
            ])
            ->assertStatus(403);
    }

    // ─── Lien de partage observer : join-observer/{token} ─────────────────────

    public function test_join_observer_endpoint_attaches_as_observer(): void
    {
        $token = Str::uuid()->toString();
        $this->trip->update(['invite_token' => $token]);

        $newUser = User::factory()->create();
        $newPilgrim = Pilgrim::factory()->create(['user_id' => $newUser->id]);

        $this->actingAs($newUser, 'web')
            ->postJson("/api/pilgrimage/trips/join-observer/{$token}")
            ->assertStatus(200)
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'lecture seule'));

        $this->assertDatabaseHas('trip_members', [
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $newPilgrim->id,
            'role' => 'observer',
        ]);
    }

    public function test_join_observer_with_invalid_token_returns_404(): void
    {
        $newUser = User::factory()->create();
        Pilgrim::factory()->create(['user_id' => $newUser->id]);

        $this->actingAs($newUser, 'web')
            ->postJson('/api/pilgrimage/trips/join-observer/invalid-token-xyz')
            ->assertStatus(404);
    }

    public function test_join_observer_already_member_returns_409(): void
    {
        $token = Str::uuid()->toString();
        $this->trip->update(['invite_token' => $token]);

        // L'organisateur tente de rejoindre son propre Trip en observer
        $this->actingAs($this->organizerUser, 'web')
            ->postJson("/api/pilgrimage/trips/join-observer/{$token}")
            ->assertStatus(409);
    }

    // ─── JournalEntryPolicy::viewAny — fix signature admin P0 ────────────────

    public function test_journal_entry_policy_view_any_accepts_single_user_argument(): void
    {
        // Vérifie que viewAny(User $user) n'a qu'un seul paramètre obligatoire.
        // Un 2ème argument causait un ArgumentCountError dans Filament (admin/500).
        // Note : deux instances séparées pour éviter le cache per-instance de ResolvesCurrentPilgrim.

        $userWithPilgrim = User::factory()->create();
        Pilgrim::factory()->create(['user_id' => $userWithPilgrim->id]);
        $this->assertTrue((new JournalEntryPolicy)->viewAny($userWithPilgrim));

        $userWithoutPilgrim = User::factory()->create();
        $this->assertFalse((new JournalEntryPolicy)->viewAny($userWithoutPilgrim));
    }

    // ─── Observer voit les entrées journal publiques ───────────────────────────

    public function test_observer_can_view_public_journal_entry_on_public_trip(): void
    {
        $publicTrip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->organizerPilgrim->id,
            'is_public' => true,
        ]);
        $publicTrip->members()->attach($this->organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $observerUser = User::factory()->create();
        $observerPilgrim = Pilgrim::factory()->create(['user_id' => $observerUser->id]);
        $publicTrip->members()->attach($observerPilgrim->id, ['role' => 'observer', 'joined_at' => now()]);

        $entry = JournalEntry::factory()->create([
            'trip_id' => $publicTrip->id,
            'pilgrim_id' => $this->organizerPilgrim->id,
            'visibility' => 'public',
        ]);

        $this->actingAs($observerUser, 'web')
            ->getJson("/api/pilgrimage/journal/entries/{$entry->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $entry->id);
    }

    public function test_observer_cannot_view_members_only_journal_entry(): void
    {
        $observerUser = User::factory()->create();
        $observerPilgrim = Pilgrim::factory()->create(['user_id' => $observerUser->id]);
        $this->trip->members()->attach($observerPilgrim->id, ['role' => 'observer', 'joined_at' => now()]);

        $entry = JournalEntry::factory()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $this->organizerPilgrim->id,
            'visibility' => 'members',
        ]);

        $this->actingAs($observerUser, 'web')
            ->getJson("/api/pilgrimage/journal/entries/{$entry->id}")
            ->assertStatus(403);
    }
}
