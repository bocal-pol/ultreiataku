<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\PackScenario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PackScenario
 */
class PackScenarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $baseWeightG = $this->baseWeightG();
        $totalWeightG = $this->totalWeightG();

        return [
            'id' => $this->id,
            'pilgrim_id' => $this->pilgrim_id,
            'name' => $this->name,
            'description' => $this->description,
            'target_base_weight_kg' => $this->target_base_weight_kg,
            'configuration' => $this->configuration?->value,
            'season' => $this->season?->value,
            'season_label' => $this->season?->label(),
            // RG-01 — totaux calculés
            'base_weight_g' => $baseWeightG,
            'base_weight_kg' => round($baseWeightG / 1000, 2),
            'total_weight_g' => $totalWeightG,
            'total_weight_kg' => round($totalWeightG / 1000, 2),
            'weight_indicator' => $this->weightIndicator(),
            // Items groupés par catégorie (chargés si relation eager-loaded)
            'items' => PackItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
