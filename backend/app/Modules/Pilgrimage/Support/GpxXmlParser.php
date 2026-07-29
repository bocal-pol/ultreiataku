<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Support;

use RuntimeException;

/**
 * Parseur GPX léger basé sur XMLReader pour minimiser la mémoire.
 * Extrait les points de trace (trk/trkpt ou rte/rtept) et les métadonnées.
 */
final class GpxXmlParser
{
    /**
     * @return array{
     *   points: array<int, array{lat: float, lon: float, ele: float|null}>,
     *   distance_km: float,
     *   elevation_gain_m: int,
     *   elevation_loss_m: int,
     *   track_points_count: int
     * }
     */
    public static function parse(string $gpxContent): array
    {
        $reader = new \XMLReader;

        if (! $reader->XML($gpxContent, null, LIBXML_NOWARNING | LIBXML_NOERROR)) {
            throw new RuntimeException('Contenu GPX invalide — impossible de parser le XML.');
        }

        /** @var array<int, array{lat: float, lon: float, ele: float|null}> $points */
        $points = [];
        $inTrack = false;
        $inRoute = false;

        while ($reader->read()) {
            if ($reader->nodeType !== \XMLReader::ELEMENT) {
                continue;
            }

            $name = strtolower($reader->localName);

            if ($name === 'trk') {
                $inTrack = true;
            } elseif ($name === 'rte') {
                $inRoute = true;
            } elseif (($name === 'trkpt' && $inTrack) || ($name === 'rtept' && $inRoute)) {
                $lat = (float) $reader->getAttribute('lat');
                $lon = (float) $reader->getAttribute('lon');
                $ele = null;

                // Lire l'élévation si présente
                if (! $reader->isEmptyElement) {
                    $depth = $reader->depth;
                    while ($reader->read()) {
                        if ($reader->nodeType === \XMLReader::ELEMENT
                            && strtolower($reader->localName) === 'ele'
                        ) {
                            $reader->read(); // TEXT node
                            $ele = (float) $reader->value;
                        }
                        if ($reader->nodeType === \XMLReader::END_ELEMENT
                            && $reader->depth <= $depth
                        ) {
                            break;
                        }
                    }
                }

                $points[] = ['lat' => $lat, 'lon' => $lon, 'ele' => $ele];
            }
        }

        $reader->close();

        if (count($points) < 2) {
            throw new RuntimeException('Le fichier GPX ne contient pas de trace valide (au moins 2 points requis).');
        }

        $metrics = self::computeMetrics($points);

        return array_merge(['points' => $points, 'track_points_count' => count($points)], $metrics);
    }

    /**
     * Vérifie si un contenu XML est un GPX valide (contient au moins un <trk> ou <rte>).
     */
    public static function isValid(string $gpxContent): bool
    {
        try {
            $result = self::parse($gpxContent);

            return $result['track_points_count'] >= 2;
        } catch (RuntimeException) {
            return false;
        }
    }

    /**
     * @param  array<int, array{lat: float, lon: float, ele: float|null}>  $points
     * @return array{distance_km: float, elevation_gain_m: int, elevation_loss_m: int}
     */
    private static function computeMetrics(array $points): array
    {
        $distanceKm = 0.0;
        $elevationGain = 0;
        $elevationLoss = 0;

        $count = count($points);
        for ($i = 1; $i < $count; $i++) {
            $distanceKm += self::haversineKm(
                $points[$i - 1]['lat'],
                $points[$i - 1]['lon'],
                $points[$i]['lat'],
                $points[$i]['lon']
            );

            if ($points[$i]['ele'] !== null && $points[$i - 1]['ele'] !== null) {
                $diff = $points[$i]['ele'] - $points[$i - 1]['ele'];
                if ($diff > 0) {
                    $elevationGain += (int) round($diff);
                } else {
                    $elevationLoss += (int) round(abs($diff));
                }
            }
        }

        return [
            'distance_km' => round($distanceKm, 3),
            'elevation_gain_m' => $elevationGain,
            'elevation_loss_m' => $elevationLoss,
        ];
    }

    private static function haversineKm(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
