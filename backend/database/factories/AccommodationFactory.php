<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\AccommodationType;
use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Accommodation>
 */
class AccommodationFactory extends Factory
{
    protected $model = Accommodation::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'waypoint_id' => null,
            'name' => [
                'fr' => 'Gîte ' . $this->faker->lastName(),
                'nl' => 'Gîte ' . $this->faker->lastName(),
                'de' => 'Gîte ' . $this->faker->lastName(),
            ],
            'type' => $this->faker->randomElement(AccommodationType::cases())->value,
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'website' => null,
            'email' => null,
            'price_min_eur' => $this->faker->randomFloat(2, 5, 25),
            'price_max_eur' => $this->faker->randomFloat(2, 25, 80),
            'is_donativo' => false,
            'capacity' => $this->faker->numberBetween(4, 50),
            'has_shower' => $this->faker->boolean(80),
            'has_kitchen' => $this->faker->boolean(60),
            'has_wifi' => $this->faker->boolean(40),
            'stamps_credencial' => $this->faker->boolean(70),
            'pilgrim_friendly' => true,
            'booking_required' => $this->faker->boolean(50),
            'booking_notice_days' => null,
            'bivouac_legal' => false,
            'bivouac_notes' => null,
            'is_primary' => true,
            'sort_order' => 1,
            'notes' => null,
            'verified_at' => now()->subMonths($this->faker->numberBetween(0, 3)),
        ];
    }

    public function obsolete(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now()->subMonths($this->faker->numberBetween(7, 24)),
        ]);
    }

    public function neverVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => null,
        ]);
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'sort_order' => 1,
        ]);
    }

    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => false,
            'sort_order' => 2,
        ]);
    }

    public function forStage(Stage $stage): static
    {
        return $this->state(fn (array $attributes) => [
            'stage_id' => $stage->id,
        ]);
    }
}
