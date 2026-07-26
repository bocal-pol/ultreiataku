<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OccupancyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'accommodation_id' => $this->accommodation_id,
            'date' => $this->date?->toDateString(),
            'trip_id' => $this->trip_id,
            'count' => $this->count,
        ];
    }
}
