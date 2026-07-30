<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Policies;

use App\Models\User;
use App\Modules\Pilgrimage\Concerns\ResolvesCurrentPilgrim;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Models\Departure;
use App\Policies\Concerns\InteractsWithPanelAuth;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ULTREIA-33 — Politique d'accès aux Departures.
 *
 * Matrice (specs §4.3) :
 *   organizer   → CRUD complet dans son Trip
 *   participant → CRUD sur le sien uniquement
 *   observer    → aucun accès
 *
 * Panel admin (super_admin / admin) → CRUD total via bypass InteractsWithPanelAuth.
 */
class DeparturePolicy
{
    use HandlesAuthorization;
    use InteractsWithPanelAuth;
    use ResolvesCurrentPilgrim;

    public function view(User $user, Departure $departure): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        $trip = $departure->trip;

        if ($trip === null) {
            return false;
        }

        $role = $trip->roleOf($pilgrim->id);

        return in_array($role, [TripMemberRole::Organizer, TripMemberRole::Participant], true);
    }

    public function create(User $user): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->resolvePilgrim($user) !== null;
    }

    public function update(User $user, Departure $departure): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        $trip = $departure->trip;

        if ($trip === null) {
            return false;
        }

        $role = $trip->roleOf($pilgrim->id);

        if ($role === TripMemberRole::Organizer) {
            return true;
        }

        if ($role === TripMemberRole::Participant) {
            return $departure->pilgrim_id === $pilgrim->id;
        }

        return false;
    }

    public function delete(User $user, Departure $departure): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->update($user, $departure);
    }
}
