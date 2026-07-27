<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Rgpd;

use App\Models\User;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RGPD-U03 — removeMember avec choix journal_action.
 *
 * Décision produit (2026-07-27) :
 *   journal_action=keep   → les entrées du membre gardent leur visibilité courante.
 *   journal_action=remove → les entrées non-private du membre passent à "private"
 *                           (elles ne sont pas supprimées, juste masquées au Trip).
 *
 * Les deux branches sont testées sur removeMember (organisateur éjecte un membre).
 * La valeur par défaut est "keep" (aucun paramètre envoyé).
 */
class RemoveMemberRgpdTest extends TestCase
{
    use RefreshDatabase;

    private User $organizerUser;

    private Pilgrim $organizerPilgrim;

    private User $memberUser;

    private Pilgrim $memberPilgrim;

    private Trip $trip;

    private PilgrimageRoute $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->route = PilgrimageRoute::factory()->create();

        $this->organizerUser    = User::factory()->create();
        $this->organizerPilgrim = Pilgrim::factory()->create(['user_id' => $this->organizerUser->id]);

        $this->memberUser    = User::factory()->create();
        $this->memberPilgrim = Pilgrim::factory()->create(['user_id' => $this->memberUser->id]);

        $this->trip = Trip::factory()->create([
            'route_id'     => $this->route->id,
            'organizer_id' => $this->organizerPilgrim->id,
        ]);

        $this->trip->members()->attach($this->organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $this->trip->members()->attach($this->memberPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);
    }

    // ─── journal_action=keep (défaut) ─────────────────────────────────────────

    public function test_remove_member_keep_preserves_entry_visibility(): void
    {
        $entry = JournalEntry::factory()->create([
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
            'visibility' => JournalVisibility::Members->value,
            'entry_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($this->organizerUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/members/{$this->memberPilgrim->id}", [
                'journal_action' => 'keep',
            ])
            ->assertOk();

        // Visibilité inchangée
        $this->assertDatabaseHas('journal_entries', [
            'id'         => $entry->id,
            'visibility' => JournalVisibility::Members->value,
        ]);

        // Membre retiré du pivot
        $this->assertDatabaseMissing('trip_members', [
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
        ]);
    }

    public function test_remove_member_default_is_keep(): void
    {
        $entry = JournalEntry::factory()->create([
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
            'visibility' => JournalVisibility::Public->value,
            'entry_date' => now()->format('Y-m-d'),
        ]);

        // Aucun paramètre journal_action envoyé → défaut keep
        $this->actingAs($this->organizerUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/members/{$this->memberPilgrim->id}")
            ->assertOk();

        $this->assertDatabaseHas('journal_entries', [
            'id'         => $entry->id,
            'visibility' => JournalVisibility::Public->value,
        ]);
    }

    // ─── journal_action=remove ────────────────────────────────────────────────

    public function test_remove_member_remove_masks_non_private_entries(): void
    {
        $membersEntry = JournalEntry::factory()->create([
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
            'visibility' => JournalVisibility::Members->value,
            'entry_date' => now()->format('Y-m-d'),
        ]);
        $publicEntry = JournalEntry::factory()->create([
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
            'visibility' => JournalVisibility::Public->value,
            'entry_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->actingAs($this->organizerUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/members/{$this->memberPilgrim->id}", [
                'journal_action' => 'remove',
            ])
            ->assertOk();

        // Les entrées members et public passent à private
        $this->assertDatabaseHas('journal_entries', [
            'id'         => $membersEntry->id,
            'visibility' => JournalVisibility::Private->value,
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'id'         => $publicEntry->id,
            'visibility' => JournalVisibility::Private->value,
        ]);

        // Membre retiré
        $this->assertDatabaseMissing('trip_members', [
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
        ]);
    }

    public function test_remove_member_remove_does_not_change_already_private_entries(): void
    {
        $privateEntry = JournalEntry::factory()->create([
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
            'visibility' => JournalVisibility::Private->value,
            'entry_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($this->organizerUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/members/{$this->memberPilgrim->id}", [
                'journal_action' => 'remove',
            ])
            ->assertOk();

        // L'entrée déjà private est inchangée
        $this->assertDatabaseHas('journal_entries', [
            'id'         => $privateEntry->id,
            'visibility' => JournalVisibility::Private->value,
        ]);
    }

    public function test_remove_member_remove_only_affects_this_trip_entries(): void
    {
        // Second trip avec une entrée du même pèlerin
        $otherTrip = Trip::factory()->create([
            'route_id'     => $this->route->id,
            'organizer_id' => $this->organizerPilgrim->id,
        ]);
        $otherTrip->members()->attach($this->organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $otherTrip->members()->attach($this->memberPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);

        $otherTripEntry = JournalEntry::factory()->create([
            'trip_id'    => $otherTrip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
            'visibility' => JournalVisibility::Members->value,
            'entry_date' => now()->format('Y-m-d'),
        ]);

        $thisEntry = JournalEntry::factory()->create([
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->memberPilgrim->id,
            'visibility' => JournalVisibility::Members->value,
            'entry_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($this->organizerUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/members/{$this->memberPilgrim->id}", [
                'journal_action' => 'remove',
            ])
            ->assertOk();

        // L'entrée de l'autre trip est inchangée
        $this->assertDatabaseHas('journal_entries', [
            'id'         => $otherTripEntry->id,
            'visibility' => JournalVisibility::Members->value,
        ]);

        // L'entrée de ce trip est passée à private
        $this->assertDatabaseHas('journal_entries', [
            'id'         => $thisEntry->id,
            'visibility' => JournalVisibility::Private->value,
        ]);
    }

    public function test_remove_member_rejects_invalid_journal_action(): void
    {
        $this->actingAs($this->organizerUser, 'web')
            ->deleteJson("/api/pilgrimage/trips/{$this->trip->id}/members/{$this->memberPilgrim->id}", [
                'journal_action' => 'destroy',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['journal_action']);
    }
}
