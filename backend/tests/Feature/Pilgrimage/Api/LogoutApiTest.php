<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Models\User;
use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature pour POST /api/pilgrimage/logout.
 *
 * Valide :
 *   1. Session active → logout → /me renvoie 401
 *   2. Logout sans session → 401 (route protégée par auth)
 *   3. Réponse JSON correcte après logout
 */
class LogoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_returns_200_with_message(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson('/api/pilgrimage/logout')
            ->assertOk()
            ->assertJsonStructure(['message']);
    }

    public function test_after_logout_me_returns_401(): void
    {
        $user = User::factory()->create();
        Pilgrim::factory()->create(['user_id' => $user->id]);

        // Vérification que /me est accessible AVANT le logout
        $this->actingAs($user, 'web')
            ->getJson('/api/pilgrimage/me')
            ->assertOk();

        // Logout
        $this->actingAs($user, 'web')
            ->postJson('/api/pilgrimage/logout')
            ->assertOk();

        // Après logout (nouvelle requête sans actingAs) → 401
        $this->getJson('/api/pilgrimage/me')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'unauthenticated');
    }

    public function test_logout_without_session_returns_401(): void
    {
        $this->postJson('/api/pilgrimage/logout')
            ->assertUnauthorized();
    }
}
