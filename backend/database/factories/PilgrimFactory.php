<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pilgrim>
 */
class PilgrimFactory extends Factory
{
    protected $model = Pilgrim::class;

    private static int $userIdCounter = 1000;

    public function definition(): array
    {
        return [
            'user_id' => self::$userIdCounter++,
            'display_name' => $this->faker->name(),
            'avatar_url' => null,
            'preferred_locale' => $this->faker->randomElement(['fr', 'nl', 'de']),
            'configuration' => $this->faker->randomElement(['solo', 'duo']),
            'target_base_weight_kg' => $this->faker->randomFloat(2, 6, 12),
            'target_daily_kcal' => $this->faker->numberBetween(2500, 4000),
        ];
    }

    public function bocal(): static
    {
        return $this->state([
            'user_id' => 1,
            'display_name' => 'bocal',
            'preferred_locale' => 'fr',
            'configuration' => 'solo',
        ]);
    }
}
