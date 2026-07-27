<?php

use App\Modules\Pilgrimage\Http\Controllers\Api\AccommodationController;
use App\Modules\Pilgrimage\Http\Controllers\Api\GpxTraceController;
use App\Modules\Pilgrimage\Http\Controllers\Api\JournalEntryController;
use App\Modules\Pilgrimage\Http\Controllers\Api\JournalPhotoController;
use App\Modules\Pilgrimage\Http\Controllers\Api\MealController;
use App\Modules\Pilgrimage\Http\Controllers\Api\MeController;
use App\Modules\Pilgrimage\Http\Controllers\Api\PackScenarioController;
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
| Vague 1d — Sac (ULTREIA-40/41/42/43)
| Vague 1e — Journal de voyage (ULTREIA-50/51/52/53/54)
| RGPD     — Droits Art. 15/17/20 (RGPD-U01/U03/U05)
| RGPD-R02 — Self-leave Trip avec choix journal (Art. 17)
|
| P0-01 (SEC-ULTREIA-AUTH) — Remplacement de auth:api par le pattern monorepo.
| Le guard `api` driver session a été supprimé de config/auth.php.
| Les routes protégées utilisent désormais le middleware `web` (StartSession inclus)
| + `auth` (qui résout le guard `web` par défaut) — identique au pattern Oikotaku.
| Le SPA frontend envoie les cookies de session via credentials: 'include'.
| Aucun Bearer token en localStorage (P1-05 résolu par construction).
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

    // ─── GPX — authentifié (session cookie) ─────────────────────────────────
    // P0-01 : middleware web (StartSession) + auth (guard web, driver session)

    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('/gpx/{id}', [GpxTraceController::class, 'stream'])->name('api.pilgrimage.gpx.stream');
        Route::get('/gpx/{id}/simplified', [GpxTraceController::class, 'simplified'])->name('api.pilgrimage.gpx.simplified');
    });

    // ─── Vague 1c — Trips (ULTREIA-35) — authentifié ────────────────────────

    Route::middleware(['web', 'auth'])->group(function () {

        // Utilisateur courant + profil Pilgrim (contrat frontend MeResponseDto)
        Route::get('/me', [MeController::class, 'show'])
            ->name('api.pilgrimage.me');

        // RGPD-U01 — Art. 20 — Export portabilité données pèlerin
        Route::get('/me/export', [MeController::class, 'export'])
            ->name('api.pilgrimage.me.export');

        // RGPD-U01 — Art. 17 — Droit à l'oubli (suppression compte pèlerin)
        Route::delete('/me', [MeController::class, 'destroy'])
            ->name('api.pilgrimage.me.destroy');

        // ULTREIA-32 : rejoindre via token (avant le groupe /{id} pour éviter le conflit)
        Route::post('/trips/join/{token}', [TripController::class, 'joinByToken'])
            ->name('api.pilgrimage.trips.join');

        // B-01 : Liste des Trips du pèlerin courant (organizer OU membre)
        Route::get('/trips', [TripController::class, 'index'])
            ->name('api.pilgrimage.trips.index');

        // ULTREIA-35 : CRUD Trip
        Route::post('/trips', [TripController::class, 'store'])
            ->name('api.pilgrimage.trips.store');

        Route::get('/trips/{id}', [TripController::class, 'show'])
            ->name('api.pilgrimage.trips.show');

        // Membres
        Route::post('/trips/{id}/members', [TripController::class, 'addMember'])
            ->name('api.pilgrimage.trips.members.add');

        // RGPD-U03 : journal_action={keep|remove} dans le body
        Route::delete('/trips/{id}/members/{pilgrimId}', [TripController::class, 'removeMember'])
            ->name('api.pilgrimage.trips.members.remove');

        // RGPD-R02 — Art. 17 — Self-leave : le pèlerin courant quitte le Trip lui-même
        // Interdit à l'organizer (422 explicite) — doit transférer/supprimer le Trip d'abord.
        Route::delete('/trips/{id}/membership', [TripController::class, 'selfLeave'])
            ->name('api.pilgrimage.trips.membership.leave');

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

    // ─── Vague 1d — Sac (ULTREIA-43) — authentifié ──────────────────────────

    Route::middleware(['web', 'auth'])->group(function () {

        // Scénarios d'un pèlerin
        Route::get('/pilgrims/{pilgrimId}/pack-scenarios', [PackScenarioController::class, 'indexForPilgrim'])
            ->name('api.pilgrimage.pack-scenarios.index-for-pilgrim');

        // CRUD scénarios
        Route::get('/pack-scenarios/{id}', [PackScenarioController::class, 'show'])
            ->name('api.pilgrimage.pack-scenarios.show');

        Route::post('/pack-scenarios', [PackScenarioController::class, 'store'])
            ->name('api.pilgrimage.pack-scenarios.store');

        Route::put('/pack-scenarios/{id}', [PackScenarioController::class, 'update'])
            ->name('api.pilgrimage.pack-scenarios.update');

        // Ajouter un item à un scénario
        Route::post('/pack-scenarios/{id}/items', [PackScenarioController::class, 'addItem'])
            ->name('api.pilgrimage.pack-scenarios.items.add');

        // Assignations par departure
        Route::post('/departures/{id}/assignments', [PackScenarioController::class, 'addAssignment'])
            ->name('api.pilgrimage.departures.assignments.add');
    });

    // ─── Vague 1e — Journal de voyage (ULTREIA-50/51/52/53/54) — authentifié ─

    Route::middleware(['web', 'auth'])->group(function () {

        // ULTREIA-54 : Journal d'un Trip (filtré par visibilité du lecteur, pagination curseur)
        Route::get('/trips/{id}/journal', [JournalEntryController::class, 'index'])
            ->name('api.pilgrimage.trips.journal.index');

        // ULTREIA-51 : Sync offline — idempotence local_id, last-write-wins
        Route::post('/journal/entries', [JournalEntryController::class, 'store'])
            ->name('api.pilgrimage.journal.entries.store');

        Route::get('/journal/entries/{entryId}', [JournalEntryController::class, 'show'])
            ->name('api.pilgrimage.journal.entries.show');

        Route::put('/journal/entries/{entryId}', [JournalEntryController::class, 'update'])
            ->name('api.pilgrimage.journal.entries.update');

        Route::delete('/journal/entries/{entryId}', [JournalEntryController::class, 'destroy'])
            ->name('api.pilgrimage.journal.entries.destroy');

        // ULTREIA-52 : Upload photo journal (strip EXIF, minio_journal)
        Route::post('/journal/entries/{entryId}/photos', [JournalPhotoController::class, 'store'])
            ->name('api.pilgrimage.journal.photos.store');

        // ULTREIA-52 : Proxy stream MinIO — auth + policy — jamais d'URL directe
        Route::get('/journal/photos/{id}', [JournalPhotoController::class, 'stream'])
            ->name('api.pilgrimage.journal.photos.stream');

        Route::delete('/journal/photos/{id}', [JournalPhotoController::class, 'destroy'])
            ->name('api.pilgrimage.journal.photos.destroy');

        // RGPD-U05 — Art. 17 partiel — Révocation coordonnées GPS d'une photo
        Route::patch('/journal/photos/{id}/revoke-location', [JournalPhotoController::class, 'revokeLocation'])
            ->name('api.pilgrimage.journal.photos.revoke-location');
    });
});
