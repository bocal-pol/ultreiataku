<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Services\GpxImportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Importe les traces GPX des étapes principales Espagne vers MinIO.
 *
 * Sources :
 *   gpx/reel/jours/ES-01-Bidarray.gpx … ES-39-Santiago.gpx  (39 fichiers)
 *   gpx/reel/jours/PC-01-Cades.gpx    … PC-09-Ribadesella.gpx (9 fichiers)
 *
 * trace_type = 'stage_main'
 * disk MinIO = 'minio_gpx'
 * Idempotent : skip si trace déjà importée pour l'étape.
 *
 * Politique fail-fast (fix ULTREIA-GPX-ES) :
 *   - Si le fichier source est ABSENT → skip + log warning.
 *   - Si le fichier source existe mais que l'upload MinIO échoue → exception propagée,
 *     comptée en erreur. Aucun fallback silencieux ne masque les pannes MinIO.
 */
class GpxTraceSeederEspagne extends Seeder
{
    /**
     * Mapping code étape → fichier GPX source.
     * Chemin relatif depuis la racine monorepo (backend/../).
     */
    private const GPX_MAPPING = [
        // Camino del Norte — 39 étapes ES
        'ES-01' => 'gpx/reel/jours/ES-01-Bidarray.gpx',
        'ES-02' => 'gpx/reel/jours/ES-02-Itxassou.gpx',
        'ES-03' => 'gpx/reel/jours/ES-03-Ascain.gpx',
        'ES-04' => 'gpx/reel/jours/ES-04-Irun.gpx',
        'ES-05' => 'gpx/reel/jours/ES-05-San-Sebastian.gpx',
        'ES-06' => 'gpx/reel/jours/ES-06-Zarautz.gpx',
        'ES-07' => 'gpx/reel/jours/ES-07-Deba.gpx',
        'ES-08' => 'gpx/reel/jours/ES-08-Markina.gpx',
        'ES-09' => 'gpx/reel/jours/ES-09-Gernika.gpx',
        'ES-10' => 'gpx/reel/jours/ES-10-Bilbao.gpx',
        'ES-11' => 'gpx/reel/jours/ES-11-Portugalete.gpx',
        'ES-12' => 'gpx/reel/jours/ES-12-Castro-Urdiales.gpx',
        'ES-13' => 'gpx/reel/jours/ES-13-Laredo.gpx',
        'ES-14' => 'gpx/reel/jours/ES-14-Santona.gpx',
        'ES-15' => 'gpx/reel/jours/ES-15-Guemes.gpx',
        'ES-16' => 'gpx/reel/jours/ES-16-Santander.gpx',
        'ES-17' => 'gpx/reel/jours/ES-17-Requejada.gpx',
        'ES-18' => 'gpx/reel/jours/ES-18-Santillana.gpx',
        'ES-19' => 'gpx/reel/jours/ES-19-Comillas.gpx',
        'ES-20' => 'gpx/reel/jours/ES-20-San-Vicente.gpx',
        'ES-21' => 'gpx/reel/jours/ES-21-Colombres.gpx',
        'ES-22' => 'gpx/reel/jours/ES-22-Llanes.gpx',
        'ES-23' => 'gpx/reel/jours/ES-23-Nueva.gpx',
        'ES-24' => 'gpx/reel/jours/ES-24-Ribadesella.gpx',
        'ES-25' => 'gpx/reel/jours/ES-25-Villaviciosa.gpx',
        'ES-26' => 'gpx/reel/jours/ES-26-Gijon.gpx',
        'ES-27' => 'gpx/reel/jours/ES-27-Aviles.gpx',
        'ES-28' => 'gpx/reel/jours/ES-28-Soto-de-Luina.gpx',
        'ES-29' => 'gpx/reel/jours/ES-29-Luarca.gpx',
        'ES-30' => 'gpx/reel/jours/ES-30-Navia.gpx',
        'ES-31' => 'gpx/reel/jours/ES-31-Ribadeo.gpx',
        'ES-32' => 'gpx/reel/jours/ES-32-Lourenza.gpx',
        'ES-33' => 'gpx/reel/jours/ES-33-Mondonedo.gpx',
        'ES-34' => 'gpx/reel/jours/ES-34-Vilalba.gpx',
        'ES-35' => 'gpx/reel/jours/ES-35-Miraz.gpx',
        'ES-36' => 'gpx/reel/jours/ES-36-Sobrado.gpx',
        'ES-37' => 'gpx/reel/jours/ES-37-Arzua.gpx',
        'ES-38' => 'gpx/reel/jours/ES-38-O-Pedrouzo.gpx',
        'ES-39' => 'gpx/reel/jours/ES-39-Santiago.gpx',
        // Module Picos de Europa — 9 étapes PC
        'PC-01' => 'gpx/reel/jours/PC-01-Cades.gpx',
        'PC-02' => 'gpx/reel/jours/PC-02-Cabanes.gpx',
        'PC-03' => 'gpx/reel/jours/PC-03-Potes.gpx',
        'PC-04' => 'gpx/reel/jours/PC-04-Fuente-De.gpx',
        'PC-05' => 'gpx/reel/jours/PC-05-Sotres.gpx',
        'PC-06' => 'gpx/reel/jours/PC-06-Arenas-de-Cabrales.gpx',
        'PC-07' => 'gpx/reel/jours/PC-07-Covadonga.gpx',
        'PC-08' => 'gpx/reel/jours/PC-08-Arriondas.gpx',
        'PC-09' => 'gpx/reel/jours/PC-09-Ribadesella.gpx',
    ];

