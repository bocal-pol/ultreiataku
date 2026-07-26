<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Providers;

use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Models\Trip;
use App\Modules\Pilgrimage\Observers\AccommodationObserver;
use App\Modules\Pilgrimage\Observers\GpxTraceObserver;
use App\Modules\Pilgrimage\Observers\OccupancyObserver;
use App\Modules\Pilgrimage\Policies\DeparturePolicy;
use App\Modules\Pilgrimage\Policies\TripPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PilgrimageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../../config/pilgrimage.php',
            'pilgrimage',
        );
    }

    public function boot(): void
    {
        // Charger les migrations du module
        $this->loadMigrationsFrom(database_path('migrations/pilgrimage'));

        // Charger les routes du module
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

        // Charger les routes SSO (web — session Filament)
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // ─── Observers ────────────────────────────────────────────────────────
        GpxTrace::observe(GpxTraceObserver::class);
        Accommodation::observe(AccommodationObserver::class);
        // ADR-U03 — OccupancyObserver sur Departure (recalcul table matérialisée)
        Departure::observe(OccupancyObserver::class);

        // ─── Policies ─────────────────────────────────────────────────────────
        // ULTREIA-33
        Gate::policy(Trip::class, TripPolicy::class);
        Gate::policy(Departure::class, DeparturePolicy::class);
    }
}
