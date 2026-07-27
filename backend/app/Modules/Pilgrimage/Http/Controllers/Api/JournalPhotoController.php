<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\JournalPhotoResource;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\JournalPhoto;
use App\Modules\Pilgrimage\Services\JournalPhotoUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ULTREIA-52 — Proxy photos journal.
 *
 * Routes :
 *   POST   /api/pilgrimage/journal/entries/{entryId}/photos   (upload)
 *   GET    /api/pilgrimage/journal/photos/{id}                (stream proxy — RG-04)
 *   DELETE /api/pilgrimage/journal/photos/{id}                (destroy)
 *   PATCH  /api/pilgrimage/journal/photos/{id}/revoke-location (RGPD-U05 — Art. 17 partiel)
 *
 * RG-04 — Proxy stream :
 *   1. Auth SSO obligatoire.
 *   2. JournalPhotoPolicy::view() → visibilité de l'entrée parente.
 *   3. Stream depuis MinIO avec Storage::disk()->get().
 *   4. Cache-Control: private, max-age=3600.
 *   5. Jamais d'URL MinIO directe exposée.
 */
class JournalPhotoController extends Controller
{
    public function __construct(private readonly JournalPhotoUploadService $uploadService) {}

    // ─── POST /api/pilgrimage/journal/entries/{entryId}/photos ───────────────

    public function store(Request $request, string $entryId): JournalPhotoResource|JsonResponse
    {
        /** @var JournalEntry $entry */
        $entry = JournalEntry::query()->findOrFail($entryId);

        $this->authorize('create', [JournalPhoto::class, $entry]);

        $validator = Validator::make($request->all(), [
            'photo' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
            'alt_text' => 'nullable|string|max:500',
            'caption' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'keep_location' => 'nullable|boolean',
        ], [
            'photo.max' => 'La photo ne doit pas dépasser 10 Mo.',
            'photo.mimes' => 'Formats acceptés : JPEG, PNG, WEBP.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('photo');
        $keepLocation = $request->boolean('keep_location', false);

        $meta = $this->uploadService->upload($file, $entry->id, $keepLocation);

        $photo = DB::transaction(function () use ($entry, $meta, $validator): JournalPhoto {
            $nextOrder = JournalPhoto::query()
                ->where('journal_entry_id', $entry->id)
                ->max('sort_order') ?? -1;

            /** @var JournalPhoto $photo */
            $photo = JournalPhoto::query()->create(array_merge($meta, [
                'journal_entry_id' => $entry->id,
                'alt_text' => $validator->validated()['alt_text'] ?? null,
                'caption' => $validator->validated()['caption'] ?? null,
                'sort_order' => $validator->validated()['sort_order'] ?? ($nextOrder + 1),
                'is_synced' => true,
            ]));

            return $photo;
        });

        Log::info('journal.photo.created', [
            'photo_id' => $photo->id,
            'entry_id' => $entry->id,
        ]);

        return (new JournalPhotoResource($photo))
            ->response()
            ->setStatusCode(201);
    }

    // ─── GET /api/pilgrimage/journal/photos/{id} ─────────────────────────────

    /**
     * RG-04 — Proxy stream MinIO.
     * Auth + policy vérifiés avant de streamer le binaire.
     * Cache-Control: private, max-age=3600.
     */
    public function stream(string $id): StreamedResponse|JsonResponse
    {
        /** @var JournalPhoto $photo */
        $photo = JournalPhoto::query()->findOrFail($id);

        $this->authorize('view', $photo);

        $disk = $photo->minio_disk ?: 'minio_journal';
        $path = $photo->minio_path;

        if (! Storage::disk($disk)->exists($path)) {
            Log::warning('journal.photo.missing', ['photo_id' => $id, 'path' => $path]);

            return response()->json(['message' => 'Photo introuvable.'], 404);
        }

        $stream = Storage::disk($disk)->readStream($path);
        $mimeType = $photo->mime_type ?: 'image/jpeg';

        return response()->stream(
            function () use ($stream): void {
                if (is_resource($stream)) {
                    fpassthru($stream);
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    // ─── DELETE /api/pilgrimage/journal/photos/{id} ───────────────────────────

    public function destroy(string $id): JsonResponse
    {
        /** @var JournalPhoto $photo */
        $photo = JournalPhoto::query()->findOrFail($id);

        $this->authorize('delete', $photo);

        DB::transaction(function () use ($photo): void {
            // Supprimer le fichier MinIO
            $disk = $photo->minio_disk ?: 'minio_journal';
            if (Storage::disk($disk)->exists($photo->minio_path)) {
                Storage::disk($disk)->delete($photo->minio_path);
            }

            $photo->delete();
        });

        Log::info('journal.photo.deleted', ['photo_id' => $id]);

        return response()->json(['message' => 'Photo supprimée.']);
    }

    // ─── PATCH /api/pilgrimage/journal/photos/{id}/revoke-location ───────────

    /**
     * RGPD-U05 — Art. 17 partiel — Révocation de la géolocalisation d'une photo.
     *
     * Efface les coordonnées GPS (latitude/longitude) d'une photo déjà uploadée
     * sans supprimer la photo elle-même. Le consentement `keep_location` est
     * transitoire à l'upload (non persisté) ; cet endpoint permet la révocation
     * a posteriori conformément à l'Art. 7.3 RGPD.
     *
     * Seul le propriétaire de l'entrée parente peut révoquer la géoloc.
     * (Contrôle via JournalPhotoPolicy::delete, réutilisé pour la modification.)
     */
    public function revokeLocation(string $id): JsonResponse
    {
        /** @var JournalPhoto $photo */
        $photo = JournalPhoto::query()->findOrFail($id);

        $this->authorize('delete', $photo);

        if ($photo->latitude === null && $photo->longitude === null) {
            return response()->json(['message' => 'Aucune coordonnée GPS à supprimer pour cette photo.']);
        }

        DB::transaction(function () use ($photo): void {
            $photo->update([
                'latitude' => null,
                'longitude' => null,
            ]);
        });

        Log::info('rgpd.photo.location_revoked', ['photo_id' => $id]);

        return response()->json(['message' => 'Coordonnées GPS supprimées.']);
    }
}
