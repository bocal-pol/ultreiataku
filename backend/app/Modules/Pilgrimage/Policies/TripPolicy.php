<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Policies;

use App\Models\User;
use App\Modules\Pilgrimage\Concerns\ResolvesCurrentPilgrim;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\Trip;
use App\Policies\Concerns\InteractsWithPanelAuth;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ULTREIA-33 — Politique d'accès aux Trips.
 *
 * Matrice (specs §4.3) :
 *   organizer     → CRUD complet sur son Trip, invitations, membres
 *   participant   → lecture, création Departure (le sien)
 *   observer      → lecture seule (pas d'occupancy)
 *
 * Panel admin (super_admin / admin) → CRUD total via bypass InteractsWithPanelAuth.
 *
 * L'utilisateur passé est le User Eloquent local (lié au SSO).
 * Le Pilgrim est résolu par user_id via ResolvesCurrentPilgrim (cache par requête).
 */
class TripPolicy
{
    use HandlesAuthorization;
    use InteractsWithPanelAuth;
    use ResolvesCurrentPilgrim;

    public function viewAny(User $user): bool
    {
        // Tout utilisateur authentifié peut voir la liste de ses trips
        return true;
    }

    public function view(User $user, Trip $trip): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        return $trip->hasMember($pilgrim->id)
            || $trip->organizer_id === $pilgrim->id;
    }

    public function create(User $user): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Tout pilgrim authentifié peut créer un Trip
        return $this->resolvePilgrim($user) !== null;
    }

    /**
     * Seul l'organizer peut modifier le Trip (ou l'admin panel).
     */
    public function update(User $user, Trip $trip): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isOrganizer($user, $trip);
    }

    public function delete(User $user, Trip $trip): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isOrganizer($user, $trip);
    }

    /**
     * Inviter un membre = pouvoir de l'organizer.
     */
    public function invite(User $user, Trip $trip): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isOrganizer($user, $trip);
    }

    /**
     * Modifier les rôles / retirer un membre = organizer.
     */
    public function manageMember(User $user, Trip $trip): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isOrganizer($user, $trip);
    }

    /**
     * RGPD-R02 — Self-leave : tout membre du Trip peut accéder à cet endpoint.
     *
     * La Policy autorise l'accès à tout membre (y compris l'organizer) afin que
     * le controller puisse retourner un 422 métier explicite à l'organizer plutôt
     * qu'un 403 opaque. Les non-membres reçoivent 403.
     *
     * La garde « organizer interdit » est portée par le controller (422 avec message).
     */
    public function selfLeave(User $user, Trip $trip): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        return $trip->hasMember($pilgrim->id);
    }

    /**
     * Voir l'occupancy : organizer ou participant (pas observer).
     */
    public function viewOccupancy(User $user, Trip $trip): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        $role = $trip->roleOf($pilgrim->id);

        return in_array($role, [TripMemberRole::Organizer, TripMemberRole::Participant], true);
    }

    /**
     * Créer un Departure :
     *   - organizer  → peut créer pour n'importe quel membre
     *   - participant → uniquement le sien
     *   - observer   → interdit
     */
    public function createDeparture(User $user, Trip $trip): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        $role = $trip->roleOf($pilgrim->id);

        return in_array($role, [TripMemberRole::Organizer, TripMemberRole::Participant], true);
    }

    /**
     * Modifier un Departure :
     *   - organizer → tout
     *   - participant → uniquement le sien
     */
    public function updateDeparture(User $user, Trip $trip, Departure $departure): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
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

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function isOrganizer(User $user, Trip $trip): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        return $pilgrim !== null && $trip->organizer_id === $pilgrim->id;
    }
}
