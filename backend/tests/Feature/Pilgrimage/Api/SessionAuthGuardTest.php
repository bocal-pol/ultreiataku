<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Models\User;
use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de régression — P0-01 (SEC-ULTREIA-AUTH).
 *
 * Valide que le pattern session cookie (guard web) est correctement appliqué
 * sur toutes les routes API protégées d'Ultreiataku.
 *
 * Règles vérifiées :
 *   1. Requête SANS session → 401 JSON (aucune redirection HTML)
 *   2. Requête AVEC session (guard web) → passe correctement
 *   3. Requête AVEC Bearer token SANS session → 401 (plus de Bearer auth)
 */
class SessionAuthGuardTest extends TestCase
{
    use RefreshDatabase;

    // ─── Routes représentatives des 4 groupes auth ───────────────────────────

    private const PROTECTED_ROUTES = [
        ['GET', '/api/pilgrimage/me'],
        ['GET', '/api/pilgrimage/trips'],
        ['POST', '/api/pilgrimage/trips'],
        ['GET', '/api/pilgrimage/pack-scenarios/00000000-0000-0000-0000-000000000001'],
        ['GET', '/api/pilgrimage/gpx/00000000-0000-0000-0000-000000000001'],
        ['GET', '/api/pilgrimage/journal/entries/00000000-0000-0000-0000-000000000001'],
    ];

    /**
     * Toute route protégée doit retourner 401 JSON sans session.
     * Prouve que le guard web session ne laisse pas passer les requêtes anonymes.
     */
    public function test_protected_routes_return_401_without_session(): void
    {
        foreach (self::PROTECTED_ROUTES as [$method, $path]) {
            $response = $this->json($method, $path);

            $this->assertSame(
                401,
                $response->status(),
                "Expected 401 on {$method} {$path} without session, got {$response->status()}"
            );

            // Doit être du JSON, pas une redirection HTML vers /login
            $response->assertJsonStructure(['error', 'message', 'status']);
            $this->assertSame('unauthenticated', $response->json('error'));
        }
    }

    /**
     * Un Bearer token dans Authorization sans session cookie ne doit pas authentifier.
     * Valide l'absence de guard token/passport.
     */
    public function test_bearer_token_without_session_returns_401(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer fake-token-that-should-not-work',
        ])->getJson('/api/pilgrimage/me');

        $response->assertUnauthorized();
        $response->assertJsonPath('error', 'unauthenticated');
    }

    /**
     * Une session valide (guard web) permet l'accès aux routes protégées.
     * Valide que le cookie de session HttpOnly fonctionne correctement.
     */
    public function test_session_cookie_authenticates_protected_route(): void
    {
        $user = User::factory()->create();
        Pilgrim::factory()->create(['user_id' => $user->id]);

        // actingAs($user, 'web') simule la session cookie côté serveur
        $this->actingAs($user, 'web')
            ->getJson('/api/pilgrimage/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    /**
     * Les routes publiques restent accessibles sans session.
     */
    public function test_public_routes_accessible_without_session(): void
    {
        $this->getJson('/api/pilgrimage/routes')->assertOk();
        $this->getJson('/api/pilgrimage/stages')->assertOk();
        $this->getJson('/api/pilgrimage/waypoints')->assertOk();
        $this->getJson('/api/pilgrimage/accommodations')->assertOk();
        $this->getJson('/api/pilgrimage/meals')->assertOk();
    }
}
