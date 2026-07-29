<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Policies;

use App\Models\User;
use App\Modules\Pilgrimage\Concerns\ResolvesCurrentPilgrim;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ULTREIA-53 — Politique d'accès aux JournalEntry.
 *
 * RG-03 — Matrice de visibilité :
 *   private  → auteur seul
 *   members  → auteur + participants + organizer (pas observer)
 *   public   → auteur + participants + organizer + observers
 *              + tout utilisateur si Trip is_public (V1 : auth requise)
 *
 * Matrice write (specs §4.5) :
 *   Créer    → organizer + participant (pas observer)
 *   Modifier → auteur de l'entrée (organizer peut changer la visibilité de toute entrée du Trip)
 *   Supprimer → auteur de l'entrée
 *   Changer visibilité → auteur (ses propres) | organizer (toute entrée du Trip)
 *
 * Fix ULTREIA-ADMIN-P0 : viewAny(User) — signature standard Laravel/Filament (1 arg).
 *   Le filtrage par Trip est porté par viewTripJournal(User, Trip), appelé depuis
 *   JournalEntryController::index(). viewAny répond à « cet utilisateur peut-il accéder
 *   à la ressource JournalEntry dans le panel admin (modération) ? ».
 */
class JournalEntryPolicy
{
    use HandlesAuthorization;
    use ResolvesCurrentPilgrim;

    // ─── Panel admin ──────────────────────────────────────────────────────────

    /**
     * Signature standard Laravel/Filament (1 argument).
     * Répond à « cet utilisateur peut-il voir la ressource JournalEntry
     * dans le panel admin (liste de modération) ? ».
     *
     * Seuls les rôles admin / super-admin y accèdent.
     * Le filtrage par Trip pour l'API est géré par viewTripJournal().
     */
    public function viewAny(User $user): bool
    {
        return $this->resolvePilgrim($user) !== null;
    }

    // ─── API — Lecture filtrée par Trip ───────────────────────────────────────

    /**
     * Peut lister les entrées d'un Trip spécifique (GET /trips/{id}/journal).
     * Remplace l'ancienne signature viewAny(User, Trip) qui cassait Filament.
     * La visibilité individuelle est filtrée ensuite par buildVisibilityScope().
     */
    public function viewTripJournal(User $user, Trip $trip): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        // Organizer ou membre du trip
        return $trip->organizer_id === $pilgrim->id
            || $trip->hasMember($pilgrim->id);
    }

    /**
     * RG-03 — Peut lire une entrée précise selon sa visibilité.
     */
    public function view(User $user, JournalEntry $entry): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        // Auteur : voit toujours
        if ($entry->pilgrim_id === $pilgrim->id) {
            return true;
        }

        $trip = Trip::query()->find($entry->trip_id);

        if ($trip === null) {
            return false;
        }

        $role = $trip->roleOf($pilgrim->id);

        return match ($entry->visibility) {
            JournalVisibility::Private => false, // auteur seul — déjà géré ci-dessus
            JournalVisibility::Members => in_array($role, [
                TripMemberRole::Organizer,
                TripMemberRole::Participant,
            ], true),
            JournalVisibility::Public => in_array($role, [
                TripMemberRole::Organizer,
                TripMemberRole::Participant,
                TripMemberRole::Observer,
            ], true) || ($trip->is_public && $role !== null),
        };
    }

    // ─── Écriture ─────────────────────────────────────────────────────────────

    /**
     * Créer une entrée : organizer ou participant du Trip (pas observer).
     */
    public function create(User $user, ?Trip $trip = null): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        $role = $trip->roleOf($pilgrim->id);

        return in_array($role, [
            TripMemberRole::Organizer,
            TripMemberRole::Participant,
        ], true);
    }

    /**
     * Modifier : auteur de l'entrée.
     * L'organizer peut modifier la visibilité via updateVisibility().
     */
    public function update(User $user, JournalEntry $entry): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        return $pilgrim !== null && $entry->pilgrim_id === $pilgrim->id;
    }

    /**
     * Supprimer : auteur de l'entrée uniquement.
     */
    public function delete(User $user, JournalEntry $entry): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        return $pilgrim !== null && $entry->pilgrim_id === $pilgrim->id;
    }

    /**
     * Changer la visibilité d'une entrée :
     *   - auteur : peut changer la visibilité de ses propres entrées
     *   - organizer : peut changer la visibilité de toute entrée du Trip
     */
    public function updateVisibility(User $user, JournalEntry $entry): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        if ($entry->pilgrim_id === $pilgrim->id) {
            return true;
        }

        $trip = Trip::query()->find($entry->trip_id);

        if ($trip === null) {
            return false;
        }

        return $trip->roleOf($pilgrim->id) === TripMemberRole::Organizer;
    }
}
