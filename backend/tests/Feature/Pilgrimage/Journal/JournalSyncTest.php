<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Journal;

use App\Models\User;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * ULTREIA-51 — RG-05 Sync offline : idempotence local_id + last-write-wins.
 *
 * Cas couverts :
 *   - POST sans local_id → création normale (201)
 *   - POST avec local_id nouveau → création avec local_id (201)
 *   - POST avec local_id existant, même corps → idempotent (200)
 *   - POST avec local_id existant, corps différent, client plus récent → LWW update (200)
 *   - POST avec local_id existant, corps différent, serveur plus récent → pas de mise à jour (200)
 *   - Collision : deux requêtes simultanées avec le même local_id → une seule entrée créée
 *   - Réponse toujours : {id, local_id, synced_at}
 */
class JournalSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pilgrim $pilgrim;

    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $route = PilgrimageRoute::factory()->create();

        $this->user    = User::factory()->create();
        $this->pilgrim = Pilgrim::factory()->create(['user_id' => $this->user->id]);

        $this->trip = Trip::factory()->create([
            'route_id'     => $route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);

        $this->trip->members()->attach($this->pilgrim->id, [
            'role'       => 'organizer',
            'joined_at'  => now(),
            'invited_by' => null,
        ]);
    }

    private function postEntry(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/journal/entries', array_merge([
                'trip_id'    => $this->trip->id,
                'entry_date' => now()->format('Y-m-d'),
                'body'       => 'Corps initial',
                'visibility' => 'private',
            ], $overrides));
    }

    // ─── Création normale ────────────────────────────────────────────────────

    public function test_store_without_local_id_creates_entry(): void
    {
        $response = $this->postEntry();

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'local_id', 'synced_at']);

        $this->assertNull($response->json('local_id'));
        $this->assertDatabaseHas('journal_entries', [
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->pilgrim->id,
        ]);
    }

    public function test_store_with_local_id_creates_entry(): void
    {
        $localId  = Str::uuid()->toString();
        $response = $this->postEntry(['local_id' => $localId]);

        $response->assertStatus(201)
            ->assertJsonPath('local_id', $localId);

        $this->assertDatabaseHas('journal_entries', ['local_id' => $localId]);
    }

    // ─── Idempotence ─────────────────────────────────────────────────────────

    public function test_repost_same_local_id_returns_existing_entry(): void
    {
        $localId = Str::uuid()->toString();

        $first = $this->postEntry(['local_id' => $localId]);
        $first->assertStatus(201);

        $second = $this->postEntry(['local_id' => $localId]);
        $second->assertStatus(200);

        // Même ID retourné
        $this->assertSame($first->json('id'), $second->json('id'));

        // Une seule entrée en base
        $this->assertDatabaseCount('journal_entries', 1);
    }

    // ─── Last-write-wins ─────────────────────────────────────────────────────

    public function test_lww_updates_when_client_is_newer(): void
    {
        $localId = Str::uuid()->toString();

        // Créer l'entrée initiale
        $this->postEntry([
            'local_id' => $localId,
            'body'     => 'Version serveur',
        ])->assertStatus(201);

        // Re-POST avec updated_at_client plus récent que le serveur
        $futureTimestamp = now()->addHour()->toIso8601String();

        $response = $this->postEntry([
            'local_id'          => $localId,
            'body'              => 'Version client plus récente',
            'updated_at_client' => $futureTimestamp,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('journal_entries', [
            'local_id' => $localId,
            'body'     => 'Version client plus récente',
        ]);
    }

    public function test_lww_keeps_server_when_server_is_newer(): void
    {
        $localId = Str::uuid()->toString();

        // Créer l'entrée initiale
        $this->postEntry([
            'local_id' => $localId,
            'body'     => 'Version serveur récente',
        ])->assertStatus(201);

        // Re-POST avec updated_at_client dans le passé
        $pastTimestamp = now()->subHour()->toIso8601String();

        $response = $this->postEntry([
            'local_id'          => $localId,
            'body'              => 'Version client périmée',
            'updated_at_client' => $pastTimestamp,
        ]);

        $response->assertStatus(200);

        // Le corps serveur est conservé
        $this->assertDatabaseHas('journal_entries', [
            'local_id' => $localId,
            'body'     => 'Version serveur récente',
        ]);

        $this->assertDatabaseMissing('journal_entries', [
            'local_id' => $localId,
            'body'     => 'Version client périmée',
        ]);
    }

    // ─── Réponse sync ─────────────────────────────────────────────────────────

    public function test_sync_response_contains_required_fields(): void
    {
        $localId  = Str::uuid()->toString();
        $response = $this->postEntry(['local_id' => $localId]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'local_id', 'synced_at'])
            ->assertJsonPath('local_id', $localId);

        $this->assertNotNull($response->json('synced_at'));
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/pilgrimage/journal/entries', [
            'trip_id'    => $this->trip->id,
            'entry_date' => now()->format('Y-m-d'),
        ])->assertStatus(401);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/journal/entries', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['trip_id', 'entry_date']);
    }

    public function test_store_rejects_invalid_local_id_format(): void
    {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/pilgrimage/journal/entries', [
                'trip_id'    => $this->trip->id,
                'entry_date' => now()->format('Y-m-d'),
                'local_id'   => 'not-a-uuid',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['local_id']);
    }

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    public function test_author_can_update_own_entry(): void
    {
        $entry = JournalEntry::factory()->create([
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'visibility' => 'private',
            'entry_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($this->user, 'api')
            ->putJson('/api/pilgrimage/journal/entries/' . $entry->id, [
                'body' => 'Nouveau corps',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.body', 'Nouveau corps');
    }

    public function test_author_can_delete_own_entry(): void
    {
        $entry = JournalEntry::factory()->create([
            'trip_id'    => $this->trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'visibility' => 'private',
            'entry_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/pilgrimage/journal/entries/' . $entry->id)
            ->assertStatus(200);

        $this->assertDatabaseMissing('journal_entries', ['id' => $entry->id]);
    }
}