    public function __construct(
        private GpxImportService $gpxImport,
    ) {}

    public function run(): void
    {
        $stages = Stage::whereIn('code', array_keys(self::GPX_MAPPING))
            ->get()
            ->keyBy('code');

        if ($stages->isEmpty()) {
            $this->command->error('Étapes ES/PC manquantes. Exécutez StageSeederEspagne d\'abord.');

            return;
        }

        $backendPath = base_path();
        $gpxRootDir = realpath($backendPath . '/..') ?: ($backendPath . '/..');

        $seedGpxDir = storage_path('seeds/gpx/espagne');
        if (! is_dir($seedGpxDir)) {
            mkdir($seedGpxDir, 0755, true);
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach (self::GPX_MAPPING as $stageCode => $relativePath) {
            $stage = $stages->get($stageCode);

            if ($stage === null) {
                $this->command->warn("Étape {$stageCode} non trouvée en base, skip.");
                $skipped++;

                continue;
            }

            // Idempotence : skip si trace principale déjà importée
            if ($stage->gpxTraces()->where('trace_type', 'stage_main')->exists()) {
                $this->command->line("  GPX {$stageCode} déjà importé, skip.");
                $skipped++;

                continue;
            }

            $sourcePath = $gpxRootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $filename = basename($relativePath);
            $destPath = $seedGpxDir . DIRECTORY_SEPARATOR . $filename;

            if (! file_exists($destPath)) {
                if (file_exists($sourcePath)) {
                    copy($sourcePath, $destPath);
                } else {
                    // Fichier source absent → skip explicite (cas normal : étape sans GPX disponible).
                    $this->command->warn("Fichier GPX introuvable : {$sourcePath}. Skip {$stageCode}.");
                    Log::warning('GpxTraceSeederEspagne: source file missing', [
                        'stage' => $stageCode,
                        'file' => $relativePath,
                        'path' => $sourcePath,
                    ]);
                    $skipped++;

                    continue;
                }
            }

            $gpxContent = file_get_contents($destPath);

            if ($gpxContent === false || empty(trim((string) $gpxContent))) {
                $this->command->warn("Fichier GPX vide : {$filename}. Skip {$stageCode}.");
                $skipped++;

                continue;
            }

            // Fail-fast : si le fichier source existe, l'upload MinIO doit réussir.
            // Toute exception se propage — aucun fallback silencieux ne masque les pannes MinIO.
            try {
                $trace = $this->gpxImport->importFromLocalPath($destPath, [
                    'stage_id' => $stage->id,
                    'stage_code' => $stageCode,
                    'trace_type' => 'stage_main',
                    'precision' => 'exact',
                    'name' => "{$stageCode} — trace principale",
                    'source' => $relativePath,
                    'minio_disk' => 'minio_gpx',
                ]);

                $this->command->info("  {$stageCode} : importé → {$trace->id}");
                $imported++;

            } catch (\Throwable $e) {
                Log::error('GpxTraceSeederEspagne: MinIO upload failed — fail-fast', [
                    'stage' => $stageCode,
                    'file' => $relativePath,
                    'error' => $e->getMessage(),
                ]);

                $this->command->error("  {$stageCode} : ERREUR MinIO ({$e->getMessage()}) — import annulé pour cette étape.");
                $errors++;
            }
        }

        $this->command->info(sprintf(
            'GpxTraceSeederEspagne : %d importés, %d ignorés, %d erreurs.',
            $imported,
            $skipped,
            $errors,
        ));

        if ($errors > 0) {
            $this->command->error("{$errors} import(s) ont échoué — vérifier les logs MinIO.");
        }
    }
}
