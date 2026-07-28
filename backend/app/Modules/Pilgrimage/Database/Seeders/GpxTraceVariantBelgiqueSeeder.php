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
 * Importe les GPX des étapes-variantes Belgique et la branche Bruxelles vers MinIO.
 *
 * Sources :
 *   - gpx/reel/jours/BE-J3-variante-Scladina.gpx
 *   - gpx/reel/jours/BE-J5-variante-Poilvache.gpx
 *   - gpx/reel/jours/BE-J7-variante-Chateau-Thierry.gpx
 *   - gpx/reel/jours/BE-J10-variante-Roche-a-Lomme.gpx
 *   - gpx/reel/jours/BE-J11-variante-Grotte-de-Neptune.gpx
 *   - gpx/reel/compagnon/bruxelles-namur.gpx
 *
 * Idempotent via updateOrCreate sur (stage_id, trace_type='stage_variant').
 * Skip gracieux si MinIO down (ULTREIA-14).
 */
class GpxTraceVariantBelgiqueSeeder extends Seeder
{
    /**
     * Mapping stage code → fichier GPX source (chemin relatif depuis backend/../).
     * Les variantes utilisent trace_type='stage_variant' pour ne pas conflictuer
     * avec les traces principales (trace_type='stage_main').
     */
    private const GPX_VARIANT_MAPPING = [
        'BE-03V-SCLADINA' => [
            'file' => 'gpx/reel/jours/BE-J3-variante-Scladina.gpx',
            'name' => 'Variante J3 — Grotte Scladina',
        ],
        'BE-05V-POILVACHE' => [
            'file' => 'gpx/reel/jours/BE-J5-variante-Poilvache.gpx',
            'name' => 'Variante J5 — Forteresse Poilvache',
        ],
        'BE-07V-CHATEAU-THIERRY' => [
            'file' => 'gpx/reel/jours/BE-J7-variante-Chateau-Thierry.gpx',
            'name' => 'Variante J7 — Château-Thierry',
        ],
        'BE-10V-ROCHE-A-LOMME' => [
            'file' => 'gpx/reel/jours/BE-J10-variante-Roche-a-Lomme.gpx',
            'name' => 'Variante J10 — Roche à Lomme',
        ],
        'BE-11V-GROTTE-NEPTUNE' => [
            'file' => 'gpx/reel/jours/BE-J11-variante-Grotte-de-Neptune.gpx',
            'name' => 'Variante J11 — Grotte de Neptune',
        ],
        'BE-BXL-NAMUR' => [
            'file' => 'gpx/reel/compagnon/bruxelles-namur.gpx',
            'name' => 'Branche Bruxelles → Namur',
        ],
    ];

    public function __construct(
        private GpxImportService $gpxImport,
    ) {}

    public function run(): void
    {
        $stages = Stage::whereIn('code', array_keys(self::GPX_VARIANT_MAPPING))
            ->get()
            ->keyBy('code');

        if ($stages->isEmpty()) {
            $this->command->error('Variantes manquantes. Exécutez StageVariantBelgiqueSeeder d\'abord.');

            return;
        }

        $backendPath = base_path();
        $gpxRootDir = realpath($backendPath . '/..') ?: ($backendPath . '/..');

        $seedGpxDir = storage_path('seeds/gpx/variants');
        if (! is_dir($seedGpxDir)) {
            mkdir($seedGpxDir, 0755, true);
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach (self::GPX_VARIANT_MAPPING as $stageCode => $meta) {
            $stage = $stages->get($stageCode);

            if ($stage === null) {
                $this->command->warn("Étape {$stageCode} non trouvée en base, skip GPX.");
                $skipped++;

                continue;
            }

            // Idempotence : skip si trace variante déjà importée
            if ($stage->gpxTraces()->where('trace_type', 'stage_variant')->exists()) {
                $this->command->line("  GPX {$stageCode} (variante) déjà importé, skip.");
                $skipped++;

                continue;
            }

            $sourcePath = $gpxRootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $meta['file']);
            $filename = basename($meta['file']);
            $destPath = $seedGpxDir . DIRECTORY_SEPARATOR . $filename;

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
                    'trace_type' => 'stage_variant',
                    'precision' => 'exact',
                    'name' => $meta['name'],
                    'source' => $meta['file'],
                    'minio_disk' => 'minio_gpx',
                ]);

                $this->command->info("  {$stageCode} : GPX variante importé → {$trace->id}");
                $imported++;

            } catch (\Throwable $e) {
                Log::warning('GpxTraceVariantBelgiqueSeeder: import failed', [
                    'stage' => $stageCode,
                    'file' => $meta['file'],
                    'error' => $e->getMessage(),
                ]);

                $this->command->warn("  {$stageCode} : exception ({$e->getMessage()}). Trace sans MinIO créée.");

                try {
                    $this->createFallbackTrace(
                        $stage,
                        (string) file_get_contents($destPath),
                        $filename,
                        $stageCode,
                        $meta['name'],
                        $meta['file'],
                    );
                } catch (\Throwable $fallbackError) {
                    $this->command->error("  {$stageCode} : fallback échoué — {$fallbackError->getMessage()}");
                    $errors++;

                    continue;
                }

                $skipped++;
            }
        }

        $this->command->info(sprintf(
            'GpxTraceVariantBelgiqueSeeder : %d importés, %d ignorés/fallback, %d erreurs.',
            $imported,
            $skipped,
            $errors,
        ));
    }

    private function createFallbackTrace(
        Stage $stage,
        string $gpxContent,
        string $filename,
        string $stageCode,
        string $name,
        string $sourcePath,
    ): void {
        $parser = new GpxXmlParser;
        $parsed = $parser->parse($gpxContent);

        GpxTrace::updateOrCreate(
            ['stage_id' => $stage->id, 'trace_type' => 'stage_variant'],
            [
                'waypoint_id' => null,
                'trace_type' => 'stage_variant',
                'precision' => 'approximate',
                'name' => $name,
                'minio_path' => null,
                'minio_disk' => null,
                'source' => $sourcePath,
                'distance_km' => $parsed['distance_km'] ?? null,
                'elevation_gain_m' => $parsed['elevation_gain_m'] ?? null,
                'elevation_loss_m' => $parsed['elevation_loss_m'] ?? null,
                'track_points_count' => $parsed['track_points_count'] ?? null,
                'imported_at' => now(),
            ],
        );

        Log::info('GpxTraceVariantBelgiqueSeeder: fallback trace created', ['stage' => $stageCode]);
    }
}
