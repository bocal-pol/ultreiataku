<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests pour la relation parent/variante des étapes.
 * ULTREIA-VARIANT-1 : migration + modèle + API.
 */
class StageVariantTest extends TestCase
{
    use RefreshDatabase;

    private function makeStage(array $overrides = []): Stage
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();

        return Stage::factory()->create(array_merge([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ], $overrides));
    }

    // ─── Modèle — is_variant / parent_stage_id ───────────────────────────────

    public function test_stage_has_is_variant_false_by_default(): void
    {
        $stage = $this->makeStage();

        $this->assertFalse($stage->is_variant);
        $this->assertNull($stage->parent_stage_id);
    }

    public function test_variant_stage_belongs_to_parent(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);
        $variant = $this->makeStage([
            'is_variant' => true,
            'parent_stage_id' => $parent->id,
        ]);

        $this->assertTrue($variant->is_variant);
        $this->assertSame($parent->id, $variant->parent_stage_id);
        $this->assertSame($parent->id, $variant->parentStage->id);
    }

    public function test_parent_stage_has_variants_relation(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);
        $variant1 = $this->makeStage(['is_variant' => true, 'parent_stage_id' => $parent->id, 'sort_order' => 1]);
        $variant2 = $this->makeStage(['is_variant' => true, 'parent_stage_id' => $parent->id, 'sort_order' => 2]);

        $parent->refresh();
        $variants = $parent->variants;

        $this->assertCount(2, $variants);
        $this->assertTrue($variants->contains('id', $variant1->id));
        $this->assertTrue($variants->contains('id', $variant2->id));
    }

    public function test_variants_relation_excludes_non_variant_stages(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);
        $this->makeStage(['is_variant' => false, 'parent_stage_id' => $parent->id]);

        $parent->refresh();

        $this->assertCount(0, $parent->variants);
    }

    public function test_parent_stage_id_nullable_on_delete(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);
        $variant = $this->makeStage([
            'is_variant' => true,
            'parent_stage_id' => $parent->id,
        ]);

        $parent->delete();
        $variant->refresh();

        $this->assertNull($variant->parent_stage_id);
    }

    // ─── API — Champs exposés ────────────────────────────────────────────────

    public function test_api_exposes_is_variant_and_parent_stage_id(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);
        $variant = $this->makeStage([
            'is_variant' => true,
            'parent_stage_id' => $parent->id,
        ]);

        $response = $this->getJson('/api/pilgrimage/stages/' . $variant->code);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_variant', true)
            ->assertJsonPath('data.parent_stage_id', $parent->id);
    }

    public function test_api_is_variant_false_for_main_stage(): void
    {
        $stage = $this->makeStage(['is_variant' => false]);

        $response = $this->getJson('/api/pilgrimage/stages/' . $stage->code);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_variant', false)
            ->assertJsonPath('data.parent_stage_id', null);
    }

    // ─── API — Filtrage ──────────────────────────────────────────────────────

    public function test_index_filters_by_is_variant_true(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);
        $this->makeStage(['is_variant' => true, 'parent_stage_id' => $parent->id]);
        $this->makeStage(['is_variant' => false]);

        $response = $this->getJson('/api/pilgrimage/stages?is_variant=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $stage) {
            $this->assertTrue($stage['is_variant'], 'All returned stages must be variants');
        }
        $this->assertCount(1, $data);
    }

    public function test_index_filters_by_is_variant_false(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);
        $this->makeStage(['is_variant' => true, 'parent_stage_id' => $parent->id]);
        $this->makeStage(['is_variant' => false]);

        $response = $this->getJson('/api/pilgrimage/stages?is_variant=0');

        $response->assertStatus(200);
        $data = $response->json('data');
        foreach ($data as $stage) {
            $this->assertFalse($stage['is_variant'], 'All returned stages must be main stages');
        }
        $this->assertCount(2, $data);
    }

    // ─── API — include=variants ──────────────────────────────────────────────

    public function test_show_includes_variants_when_requested(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);
        $variant = $this->makeStage([
            'is_variant' => true,
            'parent_stage_id' => $parent->id,
        ]);

        $response = $this->getJson('/api/pilgrimage/stages/' . $parent->code . '?include=variants');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['variants']]);

        $variants = $response->json('data.variants');
        $this->assertNotEmpty($variants);
        $this->assertSame($variant->id, $variants[0]['id']);
    }

    // ─── Factory — is_variant castée ────────────────────────────────────────

    public function test_is_variant_cast_to_boolean(): void
    {
        $stage = $this->makeStage(['is_variant' => true]);

        $this->assertIsBool($stage->is_variant);
        $this->assertTrue($stage->is_variant);
    }

    public function test_variants_ordered_by_sort_order(): void
    {
        $parent = $this->makeStage(['is_variant' => false]);

        $v2 = $this->makeStage(['is_variant' => true, 'parent_stage_id' => $parent->id, 'sort_order' => 20]);
        $v1 = $this->makeStage(['is_variant' => true, 'parent_stage_id' => $parent->id, 'sort_order' => 10]);

        $parent->refresh();
        $variants = $parent->variants;

        $this->assertSame($v1->id, $variants->first()->id, 'Variant with lower sort_order must come first');
        $this->assertSame($v2->id, $variants->last()->id);
    }
}
