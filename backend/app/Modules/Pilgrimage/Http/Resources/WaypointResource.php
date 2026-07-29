<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaypointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Waypoint $this */
        $locale = $request->header('Accept-Language', 'fr');
        $locale = in_array($locale, ['fr', 'nl', 'de']) ? $locale : 'fr';

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->getTranslation('name', $locale, false) ?? $this->getTranslation('name', 'fr', false),
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'poi_category' => $this->poi_category?->value,
            'poi_category_label' => $this->poi_category?->label(),
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'detour_type' => $this->detour_type?->value,
            'detour_distance_km' => $this->detour_distance_km ? (float) $this->detour_distance_km : null,
            'detour_duration_min' => $this->detour_duration_min,
            'visit_duration_min' => $this->visit_duration_min,
            'entry_cost_eur' => $this->entry_cost_eur ? (float) $this->entry_cost_eur : null,
            'booking_required' => $this->booking_required,
            'booking_contact' => $this->booking_contact,
            'opening_notes' => $this->getTranslation('opening_notes', $locale, false),
            'description' => $this->getTranslation('description', $locale, false),
            'is_active' => $this->is_active,
            'verified_at' => $this->verified_at?->toIso8601String(),
        ];
    }
}
