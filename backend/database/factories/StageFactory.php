<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\AccommodationType;
use App\Modules\Pilgrimage\Enums\StageDifficulty;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stage>
 */
class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        $dayNumber = $this->faker->numberBetween(1, 40);

        return [
            'route_id' => PilgrimageRoute::factory(),
            'code' => 'TEST-' . strtoupper($this->faker->unique()->bothify('??-##')),
            'name' => [
                'fr' => $this->faker->city() . ' → ' . $this->faker->city(),
                'nl' => $this->faker->city() . ' → ' . $this->faker->city(),
                'de' => $this->faker->city() . ' → ' . $this->faker->city(),
            ],
            'notes' => [
                'fr' => $this->faker->paragraph(),
                'nl' => $this->faker->paragraph(),
                'de' => $this->faker->paragraph(),
            ],
            'day_number' => $dayNumber,
            'start_waypoint_id' => Waypoint::factory(),
            'end_waypoint_id' => Waypoint::factory(),
            'distance_km' => $this->faker->randomFloat(2, 5, 35),
            'elevation_gain_m' => $this->faker->numberBetween(50, 800),
            'elevation_loss_m' => $this->faker->numberBetween(50, 800),
            'estimated_duration_h' => $this->faker->randomFloat(1, 1.5, 9.0),
            'difficulty' => $this->faker->randomElement(StageDifficulty::cases())->value,
            'accommodation_type_default' => $this->faker->randomElement(AccommodationType::cases())->value,
            'sort_order' => $dayNumber,
        ];
    }

    public function easy(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => StageDifficulty::Easy->value,
            'distance_km' => $this->faker->randomFloat(2, 5, 15),
            'elevation_gain_m' => $this->faker->numberBetween(50, 200),
        ]);
    }

    public function hard(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => StageDifficulty::Hard->value,
            'distance_km' => $this->faker->randomFloat(2, 20, 35),
            'elevation_gain_m' => $this->faker->numberBetween(400, 800),
        ]);
    }

    public function forRoute(PilgrimageRoute $route): static
    {
        return $this->state(fn (array $attributes) => [
            'route_id' => $route->id,
        ]);
    }
}
