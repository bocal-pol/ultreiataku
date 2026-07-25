<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Services\GpxSimplificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Proxy GPX — RG-04 + ADR-U02.
 * Toutes les traces GPX sont servies via ce proxy, jamais via URL directe MinIO.
 *
 * TODO ULTREIA-03 : brancher auth:passport + vérification membership
 * quand le SSO sera intégré. En V1a, lecture publique acceptable.
 */
class GpxTraceController extends Controller
{
    public function __construct(
        private readonly GpxSimplificationService $simplification,
    ) {}

    /**
     * GET /api/pilgrimage/gpx/{id}
     * Stream binaire GPX depuis MinIO (application/gpx+xml).
     * Fallback sur storage/seeds/gpx/{source} si MinIO indisponible ou minio_path null.
     */
    public function stream(string $id): StreamedResponse|JsonResponse
    {
        $trace = GpxTrace::find($id);

        if ($trace === null) {
            return response()->json([
                'type' => 'https://ultreiataku.example/errors/not-found',
                'title' => 'GPX trace not found',
                'status' => 404,
            ], 404);
        }

        $stream = null;

        // Tenter MinIO si les informations sont disponibles
        if ($trace->minio_disk !== null && $trace->minio_path !== null) {
            try {
                $stream = Storage::disk($trace->minio_disk)->readStream($trace->minio_path);
            } catch (\Throwable $e) {
                Log::warning('GPX MinIO stream échoué, tentative fallback local', [
                    'trace_id' => $trace->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback sur stockage local (seeds ou minio_path selon disponible)
        if (! is_resource($stream)) {
            $localFilename = $trace->source ?? basename((string) ($trace->minio_path ?? ''));
            $localPath = storage_path('seeds/gpx/' . $localFilename);

            if (empty($localFilename) || ! file_exists($localPath)) {
                Log::error('GPX stream introuvable (MinIO + local)', [
                    'trace_id' => $trace->id,
                    'source' => $trace->source,
                    'minio_path' => $trace->minio_path,
                ]);

                return response()->json([
                    'type' => 'https://ultreiataku.example/errors/gpx-unavailable',
                    'title' => 'GPX file unavailable',
                    'status' => 404,
                    'detail' => 'La trace GPX est introuvable.',
                ], 404);
            }

            $stream = fopen($localPath, 'r');
        }

        $filename = $trace->source
            ?? basename((string) ($trace->minio_path ?? 'trace.gpx'));

        return response()->stream(
            function () use ($stream): void {
                if (is_resource($stream)) {
                    fpassthru($stream);
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => 'application/gpx+xml',
                'Content-Disposition' => "inline; filename=\"{$filename}\"",
                'Cache-Control' => 'private, max-age=3600',
            ],
        );
    }

    /**
     * GET /api/pilgrimage/gpx/{id}/simplified
     * GeoJSON simplifié Douglas-Peucker (cache Redis TTL 24h).
     * Cache-Control: public, max-age=3600 (données lecture seule du Chemin).
     */
    public function simplified(Request $request, string $id): JsonResponse
    {
        $trace = GpxTrace::find($id);

        if ($trace === null) {
            return response()->json([
                'type' => 'https://ultreiataku.example/errors/not-found',
                'title' => 'GPX trace not found',
                'status' => 404,
            ], 404);
        }

        $tolerance = (float) $request->input(
            'tolerance',
            config('pilgrimage.gpx.simplification_tolerance', 0.0001),
        );

        try {
            $geojson = $this->simplification->simplify($trace, $tolerance);
        } catch (\Throwable $e) {
            Log::error('Erreur simplification GPX', [
                'trace_id' => $trace->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'type' => 'https://ultreiataku.example/errors/gpx-simplification-failed',
                'title' => 'GPX simplification failed',
                'status' => 503,
                'detail' => 'La simplification GPX est temporairement indisponible.',
            ], 503);
        }

        return response()
            ->json($geojson)
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
