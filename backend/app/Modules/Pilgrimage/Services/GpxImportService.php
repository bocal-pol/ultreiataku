<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Services;

use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Support\GpxXmlParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service d'import de fichiers GPX vers MinIO minio_gpx.
 * Parse les métadonnées (distance, D+, D-, points_count) à l'import.
 *
 * Incident seeder : l'ancien try/catch autour de Storage::put() avalait les erreurs MinIO
 * et créait des GpxTrace orphelines (minio_path inexistant). Supprimé — les exceptions
 * propagent maintenant correctement. Le seeder doit gérer le fallback si besoin.
 *
 * Fix ULTREIA-GPX-FR/ES : le chemin MinIO était hardcodé en "gpx/belgique/" quel que
 * soit le pays. Le sous-dossier est maintenant dérivé du préfixe du stage_code :
 *   BE-* → belgique/
 *   FR-* → france/
 *   ES-* / PC-* → espagne/
 *   autres → autres/
 */
final class GpxImportService
{
    public function __construct(
        private readonly GpxSimplificationService $simplification
    ) {}

    /**
     * Importe un fichier GPX uploadé via Filament et crée une GpxTrace.
     *
     * @param  array<string, mixed>  $attributes  Attributs complémentaires (stage_id, trace_type, name, precision, source)
     */
    public function importUploadedFile(UploadedFile $file, array $attributes): GpxTrace
    {
        $gpxContent = file_get_contents($file->getRealPath());

        if ($gpxContent === false) {
            throw new \RuntimeException('Impossible de lire le fichier GPX uploadé.');
        }

        if (! GpxXmlParser::isValid($gpxContent)) {
            throw new \InvalidArgumentException('Le fichier GPX ne contient pas de trace valide (trk ou rte requis).');
        }

        return $this->doImport($gpxContent, $attributes, $file->getClientOriginalName());
    }

    /**
     * Importe un fichier GPX depuis le stockage local (seeds).
     */
    public function importFromLocalPath(string $localPath, array $attributes): GpxTrace
    {
        if (! file_exists($localPath)) {
            throw new \RuntimeException("Fichier GPX introuvable : {$localPath}");
        }

        $gpxContent = file_get_contents($localPath);

        if ($gpxContent === false) {
            throw new \RuntimeException("Impossible de lire le fichier GPX : {$localPath}");
        }

        if (! GpxXmlParser::isValid($gpxContent)) {
            throw new \InvalidArgumentException("GPX invalide : {$localPath}");
        }

        return $this->doImport($gpxContent, $attributes, basename($localPath));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function doImport(string $gpxContent, array $attributes, string $filename): GpxTrace
    {
        $parsed = GpxXmlParser::parse($gpxContent);

        $stageCode = strtolower($attributes['stage_code'] ?? 'unknown');
        $traceType = $attributes['trace_type'] ?? 'stage_main';
        $minioDisk = $attributes['minio_disk'] ?? 'minio_gpx';

        $subfolder = $this->resolveSubfolder($attributes['stage_code'] ?? '');
        $minioPath = "gpx/{$subfolder}/{$stageCode}-{$traceType}-" . time() . '.gpx';

        // Upload vers MinIO — ne pas swallower l'exception.
        // Si MinIO est indisponible, l'exception se propage : le seeder (ou l'appelant)
        // gère le fallback via createFallbackTrace(). Cela évite les GpxTrace orphelines
        // dont minio_path pointe vers un fichier absent.
        Storage::disk($minioDisk)->put($minioPath, $gpxContent);

        Log::info('GPX uploadé sur MinIO', ['path' => $minioPath, 'disk' => $minioDisk]);

        $trace = GpxTrace::create([
            'stage_id' => $attributes['stage_id'] ?? null,
            'waypoint_id' => $attributes['waypoint_id'] ?? null,
            'trace_type' => $traceType,
            'name' => $attributes['name'] ?? $filename,
            'minio_path' => $minioPath,
            'minio_disk' => $minioDisk,
            'distance_km' => $parsed['distance_km'],
            'elevation_gain_m' => $parsed['elevation_gain_m'],
            'elevation_loss_m' => $parsed['elevation_loss_m'],
            'track_points_count' => $parsed['track_points_count'],
            'precision' => $attributes['precision'] ?? 'approximate',
            'source' => $attributes['source'] ?? null,
            'imported_at' => now(),
        ]);

        // Invalider le cache simplifié si la trace existait déjà
        $this->simplification->invalidateCache($trace);

        return $trace;
    }

    /**
     * Dérive le sous-dossier MinIO depuis le code d'étape.
     *
     *   BE-* → belgique
     *   FR-* → france
     *   ES-* / PC-* → espagne
     *   autres → autres
     */
    private function resolveSubfolder(string $stageCode): string
    {
        $prefix = strtoupper(substr($stageCode, 0, 2));

        return match ($prefix) {
            'BE' => 'belgique',
            'FR' => 'france',
            'ES', 'PC' => 'espagne',
            default => 'autres',
        };
    }
}
