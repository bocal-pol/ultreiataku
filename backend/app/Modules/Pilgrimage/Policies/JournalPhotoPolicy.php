<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Policies;

use App\Models\User;
use App\Modules\Pilgrimage\Concerns\ResolvesCurrentPilgrim;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\JournalPhoto;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ULTREIA-53 — Politique d'accès aux JournalPhoto.
 *
 * Alignée sur JournalEntryPolicy (RG-03) :
 *   - La photo hérite de la visibilité de son entrée parente.
 *   - Ajouter une photo : auteur de l'entrée (organizer ou participant).
 *   - Lire/streamer une photo (proxy RG-04) : mêmes droits que lire l'entrée.
 *   - Supprimer : auteur de l'entrée.
 *
 * Specs §4.5 :
 *   Voir photos (proxy backend) : organizer + participant + observer (entrées publiques seulement).
 */
class JournalPhotoPolicy
{
    use HandlesAuthorization;
    use ResolvesCurrentPilgrim;

    /**
     * Stream proxy (GET /api/pilgrimage/journal/photos/{id}).
     * Mêmes droits que JournalEntryPolicy::view().
     */
    public function view(User $user, JournalPhoto $photo): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        $entry = JournalEntry::query()->find($photo->journal_entry_id);

        if ($entry === null) {
            return false;
        }

        // Auteur de l'entrée : accès total
        if ($entry->pilgrim_id === $pilgrim->id) {
            return true;
        }

        $trip = Trip::query()->find($entry->trip_id);

        if ($trip === null) {
            return false;
        }

        $role = $trip->roleOf($pilgrim->id);

        return match ($entry->visibility) {
            JournalVisibility::Private => false,
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

    /**
     * Ajouter une photo : auteur de l'entrée parente.
     */
    public function create(User $user, ?JournalEntry $entry = null): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        return $pilgrim !== null && $entry->pilgrim_id === $pilgrim->id;
    }

    /**
     * Supprimer une photo : auteur de l'entrée parente.
     */
    public function delete(User $user, JournalPhoto $photo): bool
    {
        $pilgrim = $this->resolvePilgrim($user);

        if ($pilgrim === null) {
            return false;
        }

        $entry = JournalEntry::query()->find($photo->journal_entry_id);

        return $entry !== null && $entry->pilgrim_id === $pilgrim->id;
    }
}
