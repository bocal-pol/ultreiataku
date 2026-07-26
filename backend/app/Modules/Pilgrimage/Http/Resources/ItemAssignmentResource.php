<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\ItemAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ItemAssignment
 */
class ItemAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pack_item_id' => $this->pack_item_id,
            'departure_id' => $this->departure_id,
            'assigned_to_pilgrim_id' => $this->assigned_to_pilgrim_id,
            'from_stage_id' => $this->from_stage_id,
            'to_stage_id' => $this->to_stage_id,
            'notes' => $this->notes,
            'pack_item' => new PackItemResource($this->whenLoaded('packItem')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
