<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Trip;

use App\Models\User;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests RGPD-R02 — Self-leave Trip.
 *
 * DELETE /api/pilgrimage/trips/{id}/membership
 *
 * Cas couverts :
 *   1. Participant self-leave journal_action=keep  → 200, entrées membres restent visibles
 *   2. Participant self-leave journal_action=remove → 200, ses entrées passent en private
 *   3. Observer self-leave                          → 200
 *   4. Organizer self-leave                         → 422 (message explicite)
 *   5. Non-membre                                   → 403
 *   6. Non-authentifié                              → 401
 */
class TripSelfLeaveTest extends TestCase
{
    use RefreshDatabase;

    private PilgrimageRoute $route;

    private User $organizerUser;

    private Pilgrim $organizerPilgrim;

    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->route = PilgrimageRoute::factory()->create();

        $this->organizerUser = User::factory()->create(['email' => 'organizer-sl@example.com']);
        $this->organizerPilgrim = Pilgrim::factory()->create(['user_id' => $this->organizerUser->id]);

        $this->trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->organizerPilgrim->id,
        ]);
        $this->trip->members()->attach($this->organizerPilgrim->id, [
            'role' => 'organizer',
            'joined_at' => now(),
        ]);
    }

    // ─── Cas 1 : participant self-leave keep ─────────────────────────────────

    public function test_participant_self_leave_keep_returns_200_and_entries_remain_visible(): void
    {
        $participantUser = User::factory()->create(['email' => 'participant-sl-keep@example.com']);
        $participantPilgrim = Pilgrim::factory()->create(['user_id' => $participantUser->id]);

        $this->trip->members()->attach($participantPilgrim->id, [
            'role' => 'participant',
            'joined_at' => now(),
        ]);

        // Entrée journal du participant (visible membres)
        $entry = JournalEntry::factory()->members()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $participantPilgrim->id,
        ]);

        $response = $this->actingAs($participantUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/membership", [
                'journal_action' => 'keep',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'quitté'));

        // Membership supprimé
        $this->assertDatabaseMissing('trip_members', [
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $participantPilgrim->id,
        ]);

        // Entrée reste en visibilité members (non masquée)
        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'visibility' => JournalVisibility::Members->value,
        ]);
    }

    // ─── Cas 2 : participant self-leave remove ───────────────────────────────

    public function test_participant_self_leave_remove_masks_entries_as_private(): void
    {
        $participantUser = User::factory()->create(['email' => 'participant-sl-remove@example.com']);
        $participantPilgrim = Pilgrim::factory()->create(['user_id' => $participantUser->id]);

        $this->trip->members()->attach($participantPilgrim->id, [
            'role' => 'participant',
            'joined_at' => now(),
        ]);

        // Entrée journal visible membres
        $entry = JournalEntry::factory()->members()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $participantPilgrim->id,
        ]);

        // Entrée déjà private → ne doit pas être modifiée (idempotence)
        $alreadyPrivate = JournalEntry::factory()->private()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $participantPilgrim->id,
        ]);

        $response = $this->actingAs($participantUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/membership", [
                'journal_action' => 'remove',
            ]);

        $response->assertStatus(200);

        // Membership supprimé
        $this->assertDatabaseMissing('trip_members', [
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $participantPilgrim->id,
        ]);

        // Entrée members → private
        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'visibility' => JournalVisibility::Private->value,
        ]);

        // Entrée déjà private → inchangée
        $this->assertDatabaseHas('journal_entries', [
            'id' => $alreadyPrivate->id,
            'visibility' => JournalVisibility::Private->value,
        ]);
    }

    // ─── Cas 3 : observer self-leave ─────────────────────────────────────────

    public function test_observer_can_self_leave(): void
    {
        $observerUser = User::factory()->create(['email' => 'observer-sl@example.com']);
        $observerPilgrim = Pilgrim::factory()->create(['user_id' => $observerUser->id]);

        $this->trip->members()->attach($observerPilgrim->id, [
            'role' => 'observer',
            'joined_at' => now(),
        ]);

        $this->actingAs($observerUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/membership")
            ->assertStatus(200);

        $this->assertDatabaseMissing('trip_members', [
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $observerPilgrim->id,
        ]);
    }

    // ─── Cas 4 : organizer self-leave → 422 ──────────────────────────────────

    public function test_organizer_self_leave_returns_422(): void
    {
        $response = $this->actingAs($this->organizerUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/membership", [
                'journal_action' => 'keep',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'organisateur'));

        // L'organizer est toujours membre
        $this->assertDatabaseHas('trip_members', [
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $this->organizerPilgrim->id,
        ]);
    }

    // ─── Cas 5 : non-membre → 403 ────────────────────────────────────────────

    public function test_non_member_self_leave_returns_403(): void
    {
        $outsiderUser = User::factory()->create(['email' => 'outsider-sl@example.com']);
        Pilgrim::factory()->create(['user_id' => $outsiderUser->id]);

        $this->actingAs($outsiderUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/membership")
            ->assertStatus(403);
    }

    // ─── Cas 6 : non-authentifié → 401 ───────────────────────────────────────

    public function test_unauthenticated_self_leave_returns_401(): void
    {
        $this->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/membership")
            ->assertStatus(401);
    }
}
