<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\Country;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PilgrimageRoute>
 */
class PilgrimageRouteFactory extends Factory
{
    protected $model = PilgrimageRoute::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $slug = Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999);

        return [
            'slug' => $slug,
            'name' => [
                'fr' => ucfirst($name),
                'nl' => ucfirst($name) . ' (nl)',
                'de' => ucfirst($name) . ' (de)',
            ],
            'description' => [
                'fr' => $this->faker->paragraph(),
                'nl' => $this->faker->paragraph(),
                'de' => $this->faker->paragraph(),
            ],
            'country' => $this->faker->randomElement(Country::cases())->value,
            'total_distance_km' => $this->faker->randomFloat(2, 50, 500),
            'total_elevation_gain_m' => $this->faker->numberBetween(500, 5000),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    public function belgian(): static
    {
        return $this->state(fn () => [
            'country' => Country::BE->value,
        ]);
    }
}
