<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\PackSeason;
use App\Modules\Pilgrimage\Enums\PilgrimConfiguration;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackScenario>
 */
class PackScenarioFactory extends Factory
{
    protected $model = PackScenario::class;

    public function definition(): array
    {
        return [
            'pilgrim_id' => Pilgrim::factory(),
            'name' => $this->faker->words(3, true) . ' scenario',
            'description' => $this->faker->optional()->sentence(),
            'target_base_weight_kg' => $this->faker->randomFloat(2, 6.0, 10.0),
            'configuration' => $this->faker->randomElement(PilgrimConfiguration::cases())->value,
            'season' => $this->faker->randomElement(PackSeason::cases())->value,
        ];
    }

    public function solo(): static
    {
        return $this->state([
            'configuration' => PilgrimConfiguration::Solo->value,
            'target_base_weight_kg' => 8.50,
        ]);
    }

    public function duo(): static
    {
        return $this->state([
            'configuration' => PilgrimConfiguration::Duo->value,
            'target_base_weight_kg' => 7.50,
        ]);
    }
}
