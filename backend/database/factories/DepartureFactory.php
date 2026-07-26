<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\DepartureStatus;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Departure>
 */
class DepartureFactory extends Factory
{
    protected $model = Departure::class;

    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'pilgrim_id' => Pilgrim::factory(),
            'start_stage_id' => Stage::factory(),
            'end_stage_id' => Stage::factory(),
            'planned_start_date' => $this->faker->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'planned_end_date' => $this->faker->dateTimeBetween('+4 months', '+5 months')->format('Y-m-d'),
            'actual_start_date' => null,
            'status' => DepartureStatus::Planned->value,
            'notes' => null,
        ];
    }

    public function planned(): static
    {
        return $this->state(['status' => DepartureStatus::Planned->value]);
    }

    public function active(): static
    {
        return $this->state(['status' => DepartureStatus::Active->value]);
    }
}
