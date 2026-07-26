<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Pilgrimage\Jobs\RebuildOccupancyForTripJob;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Console\Command;

/**
 * ADR-U03 — Commande artisan de rebuild forcé de la table occupancies.
 *
 * Usage :
 *   php artisan pilgrimage:occupancy:rebuild            → tous les Trips
 *   php artisan pilgrimage:occupancy:rebuild --trip=<uuid>  → un seul Trip
 */
class RebuildOccupancyCommand extends Command
{
    protected $signature = 'pilgrimage:occupancy:rebuild {--trip= : UUID du Trip à recalculer (tous si absent)}';

    protected $description = 'Recalcule la table occupancies (ADR-U03). Idempotent.';

    public function handle(): int
    {
        $tripId = $this->option('trip');

        if ($tripId !== null) {
            $this->info("Rebuild occupancy pour Trip : {$tripId}");
            RebuildOccupancyForTripJob::dispatchSync($tripId);
            $this->info('Terminé.');

            return self::SUCCESS;
        }

        $trips = Trip::query()->select('id')->get();
        $count = $trips->count();
        $this->info("Rebuild occupancy pour {$count} Trips...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($trips as $trip) {
            RebuildOccupancyForTripJob::dispatchSync($trip->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Rebuild complet.');

        return self::SUCCESS;
    }
}
