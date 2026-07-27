<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Policies;

use App\Models\User;
use App\Modules\Pilgrimage\Concerns\ResolvesCurrentPilgrim;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Services\TripAuthorizationService;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ULTREIA-41/43 — Politique d'accès aux PackScenarios.
 *
 * Matrice specs §4.4 :
 *   super_admin / admin → CRUD total
 *   organizer           → voir le sac de tous les membres de son Trip
 *   participant         → CRUD sur son propre scénario
 *   observer            → interdit
 *
 * I-02 : isOrganizerOfTripWithPilgrim délégué à TripAuthorizationService.
 */
class PackScenarioPolicy
{
    use HandlesAuthorization;
    use ResolvesCurrentPilgrim;

    public function __construct(private readonly TripAuthorizationService $tripAuthService) {}

    public function viewAny(User $user): bool
    {
        return $this->resolvePilgrim($user) !== null;
    }

    public function view(User $user, PackScenario $scenario): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        // Propriétaire peut toujours voir
        if ($scenario->pilgrim_id === $pilgrim->id) {
            return true;
        }

        // Organizer d'un Trip dont le propriétaire du scénario est membre peut voir
        return $this->tripAuthService->isOrganizerOfTripWithPilgrim($pilgrim, $scenario->pilgrim_id);
    }

    public function create(User $user): bool
    {
        return $this->resolvePilgrim($user) !== null;
    }

    public function update(User $user, PackScenario $scenario): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        return $pilgrim !== null && $scenario->pilgrim_id === $pilgrim->id;
    }

    public function delete(User $user, PackScenario $scenario): bool
    {
        return $this->update($user, $scenario);
    }

    /**
     * Ajouter un PackItem : propriétaire du scénario seulement.
     */
    public function addItem(User $user, PackScenario $scenario): bool
    {
        return $this->update($user, $scenario);
    }
}
