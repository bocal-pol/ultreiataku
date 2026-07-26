<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\PackCategory;
use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\PackScenario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackItem>
 */
class PackItemFactory extends Factory
{
    protected $model = PackItem::class;

    public function definition(): array
    {
        return [
            'pack_scenario_id' => PackScenario::factory(),
            'name' => $this->faker->words(2, true),
            'category' => $this->faker->randomElement(PackCategory::cases())->value,
            'brand' => $this->faker->optional()->company(),
            'model' => $this->faker->optional()->word(),
            'weight_g' => $this->faker->numberBetween(30, 1500),
            'is_shared' => false,
            'is_consumable' => false,
            'replacement_km' => null,
            'notes' => null,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function consumable(): static
    {
        return $this->state(['is_consumable' => true]);
    }

    public function shared(): static
    {
        return $this->state(['is_shared' => true]);
    }

    public function inCategory(PackCategory $category): static
    {
        return $this->state(['category' => $category->value]);
    }
}
