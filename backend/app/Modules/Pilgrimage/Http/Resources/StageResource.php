<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Modules\Pilgrimage\Models\Stage $this */
        $locale = $request->header('Accept-Language', 'fr');
        $locale = in_array($locale, ['fr', 'nl', 'de']) ? $locale : 'fr';

        return [
            'id' => $this->id,
            'route_id' => $this->route_id,
            'code' => $this->code,
            'name' => $this->getTranslation('name', $locale, false) ?? $this->getTranslation('name', 'fr', false),
            'day_number' => $this->day_number,
            'distance_km' => (float) $this->distance_km,
            'elevation_gain_m' => $this->elevation_gain_m,
            'elevation_loss_m' => $this->elevation_loss_m,
            'estimated_duration_h' => (float) $this->estimated_duration_h,
            'difficulty' => $this->difficulty?->value,
            'difficulty_label' => $this->difficulty?->label(),
            'accommodation_type_default' => $this->accommodation_type_default?->value,
            'notes' => $this->getTranslation('notes', $locale, false),
            'sort_order' => $this->sort_order,
            'start_waypoint' => new WaypointResource($this->whenLoaded('startWaypoint')),
            'end_waypoint' => new WaypointResource($this->whenLoaded('endWaypoint')),
            'waypoints' => WaypointResource::collection($this->whenLoaded('waypoints')),
            'gpx_traces' => GpxTraceResource::collection($this->whenLoaded('gpxTraces')),
            // Vague 1b
            'accommodations' => AccommodationResource::collection($this->whenLoaded('accommodations')),
            'meals' => MealResource::collection($this->whenLoaded('meals')),
        ];
    }
}
