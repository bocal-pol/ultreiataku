<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Support;

/**
 * Algorithme de simplification Douglas-Peucker.
 * Réduit le nombre de points d'une polyligne en conservant la forme générale.
 * Stateless — méthodes pures sans état.
 */
final class DouglasPeucker
{
    /**
     * Simplifie un tableau de points [lat, lon] avec la tolérance donnée.
     *
     * @param array<int, array{0: float, 1: float}> $points Tableau de [lat, lon]
     * @param float $tolerance Tolérance en degrés (~0.0001 ≈ 10m)
     * @return array<int, array{0: float, 1: float}>
     */
    public static function simplify(array $points, float $tolerance = 0.0001): array
    {
        $count = count($points);
        if ($count <= 2) {
            return $points;
        }

        return self::rdp($points, 0, $count - 1, $tolerance);
    }

    /**
     * @param array<int, array{0: float, 1: float}> $points
     * @return array<int, array{0: float, 1: float}>
     */
    private static function rdp(array $points, int $start, int $end, float $tolerance): array
    {
        if ($end <= $start + 1) {
            return [$points[$start], $points[$end]];
        }

        $maxDistance = 0.0;
        $maxIndex = $start;

        for ($i = $start + 1; $i < $end; $i++) {
            $distance = self::perpendicularDistance(
                $points[$i],
                $points[$start],
                $points[$end]
            );

            if ($distance > $maxDistance) {
                $maxDistance = $distance;
                $maxIndex = $i;
            }
        }

        if ($maxDistance <= $tolerance) {
            return [$points[$start], $points[$end]];
        }

        $left = self::rdp($points, $start, $maxIndex, $tolerance);
        $right = self::rdp($points, $maxIndex, $end, $tolerance);

        // Fusionne sans dupliquer le point pivot
        return array_merge(
            array_slice($left, 0, -1),
            $right
        );
    }

    /**
     * Calcule la distance perpendiculaire d'un point à une ligne définie par deux points.
     *
     * @param array{0: float, 1: float} $point
     * @param array{0: float, 1: float} $lineStart
     * @param array{0: float, 1: float} $lineEnd
     */
    private static function perpendicularDistance(
        array $point,
        array $lineStart,
        array $lineEnd
    ): float {
        $dx = $lineEnd[1] - $lineStart[1];
        $dy = $lineEnd[0] - $lineStart[0];

        // Ligne dégénérée (deux points identiques)
        if ($dx === 0.0 && $dy === 0.0) {
            $dx = $point[1] - $lineStart[1];
            $dy = $point[0] - $lineStart[0];

            return sqrt($dx * $dx + $dy * $dy);
        }

        $t = (($point[1] - $lineStart[1]) * $dx + ($point[0] - $lineStart[0]) * $dy)
            / ($dx * $dx + $dy * $dy);

        $nearestX = $lineStart[1] + $t * $dx;
        $nearestY = $lineStart[0] + $t * $dy;

        $distX = $point[1] - $nearestX;
        $distY = $point[0] - $nearestY;

        return sqrt($distX * $distX + $distY * $distY);
    }
}
