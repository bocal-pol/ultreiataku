<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Services;

use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Support\DouglasPeucker;
use App\Modules\Pilgrimage\Support\GpxXmlParser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service de simplification GPX — RG-06 + ADR-U05.
 *
 * Retourne un GeoJSON FeatureCollection avec la LineString simplifiée
 * mise en cache Redis (clé : pilgrimage:gpx:{id}:simplified:{tolerance}).
 */
final class GpxSimplificationService
{
    private const CACHE_TTL_SECONDS = 86400; // 24h (ADR-U05)

    /**
     * @return array<string, mixed> GeoJSON FeatureCollection
     */
    public function simplify(GpxTrace $trace, float $tolerance = 0.0001): array
    {
        $cacheKey = "pilgrimage:gpx:{$trace->id}:simplified:{$tolerance}";

        $result = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($trace, $tolerance) {
            return $this->computeSimplified($trace, $tolerance);
        });

        /** @var array<string, mixed> $result */
        return $result;
    }

    /**
     * Invalide tous les caches simplifiés pour une trace donnée (ADR-U05 Observer).
     */
    public function invalidateCache(GpxTrace $trace): void
    {
        try {
            $redis = Cache::getRedis();
            $prefix = config('cache.prefix', 'ultreiataku_cache');
            $pattern = "{$prefix}:pilgrimage:gpx:{$trace->id}:simplified:*";

            $keys = $redis->keys($pattern);
            if (! empty($keys)) {
                $redis->del($keys);
                Log::info('GPX cache invalidé', ['trace_id' => $trace->id, 'keys_deleted' => count($keys)]);
            }
        } catch (\Throwable $e) {
            Log::warning('Echec invalidation cache GPX', [
                'trace_id' => $trace->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function computeSimplified(GpxTrace $trace, float $tolerance): array
    {
        $gpxContent = $this->readGpxContent($trace);

        $parsed = GpxXmlParser::parse($gpxContent);

        /** @var array<int, array{0: float, 1: float}> $points2d */
        $points2d = array_map(
            static fn (array $p): array => [(float) $p['lat'], (float) $p['lon']],
            $parsed['points'],
        );

        $simplified = DouglasPeucker::simplify($points2d, $tolerance);

        // Format GeoJSON LineString — coordonnées [lon, lat] (convention GeoJSON)
        $coordinates = array_map(
            static fn (array $p): array => [$p[1], $p[0]],
            $simplified,
        );

        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => $coordinates,
                    ],
                    'properties' => [
                        'trace_id' => $trace->id,
                        'name' => $trace->name,
                        'trace_type' => $trace->trace_type->value,
                        'distance_km' => $trace->distance_km,
                        'elevation_gain_m' => $trace->elevation_gain_m,
                        'elevation_loss_m' => $trace->elevation_loss_m,
                        'points_count_original' => $parsed['track_points_count'],
                        'points_count_simplified' => count($simplified),
                        'tolerance' => $tolerance,
                        'color' => $trace->trace_type->color(),
                    ],
                ],
            ],
        ];
    }

    /**
     * Lit le contenu GPX — priorité MinIO, fallback local (seeds/gpx/{source}).
     */
    private function readGpxContent(GpxTrace $trace): string
    {
        // Tentative MinIO si les métadonnées sont disponibles
        if ($trace->minio_disk !== null && $trace->minio_path !== null) {
            try {
                $content = Storage::disk($trace->minio_disk)->get($trace->minio_path);
                if ($content !== null && $content !== false) {
                    return $content;
                }
            } catch (\Throwable $e) {
                Log::warning('GpxSimplificationService: MinIO indisponible, fallback local', [
                    'trace_id' => $trace->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback fichier local seeds/gpx/{source}
        $localFilename = $trace->source ?? basename((string) ($trace->minio_path ?? ''));

        if (! empty($localFilename)) {
            $localPath = storage_path('seeds/gpx/' . $localFilename);
            if (file_exists($localPath)) {
                $content = file_get_contents($localPath);
                if ($content !== false) {
                    Log::info('GPX servi depuis stockage local (MinIO indisponible)', [
                        'trace_id' => $trace->id,
                        'source' => $localFilename,
                    ]);

                    return $content;
                }
            }
        }

        throw new \RuntimeException(
            "Contenu GPX introuvable pour la trace {$trace->id} (MinIO + local).",
        );
    }
}
