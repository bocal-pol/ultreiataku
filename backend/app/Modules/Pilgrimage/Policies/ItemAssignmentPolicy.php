<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Policies;

use App\Models\User;
use App\Modules\Pilgrimage\Concerns\ResolvesCurrentPilgrim;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\ItemAssignment;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ULTREIA-41 — Politique d'accès aux ItemAssignments.
 *
 * Matrice specs §4.4 :
 *   organizer  → voit tout le sac du Trip
 *   participant → uniquement son Departure
 *   observer   → interdit
 */
class ItemAssignmentPolicy
{
    use HandlesAuthorization;
    use ResolvesCurrentPilgrim;

    public function create(User $user, Departure $departure): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        $trip = Trip::query()->find($departure->trip_id);

        if ($trip === null) {
            return false;
        }

        /** @var Trip $trip */
        $role = $trip->roleOf($pilgrim->id);

        if ($role === TripMemberRole::Organizer) {
            return true;
        }

        // Participant peut assigner uniquement sur son propre Departure
        if ($role === TripMemberRole::Participant) {
            return $departure->pilgrim_id === $pilgrim->id;
        }

        return false;
    }

    public function view(User $user, ItemAssignment $assignment): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        /** @var Departure|null $departure */
        $departure = Departure::query()->find($assignment->departure_id);

        if ($departure === null) {
            return false;
        }

        $trip = Trip::query()->find($departure->trip_id);

        if ($trip === null) {
            return false;
        }

        /** @var Trip $trip */
        $role = $trip->roleOf($pilgrim->id);

        if ($role === TripMemberRole::Organizer) {
            return true;
        }

        // Participant voit uniquement ses propres assignations
        return $role === TripMemberRole::Participant
            && $assignment->assigned_to_pilgrim_id === $pilgrim->id;
    }

    public function delete(User $user, ItemAssignment $assignment): bool
    {
        return $this->view($user, $assignment);
    }
}
