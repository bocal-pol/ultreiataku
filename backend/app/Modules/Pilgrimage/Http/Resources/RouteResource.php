<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PilgrimageRoute $this */
        $locale = $request->header('Accept-Language', 'fr');
        $locale = in_array($locale, ['fr', 'nl', 'de']) ? $locale : 'fr';

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->getTranslation('name', $locale, false) ?? $this->getTranslation('name', 'fr', false),
            'description' => $this->getTranslation('description', $locale, false),
            'country' => $this->country->value,
            'country_label' => $this->country->label(),
            'total_distance_km' => (float) $this->total_distance_km,
            'total_elevation_gain_m' => $this->total_elevation_gain_m,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'stages_count' => $this->whenLoaded('stages', fn () => $this->stages->count()),
            'stages' => StageResource::collection($this->whenLoaded('stages')),
        ];
    }
}
