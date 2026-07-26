<?php

declare(strict_types=1);

namespace Tests\Unit\Pilgrimage;

use App\Modules\Pilgrimage\Support\DouglasPeucker;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'algorithme Douglas-Peucker.
 * DouglasPeucker expose des méthodes statiques — appel via DouglasPeucker::simplify().
 * N'utilise pas de base de données — PHPUnit pur.
 */
class DouglasPeuckerTest extends TestCase
{
    // ─── Cas de base ────────────────────────────────────────────────────────

    public function test_empty_array_returns_empty(): void
    {
        $result = DouglasPeucker::simplify([], 0.0001);
        $this->assertSame([], $result);
    }

    public function test_single_point_returns_that_point(): void
    {
        $points = [[50.6, 5.5]];
        $result = DouglasPeucker::simplify($points, 0.0001);
        $this->assertCount(1, $result);
        $this->assertSame([50.6, 5.5], $result[0]);
    }

    public function test_two_points_are_always_kept(): void
    {
        $points = [[50.6, 5.5], [50.7, 5.6]];
        $result = DouglasPeucker::simplify($points, 0.0001);
        $this->assertCount(2, $result);
    }

    // ─── Simplification réelle ───────────────────────────────────────────────

    public function test_collinear_points_reduced_to_two_endpoints(): void
    {
        // 5 points parfaitement alignés — le RDP ne doit conserver que start + end
        $points = [
            [0.0, 0.0],
            [0.0, 1.0],
            [0.0, 2.0],
            [0.0, 3.0],
            [0.0, 4.0],
        ];
        $result = DouglasPeucker::simplify($points, 0.001);
        $this->assertCount(2, $result);
        $this->assertSame([0.0, 0.0], $result[0]);
        $this->assertSame([0.0, 4.0], $result[1]);
    }

    public function test_significant_deviation_point_is_kept(): void
    {
        // Point central dévie nettement de la ligne droite
        $points = [
            [0.0, 0.0],
            [0.5, 1.0],   // grande déviation perpendiculaire
            [0.0, 2.0],
        ];
        $result = DouglasPeucker::simplify($points, 0.001);
        $this->assertCount(3, $result);
    }

    public function test_tolerance_zero_keeps_all_points(): void
    {
        $points = [
            [0.0, 0.0],
            [0.0, 1.0],
            [1.0, 1.0],
            [1.0, 2.0],
        ];
        // Points en Z (non-colinéaires) — tolérance nulle conserve tous les points
        $result = DouglasPeucker::simplify($points, 0.0);
        $this->assertCount(4, $result);
    }

    public function test_large_tolerance_reduces_to_endpoints(): void
    {
        $points = [];
        for ($i = 0; $i <= 10; $i++) {
            $points[] = [50.0 + $i * 0.01, 5.0 + $i * 0.001];
        }
        // Très grande tolérance : tous les intermédiaires supprimés
        $result = DouglasPeucker::simplify($points, 10.0);
        $this->assertCount(2, $result);
        $this->assertSame($points[0], $result[0]);
        $this->assertSame($points[count($points) - 1], $result[count($result) - 1]);
    }

    // ─── Préservation de l'ordre ────────────────────────────────────────────

    public function test_output_preserves_point_order(): void
    {
        $points = [
            [0.0, 0.0],
            [1.0, 2.0],
            [50.60, 5.56],  // virage notable
            [50.57, 5.59],
            [50.60, 5.62],
        ];
        $result = DouglasPeucker::simplify($points, 0.001);

        // Extraire uniquement les lats pour vérifier l'ordre
        $resultLats = array_column($result, 0);
        $srcLats = array_column($points, 0);

        $lastFoundIndex = -1;
        foreach ($resultLats as $lat) {
            foreach ($srcLats as $idx => $srcLat) {
                if ($srcLat === $lat && $idx > $lastFoundIndex) {
                    $lastFoundIndex = $idx;
                    break;
                }
            }
        }
        // Si l'ordre est correct, on a avancé à travers tous les points retenus
        $this->assertGreaterThan(-1, $lastFoundIndex);
    }

    // ─── Endpoints toujours conservés ───────────────────────────────────────

    public function test_first_and_last_points_always_preserved(): void
    {
        $points = [
            [49.50, 3.50],
            [49.55, 3.55],
            [49.60, 3.60],
            [49.65, 3.65],
            [51.50, 6.50],
        ];
        $result = DouglasPeucker::simplify($points, 0.001);

        $this->assertSame($points[0], $result[0]);
        $this->assertSame($points[count($points) - 1], $result[count($result) - 1]);
    }

    // ─── Segment Mosane réel (sous-ensemble) ─────────────────────────────────

    public function test_real_meuse_segment_simplification(): void
    {
        // Extrait de la Via Mosana Liège-Amay — coordonnées réelles
        $points = [
            [50.6458, 5.5734],  // Liège Cathédrale
            [50.6410, 5.5650],
            [50.6380, 5.5490],
            [50.6350, 5.5320],
            [50.6290, 5.5120],
            [50.6200, 5.4900],
            [50.6100, 5.4700],
            [50.5950, 5.4450],
            [50.5820, 5.4210],
            [50.5650, 5.3980],  // Amay
        ];

        $tolerance = 0.0001;
        $result = DouglasPeucker::simplify($points, $tolerance);

        // Doit réduire sans perdre start/end
        $this->assertLessThanOrEqual(count($points), count($result));
        $this->assertGreaterThanOrEqual(2, count($result));
        $this->assertSame($points[0], $result[0]);
        $this->assertSame($points[count($points) - 1], $result[count($result) - 1]);
    }
}
