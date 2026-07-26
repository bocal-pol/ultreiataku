<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\MealContext;
use App\Modules\Pilgrimage\Enums\MealType;
use App\Modules\Pilgrimage\Models\Meal;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
{
    protected $model = Meal::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'waypoint_id' => null,
            'meal_type' => $this->faker->randomElement(MealType::cases())->value,
            'name' => [
                'fr' => $this->faker->words(3, true),
                'nl' => $this->faker->words(3, true),
                'de' => $this->faker->words(3, true),
            ],
            'description' => [
                'fr' => $this->faker->sentence(),
                'nl' => $this->faker->sentence(),
                'de' => $this->faker->sentence(),
            ],
            'meal_context' => $this->faker->randomElement(MealContext::cases())->value,
            'restaurant_name' => $this->faker->company(),
            'restaurant_address' => $this->faker->address(),
            'price_estimate_eur' => $this->faker->randomFloat(2, 5, 25),
            'kcal_estimate' => $this->faker->numberBetween(300, 1500),
            'weight_g' => null,
            'notes' => null,
        ];
    }

    public function forStage(Stage $stage): static
    {
        return $this->state(fn (array $attributes) => [
            'stage_id' => $stage->id,
        ]);
    }

    public function dinner(): static
    {
        return $this->state(fn (array $attributes) => [
            'meal_type' => MealType::Dinner->value,
        ]);
    }
}
