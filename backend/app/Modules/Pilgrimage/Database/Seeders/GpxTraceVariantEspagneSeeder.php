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
 * Importe les traces GPX des étapes-variantes Espagne vers MinIO.
 *
 * Sources :
 *   ES-J15-variante-Faro-del-Caballo.gpx      → ES-15V-FARO-CABALLO
 *   ES-J19-variante-Altamira.gpx               → ES-18V-ALTAMIRA
 *   ES-J19b-variante-familiale-Cohicillos.gpx  → ES-18V-COHICILLOS
 *   ES-J21-variante-Picos-Liebana.gpx          → ES-20V-PICOS-LIEBANA
 *   ES-J24-variante-Gulpiyuri.gpx              → ES-23V-GULPIYURI
 *   ES-J25-variante-Picos-Covadonga.gpx        → ES-24V-PICOS-COVADONGA
 *   ES-J31-variante-Cudillero.gpx              → ES-28V-CUDILLERO
 *   PC-J5-variante-Bulnes-Urriellu.gpx         → PC-05V-BULNES-URRIELLU
 *   PC-J7-variante-Montagne.gpx                → PC-07V-MONTAGNE
 *
 * trace_type = 'stage_variant'
 * Idempotent : skip si trace variante déjà importée.
 * Skip gracieux si MinIO down (ULTREIA-14).
 */
class GpxTraceVariantEspagneSeeder extends Seeder
{
    private const GPX_VARIANT_MAPPING = [
        'ES-15V-FARO-CABALLO' => [
            'file' => 'gpx/reel/jours/ES-J15-variante-Faro-del-Caballo.gpx',
            'name' => 'Variante J14 — Faro del Caballo (Monte Buciero, 763 marches)',
        ],
        'ES-18V-ALTAMIRA' => [
            'file' => 'gpx/reel/jours/ES-J19-variante-Altamira.gpx',
            'name' => 'Variante J18 repos — Grottes d\'Altamira (excursion)',
        ],
        'ES-18V-COHICILLOS' => [
            'file' => 'gpx/reel/jours/ES-J19b-variante-familiale-Cohicillos.gpx',
            'name' => 'Variante J17-J18 familiale — Cohicillos (village du grand-père)',
        ],
        'ES-20V-PICOS-LIEBANA' => [
            'file' => 'gpx/reel/jours/ES-J21-variante-Picos-Liebana.gpx',
            'name' => 'Variante J20 — Module Picos Liébana (San Vicente → Potes)',
        ],
        'ES-23V-GULPIYURI' => [
            'file' => 'gpx/reel/jours/ES-J24-variante-Gulpiyuri.gpx',
            'name' => 'Variante J23 — Playa de Gulpiyuri (600 m détour)',
        ],
        'ES-24V-PICOS-COVADONGA' => [
            'file' => 'gpx/reel/jours/ES-J25-variante-Picos-Covadonga.gpx',
            'name' => 'Variante J24 — Module Picos Covadonga (depuis Ribadesella)',
        ],
        'ES-28V-CUDILLERO' => [
            'file' => 'gpx/reel/jours/ES-J31-variante-Cudillero.gpx',
            'name' => 'Variante J28 — Cudillero (+2 km détour village-amphithéâtre)',
        ],
        'PC-05V-BULNES-URRIELLU' => [
            'file' => 'gpx/reel/jours/PC-J5-variante-Bulnes-Urriellu.gpx',
            'name' => 'Variante PC-05 — Bulnes + Naranjo de Bulnes (Urriellu)',
        ],
        'PC-07V-MONTAGNE' => [
            'file' => 'gpx/reel/jours/PC-J7-variante-Montagne.gpx',
            'name' => 'Variante PC-07 — Lacs Enol/Ercina (haute route Covadonga)',
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
            $this->command->error('Variantes ES manquantes. Exécutez StageVariantEspagneSeeder d\'abord.');

            return;
        }

        $backendPath = base_path();
        $gpxRootDir = realpath($backendPath . '/..') ?: ($backendPath . '/..');

        $seedGpxDir = storage_path('seeds/gpx/variants-espagne');
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
                    $this->command->warn("Fichier GPX introuvable : {$sourcePath}. Skip {$stageCode}.");
                    $skipped++;

                    continue;
                }
            }

            try {
                $gpxContent = file_get_contents($destPath);

                if ($gpxContent === false || empty(trim((string) $gpxContent))) {
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
                Log::warning('GpxTraceVariantEspagneSeeder: import failed', [
                    'stage' => $stageCode,
                    'file' => $meta['file'],
                    'error' => $e->getMessage(),
                ]);

                $this->command->warn("  {$stageCode} : exception ({$e->getMessage()}). Trace fallback créée.");

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
            'GpxTraceVariantEspagneSeeder : %d importés, %d ignorés/fallback, %d erreurs.',
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

        Log::info('GpxTraceVariantEspagneSeeder: fallback trace created', ['stage' => $stageCode]);
    }
}
