<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Jobs;

use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\Occupancy;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ADR-U03 — Recalcule la table occupancies pour un Trip donné.
 *
 * RG-02 : Pour chaque Departure actif/planifié du Trip,
 * pour chaque nuit couverte, pour chaque hébergement principal
 * de la Stage correspondante, compte le nombre de pèlerins.
 *
 * Idempotent : supprime et recalcule entièrement.
 * Stratégie : accumule en mémoire, puis upsert batch.
 */
class RebuildOccupancyForTripJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(private readonly string $tripId) {}

    public function handle(): void
    {
        Log::info('occupancy.rebuild_started', ['trip_id' => $this->tripId]);

        DB::transaction(function (): void {
            // Verrou pessimiste sur les Departures du Trip (ADR-U03 concurrence)
            $departures = Departure::query()
                ->where('trip_id', $this->tripId)
                ->whereIn('status', ['planned', 'active'])
                ->with(['startStage', 'endStage'])
                ->lockForUpdate()
                ->get();

            // Supprimer les occupancies existantes du Trip avant recalcul
            Occupancy::query()->where('trip_id', $this->tripId)->delete();

            // Accumulation en mémoire : clé = "accommodation_id|date"
            /** @var array<string, array{accommodation_id: string, date: string, trip_id: string, count: int}> $counts */
            $counts = [];

            foreach ($departures as $departure) {
                $this->accumulateOneDeparture($departure, $counts);
            }

            // Insérer toutes les occupancies calculées
            foreach ($counts as $data) {
                Occupancy::create([
                    'accommodation_id' => $data['accommodation_id'],
                    'date' => $data['date'],
                    'trip_id' => $this->tripId,
                    'count' => $data['count'],
                ]);
            }
        });

        Log::info('occupancy.rebuild_done', ['trip_id' => $this->tripId]);
    }

    /**
     * @param  array<string, array{accommodation_id: string, date: string, trip_id: string, count: int}>  $counts
     */
    private function accumulateOneDeparture(Departure $departure, array &$counts): void
    {
        $startStage = $departure->startStage;
        $endStage = $departure->endStage;

        if ($startStage === null || $endStage === null) {
            return;
        }

        // Toutes les Stages entre start_day et end_day sur la même Route
        $stages = Stage::query()
            ->where('route_id', $startStage->route_id)
            ->whereBetween('day_number', [$startStage->day_number, $endStage->day_number])
            ->with(['accommodations' => fn ($q) => $q->where('is_primary', true)])
            ->orderBy('day_number')
            ->get();

        foreach ($stages as $stage) {
            // La nuit = planned_start_date + (day_number - start_day_number) jours
            $nightDate = $departure->planned_start_date->copy()
                ->addDays($stage->day_number - $startStage->day_number)
                ->toDateString();

            foreach ($stage->accommodations as $accommodation) {
                $key = $accommodation->id . '|' . $nightDate;

                if (isset($counts[$key])) {
                    $counts[$key]['count']++;
                } else {
                    $counts[$key] = [
                        'accommodation_id' => $accommodation->id,
                        'date' => $nightDate,
                        'trip_id' => $this->tripId,
                        'count' => 1,
                    ];
                }
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('occupancy.rebuild_job_failed', [
            'trip_id' => $this->tripId,
            'error' => $exception->getMessage(),
        ]);
    }
}
