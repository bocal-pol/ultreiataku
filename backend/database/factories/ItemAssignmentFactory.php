<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\ItemAssignment;
use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemAssignment>
 */
class ItemAssignmentFactory extends Factory
{
    protected $model = ItemAssignment::class;

    public function definition(): array
    {
        return [
            'pack_item_id' => PackItem::factory(),
            'departure_id' => Departure::factory(),
            'assigned_to_pilgrim_id' => Pilgrim::factory(),
            'from_stage_id' => null,
            'to_stage_id' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
