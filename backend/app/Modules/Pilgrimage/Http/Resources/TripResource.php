<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // B-02 — expose invite_token uniquement si le lecteur est l'organisateur du Trip.
        // Pour tous les autres rôles (participant, observer), la valeur est null par sécurité.
        // has_invite_token reste exposé pour la compatibilité frontend (affichage conditionnel).
        $currentUser = $request->user();
        $isOrganizer = false;

        if ($currentUser !== null) {
            $pilgrim = Pilgrim::query()->where('user_id', $currentUser->id)->first();
            $isOrganizer = $pilgrim !== null && $this->organizer_id === $pilgrim->id;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status?->value,
            'configuration' => $this->configuration?->value,
            'is_public' => $this->is_public,
            'estimated_start_date' => $this->estimated_start_date?->toDateString(),
            'estimated_end_date' => $this->estimated_end_date?->toDateString(),
            // B-02 — invite_token exposé uniquement à l'organisateur, null sinon
            'invite_token' => $isOrganizer ? $this->invite_token : null,
            // has_invite_token conservé pour compatibilité (indique s'il existe un token)
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
