<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use App\Modules\Pilgrimage\Http\Resources\JournalEntryResource;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * ULTREIA-51/54 — API REST JournalEntry.
 *
 * Routes :
 *   GET    /api/pilgrimage/trips/{id}/journal         (index — filtré visibilité, curseur)
 *   POST   /api/pilgrimage/journal/entries            (store — idempotence local_id, RG-05)
 *   GET    /api/pilgrimage/journal/entries/{entryId}  (show)
 *   PUT    /api/pilgrimage/journal/entries/{entryId}  (update)
 *   DELETE /api/pilgrimage/journal/entries/{entryId}  (destroy)
 *
 * RG-05 — Sync offline :
 *   - POST avec local_id → vérifie existence → idempotence (200) ou crée (201).
 *   - Conflit (même local_id, body différent) → last-write-wins sur updated_at serveur.
 *   - Réponse : {id, local_id, synced_at}.
 *
 * RG-03 — Visibilité :
 *   - private  → auteur seul
 *   - members  → organizer + participants
 *   - public   → + observers (si Trip is_public)
 *   La visibilité est filtrée au niveau SQL (pas post-fetch) pour la pagination curseur.
 */
class JournalEntryController extends Controller
{
    // ─── GET /api/pilgrimage/trips/{id}/journal ───────────────────────────────

    /**
     * Liste les entrées d'un Trip filtrées par la visibilité du lecteur.
     * Pagination curseur (after_id) pour le feed chronologique mobile.
     */
    public function index(Request $request, string $id): AnonymousResourceCollection|JsonResponse
    {
        $trip = Trip::query()->findOrFail($id);

        $this->authorize('viewAny', [JournalEntry::class, $trip]);

        $pilgrim = $this->resolvePilgrim($request);

        // Construire le scope visibilité SQL
        $visibilityScope = $this->buildVisibilityScope($trip, $pilgrim);

        $query = JournalEntry::query()
            ->where('trip_id', $id)
            ->where($visibilityScope)
            ->withCount('photos')
            ->with('pilgrim')
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Pagination curseur : after_id (UUID de la dernière entrée reçue)
        if ($request->filled('after_id')) {
            $cursor = JournalEntry::query()->find($request->string('after_id')->toString());
            if ($cursor !== null) {
                $query->where(function ($q) use ($cursor): void {
                    $q->where('entry_date', '<', $cursor->entry_date)
                        ->orWhere(function ($q2) use ($cursor): void {
                            $q2->where('entry_date', '=', $cursor->entry_date)
                                ->where('created_at', '<', $cursor->created_at);
                        });
                });
            }
        }

        $limit = min((int) $request->integer('limit', 20), 50);
        $entries = $query->limit($limit + 1)->get();

        $hasMore = $entries->count() > $limit;
        $entries = $entries->take($limit);

        return JournalEntryResource::collection($entries)
            ->additional([
                'meta' => [
                    'has_more'  => $hasMore,
                    'next_cursor' => $hasMore ? $entries->last()?->id : null,
                ],
            ]);
    }

    // ─── POST /api/pilgrimage/journal/entries ─────────────────────────────────

    /**
     * RG-05 — Crée ou retourne une entrée existante (idempotence local_id).
     * Last-write-wins sur updated_at si conflit de contenu.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'trip_id'         => 'required|uuid|exists:trips,id',
            'stage_id'        => 'nullable|uuid|exists:stages,id',
            'title'           => 'nullable|string|max:300',
            'body'            => 'nullable|string',
            'entry_date'      => 'required|date',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'visibility'      => 'nullable|in:private,members,public',
            'mood'            => 'nullable|in:great,good,neutral,tired,difficult',
            'km_walked_today' => 'nullable|numeric|min:0|max:100',
            'is_synced'       => 'nullable|boolean',
            'local_id'        => 'nullable|string|size:36|regex:/^[0-9a-f-]{36}$/i',
            'updated_at_client' => 'nullable|date',
        ], [
            'local_id.size'  => 'Le local_id doit être un UUID v4 (36 caractères).',
            'local_id.regex' => 'Le local_id doit être un UUID v4 valide.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $trip    = Trip::query()->findOrFail($request->string('trip_id')->toString());
        $pilgrim = $this->resolvePilgrim($request);

        $this->authorize('create', [JournalEntry::class, $trip]);

        $localId = $request->filled('local_id')
            ? $request->string('local_id')->toString()
            : null;

        // ─── RG-05 Idempotence ────────────────────────────────────────────────
        if ($localId !== null) {
            $existing = JournalEntry::query()
                ->where('local_id', $localId)
                ->first();

            if ($existing !== null) {
                // Last-write-wins : si le client a une version plus récente, on met à jour
                $clientUpdatedAt = $request->filled('updated_at_client')
                    ? strtotime($request->string('updated_at_client')->toString())
                    : 0;

                $serverUpdatedAt = $existing->updated_at
                    ? $existing->updated_at->timestamp
                    : 0;

                if ($clientUpdatedAt > $serverUpdatedAt) {
                    DB::transaction(function () use ($existing, $validator, $pilgrim): void {
                        $existing->update(array_merge(
                            array_filter(
                                $validator->validated(),
                                fn ($v) => $v !== null,
                            ),
                            ['is_synced' => true],
                        ));

                        Log::info('journal.entry.sync_lww', [
                            'entry_id'  => $existing->id,
                            'local_id'  => $existing->local_id,
                            'pilgrim_id' => $pilgrim?->id,
                        ]);
                    });
                    $existing->refresh();
                }

                return response()->json([
                    'id'        => $existing->id,
                    'local_id'  => $existing->local_id,
                    'synced_at' => $existing->updated_at?->toIso8601String(),
                ], 200);
            }
        }

        // ─── Création ─────────────────────────────────────────────────────────
        $entry = DB::transaction(function () use ($validator, $trip, $pilgrim, $localId): JournalEntry {
            $data = array_merge($validator->validated(), [
                'trip_id'     => $trip->id,
                'pilgrim_id'  => $pilgrim?->id,
                'visibility'  => $validator->validated()['visibility'] ?? JournalVisibility::Private->value,
                'is_synced'   => true,
                'local_id'    => $localId,
            ]);

            /** @var JournalEntry $entry */
            $entry = JournalEntry::query()->create($data);

