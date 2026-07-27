<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Models\User;
use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature — GET /api/pilgrimage/me (contrat frontend MeResponseDto).
 */
class MeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/pilgrimage/me')->assertUnauthorized();
    }

    public function test_me_returns_user_and_existing_pilgrim(): void
    {
        $user = User::factory()->create();
        $pilgrim = Pilgrim::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'web')
            ->getJson('/api/pilgrimage/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('pilgrim.id', $pilgrim->id)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'pilgrim' => ['id', 'user_id', 'display_name', 'preferred_locale', 'configuration'],
            ]);
    }

    public function test_me_creates_pilgrim_on_first_access(): void
    {
        $user = User::factory()->create(['name' => 'Nouveau Pèlerin']);

        $this->assertDatabaseMissing('pilgrims', ['user_id' => $user->id]);

        $this->actingAs($user, 'web')
            ->getJson('/api/pilgrimage/me')
            ->assertOk()
            ->assertJsonPath('pilgrim.display_name', 'Nouveau Pèlerin')
            ->assertJsonPath('pilgrim.configuration', 'solo');

        $this->assertDatabaseHas('pilgrims', ['user_id' => $user->id]);
    }
}
