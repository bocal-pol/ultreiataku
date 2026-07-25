<?php

declare(strict_types=1);

namespace Tests\Unit\Pilgrimage;

use App\Modules\Pilgrimage\Support\GpxXmlParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests unitaires pour le parser GPX (XMLReader stream).
 * GpxXmlParser expose des méthodes statiques — appel via GpxXmlParser::parse().
 */
class GpxXmlParserTest extends TestCase
{
    // ─── GPX valide avec <trk>/<trkpt> ──────────────────────────────────────

    public function test_parses_valid_gpx_track(): void
    {
        $gpx = $this->buildGpxTrack([
            ['lat' => 50.6458, 'lon' => 5.5734, 'ele' => 65.0],
            ['lat' => 50.6400, 'lon' => 5.5650, 'ele' => 80.0],
            ['lat' => 50.6350, 'lon' => 5.5500, 'ele' => 70.0],
        ]);

        $result = GpxXmlParser::parse($gpx);

        $this->assertArrayHasKey('points', $result);
        $this->assertArrayHasKey('distance_km', $result);
        $this->assertArrayHasKey('elevation_gain_m', $result);
        $this->assertArrayHasKey('elevation_loss_m', $result);
        $this->assertArrayHasKey('track_points_count', $result);

        $this->assertCount(3, $result['points']);
        $this->assertSame(3, $result['track_points_count']);
        $this->assertGreaterThan(0.0, $result['distance_km']);
    }

    public function test_elevation_gain_and_loss_computed(): void
    {
        $gpx = $this->buildGpxTrack([
            ['lat' => 50.60, 'lon' => 5.50, 'ele' => 100.0],
            ['lat' => 50.61, 'lon' => 5.51, 'ele' => 150.0],  // +50m
            ['lat' => 50.62, 'lon' => 5.52, 'ele' => 120.0],  // -30m
            ['lat' => 50.63, 'lon' => 5.53, 'ele' => 200.0],  // +80m
        ]);

        $result = GpxXmlParser::parse($gpx);

        $this->assertEqualsWithDelta(130.0, $result['elevation_gain_m'], 1.0);
        $this->assertEqualsWithDelta(30.0, $result['elevation_loss_m'], 1.0);
    }

    public function test_point_structure_contains_lat_lon(): void
    {
        $gpx = $this->buildGpxTrack([
            ['lat' => 50.6458, 'lon' => 5.5734, 'ele' => 65.0],
            ['lat' => 50.5650, 'lon' => 5.3980, 'ele' => 55.0],
        ]);

        $result = GpxXmlParser::parse($gpx);

        $this->assertSame(50.6458, $result['points'][0]['lat']);
        $this->assertSame(5.5734, $result['points'][0]['lon']);
        $this->assertSame(50.5650, $result['points'][1]['lat']);
    }

    // ─── GPX vide / invalide ─────────────────────────────────────────────────

    public function test_gpx_with_fewer_than_2_points_throws(): void
    {
        $this->expectException(RuntimeException::class);

        $gpx = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<gpx version="1.1" xmlns="http://www.topografix.com/GPX/1/1">'
            . '<trk><trkseg>'
            . '<trkpt lat="50.6" lon="5.5"><ele>65</ele></trkpt>'
            . '</trkseg></trk></gpx>';

        GpxXmlParser::parse($gpx);
    }

    public function test_is_valid_returns_false_for_malformed_xml(): void
    {
        $this->assertFalse(GpxXmlParser::isValid('<not-gpx>bad</not-gpx>'));
    }

    public function test_is_valid_returns_true_for_valid_gpx(): void
    {
        $gpx = $this->buildGpxTrack([
            ['lat' => 50.60, 'lon' => 5.50, 'ele' => 100.0],
            ['lat' => 50.61, 'lon' => 5.51, 'ele' => 110.0],
        ]);
        $this->assertTrue(GpxXmlParser::isValid($gpx));
    }

    // ─── Distance haversine ──────────────────────────────────────────────────

    public function test_distance_between_liege_and_amay(): void
    {
        // Distance réelle Liège Cathédrale → Amay vol d'oiseau ≈ 15-17 km
        $gpx = $this->buildGpxTrack([
            ['lat' => 50.6458, 'lon' => 5.5734, 'ele' => 65.0],
            ['lat' => 50.5650, 'lon' => 5.3980, 'ele' => 55.0],
        ]);

        $result = GpxXmlParser::parse($gpx);

        $this->assertGreaterThan(10.0, $result['distance_km']);
        $this->assertLessThan(25.0, $result['distance_km']);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * @param array<int, array{lat: float, lon: float, ele: float}> $points
     */
    private function buildGpxTrack(array $points): string
    {
        $trkpts = '';
        foreach ($points as $p) {
            $trkpts .= sprintf(
                '<trkpt lat="%s" lon="%s"><ele>%s</ele></trkpt>',
                $p['lat'],
                $p['lon'],
                $p['ele'],
            );
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<gpx version="1.1" creator="test" xmlns="http://www.topografix.com/GPX/1/1">'
            . '<trk><name>Test Track</name><trkseg>'
            . $trkpts
            . '</trkseg></trk>'
            . '</gpx>';
    }
}
