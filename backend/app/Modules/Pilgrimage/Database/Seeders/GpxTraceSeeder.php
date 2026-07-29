<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Services\GpxImportService;
use App\Modules\Pilgrimage\Support\GpxXmlParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Importe les fichiers GPX réels (Ultreiataku/gpx/reel/jours/BE-*.gpx)
 * vers MinIO minio_gpx et crée les enregistrements GpxTrace.
 *
 * Stratégie :
 *   1. Copier le fichier dans storage/seeds/gpx/ (chemin local de fallback).
 *   2. Tenter l'import MinIO via GpxImportService.
 *   3. Si MinIO indisponible → créer l'enregistrement avec minio_path/disk=null,
 *      log warning — seed non bloquant.
 *
 * ULTREIA-14 : skip gracieux si MinIO down, warning visible, aucune exception.
 */
class GpxTraceSeeder extends Seeder
{
    /**
     * Mapping stage code → fichier GPX source (relatif au dossier gpx/reel/jours/).
     * BE-01 absent : pas de fichier GPX disponible pour cette étape.
     */
    private const GPX_MAPPING = [
        'BE-02' => 'BE-02-Huy.gpx',
        'BE-03' => 'BE-03-Andenne.gpx',
        'BE-04' => 'BE-04-Namur.gpx',
        'BE-05' => 'BE-05-Yvoir.gpx',
        'BE-06' => 'BE-06-Dinant.gpx',
        'BE-07' => 'BE-07-Hastiere.gpx',
        'BE-08' => 'BE-08-Givet.gpx',
        'BE-09' => 'BE-09-Doische.gpx',
        'BE-10' => 'BE-10-Olloy.gpx',
        'BE-11' => 'BE-11-Couvin.gpx',
        'BE-12' => 'BE-12-Rocroi.gpx',
    ];

    private const GPX_SOURCE_SUBPATH = 'gpx/reel/jours';

    public function __construct(
        private GpxImportService $gpxImport,
    ) {}

    public function run(): void
    {
        $stages = Stage::all()->keyBy('code');

        if ($stages->isEmpty()) {
            $this->command->error('Stages manquants. Exécutez StageSeeder d\'abord.');

            return;
        }

        // Résoudre le dossier source GPX : ../gpx/reel/jours
        // depuis backend/ (qui est à Ultreiataku/backend/)
        $backendPath = base_path();
        $gpxSourceDir = $backendPath . '/../' . self::GPX_SOURCE_SUBPATH;
        $resolvedSourceDir = realpath($gpxSourceDir) ?: $gpxSourceDir;

        // Préparer le dossier local de fallback
        $seedGpxDir = storage_path('seeds/gpx');
        if (! is_dir($seedGpxDir)) {
            mkdir($seedGpxDir, 0755, true);
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach (self::GPX_MAPPING as $stageCode => $filename) {
            $stage = $stages->get($stageCode);

            if ($stage === null) {
                $this->command->warn("Étape {$stageCode} non trouvée en base, skip GPX {$filename}.");
                $skipped++;

                continue;
            }

            // Sauter si une trace principale existe déjà
            if ($stage->gpxTraces()->where('trace_type', 'stage_main')->exists()) {
                $this->command->line("  GPX {$stageCode} déjà importé, skip.");
                $skipped++;

                continue;
            }

            $sourcePath = $resolvedSourceDir . DIRECTORY_SEPARATOR . $filename;
            $destPath = $seedGpxDir . DIRECTORY_SEPARATOR . $filename;

            // Copier dans storage/seeds/gpx/ si pas déjà là
            if (! file_exists($destPath)) {
                if (file_exists($sourcePath)) {
                    copy($sourcePath, $destPath);
                } else {
                    $this->command->warn("Fichier source GPX introuvable : {$sourcePath}. Skip {$stageCode}.");
                    $skipped++;

                    continue;
                }
            }

            try {
                $gpxContent = file_get_contents($destPath);

                if ($gpxContent === false || empty(trim($gpxContent))) {
                    $this->command->warn("Fichier GPX vide : {$filename}. Skip {$stageCode}.");
                    $skipped++;

                    continue;
                }

                $trace = $this->gpxImport->importFromLocalPath($destPath, [
                    'stage_id' => $stage->id,
                    'stage_code' => $stageCode,
                    'trace_type' => 'stage_main',
                    'precision' => 'exact',
                    'name' => "Étape {$stageCode}",
                    'source' => $filename,
                    'minio_disk' => 'minio_gpx',
                ]);

                $this->command->info("  {$stageCode} : GPX importé → {$trace->id}");
                $imported++;

            } catch (\Throwable $e) {
                Log::warning('GpxTraceSeeder: import failed', [
                    'stage' => $stageCode,
                    'file' => $filename,
                    'error' => $e->getMessage(),
                ]);

                $this->command->warn("  {$stageCode} : exception ({$e->getMessage()}). Trace sans MinIO créée.");

                try {
                    $this->createFallbackTrace($stage, (string) file_get_contents($destPath), $filename, $stageCode);
                } catch (\Throwable $fallbackError) {
                    $this->command->error("  {$stageCode} : fallback échoué — {$fallbackError->getMessage()}");
                    $errors++;

                    continue;
                }

                $skipped++;
            }
        }

        $this->command->info(sprintf(
            'GpxTraceSeeder : %d importés, %d ignorés/fallback, %d erreurs.',
            $imported,
            $skipped,
            $errors,
        ));
    }

    /**
     * Crée un enregistrement GpxTrace minimal sans chemin MinIO.
     * Le proxy /api/pilgrimage/gpx/{id} utilisera le fallback storage/seeds/gpx/.
     */
    private function createFallbackTrace(
        Stage $stage,
        string $gpxContent,
        string $filename,
        string $stageCode,
    ): void {
        $parser = new GpxXmlParser;
        $parsed = $parser->parse($gpxContent);

        GpxTrace::updateOrCreate(
            ['stage_id' => $stage->id, 'trace_type' => 'stage_main'],
            [
                'waypoint_id' => null,
                'trace_type' => 'stage_main',
                'precision' => 'approximate',
                'name' => "Étape {$stageCode}",
                'minio_path' => null,
                'minio_disk' => null,
                'source' => $filename,
                'distance_km' => $parsed['distance_km'] ?? null,
                'elevation_gain_m' => $parsed['elevation_gain_m'] ?? null,
                'elevation_loss_m' => $parsed['elevation_loss_m'] ?? null,
                'track_points_count' => $parsed['track_points_count'] ?? null,
                'imported_at' => now(),
            ],
        );

        Log::info('GpxTraceSeeder: fallback trace created', ['stage' => $stageCode]);
    }
}
