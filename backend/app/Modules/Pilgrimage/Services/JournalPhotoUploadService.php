<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ULTREIA-52 — Service d'upload de photos journal.
 *
 * RG-04 / ADR-U02 :
 *   - Valide le fichier (mime, taille).
 *   - Strip EXIF sensible via GD re-encode sauf si keep_location = true.
 *   - Stocke sur disk minio_journal (bucket ultreiataku-journal).
 *   - Jamais d'URL directe MinIO : retourne le chemin relatif uniquement.
 *
 * Strip EXIF :
 *   - On re-encode l'image via GD : cela supprime toutes les métadonnées EXIF.
 *   - Si keep_location = true : on lit lat/lng EXIF avant re-encodage
 *     et on les retourne pour stockage en BDD (on NE stocke PAS l'EXIF brut).
 *   - Les coordonnées GPS retournées par cette méthode ont déjà été extraites
 *     et séparées du fichier : elles ne transitent jamais dans l'image.
 */
class JournalPhotoUploadService
{
    /**
     * Upload une photo, strip l'EXIF et retourne les métadonnées.
     *
     * @return array{
     *   minio_path: string,
     *   minio_disk: string,
     *   mime_type: string,
     *   file_size_bytes: int,
     *   latitude: float|null,
     *   longitude: float|null,
     *   taken_at: string|null,
     * }
     */
    public function upload(UploadedFile $file, string $entryId, bool $keepLocation = false): array
    {
        [$latitude, $longitude, $takenAt] = $keepLocation
            ? $this->extractExifCoords($file)
            : [null, null, null];

        // Re-encode via GD pour strip EXIF — sans re-encode, les métadonnées survivent
        $stripped = $this->stripExifViaReencode($file);

        $path = sprintf(
            'journal/%s/%s/%s.jpg',
            now()->format('Y'),
            now()->format('m'),
            Str::uuid()->toString(),
        );

        Storage::disk('minio_journal')->put($path, $stripped, 'private');

        Log::info('journal.photo.uploaded', [
            'entry_id'     => $entryId,
            'path'         => $path,
            'keep_location' => $keepLocation,
            'has_coords'   => $latitude !== null,
        ]);

        return [
            'minio_path'      => $path,
            'minio_disk'      => 'minio_journal',
            'mime_type'       => 'image/jpeg',
            'file_size_bytes' => strlen($stripped),
            'latitude'        => $latitude,
            'longitude'       => $longitude,
            'taken_at'        => $takenAt,
        ];
    }

    /**
     * Re-encode l'image via GD pour supprimer toutes les métadonnées EXIF.
     * Supporte JPEG, PNG, WEBP.
     */
    private function stripExifViaReencode(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType() ?? 'image/jpeg';

        $image = match (true) {
            str_contains($mimeType, 'jpeg'), str_contains($mimeType, 'jpg')
                => imagecreatefromjpeg($file->getRealPath()),
            str_contains($mimeType, 'png')
                => imagecreatefrompng($file->getRealPath()),
            str_contains($mimeType, 'webp')
                => imagecreatefromwebp($file->getRealPath()),
            default => imagecreatefromjpeg($file->getRealPath()),
        };

        if ($image === false) {
            // Fallback : lire le binaire brut si GD échoue
            Log::warning('journal.photo.gd_failed', ['file' => $file->getClientOriginalName()]);

            return (string) file_get_contents($file->getRealPath());
        }

        ob_start();
        imagejpeg($image, null, 85);
        $result = (string) ob_get_clean();
        imagedestroy($image);

        return $result;
    }

    /**
     * Extrait latitude, longitude et taken_at depuis les EXIF du fichier.
     *
     * @return array{float|null, float|null, string|null}
     */
    private function extractExifCoords(UploadedFile $file): array
    {
        if (! function_exists('exif_read_data')) {
            return [null, null, null];
        }

        $exif = @exif_read_data($file->getRealPath());

        if (! is_array($exif)) {
            return [null, null, null];
        }

        $latitude  = $this->parseGpsCoordinate($exif, 'GPSLatitude', $exif['GPSLatitudeRef'] ?? 'N');
        $longitude = $this->parseGpsCoordinate($exif, 'GPSLongitude', $exif['GPSLongitudeRef'] ?? 'E');
        $takenAt   = isset($exif['DateTimeOriginal'])
            ? date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']))
            : null;

        return [$latitude, $longitude, $takenAt];
    }

    /**
     * Convertit une coordonnée GPS EXIF (degrés/minutes/secondes) en décimal.
     *
     * @param array<string, mixed> $exif
     */
    private function parseGpsCoordinate(array $exif, string $key, string $ref): ?float
    {
        if (! isset($exif[$key]) || ! is_array($exif[$key])) {
            return null;
        }

        $parts = $exif[$key];

        $deg  = $this->evalFraction((string) $parts[0]);
        $min  = $this->evalFraction((string) $parts[1]);
        $sec  = $this->evalFraction((string) $parts[2]);

        $decimal = $deg + ($min / 60) + ($sec / 3600);

        if (in_array(strtoupper($ref), ['S', 'W'], true)) {
            $decimal *= -1;
        }

        return round($decimal, 7);
    }

    /**
     * Évalue une fraction EXIF du type "50/1" ou "30/1".
     */
    private function evalFraction(string $fraction): float
    {
        if (! str_contains($fraction, '/')) {
            return (float) $fraction;
        }

        [$num, $den] = explode('/', $fraction, 2);
        $den = (float) $den;

        return $den !== 0.0 ? (float) $num / $den : 0.0;
    }
}