            Log::info('journal.entry.created', [
                'entry_id'   => $entry->id,
                'trip_id'    => $trip->id,
                'pilgrim_id' => $pilgrim?->id,
                'local_id'   => $localId,
            ]);

            return $entry;
        });

        return response()->json([
            'id'        => $entry->id,
            'local_id'  => $entry->local_id,
            'synced_at' => $entry->updated_at?->toIso8601String(),
        ], 201);
    }

    // ─── GET /api/pilgrimage/journal/entries/{entryId} ───────────────────────

    public function show(Request $request, string $entryId): JournalEntryResource|JsonResponse
    {
        /** @var JournalEntry $entry */
        $entry = JournalEntry::query()
            ->with(['photos', 'pilgrim', 'stage'])
            ->findOrFail($entryId);

        $this->authorize('view', $entry);

        return new JournalEntryResource($entry);
    }

    // ─── PUT /api/pilgrimage/journal/entries/{entryId} ───────────────────────

    public function update(Request $request, string $entryId): JournalEntryResource|JsonResponse
    {
        /** @var JournalEntry $entry */
        $entry = JournalEntry::query()->findOrFail($entryId);

        // Changer la visibilité peut être fait par l'auteur ou l'organizer
        if ($request->has('visibility') && ! $request->hasAny(['title', 'body', 'mood', 'km_walked_today'])) {
            $this->authorize('updateVisibility', $entry);
        } else {
            $this->authorize('update', $entry);
        }

        $validator = Validator::make($request->all(), [
            'stage_id'        => 'nullable|uuid|exists:stages,id',
            'title'           => 'nullable|string|max:300',
            'body'            => 'nullable|string',
            'entry_date'      => 'nullable|date',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'visibility'      => 'nullable|in:private,members,public',
            'mood'            => 'nullable|in:great,good,neutral,tired,difficult',
            'km_walked_today' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($entry, $validator): void {
            $entry->update($validator->validated());
        });

        Log::info('journal.entry.updated', [
            'entry_id' => $entry->id,
        ]);

        return new JournalEntryResource($entry->load(['photos', 'pilgrim', 'stage']));
    }

    // ─── DELETE /api/pilgrimage/journal/entries/{entryId} ────────────────────

    public function destroy(Request $request, string $entryId): JsonResponse
    {
        /** @var JournalEntry $entry */
        $entry = JournalEntry::query()->findOrFail($entryId);

        $this->authorize('delete', $entry);

        DB::transaction(function () use ($entry): void {
            // Les photos sont supprimées en cascade par la FK (onDelete cascade)
            // La purge MinIO se fait via un Job dédié (RGPD)
            $entry->delete();
        });

        Log::info('journal.entry.deleted', ['entry_id' => $entryId]);

        return response()->json(['message' => 'Entrée supprimée.']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Construit le scope SQL de visibilité pour un lecteur donné.
     * Retourne une closure utilisable dans where().
     */
    private function buildVisibilityScope(Trip $trip, ?Pilgrim $pilgrim): \Closure
    {
        return function ($query) use ($trip, $pilgrim): void {
            if ($pilgrim === null) {
                // Non authentifié — aucun accès (V1)
                $query->whereRaw('1=0');

                return;
            }

            $pilgrimId = $pilgrim->id;
            $role      = $trip->roleOf($pilgrimId);

            $query->where(function ($q) use ($pilgrimId, $role, $trip): void {
                // Auteur : voit tout
                $q->where('pilgrim_id', $pilgrimId);

                if ($role !== null) {
                    // Organizer + Participant : voient members + public
                    if (in_array($role->value, ['organizer', 'participant'], true)) {
                        $q->orWhereIn('visibility', ['members', 'public']);
                    }

                    // Observer : voit public seulement
                    if ($role->value === 'observer') {
                        $q->orWhere('visibility', 'public');
                    }
                }
            });
        };
    }

    private function resolvePilgrim(Request $request): ?Pilgrim
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        return Pilgrim::query()->where('user_id', $user->id)->first();
    }
}
