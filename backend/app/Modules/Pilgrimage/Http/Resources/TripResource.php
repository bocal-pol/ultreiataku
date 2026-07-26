<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status?->value,
            'configuration' => $this->configuration?->value,
            'is_public' => $this->is_public,
            'estimated_start_date' => $this->estimated_start_date?->toDateString(),
            'estimated_end_date' => $this->estimated_end_date?->toDateString(),
            // invite_token jamais exposé en liste — seulement au moment de la génération
            'has_invite_token' => $this->invite_token !== null,
            'route_id' => $this->route_id,
            'organizer_id' => $this->organizer_id,
            'organizer' => $this->whenLoaded('organizer', fn () => [
                'id' => $this->organizer->id,
                'display_name' => $this->organizer->display_name,
            ]),
            'members' => $this->whenLoaded('members', function () {
                return $this->members->map(fn ($m) => [
                    'id' => $m->id,
                    'display_name' => $m->display_name,
                    'role' => $m->pivot->role,
                    'joined_at' => $m->pivot->joined_at,
                ]);
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
