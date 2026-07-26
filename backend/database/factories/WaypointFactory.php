<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\WaypointType;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Waypoint>
 */
class WaypointFactory extends Factory
{
    protected $model = Waypoint::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->city() . ' ' . $this->faker->numberBetween(1, 9999);
        $slug = Str::slug($name);

        return [
            'slug' => $slug,
            'name' => [
                'fr' => $name,
                'nl' => $name . ' (nl)',
                'de' => $name . ' (de)',
            ],
            'description' => [
                'fr' => $this->faker->paragraph(),
                'nl' => $this->faker->paragraph(),
                'de' => $this->faker->paragraph(),
            ],
            'opening_notes' => [
                'fr' => null,
                'nl' => null,
                'de' => null,
            ],
            'type' => WaypointType::City->value,
            'poi_category' => null,
            'detour_type' => null,
            'latitude' => $this->faker->latitude(49.5, 51.5),
            'longitude' => $this->faker->longitude(3.5, 6.5),
            'is_active' => true,
            'verified_at' => now(),
        ];
    }

    public function poi(): static
    {
        return $this->state(fn () => [
            'type' => WaypointType::Poi->value,
            'poi_category' => 'religious',
        ]);
    }

    public function city(): static
    {
        return $this->state(fn () => [
            'type' => WaypointType::City->value,
            'poi_category' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
