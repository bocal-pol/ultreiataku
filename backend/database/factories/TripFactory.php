<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\TripConfiguration;
use App\Modules\Pilgrimage\Enums\TripStatus;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        return [
            'route_id' => PilgrimageRoute::factory(),
            'organizer_id' => Pilgrim::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'status' => TripStatus::Planned->value,
            'configuration' => TripConfiguration::Solo->value,
            'is_public' => false,
            'estimated_start_date' => $this->faker->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            'estimated_end_date' => $this->faker->dateTimeBetween('+7 months', '+8 months')->format('Y-m-d'),
            'invite_token' => null,
        ];
    }

    public function planned(): static
    {
        return $this->state(['status' => TripStatus::Planned->value]);
    }

    public function active(): static
    {
        return $this->state(['status' => TripStatus::Active->value]);
    }

    public function withInviteToken(): static
    {
        return $this->state(['invite_token' => \Illuminate\Support\Str::uuid()->toString()]);
    }
}
