<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Policies;

use App\Models\User;
use App\Modules\Pilgrimage\Concerns\ResolvesCurrentPilgrim;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\ItemAssignment;
use App\Modules\Pilgrimage\Models\Trip;
use App\Policies\Concerns\InteractsWithPanelAuth;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ULTREIA-41 — Politique d'accès aux ItemAssignments.
 *
 * Matrice specs §4.4 :
 *   organizer  → voit tout le sac du Trip
 *   participant → uniquement son Departure
 *   observer   → interdit
 *
 * Panel admin (super_admin / admin) → CRUD total via bypass InteractsWithPanelAuth.
 */
class ItemAssignmentPolicy
{
    use HandlesAuthorization;
    use InteractsWithPanelAuth;
    use ResolvesCurrentPilgrim;

    public function viewAny(User $user): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->resolvePilgrim($user) !== null;
    }

    public function create(User $user, ?Departure $departure = null): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $pilgrim = $this->resolvePilgrim($user);

        // $departure null = appel Filament au niveau resource (sans contexte).
        if ($pilgrim === null || $departure === null) {
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
        if ($this->isAdmin()) {
            return true;
        }

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

    /**
     * Modifier une assignation : même logique que view (organizer ou propriétaire).
     * Ajout admin bypass pour le panel Filament.
     */
    public function update(User $user, ItemAssignment $assignment): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->view($user, $assignment);
    }

    public function delete(User $user, ItemAssignment $assignment): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->view($user, $assignment);
    }
}
