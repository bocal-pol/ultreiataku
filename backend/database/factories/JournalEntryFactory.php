<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Enums\JournalMood;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use App\Modules\Pilgrimage\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        return [
            'trip_id' => null, // à surcharger
            'pilgrim_id' => null, // à surcharger
            'stage_id' => null,
            'title' => $this->faker->sentence(5),
            'body' => $this->faker->paragraphs(2, true),
            'entry_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'latitude' => null,
            'longitude' => null,
            'visibility' => JournalVisibility::Members->value,
            'mood' => $this->faker->randomElement(JournalMood::cases())->value,
            'km_walked_today' => $this->faker->randomFloat(2, 8, 28),
            'is_synced' => true,
            'local_id' => null,
        ];
    }

    public function private(): static
    {
        return $this->state(['visibility' => JournalVisibility::Private->value]);
    }

    public function members(): static
    {
        return $this->state(['visibility' => JournalVisibility::Members->value]);
    }

    public function public(): static
    {
        return $this->state(['visibility' => JournalVisibility::Public->value]);
    }

    public function offline(): static
    {
        return $this->state([
            'is_synced' => false,
            'local_id' => Str::uuid()->toString(),
        ]);
    }
}
