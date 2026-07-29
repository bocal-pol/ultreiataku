<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ULTREIA-54 — Resource API JournalEntry.
 *
 * Jamais de Model brut en JSON.
 * photos_count inclus pour le badge Filament et la liste frontend.
 * Pas d'URL MinIO directe dans les photos — URL proxy uniquement.
 *
 * @mixin JournalEntry
 */
class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trip_id' => $this->trip_id,
            'pilgrim_id' => $this->pilgrim_id,
            'stage_id' => $this->stage_id,
            'title' => $this->title,
            'body' => $this->body,
            'entry_date' => $this->entry_date?->format('Y-m-d'),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'visibility' => $this->visibility?->value,
            'mood' => $this->mood?->value,
            'km_walked_today' => $this->km_walked_today,
            'is_synced' => $this->is_synced,
            'local_id' => $this->local_id,
            'photos_count' => $this->whenCounted('photos'),
            'photos' => JournalPhotoResource::collection($this->whenLoaded('photos')),
            'pilgrim' => new PilgrimResource($this->whenLoaded('pilgrim')),
            'stage' => new StageResource($this->whenLoaded('stage')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
