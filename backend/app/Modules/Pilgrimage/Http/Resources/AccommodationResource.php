<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Accommodation $this */
        $locale = $request->header('Accept-Language', 'fr');
        $locale = in_array($locale, ['fr', 'nl', 'de']) ? $locale : 'fr';

        return [
            'id' => $this->id,
            'stage_id' => $this->stage_id,
            'waypoint_id' => $this->waypoint_id,
            'name' => $this->getTranslation('name', $locale, false) ?? $this->getTranslation('name', 'fr', false),
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'address' => $this->address,
            'phone' => $this->phone,
            'website' => $this->website,
            'email' => $this->email,
            'price_min_eur' => $this->price_min_eur !== null ? (float) $this->price_min_eur : null,
            'price_max_eur' => $this->price_max_eur !== null ? (float) $this->price_max_eur : null,
            'is_donativo' => $this->is_donativo,
            'capacity' => $this->capacity,
            'has_shower' => $this->has_shower,
            'has_kitchen' => $this->has_kitchen,
            'has_wifi' => $this->has_wifi,
            'stamps_credencial' => $this->stamps_credencial,
            'pilgrim_friendly' => $this->pilgrim_friendly,
            'booking_required' => $this->booking_required,
            'booking_notice_days' => $this->booking_notice_days,
            'bivouac_legal' => $this->bivouac_legal,
            'bivouac_notes' => $this->bivouac_legal
                ? ($this->getTranslation('bivouac_notes', $locale, false))
                : null,
            'is_primary' => $this->is_primary,
            'sort_order' => $this->sort_order,
            'notes' => $this->getTranslation('notes', $locale, false),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'is_obsolete' => $this->isObsolete(),
        ];
    }
}
