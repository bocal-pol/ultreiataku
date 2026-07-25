<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Providers;

use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Observers\GpxTraceObserver;
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

        // Observers
        GpxTrace::observe(GpxTraceObserver::class);
    }
}
