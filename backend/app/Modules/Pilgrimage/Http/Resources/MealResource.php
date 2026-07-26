<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Meal $this */
        $locale = $request->header('Accept-Language', 'fr');
        $locale = in_array($locale, ['fr', 'nl', 'de']) ? $locale : 'fr';

        return [
            'id' => $this->id,
            'stage_id' => $this->stage_id,
            'waypoint_id' => $this->waypoint_id,
            'meal_type' => $this->meal_type?->value,
            'meal_type_label' => $this->meal_type?->label(),
            'name' => $this->getTranslation('name', $locale, false) ?? $this->getTranslation('name', 'fr', false),
            'description' => $this->getTranslation('description', $locale, false),
            'meal_context' => $this->meal_context?->value,
            'meal_context_label' => $this->meal_context?->label(),
            'restaurant_name' => $this->restaurant_name,
            'restaurant_address' => $this->restaurant_address,
            'price_estimate_eur' => $this->price_estimate_eur !== null ? (float) $this->price_estimate_eur : null,
            'kcal_estimate' => $this->kcal_estimate,
            'weight_g' => $this->weight_g,
            'notes' => $this->getTranslation('notes', $locale, false),
        ];
    }
}
