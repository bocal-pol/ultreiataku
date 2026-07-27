<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\PilgrimResource;
use App\Modules\Pilgrimage\Jobs\PurgePilgrimAssetsJob;
use App\Modules\Pilgrimage\Models\JournalPhoto;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Endpoints liés au pèlerin authentifié courant.
 *
 * GET    /api/pilgrimage/me          — profil courant (créé si absent)
 * GET    /api/pilgrimage/me/export   — export RGPD Art. 20 (portabilité)
 * DELETE /api/pilgrimage/me          — droit à l'oubli Art. 17
 */
class MeController extends Controller
{
    /**
     * Utilisateur courant + son profil Pilgrim (créé au premier accès si absent,
     * même logique que SsoCallbackController pour les logins API sans passage Filament).
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $pilgrim = Pilgrim::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->name,
                'preferred_locale' => 'fr',
                'configuration' => 'solo',
            ],
        );

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'pilgrim' => new PilgrimResource($pilgrim),
        ]);
    }

    /**
     * RGPD-U01 — Art. 20 — Portabilité des données.
     *
     * Export JSON complet des données personnelles du pèlerin authentifié :
     *   - profil Pilgrim
     *   - Trips dont il est membre (métadonnées uniquement)
     *   - ses Departures
     *   - ses PackScenarios
     *   - ses JournalEntries + métadonnées photos (pas le binaire)
     *
     * Le pèlerin n'exporte QUE ses propres données.
     * Guard : web (session cookie). Aucune donnée d'autrui dans le payload.
     *
     * Note : la jointure trips est faite via DB::table() car la relation Eloquent
     * Pilgrim::trips() utilise withTimestamps() mais trip_members n'a pas de
     * created_at/updated_at (décision schema). DB::table évite la sélection implicite.
     */
    public function export(Request $request): JsonResponse
    {
        $user = $request->user();

        /** @var Pilgrim|null $pilgrim */
        $pilgrim = Pilgrim::query()->where('user_id', $user->id)->first();

        if ($pilgrim === null) {
            return response()->json(['message' => 'Aucun profil pèlerin trouvé.'], 404);
        }

        // ─── Trips (membership) via raw join pour éviter le withTimestamps() pivot ──
        $trips = DB::table('trips')
            ->join('trip_members', 'trips.id', '=', 'trip_members.trip_id')
            ->where('trip_members.pilgrim_id', $pilgrim->id)
            ->select([
                'trips.id',
                'trips.name',
                'trips.status',
                'trips.estimated_start_date',
                'trips.estimated_end_date',
                'trips.organizer_id',
                'trip_members.role',
                'trip_members.joined_at',
            ])
            ->get()
            ->map(fn ($row): array => [
                'id' => $row->id,
                'name' => $row->name,
                'status' => $row->status,
                'estimated_start_date' => $row->estimated_start_date,
                'estimated_end_date' => $row->estimated_end_date,
                'is_organizer' => $row->organizer_id === $pilgrim->id,
                'role' => $row->role,
                'joined_at' => $row->joined_at,
            ]);

        // ─── Departures ───────────────────────────────────────────────────────
        $departures = $pilgrim->departures()
            ->select(['id', 'trip_id', 'start_stage_id', 'end_stage_id', 'planned_start_date', 'planned_end_date', 'status', 'notes', 'created_at'])
            ->get()
            ->map(fn ($d): array => [
                'id' => $d->id,
                'trip_id' => $d->trip_id,
                'start_stage_id' => $d->start_stage_id,
                'end_stage_id' => $d->end_stage_id,
                'planned_start_date' => $d->planned_start_date?->toDateString(),
                'planned_end_date' => $d->planned_end_date?->toDateString(),
                'status' => $d->status?->value,
                'notes' => $d->notes,
                'created_at' => $d->created_at?->toISOString(),
            ]);

        // ─── PackScenarios ────────────────────────────────────────────────────
        $packScenarios = $pilgrim->packScenarios()
            ->select(['id', 'name', 'description', 'target_base_weight_kg', 'configuration', 'season', 'created_at'])
            ->get()
            ->map(fn ($ps): array => [
                'id' => $ps->id,
                'name' => $ps->name,
                'description' => $ps->description,
                'target_base_weight_kg' => $ps->target_base_weight_kg,
                'configuration' => $ps->configuration?->value,
                'season' => $ps->season?->value,
                'created_at' => $ps->created_at?->toISOString(),
            ]);

        // ─── JournalEntries + métadonnées photos ──────────────────────────────
        $journalEntries = $pilgrim->journalEntries()
            ->with(['photos' => fn ($q) => $q->select(['id', 'journal_entry_id', 'alt_text', 'caption', 'taken_at', 'latitude', 'longitude', 'file_size_bytes', 'mime_type', 'sort_order'])])
            ->select(['id', 'trip_id', 'stage_id', 'title', 'body', 'entry_date', 'latitude', 'longitude', 'visibility', 'mood', 'km_walked_today', 'created_at'])
            ->orderBy('entry_date')
            ->get()
            ->map(fn ($entry): array => [
                'id' => $entry->id,
                'trip_id' => $entry->trip_id,
                'stage_id' => $entry->stage_id,
                'title' => $entry->title,
                'body' => $entry->body,
                'entry_date' => $entry->entry_date?->toDateString(),
                'latitude' => $entry->latitude,
                'longitude' => $entry->longitude,
                'visibility' => $entry->visibility?->value,
                'mood' => $entry->mood?->value,
                'km_walked_today' => $entry->km_walked_today,
                'created_at' => $entry->created_at?->toISOString(),
                'photos' => $entry->photos->map(fn ($p): array => [
                    'id' => $p->id,
                    'alt_text' => $p->alt_text,
                    'caption' => $p->caption,
                    'taken_at' => $p->taken_at?->toISOString(),
                    'latitude' => $p->latitude,
                    'longitude' => $p->longitude,
                    'file_size_bytes' => $p->file_size_bytes,
                    'mime_type' => $p->mime_type,
                    'sort_order' => $p->sort_order,
                ])->all(),
            ]);

        Log::info('rgpd.export.requested', ['pilgrim_id' => $pilgrim->id]);

        return response()->json([
            'export_date' => now()->toISOString(),
            'pilgrim' => [
                'id' => $pilgrim->id,
                'display_name' => $pilgrim->display_name,
                'preferred_locale' => $pilgrim->preferred_locale,
                'configuration' => $pilgrim->configuration?->value,
                'target_base_weight_kg' => $pilgrim->target_base_weight_kg,
                'target_daily_kcal' => $pilgrim->target_daily_kcal,
                'created_at' => $pilgrim->created_at?->toISOString(),
            ],
            'trips' => $trips,
            'departures' => $departures,
            'pack_scenarios' => $packScenarios,
            'journal_entries' => $journalEntries,
        ]);
    }

