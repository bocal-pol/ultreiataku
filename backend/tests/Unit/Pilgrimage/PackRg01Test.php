<?php

declare(strict_types=1);

namespace Tests\Unit\Pilgrimage;

use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ULTREIA-4T — Tests RG-01 : calcul poids de base, poids total, indicateurs couleur.
 *
 * RG-01 :
 *   base_weight_g = SUM(weight_g WHERE is_consumable = false)
 *   total_weight_g = SUM(weight_g) (tous items)
 *
 * Indicateur :
 *   green  : base_kg <= target_kg
 *   orange : base_kg <= target_kg + 1.0
 *   red    : base_kg > target_kg + 1.0
 *   unknown: target_base_weight_kg IS NULL
 */
class PackRg01Test extends TestCase
{
    use RefreshDatabase;

    private Pilgrim $pilgrim;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pilgrim = Pilgrim::factory()->create();
    }

    // ─── baseWeightG ──────────────────────────────────────────────────────────

    public function test_base_weight_excludes_consumables(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        // Non-consommable 1000 g
        PackItem::factory()->create([
            'pack_scenario_id' => $scenario->id,
            'weight_g' => 1000,
            'is_consumable' => false,
        ]);
        // Consommable 500 g (exclu du poids de base)
        PackItem::factory()->create([
            'pack_scenario_id' => $scenario->id,
            'weight_g' => 500,
            'is_consumable' => true,
        ]);

        $this->assertSame(1000, $scenario->baseWeightG());
    }

    public function test_base_weight_is_zero_when_no_items(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        $this->assertSame(0, $scenario->baseWeightG());
    }

    public function test_base_weight_sums_all_non_consumables(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 1100, 'is_consumable' => false]);
        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 350, 'is_consumable' => false]);
        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 550, 'is_consumable' => false]);

        $this->assertSame(2000, $scenario->baseWeightG());
    }

    // ─── totalWeightG ─────────────────────────────────────────────────────────

    public function test_total_weight_includes_consumables(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 8000, 'is_consumable' => false]);
        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 190, 'is_consumable' => true]);

        $this->assertSame(8190, $scenario->totalWeightG());
    }

    public function test_total_weight_is_zero_when_no_items(): void
    {
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $this->pilgrim->id]);

        $this->assertSame(0, $scenario->totalWeightG());
    }

    // ─── weightIndicator — RG-01 ──────────────────────────────────────────────

    public function test_indicator_unknown_when_no_target(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => null,
        ]);

        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 8500, 'is_consumable' => false]);

        $this->assertSame('unknown', $scenario->weightIndicator());
    }

    public function test_indicator_green_when_exactly_at_target(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);
        // 8500 g = 8.5 kg = target → green
        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 8500, 'is_consumable' => false]);

        $this->assertSame('green', $scenario->weightIndicator());
    }

    public function test_indicator_green_when_below_target(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);
        // 7000 g = 7 kg < 8.5 kg → green
        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 7000, 'is_consumable' => false]);

        $this->assertSame('green', $scenario->weightIndicator());
    }

    public function test_indicator_orange_when_over_target_but_within_1kg(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);
        // 9000 g = 9 kg, target=8.5, diff=0.5 kg → orange (0.5 <= 1.0)
        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 9000, 'is_consumable' => false]);

        $this->assertSame('orange', $scenario->weightIndicator());
    }

    public function test_indicator_orange_at_target_plus_exactly_1kg(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);
        // 9500 g = 9.5 kg = 8.5 + 1.0 → orange (exactly at boundary)
        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 9500, 'is_consumable' => false]);

        $this->assertSame('orange', $scenario->weightIndicator());
    }

    public function test_indicator_red_when_over_target_plus_1kg(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);
        // 9600 g = 9.6 kg > 8.5 + 1.0 = 9.5 → red
        PackItem::factory()->create(['pack_scenario_id' => $scenario->id, 'weight_g' => 9600, 'is_consumable' => false]);

        $this->assertSame('red', $scenario->weightIndicator());
    }

    public function test_indicator_green_with_empty_scenario_and_target(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);
        // 0 g < 8.5 kg → green
        $this->assertSame('green', $scenario->weightIndicator());
    }

    // ─── Scenario réel Solo ~8,87 kg ─────────────────────────────────────────

    public function test_solo_scenario_real_weights_trigger_orange(): void
    {
        $scenario = PackScenario::factory()->create([
            'pilgrim_id' => $this->pilgrim->id,
            'target_base_weight_kg' => 8.50,
        ]);

        // Poids par catégorie (inventaire.md récap SOLO, non-consommables uniquement).
        // Consommables exclus : gaz 190 g (cuisine), pastilles eau 15 g (eau), crème Nok 65 g (santé).
        // Total non-consommable : ~8 870 g → zone orange (8,5 < base ≤ 9,5).
        //
        // Catégorie         | Poids source
        // ------------------|----------------------------------------------
        // Portage           | 1300 g (sac+housse+sacoche+sifflet+étanche)
        // Couchage          | 2050 g (tente 1P+matelas+sac couchage+sac viande)
        // Cuisine           |  380 g (570 total − 190 gaz consumable)
        // Eau               |  320 g (poche+bouteille+filtre ; pastilles exclues)
        // Vêtements marche  |  880 g (rechange textile + bâtons)
        // Repos/nuit        |  550 g (t-shirt nuit+legging+chaussons)
        // Pluie/froid       | 1200 g (hardshell+surpantalon+polaire+doudoune+gants+bonnet)
        // Hygiène+LNT       |  440 g (savon+brossedents+serviette+trowel+etc.)
        // Santé+kit pieds   |  580 g (pharmacie − Nok consumable)
        // Nav+admin         | 1030 g (smartphone+batterie+frontale+cartes+credencial+etc.)
        // Réparation        |  140 g (kit couture+tenacious+aquaseal+paracorde+etc.)
        $categoryWeights = [1300, 2050, 380, 320, 880, 550, 1200, 440, 580, 1030, 140];

        foreach ($categoryWeights as $w) {
            PackItem::factory()->create([
                'pack_scenario_id' => $scenario->id,
                'weight_g' => $w,
                'is_consumable' => false,
            ]);
        }

        $baseKg = $scenario->baseWeightG() / 1000;

        // inventaire.md indique ~9,06 kg total; hors consommables ≈ 8,87 kg → orange (8,5 < base ≤ 9,5)
        $this->assertGreaterThan(8.5, $baseKg, 'Le base weight doit dépasser l\'objectif de 8.5 kg');
        $this->assertLessThanOrEqual(9.5, $baseKg, 'Le base weight doit rester dans la zone orange (≤ 9.5 kg)');
        $this->assertSame('orange', $scenario->weightIndicator());
    }
}
