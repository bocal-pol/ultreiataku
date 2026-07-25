<?php

use App\Modules\Pilgrimage\Http\Controllers\Api\GpxTraceController;
use App\Modules\Pilgrimage\Http\Controllers\Api\RouteController;
use App\Modules\Pilgrimage\Http\Controllers\Api\StageController;
use App\Modules\Pilgrimage\Http\Controllers\Api\WaypointController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vague 1a — Routes API publiques (lecture Chemin)
|--------------------------------------------------------------------------
|
| Routes publiques : /api/pilgrimage/routes, /stages, /waypoints
| Routes auth (Bearer Passport) : /api/pilgrimage/gpx/{id}
|
*/

Route::prefix('api/pilgrimage')->group(function () {

    // ─── Lecture publique (Vague 1a) ────────────────────────────────────────

    Route::get('/routes', [RouteController::class, 'index'])->name('api.pilgrimage.routes.index');
    Route::get('/routes/{slug}', [RouteController::class, 'show'])->name('api.pilgrimage.routes.show');

    Route::get('/stages', [StageController::class, 'index'])->name('api.pilgrimage.stages.index');
    Route::get('/stages/{code}', [StageController::class, 'show'])->name('api.pilgrimage.stages.show');

    Route::get('/waypoints', [WaypointController::class, 'index'])->name('api.pilgrimage.waypoints.index');
    Route::get('/waypoints/{slug}', [WaypointController::class, 'show'])->name('api.pilgrimage.waypoints.show');

    // ─── GPX — authentifié (Bearer Passport) ────────────────────────────────
    // TODO ULTREIA-03 : brancher middleware auth:passport après intégration SSO
    // Pour la V1a, l'endpoint GPX est accessible publiquement mais le TODO est posé.

    Route::get('/gpx/{id}', [GpxTraceController::class, 'stream'])->name('api.pilgrimage.gpx.stream');
    Route::get('/gpx/{id}/simplified', [GpxTraceController::class, 'simplified'])->name('api.pilgrimage.gpx.simplified');
});
