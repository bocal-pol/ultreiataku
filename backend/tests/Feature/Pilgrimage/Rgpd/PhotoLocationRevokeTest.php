<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Rgpd;

use App\Models\User;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\JournalPhoto;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * RGPD-U05 — Révocation coordonnées GPS d'une photo.
 *
 * PATCH /api/pilgrimage/journal/photos/{id}/revoke-location
 *
 * Art. 7.3 / Art. 17 partiel : le consentement `keep_location` est transitoire
 * à l'upload. Cet endpoint permet la révocation a posteriori.
 */
class PhotoLocationRevokeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pilgrim $pilgrim;

    private JournalEntry $entry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->pilgrim = Pilgrim::factory()->create(['user_id' => $this->user->id]);

        $route = PilgrimageRoute::factory()->create();
        $trip = Trip::factory()->create([
            'route_id' => $route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);

        $this->entry = JournalEntry::factory()->create([
            'trip_id' => $trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'entry_date' => now()->format('Y-m-d'),
        ]);
    }

    private function makePhoto(?float $lat, ?float $lng): JournalPhoto
    {
        return JournalPhoto::factory()->create([
            'journal_entry_id' => $this->entry->id,
            'minio_path' => 'test/photo.jpg',
            'minio_disk' => 'minio_journal',
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }

    public function test_revoke_location_requires_authentication(): void
    {
        $photo = $this->makePhoto(50.85, 4.35);

        $this->patchJson("/api/pilgrimage/journal/photos/{$photo->id}/revoke-location")
            ->assertUnauthorized();
    }

    public function test_revoke_location_clears_gps_coordinates(): void
    {
        $photo = $this->makePhoto(50.85, 4.35);

        $this->actingAs($this->user, 'web')
            ->patchJson("/api/pilgrimage/journal/photos/{$photo->id}/revoke-location")
            ->assertOk()
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'GPS') || str_contains($msg, 'Coordonn'));

        $this->assertDatabaseHas('journal_photos', [
            'id' => $photo->id,
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_revoke_location_idempotent_when_already_null(): void
    {
        $photo = $this->makePhoto(null, null);

        // Déjà null → réponse 200 avec message informatif, pas d'erreur
        $this->actingAs($this->user, 'web')
            ->patchJson("/api/pilgrimage/journal/photos/{$photo->id}/revoke-location")
            ->assertOk();
    }

    public function test_revoke_location_denied_for_other_user(): void
    {
        $otherUser = User::factory()->create();
        $otherPilgrim = Pilgrim::factory()->create(['user_id' => $otherUser->id]);

        $photo = $this->makePhoto(50.85, 4.35);

        // Un autre utilisateur ne peut pas révoquer la géoloc d'une photo qui ne lui appartient pas
        $this->actingAs($otherUser, 'web')
            ->patchJson("/api/pilgrimage/journal/photos/{$photo->id}/revoke-location")
            ->assertStatus(403);
    }
}
