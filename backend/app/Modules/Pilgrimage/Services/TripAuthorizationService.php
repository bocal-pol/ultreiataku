<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Services;

use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Trip;

/**
 * Service — Logique d'autorisation partagée pour les Trips.
 *
 * I-02 : élimine la duplication de `isOrganizerOfTripWithPilgrim` entre
 * PackScenarioPolicy et PackScenarioController.
 *
 * Ce service est la source de vérité pour les vérifications d'autorisation
 * cross-couches. Les policies ET les controllers l'utilisent.
 */
class TripAuthorizationService
{
    /**
     * Vérifie si le Pilgrim $viewer est organizer d'un Trip
     * dont le Pilgrim $targetPilgrimId est membre.
     *
     * Utilisé par : PackScenarioPolicy::view() + PackScenarioController::indexForPilgrim()
     */
    public function isOrganizerOfTripWithPilgrim(Pilgrim $viewer, string $targetPilgrimId): bool
    {
        return Trip::query()
            ->where('organizer_id', $viewer->id)
            ->whereHas('members', function ($q) use ($targetPilgrimId): void {
                $q->where('pilgrim_id', $targetPilgrimId);
            })
            ->exists();
    }
}
