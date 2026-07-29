<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

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
 *
 * I-03 — Transparence PNG :
 *   - Les PNG avec canal alpha sont aplatis sur fond blanc avant re-encode JPEG.
 *   - Cela évite que la transparence vire au noir (comportement GD par défaut).
 *
 * I-04 — Mémoire :
 *   - Le re-encode passe par un fichier temporaire (imagejpeg vers chemin fichier)
 *     et non par ob_start/ob_get_clean qui charge tout en RAM.
 *   - Si GD est indisponible → fail-fast (RuntimeException) — RG-04 ne contourne
 *     jamais le strip EXIF.
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
            'entry_id' => $entryId,
            'path' => $path,
            'keep_location' => $keepLocation,
            'has_coords' => $latitude !== null,
        ]);

        return [
            'minio_path' => $path,
            'minio_disk' => 'minio_journal',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => strlen($stripped),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'taken_at' => $takenAt,
        ];
    }

    /**
     * Re-encode l'image via GD pour supprimer toutes les métadonnées EXIF.
     * Supporte JPEG, PNG (avec gestion transparence → fond blanc), WEBP.
     *
     * I-04 : utilise un fichier temporaire au lieu de ob_start/ob_get_clean
     *        pour éviter la saturation RAM sur les grandes images.
     *
     * I-03 : les PNG avec canal alpha sont aplatis sur fond blanc avant conversion JPEG.
     *
     * @throws RuntimeException si GD est indisponible (RG-04 : fail-fast, pas de fallback non-strip)
     */
    private function stripExifViaReencode(UploadedFile $file): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('L\'extension GD est requise pour le strip EXIF des photos (RG-04). Activez ext-gd.');
        }

        $mimeType = $file->getMimeType() ?? 'image/jpeg';
        $isPng = str_contains($mimeType, 'png');

        $image = match (true) {
            str_contains($mimeType, 'jpeg'), str_contains($mimeType, 'jpg') => imagecreatefromjpeg($file->getRealPath()),
            $isPng => imagecreatefrompng($file->getRealPath()),
            str_contains($mimeType, 'webp') => imagecreatefromwebp($file->getRealPath()),
            default => imagecreatefromjpeg($file->getRealPath()),
        };

        if ($image === false) {
            throw new RuntimeException(
                sprintf('GD n\'a pas pu décoder l\'image "%s" (mime: %s). Upload annulé.', $file->getClientOriginalName(), $mimeType),
            );
        }

        // I-03 — Aplatir la transparence PNG sur fond blanc avant conversion JPEG
        if ($isPng) {
            $width = imagesx($image);
            $height = imagesy($image);
            $canvas = imagecreatetruecolor($width, $height);

            if ($canvas === false) {
                imagedestroy($image);
                throw new RuntimeException('GD n\'a pas pu créer le canvas pour l\'aplatissement du canal alpha PNG.');
            }

            // Fond blanc
            $white = imagecolorallocate($canvas, 255, 255, 255);
            if ($white !== false) {
                imagefilledrectangle($canvas, 0, 0, $width - 1, $height - 1, $white);
            }

            imagealphablending($image, true);
            imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
            imagedestroy($image);
            $image = $canvas;
        }

        // I-04 — Écrire dans un fichier temporaire plutôt qu'en mémoire via ob_start
        $tmpPath = tempnam(sys_get_temp_dir(), 'ultreia_photo_');
        if ($tmpPath === false) {
            imagedestroy($image);
            throw new RuntimeException('Impossible de créer un fichier temporaire pour le re-encode JPEG.');
        }

        try {
            $success = imagejpeg($image, $tmpPath, 85);
            imagedestroy($image);

            if (! $success) {
                throw new RuntimeException('GD n\'a pas pu encoder le fichier JPEG.');
            }

            $result = file_get_contents($tmpPath);

            if ($result === false) {
                throw new RuntimeException('Impossible de lire le fichier temporaire après le re-encode.');
            }

            return $result;
        } finally {
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
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

        $latitude = $this->parseGpsCoordinate($exif, 'GPSLatitude', $exif['GPSLatitudeRef'] ?? 'N');
        $longitude = $this->parseGpsCoordinate($exif, 'GPSLongitude', $exif['GPSLongitudeRef'] ?? 'E');
        $takenAt = isset($exif['DateTimeOriginal'])
            ? date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']))
            : null;

        return [$latitude, $longitude, $takenAt];
    }

    /**
     * Convertit une coordonnée GPS EXIF (degrés/minutes/secondes) en décimal.
     *
     * @param  array<string, mixed>  $exif
     */
    private function parseGpsCoordinate(array $exif, string $key, string $ref): ?float
    {
        if (! isset($exif[$key]) || ! is_array($exif[$key])) {
            return null;
        }

        $parts = $exif[$key];

        $deg = $this->evalFraction((string) $parts[0]);
        $min = $this->evalFraction((string) $parts[1]);
        $sec = $this->evalFraction((string) $parts[2]);

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
