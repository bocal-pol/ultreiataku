<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Services\GpxImportService;
use App\Modules\Pilgrimage\Support\GpxXmlParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Importe les 40 fichiers GPX FR (gpx/reel/jours/FR-*.gpx) + la variante Faux de Verzy.
 *
 * Mapping stage code → GPX filename (répertoire gpx/reel/jours/).
 * Skip gracieux si fichier absent ou MinIO down (ULTREIA-14).
 *
 * Sources vérifiées : 40 GPX FR-01 → FR-40 + 1 variante FR-J7-variante-Faux-de-Verzy.gpx
 */
class GpxTraceSeederFrance extends Seeder
{
    /**
     * Étapes principales FR-01 → FR-40.
     *
     * @var array<string, string>
     */
    private const GPX_MAPPING = [
        'FR-01' => 'FR-01-Signy.gpx',
        'FR-02' => 'FR-02-Chateau-Porcien.gpx',
        'FR-03' => 'FR-03-Reims.gpx',
        'FR-04' => 'FR-04-Verzy.gpx',
        'FR-05' => 'FR-05-Chalons.gpx',
        'FR-06' => 'FR-06-Vitry.gpx',
        'FR-07' => 'FR-07-Giffaumont.gpx',
        'FR-08' => 'FR-08-Montier.gpx',
        'FR-09' => 'FR-09-Bar-sur-Seine.gpx',
        'FR-10' => 'FR-10-Les-Riceys.gpx',
        'FR-11' => 'FR-11-Tonnerre.gpx',
        'FR-12' => 'FR-12-Chablis.gpx',
        'FR-13' => 'FR-13-Auxerre.gpx',
        'FR-14' => 'FR-14-Arcy.gpx',
        'FR-15' => 'FR-15-Vezelay.gpx',
        'FR-16' => 'FR-16-Clamecy.gpx',
        'FR-17' => 'FR-17-La-Charite.gpx',
        'FR-18' => 'FR-18-Bourges.gpx',
        'FR-19' => 'FR-19-Issoudun.gpx',
        'FR-20' => 'FR-20-Chateauroux.gpx',
        'FR-21' => 'FR-21-Argenton.gpx',
        'FR-22' => 'FR-22-La-Souterraine.gpx',
        'FR-23' => 'FR-23-Le-Dorat.gpx',
        'FR-24' => 'FR-24-Bellac.gpx',
        'FR-25' => 'FR-25-Oradour.gpx',
        'FR-26' => 'FR-26-Limoges.gpx',
        'FR-27' => 'FR-27-Chalus.gpx',
        'FR-28' => 'FR-28-Thiviers.gpx',
        'FR-29' => 'FR-29-Perigueux.gpx',
        'FR-30' => 'FR-30-Sainte-Foy.gpx',
        'FR-31' => 'FR-31-La-Reole.gpx',
        'FR-32' => 'FR-32-Bazas.gpx',
        'FR-33' => 'FR-33-Captieux.gpx',
        'FR-34' => 'FR-34-Mont-de-Marsan.gpx',
        'FR-35' => 'FR-35-Saint-Sever.gpx',
        'FR-36' => 'FR-36-Orthez.gpx',
        'FR-37' => 'FR-37-Sauveterre.gpx',
        'FR-38' => 'FR-38-Saint-Palais.gpx',
        'FR-39' => 'FR-39-Ostabat.gpx',
        'FR-40' => 'FR-40-SJPP.gpx',
    ];

    /**
     * Variante Faux de Verzy.
     *
     * @var array<string, array<string, string>>
     */
    private const GPX_VARIANT_MAPPING = [
        'FR-04V-FAUX-VERZY' => [
            'file' => 'FR-J7-variante-Faux-de-Verzy.gpx',
            'name' => 'Variante — Boucle des Faux de Verzy',
        ],
    ];

    private const GPX_SOURCE_SUBPATH = 'gpx/reel/jours';

    public function __construct(
        private GpxImportService $gpxImport,
    ) {}

