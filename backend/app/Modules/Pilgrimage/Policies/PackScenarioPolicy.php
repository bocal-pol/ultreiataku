<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Policies;

use App\Models\User;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ULTREIA-41/43 — Politique d'accès aux PackScenarios.
 *
 * Matrice specs §4.4 :
 *   super_admin / admin → CRUD total
 *   organizer           → voir le sac de tous les membres de son Trip
 *   participant         → CRUD sur son propre scénario
 *   observer            → interdit
 */
class PackScenarioPolicy
{
    use HandlesAuthorization;

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
        return $this->isOrganizerOfTripWithPilgrim($pilgrim, $scenario->pilgrim_id);
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

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function resolvePilgrim(User $user): ?Pilgrim
    {
        return Pilgrim::query()->where('user_id', $user->id)->first();
    }

    /**
     * Vérifie si le Pilgrim $viewer est organizer d'un Trip
     * dont le Pilgrim $targetPilgrimId est membre.
     */
    private function isOrganizerOfTripWithPilgrim(Pilgrim $viewer, string $targetPilgrimId): bool
    {
        return Trip::query()
            ->where('organizer_id', $viewer->id)
            ->whereHas('members', function ($q) use ($targetPilgrimId): void {
                $q->where('pilgrim_id', $targetPilgrimId);
            })
            ->exists();
    }
}
