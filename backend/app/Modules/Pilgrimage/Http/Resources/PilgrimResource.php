<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PilgrimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'display_name' => $this->display_name,
            'avatar_url' => $this->avatar_url,
            'preferred_locale' => $this->preferred_locale,
            'configuration' => $this->configuration?->value,
            'target_base_weight_kg' => $this->target_base_weight_kg,
            'target_daily_kcal' => $this->target_daily_kcal,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
