<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\GpxPrecision;
use App\Modules\Pilgrimage\Enums\GpxTraceType;
use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GpxTrace>
 */
class GpxTraceFactory extends Factory
{
    protected $model = GpxTrace::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'waypoint_id' => null,
            'trace_type' => GpxTraceType::StageMain->value,
            'name' => 'Trace test ' . $this->faker->word(),
            'precision' => GpxPrecision::Exact->value,
            'minio_path' => 'belgique/TEST-01/trace.gpx',
            'minio_disk' => 'minio_gpx',
            'source' => 'trace.gpx',
            'distance_km' => $this->faker->randomFloat(2, 5, 35),
            'elevation_gain_m' => $this->faker->numberBetween(50, 600),
            'elevation_loss_m' => $this->faker->numberBetween(50, 600),
            'track_points_count' => $this->faker->numberBetween(100, 5000),
            'imported_at' => now(),
        ];
    }

    public function withoutMinio(): static
    {
        return $this->state(fn () => [
            'minio_path' => null,
            'minio_disk' => null,
            'precision' => GpxPrecision::Approximate->value,
        ]);
    }

    public function detour(): static
    {
        return $this->state(fn () => [
            'trace_type' => GpxTraceType::Detour->value,
        ]);
    }

    public function variant(): static
    {
        return $this->state(fn () => [
            'trace_type' => GpxTraceType::Variant->value,
        ]);
    }
}
