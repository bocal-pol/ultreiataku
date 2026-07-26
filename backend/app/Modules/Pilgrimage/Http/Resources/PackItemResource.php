<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\PackItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PackItem
 */
class PackItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pack_scenario_id' => $this->pack_scenario_id,
            'name' => $this->name,
            'category' => $this->category?->value,
            'category_label' => $this->category?->label(),
            'brand' => $this->brand,
            'model' => $this->model,
            'weight_g' => $this->weight_g,
            'is_shared' => $this->is_shared,
            'is_consumable' => $this->is_consumable,
            'replacement_km' => $this->replacement_km,
            'notes' => $this->notes,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
