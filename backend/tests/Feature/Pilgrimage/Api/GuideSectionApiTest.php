<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Modules\Pilgrimage\Enums\GuideCategory;
use App\Modules\Pilgrimage\Models\GuideSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests feature pour GET /api/pilgrimage/guides.
 *
 * Valide :
 *   1. Index retourne 200 avec sections groupées par catégorie
 *   2. Sections non publiées exclues
 *   3. Show retourne la section par slug
 *   4. Show retourne 404 pour slug inconnu
 *   5. i18n via Accept-Language
 */
class GuideSectionApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── GET /api/pilgrimage/guides ──────────────────────────────────────────

    public function test_index_returns_200_grouped_by_category(): void
    {
        GuideSection::factory()->withCategory(GuideCategory::LeCorps)->create([
            'slug' => 'forme-physique',
            'sort_order' => 1,
        ]);
        GuideSection::factory()->withCategory(GuideCategory::Pratique)->create([
            'slug' => 'budget',
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/pilgrimage/guides');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'Le Corps',
                    'Pratique',
                ],
            ]);
    }

    public function test_index_excludes_unpublished_sections(): void
    {
        GuideSection::factory()->withCategory(GuideCategory::Pratique)->create([
            'slug' => 'publie',
            'is_published' => true,
        ]);
        GuideSection::factory()->withCategory(GuideCategory::Pratique)->unpublished()->create([
            'slug' => 'brouillon',
        ]);

        $response = $this->getJson('/api/pilgrimage/guides');

        $response->assertOk();

        // Structure : {"data": {"Pratique": [{"slug": "publie", ...}]}}
        $json = $response->json();
        $allSlugs = collect($json['data'])
            ->flatten(1)
            ->pluck('slug')
            ->toArray();

        $this->assertContains('publie', $allSlugs);
        $this->assertNotContains('brouillon', $allSlugs);
    }

    public function test_index_accessible_without_auth(): void
    {
        GuideSection::factory()->create(['slug' => 'test-public']);

        $this->getJson('/api/pilgrimage/guides')->assertOk();
    }

    // ─── GET /api/pilgrimage/guides/{slug} ───────────────────────────────────

    public function test_show_returns_guide_section(): void
    {
        GuideSection::factory()->withCategory(GuideCategory::LeCorps)->create([
            'slug' => 'forme-physique',
            'title' => ['fr' => 'Forme physique', 'nl' => 'Lichaamsconditie', 'de' => 'Körperform'],
            'content' => ['fr' => 'Contenu FR', 'nl' => 'Inhoud NL', 'de' => 'Inhalt DE'],
        ]);

        $response = $this->getJson('/api/pilgrimage/guides/forme-physique');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'slug', 'category', 'category_label',
                    'title', 'icon', 'content', 'sort_order',
                ],
            ])
            ->assertJsonPath('data.slug', 'forme-physique')
            ->assertJsonPath('data.title', 'Forme physique');
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/pilgrimage/guides/inexistant')
            ->assertStatus(404);
    }

    public function test_show_returns_404_for_unpublished(): void
    {
        GuideSection::factory()->unpublished()->create(['slug' => 'brouillon']);

        $this->getJson('/api/pilgrimage/guides/brouillon')
            ->assertStatus(404);
    }

    public function test_show_accessible_without_auth(): void
    {
        GuideSection::factory()->create(['slug' => 'test-public']);

        $this->getJson('/api/pilgrimage/guides/test-public')->assertOk();
    }

    // ─── i18n ────────────────────────────────────────────────────────────────

    public function test_title_localised_via_accept_language(): void
    {
        GuideSection::factory()->create([
            'slug' => 'meteo',
            'title' => ['fr' => 'Météo & saison', 'nl' => 'Weer & seizoen', 'de' => 'Wetter & Jahreszeit'],
            'content' => ['fr' => 'FR', 'nl' => 'NL', 'de' => 'DE'],
        ]);

        $this->getJson('/api/pilgrimage/guides/meteo', ['Accept-Language' => 'nl'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Weer & seizoen');

        $this->getJson('/api/pilgrimage/guides/meteo', ['Accept-Language' => 'de'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Wetter & Jahreszeit');
    }
}
