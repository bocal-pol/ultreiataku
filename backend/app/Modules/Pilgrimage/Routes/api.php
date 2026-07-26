<?php

use App\Modules\Pilgrimage\Http\Controllers\Api\AccommodationController;
use App\Modules\Pilgrimage\Http\Controllers\Api\GpxTraceController;
use App\Modules\Pilgrimage\Http\Controllers\Api\MealController;
use App\Modules\Pilgrimage\Http\Controllers\Api\RouteController;
use App\Modules\Pilgrimage\Http\Controllers\Api\StageController;
use App\Modules\Pilgrimage\Http\Controllers\Api\TripController;
use App\Modules\Pilgrimage\Http\Controllers\Api\WaypointController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vague 1a — Routes API publiques (lecture Chemin)
| Vague 1b — Hébergements + Repas
| Vague 1c — Trips + SSO (ULTREIA-03/30/31/32/35)
|--------------------------------------------------------------------------
*/

Route::prefix('api/pilgrimage')->group(function () {

    // ─── Lecture publique (Vague 1a) ────────────────────────────────────────

    Route::get('/routes', [RouteController::class, 'index'])->name('api.pilgrimage.routes.index');
    Route::get('/routes/{slug}', [RouteController::class, 'show'])->name('api.pilgrimage.routes.show');

    Route::get('/stages', [StageController::class, 'index'])->name('api.pilgrimage.stages.index');
    Route::get('/stages/{code}', [StageController::class, 'show'])->name('api.pilgrimage.stages.show');

    Route::get('/waypoints', [WaypointController::class, 'index'])->name('api.pilgrimage.waypoints.index');
    Route::get('/waypoints/{slug}', [WaypointController::class, 'show'])->name('api.pilgrimage.waypoints.show');

    // ─── Vague 1b — Hébergements & Repas (publics) ──────────────────────────

    Route::get('/accommodations', [AccommodationController::class, 'index'])->name('api.pilgrimage.accommodations.index');
    Route::get('/accommodations/{id}', [AccommodationController::class, 'show'])->name('api.pilgrimage.accommodations.show');

    Route::get('/meals', [MealController::class, 'index'])->name('api.pilgrimage.meals.index');
    Route::get('/meals/{id}', [MealController::class, 'show'])->name('api.pilgrimage.meals.show');

    // ─── GPX — authentifié (Bearer Passport / auth:api) ────────────────────
    // ULTREIA-03 : middleware auth:api branché (TODO vague 1a levé)

    Route::middleware('auth:api')->group(function () {
        Route::get('/gpx/{id}', [GpxTraceController::class, 'stream'])->name('api.pilgrimage.gpx.stream');
        Route::get('/gpx/{id}/simplified', [GpxTraceController::class, 'simplified'])->name('api.pilgrimage.gpx.simplified');
    });

    // ─── Vague 1c — Trips (ULTREIA-35) — authentifié ────────────────────────

    Route::middleware('auth:api')->group(function () {

        // ULTREIA-32 : rejoindre via token (avant le groupe /{id} pour éviter le conflit)
        Route::post('/trips/join/{token}', [TripController::class, 'joinByToken'])
            ->name('api.pilgrimage.trips.join');

        // ULTREIA-35 : CRUD Trip
        Route::post('/trips', [TripController::class, 'store'])
            ->name('api.pilgrimage.trips.store');

        Route::get('/trips/{id}', [TripController::class, 'show'])
            ->name('api.pilgrimage.trips.show');

        // Membres
        Route::post('/trips/{id}/members', [TripController::class, 'addMember'])
            ->name('api.pilgrimage.trips.members.add');

        Route::delete('/trips/{id}/members/{pilgrimId}', [TripController::class, 'removeMember'])
            ->name('api.pilgrimage.trips.members.remove');

        // Departures (ULTREIA-31)
        Route::post('/trips/{id}/departures', [TripController::class, 'addDeparture'])
            ->name('api.pilgrimage.trips.departures.add');

        // Occupancy (ULTREIA-31, ADR-U03)
        Route::get('/trips/{id}/occupancy', [TripController::class, 'occupancy'])
            ->name('api.pilgrimage.trips.occupancy');

        // Invitation token (ULTREIA-32)
        Route::post('/trips/{id}/invite-token', [TripController::class, 'generateInviteToken'])
            ->name('api.pilgrimage.trips.invite-token.generate');

        Route::delete('/trips/{id}/invite-token', [TripController::class, 'revokeInviteToken'])
            ->name('api.pilgrimage.trips.invite-token.revoke');

        Route::post('/trips/{id}/invite-email', [TripController::class, 'sendInvitationEmail'])
            ->name('api.pilgrimage.trips.invite-email');
    });
});
