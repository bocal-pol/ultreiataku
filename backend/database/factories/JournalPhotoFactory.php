<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Models\JournalPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalPhoto>
 */
class JournalPhotoFactory extends Factory
{
    protected $model = JournalPhoto::class;

    public function definition(): array
    {
        return [
            'journal_entry_id' => null, // à surcharger
            'minio_path'       => 'journal/test/' . $this->faker->uuid() . '.jpg',
            'minio_disk'       => 'minio_journal',
            'alt_text'         => $this->faker->sentence(6),
            'caption'          => $this->faker->sentence(4),
            'taken_at'         => $this->faker->dateTimeBetween('-30 days', 'now'),
            'latitude'         => null,
            'longitude'        => null,
            'file_size_bytes'  => $this->faker->numberBetween(100_000, 4_000_000),
            'mime_type'        => 'image/jpeg',
            'sort_order'       => 0,
            'is_synced'        => true,
        ];
    }

    public function withCoords(): static
    {
        return $this->state([
            'latitude'  => $this->faker->latitude(49.5, 51.5),
            'longitude' => $this->faker->longitude(2.5, 6.5),
        ]);
    }
}
