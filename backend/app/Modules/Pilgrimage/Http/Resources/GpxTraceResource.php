<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GpxTraceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Modules\Pilgrimage\Models\GpxTrace $this */
        return [
            'id' => $this->id,
            'stage_id' => $this->stage_id,
            'waypoint_id' => $this->waypoint_id,
            'trace_type' => $this->trace_type->value,
            'trace_type_label' => $this->trace_type->label(),
            'name' => $this->name,
            'distance_km' => $this->distance_km ? (float) $this->distance_km : null,
            'elevation_gain_m' => $this->elevation_gain_m,
            'elevation_loss_m' => $this->elevation_loss_m,
            'track_points_count' => $this->track_points_count,
            'precision' => $this->precision->value,
            'source' => $this->source,
            'imported_at' => $this->imported_at?->toIso8601String(),
            'stream_url' => route('api.pilgrimage.gpx.stream', ['id' => $this->id]),
            'simplified_url' => route('api.pilgrimage.gpx.simplified', ['id' => $this->id]),
        ];
    }
}
