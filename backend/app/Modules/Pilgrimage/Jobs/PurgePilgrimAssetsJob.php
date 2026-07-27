<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * RGPD-U01 — ADR ligne 85 — Purge des assets MinIO d'un pèlerin.
 *
 * Ce job est déclenché de manière asynchrone après la suppression RGPD
 * d'un Pilgrim (DELETE /api/pilgrimage/me) pour garantir la suppression
 * physique des photos journal sur MinIO.
 *
 * Design : la liste des assets à purger est collectée AVANT la transaction
 * de suppression DB et passée au Job. Cela évite le problème lié aux
 * ON DELETE CASCADE des FK (qui suppriment physiquement les rows avant
 * que le Job puisse les retrouver via withTrashed()).
 *
 * Idempotent : si une photo est déjà absente sur MinIO, le job continue
 * sans erreur. Les retentatives (5 max, 30s backoff) sont sûres.
 *
 * Rétention : ILLIMITÉE par décision produit (2026-07-27). La purge MinIO
 * n'est déclenchée que sur demande explicite (droit à l'oubli Art. 17 RGPD).
 *
 * @param  string  $pilgrimId  UUID du pèlerin supprimé (pour les logs seulement)
 * @param  list<array{disk:string,path:string}>  $assets  Liste des assets à purger
 */
class PurgePilgrimAssetsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $backoff = 30;

    /**
     * @param  string  $pilgrimId  UUID du pèlerin (pour les logs)
     * @param  list<array{disk:string,path:string}>  $assets  Assets MinIO à purger
     */
    public function __construct(
        private readonly string $pilgrimId,
        private readonly array $assets = [],
    ) {}

    public function handle(): void
    {
        Log::info('rgpd.purge_pilgrim_assets.started', [
            'pilgrim_id' => $this->pilgrimId,
            'assets_count' => count($this->assets),
        ]);

        $purgedCount = 0;
        $errorCount = 0;

        foreach ($this->assets as $asset) {
            $disk = $asset['disk'] ?? 'minio_journal';
            $path = $asset['path'] ?? null;

            if (! $path) {
                continue;
            }

            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                    $purgedCount++;
                }
            } catch (\Throwable $e) {
                // Ne pas faire échouer le job pour un asset individuel
                $errorCount++;
                Log::warning('rgpd.purge_pilgrim_assets.asset_error', [
                    'pilgrim_id' => $this->pilgrimId,
                    'disk' => $disk,
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('rgpd.purge_pilgrim_assets.completed', [
            'pilgrim_id' => $this->pilgrimId,
            'purged_count' => $purgedCount,
            'error_count' => $errorCount,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('rgpd.purge_pilgrim_assets.job_failed', [
            'pilgrim_id' => $this->pilgrimId,
            'error' => $exception->getMessage(),
        ]);
    }
}