    /**
     * RGPD-U01 — Art. 17 — Droit à l'oubli.
     *
     * Supprime le Pilgrim courant et toutes ses données personnelles :
     *   - JournalEntries + JournalPhotos (via ON DELETE CASCADE FK DB)
     *   - PackScenarios (via ON DELETE CASCADE FK DB)
     *   - Departures (via ON DELETE CASCADE FK DB)
     *   - Memberships Trip (detach pivot)
     *   - Pilgrim lui-même (suppression définitive)
     *   - Photos MinIO asynchrones via PurgePilgrimAssetsJob
     *
     * Architecture purge MinIO :
     *   Les assets MinIO (photos journal) sont collectés AVANT la transaction
     *   de suppression DB et passés au Job en paramètre. Cette approche évite
     *   le problème lié aux ON DELETE CASCADE des FK (qui suppriment les rows
     *   DB avant que le Job puisse les retrouver). Le Job est idempotent
     *   (Storage::exists() avant delete).
     *
     * Cas organisateur de Trip actif :
     *   REFUS si le pèlerin est organisateur d'au moins un Trip en statut
     *   `planned` ou `active`. Le pèlerin doit d'abord transférer l'organisation
     *   ou annuler les Trips concernés.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        /** @var Pilgrim|null $pilgrim */
        $pilgrim = Pilgrim::query()->where('user_id', $user->id)->first();

        if ($pilgrim === null) {
            return response()->json(['message' => 'Aucun profil pèlerin à supprimer.'], 404);
        }

        // ─── Garde organisateur Trip actif ────────────────────────────────────
        $activeOrganizedTrips = Trip::query()
            ->where('organizer_id', $pilgrim->id)
            ->whereIn('status', ['planned', 'active'])
            ->count();

        if ($activeOrganizedTrips > 0) {
            Log::warning('rgpd.delete.blocked_organizer', [
                'pilgrim_id' => $pilgrim->id,
                'active_trips_count' => $activeOrganizedTrips,
            ]);

            return response()->json([
                'message' => 'Impossible de supprimer votre compte : vous êtes organisateur de '
                    . $activeOrganizedTrips . ' Trip(s) en cours ou planifié(s). '
                    . 'Transférez d\'abord l\'organisation ou annulez ces Trips.',
                'active_organized_trips_count' => $activeOrganizedTrips,
            ], 422);
        }

        $pilgrimId = $pilgrim->id;

        // ─── Collecte des assets MinIO AVANT la transaction ───────────────────
        // La suppression DB déclenche ON DELETE CASCADE sur les FK (journal_entries,
        // pack_scenarios, departures → pilgrims). Les rows disparaissent physiquement.
        // On doit donc collecter les paths MinIO AVANT pour les passer au Job.
        $minioAssets = JournalPhoto::query()
            ->whereHas('entry', fn ($q) => $q->where('pilgrim_id', $pilgrimId))
            ->get(['minio_path', 'minio_disk'])
            ->map(fn ($photo): array => [
                'disk' => $photo->minio_disk ?: 'minio_journal',
                'path' => $photo->minio_path,
            ])
            ->filter(fn ($asset): bool => ! empty($asset['path']))
            ->values()
            ->all();

        // ─── Suppression atomique DB ──────────────────────────────────────────
        DB::transaction(function () use ($pilgrim): void {
            // Détacher les memberships Trip (pas de cascade sur la pivot)
            $pilgrim->trips()->detach();

            // forceDelete supprime le Pilgrim et déclenche ON DELETE CASCADE sur :
            //   journal_entries, journal_photos, pack_scenarios, departures
            $pilgrim->forceDelete();
        });

        // ─── Purge MinIO asynchrone ───────────────────────────────────────────
        PurgePilgrimAssetsJob::dispatch($pilgrimId, $minioAssets);

        Log::info('rgpd.delete.completed', [
            'pilgrim_id' => $pilgrimId,
            'assets_to_purge' => count($minioAssets),
        ]);

        return response()->json([
            'message' => 'Votre profil pèlerin et vos données personnelles ont été supprimés. '
                . 'La suppression des photos est en cours de traitement.',
        ]);
    }
}
