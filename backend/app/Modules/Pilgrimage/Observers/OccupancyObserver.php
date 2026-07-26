<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Observers;

use App\Modules\Pilgrimage\Jobs\RebuildOccupancyForTripJob;
use App\Modules\Pilgrimage\Models\Departure;
use Illuminate\Support\Facades\Log;

/**
 * ADR-U03 — Observer sur Departure.
 *
 * Déclenche le recalcul de l'occupancy pour le Trip impacté
 * à chaque création / modification / suppression d'un Departure.
 *
 * Le pivot trip_members est géré via TripMemberObserver (déclenché
 * dans TripController::addMember après attach/detach).
 *
 * Cohérence : éventuellement cohérente (< 1 s, sync dans la queue).
 */
class OccupancyObserver
{
    public function created(Departure $departure): void
    {
        $this->dispatchRebuild($departure, 'created');
    }

    public function updated(Departure $departure): void
    {
        $this->dispatchRebuild($departure, 'updated');
    }

    public function deleted(Departure $departure): void
    {
        $this->dispatchRebuild($departure, 'deleted');
    }

    private function dispatchRebuild(Departure $departure, string $event): void
    {
        try {
            RebuildOccupancyForTripJob::dispatch($departure->trip_id);

            Log::info('occupancy.rebuild_dispatched', [
                'trip_id' => $departure->trip_id,
                'departure_id' => $departure->id,
                'event' => $event,
            ]);
        } catch (\Throwable $e) {
            // En cas d'échec dispatch, on log mais on ne bloque pas la mutation.
            Log::error('occupancy.rebuild_dispatch_failed', [
                'trip_id' => $departure->trip_id,
                'departure_id' => $departure->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