    public function run(): void
    {
        $stages = Stage::all()->keyBy('code');

        if ($stages->isEmpty()) {
            $this->command->error('Stages manquants. Exécutez StageSeederFrance d\'abord.');

            return;
        }

        $backendPath = base_path();
        $gpxSourceDir = realpath($backendPath . '/../' . self::GPX_SOURCE_SUBPATH) ?: ($backendPath . '/../' . self::GPX_SOURCE_SUBPATH);

        $seedGpxDir = storage_path('seeds/gpx/france');

        if (! is_dir($seedGpxDir)) {
            mkdir($seedGpxDir, 0755, true);
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        // ── Étapes principales ───────────────────────────────────────────────

        foreach (self::GPX_MAPPING as $stageCode => $filename) {
            [$ok, $result] = $this->importOne(
                $stages,
                $stageCode,
                $filename,
                $gpxSourceDir,
                $seedGpxDir,
                'stage_main',
                "Étape {$stageCode}",
            );

            match ($result) {
                'imported' => $imported++,
                'skipped' => $skipped++,
                'error' => $errors++,
                default => null,
            };
        }

        // ── Variante Faux de Verzy ────────────────────────────────────────────

        foreach (self::GPX_VARIANT_MAPPING as $stageCode => $meta) {
            [$ok, $result] = $this->importOne(
                $stages,
                $stageCode,
                $meta['file'],
                $gpxSourceDir,
                $seedGpxDir,
                'stage_variant',
                $meta['name'],
            );

            match ($result) {
                'imported' => $imported++,
                'skipped' => $skipped++,
                'error' => $errors++,
                default => null,
            };
        }

        $this->command->info(sprintf(
            'GpxTraceSeederFrance : %d importés, %d ignorés/fallback, %d erreurs.',
            $imported,
            $skipped,
            $errors,
        ));
    }

    /**
     * @param  Collection<string, Stage>  $stages
     * @return array{bool, string}
     */
    private function importOne(
        Collection $stages,
        string $stageCode,
        string $filename,
        string $gpxSourceDir,
        string $seedGpxDir,
        string $traceType,
        string $name,
    ): array {
        $stage = $stages->get($stageCode);

        if ($stage === null) {
            $this->command->warn("Étape {$stageCode} non trouvée en base, skip GPX {$filename}.");

            return [false, 'skipped'];
        }

        // Idempotence
        if ($stage->gpxTraces()->where('trace_type', $traceType)->exists()) {
            $this->command->line("  GPX {$stageCode} ({$traceType}) déjà importé, skip.");

            return [true, 'skipped'];
        }

        $sourcePath = $gpxSourceDir . DIRECTORY_SEPARATOR . $filename;
        $destPath = $seedGpxDir . DIRECTORY_SEPARATOR . $filename;

        if (! file_exists($destPath)) {
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destPath);
            } else {
                $this->command->warn("Fichier GPX introuvable : {$sourcePath}. Skip {$stageCode}.");

                return [false, 'skipped'];
            }
        }

        $gpxContent = file_get_contents($destPath);

        if ($gpxContent === false || empty(trim($gpxContent))) {
            $this->command->warn("Fichier GPX vide : {$filename}. Skip {$stageCode}.");

            return [false, 'skipped'];
        }

        try {
            $trace = $this->gpxImport->importFromLocalPath($destPath, [
                'stage_id' => $stage->id,
                'stage_code' => $stageCode,
                'trace_type' => $traceType,
                'precision' => 'exact',
                'name' => $name,
                'source' => $filename,
                'minio_disk' => 'minio_gpx',
            ]);

            $this->command->info("  {$stageCode} : GPX importé → {$trace->id}");

            return [true, 'imported'];

        } catch (\Throwable $e) {
            Log::warning('GpxTraceSeederFrance: import failed', [
                'stage' => $stageCode,
                'file' => $filename,
                'error' => $e->getMessage(),
            ]);

            $this->command->warn("  {$stageCode} : exception ({$e->getMessage()}). Trace sans MinIO créée.");

            try {
                $this->createFallbackTrace($stage, $gpxContent, $filename, $stageCode, $name, $traceType);

                return [false, 'skipped'];
            } catch (\Throwable $fallbackError) {
                $this->command->error("  {$stageCode} : fallback échoué — {$fallbackError->getMessage()}");

                return [false, 'error'];
            }
        }
    }

    private function createFallbackTrace(
        Stage $stage,
        string $gpxContent,
        string $filename,
        string $stageCode,
        string $name,
        string $traceType,
    ): void {
        $parser = new GpxXmlParser;
        $parsed = $parser->parse($gpxContent);

        GpxTrace::updateOrCreate(
            ['stage_id' => $stage->id, 'trace_type' => $traceType],
            [
                'waypoint_id' => null,
                'trace_type' => $traceType,
                'precision' => 'approximate',
                'name' => $name,
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

        Log::info('GpxTraceSeederFrance: fallback trace created', ['stage' => $stageCode, 'type' => $traceType]);
    }
}
