<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Rgpd;

use App\Models\User;
use App\Modules\Pilgrimage\Jobs\PurgePilgrimAssetsJob;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * RGPD-U01 — Droits Art. 15/17/20.
 *
 * Tests :
 *   export (GET /api/pilgrimage/me/export) :
 *     - contient toutes les données du pèlerin courant
 *     - ne contient rien des données d'un autre pèlerin
 *
 *   destroy (DELETE /api/pilgrimage/me) :
 *     - supprime le Pilgrim et ses données liées
 *     - dispatche PurgePilgrimAssetsJob
 *     - refuse si le pèlerin est organisateur d'un Trip actif
 *     - autorise la suppression si les trips organisés sont completed/cancelled
 *
 * Note sur le comportement de suppression :
 *   Le forceDelete() du Pilgrim déclenche ON DELETE CASCADE sur les FK DB
 *   (journal_entries, pack_scenarios, departures → pilgrims). Ces rows sont
 *   donc supprimées PHYSIQUEMENT, pas soft-deleted. Les tests utilisent
 *   assertDatabaseMissing pour vérifier leur absence.
 *   La purge MinIO est gérée par PurgePilgrimAssetsJob avec les paths
 *   collectés AVANT la suppression DB.
 */
class MeRgpdTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pilgrim $pilgrim;

    private PilgrimageRoute $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->pilgrim = Pilgrim::factory()->create(['user_id' => $this->user->id]);
        $this->route = PilgrimageRoute::factory()->create();
    }

    // ─── Export — Art. 20 ──────────────────────────────────────────────────────

    public function test_export_requires_authentication(): void
    {
        $this->getJson('/api/pilgrimage/me/export')->assertUnauthorized();
    }

    public function test_export_returns_200_with_pilgrim_data(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/pilgrimage/me/export');

        $response->assertOk()
            ->assertJsonPath('pilgrim.id', $this->pilgrim->id)
            ->assertJsonStructure([
                'export_date',
                'pilgrim' => ['id', 'display_name', 'preferred_locale', 'configuration', 'created_at'],
                'trips',
                'departures',
                'pack_scenarios',
                'journal_entries',
            ]);
    }

    public function test_export_contains_pilgrim_trips_and_entries(): void
    {
        // Créer un Trip dont le pèlerin est membre
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        // Créer une entrée journal
        JournalEntry::factory()->create([
            'trip_id' => $trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'entry_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/pilgrimage/me/export');

        $response->assertOk();
        $this->assertCount(1, $response->json('trips'));
        $this->assertCount(1, $response->json('journal_entries'));
        $this->assertEquals($trip->id, $response->json('trips.0.id'));
    }

    public function test_export_does_not_contain_other_pilgrim_data(): void
    {
        // Autre pèlerin avec ses propres données
        $otherUser = User::factory()->create();
        $otherPilgrim = Pilgrim::factory()->create(['user_id' => $otherUser->id]);

        $otherTrip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $otherPilgrim->id,
        ]);
        $otherTrip->members()->attach($otherPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        JournalEntry::factory()->create([
            'trip_id' => $otherTrip->id,
            'pilgrim_id' => $otherPilgrim->id,
            'entry_date' => now()->format('Y-m-d'),
        ]);

        // L'utilisateur courant n'est membre d'aucun trip et n'a pas d'entrée
        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/pilgrimage/me/export');

        $response->assertOk();
        // Aucune donnée de l'autre pèlerin dans l'export
        $this->assertCount(0, $response->json('trips'));
        $this->assertCount(0, $response->json('journal_entries'));
    }

    public function test_export_returns_404_when_no_pilgrim_profile(): void
    {
        // Utilisateur sans profil pèlerin
        $userWithoutPilgrim = User::factory()->create();

        $this->actingAs($userWithoutPilgrim, 'web')
            ->getJson('/api/pilgrimage/me/export')
            ->assertNotFound();
    }

    // ─── Destroy — Art. 17 ────────────────────────────────────────────────────

    public function test_destroy_requires_authentication(): void
    {
        $this->deleteJson('/api/pilgrimage/me')->assertUnauthorized();
    }

    public function test_destroy_suppresses_pilgrim_and_related_data(): void
    {
        Queue::fake();

        // Créer des données pour ce pèlerin
        $trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
            'status' => 'completed',
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $entry = JournalEntry::factory()->create([
            'trip_id' => $trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'entry_date' => now()->format('Y-m-d'),
        ]);

        $packScenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        $pilgrimId = $this->pilgrim->id;

        $response = $this->actingAs($this->user, 'web')
            ->deleteJson('/api/pilgrimage/me');

        $response->assertOk();

        // Pilgrim supprimé définitivement (forceDelete)
        $this->assertDatabaseMissing('pilgrims', ['id' => $pilgrimId]);

        // JournalEntry supprimée (via ON DELETE CASCADE FK sur pilgrims)
        $this->assertDatabaseMissing('journal_entries', ['id' => $entry->id]);

        // PackScenario supprimé (via ON DELETE CASCADE FK sur pilgrims)
        $this->assertDatabaseMissing('pack_scenarios', ['id' => $packScenario->id]);

        // Membership détaché
        $this->assertDatabaseMissing('trip_members', ['pilgrim_id' => $pilgrimId]);

        // PurgePilgrimAssetsJob dispatché (purge MinIO asynchrone)
        Queue::assertPushed(PurgePilgrimAssetsJob::class, fn ($job) => true);
    }

    public function test_destroy_dispatches_purge_assets_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->user, 'web')
            ->deleteJson('/api/pilgrimage/me');

        $response->assertOk();

        Queue::assertPushed(PurgePilgrimAssetsJob::class);
    }

    public function test_destroy_refuses_if_organizer_of_active_trip(): void
    {
        Queue::fake();

        // Trip actif organisé par le pèlerin courant
        $activeTrip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
            'status' => 'active',
        ]);
        $activeTrip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $response = $this->actingAs($this->user, 'web')
            ->deleteJson('/api/pilgrimage/me');

        $response->assertStatus(422)
            ->assertJsonPath('active_organized_trips_count', 1);

        // Le Pilgrim n'a pas été supprimé
        $this->assertDatabaseHas('pilgrims', ['id' => $this->pilgrim->id]);

        // Aucun job dispatché
        Queue::assertNotPushed(PurgePilgrimAssetsJob::class);
    }

    public function test_destroy_refuses_if_organizer_of_planned_trip(): void
    {
        Queue::fake();

        $plannedTrip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
            'status' => 'planned',
        ]);
        $plannedTrip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $this->actingAs($this->user, 'web')
            ->deleteJson('/api/pilgrimage/me')
            ->assertStatus(422);

        $this->assertDatabaseHas('pilgrims', ['id' => $this->pilgrim->id]);
    }

    public function test_destroy_allows_if_all_organized_trips_are_completed(): void
    {
        Queue::fake();

        // Trip completed = non bloquant
        $completedTrip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
            'status' => 'completed',
        ]);
        $completedTrip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $this->actingAs($this->user, 'web')
            ->deleteJson('/api/pilgrimage/me')
            ->assertOk();

        $this->assertDatabaseMissing('pilgrims', ['id' => $this->pilgrim->id]);
    }
}
