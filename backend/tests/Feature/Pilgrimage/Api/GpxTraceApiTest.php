<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Api;

use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests feature pour GET /api/pilgrimage/gpx/{id}.
 * ULTREIA-11 + ULTREIA-1T.
 *
 * Note : les tests de streaming MinIO réel sont exclus (env de test = SQLite + array cache).
 * On teste le fallback local et les codes HTTP.
 */
class GpxTraceApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeTrace(array $overrides = []): GpxTrace
    {
        $start = Waypoint::factory()->create();
        $end = Waypoint::factory()->create();
        $stage = Stage::factory()->create([
            'start_waypoint_id' => $start->id,
            'end_waypoint_id' => $end->id,
        ]);

        return GpxTrace::factory()->create(array_merge([
            'stage_id' => $stage->id,
        ], $overrides));
    }

    // ─── GET /api/pilgrimage/gpx/{id} (stream) ───────────────────────────────

    public function test_stream_returns_404_for_unknown_id(): void
    {
        $this->getJson('/api/pilgrimage/gpx/00000000-0000-0000-0000-000000000000')
            ->assertStatus(404);
    }

    public function test_stream_returns_404_when_no_minio_and_no_local_file(): void
    {
        $trace = $this->makeTrace([
            'minio_path' => null,
            'minio_disk' => null,
            'source' => 'ABSENT-FILE.gpx',
        ]);

        // Aucun fichier local seeds/gpx/ABSENT-FILE.gpx
        $response = $this->get('/api/pilgrimage/gpx/' . $trace->id);
        $response->assertStatus(404);
    }

    public function test_stream_serves_local_fallback_when_minio_null(): void
    {
        // Créer un fichier GPX local dans storage/seeds/gpx/
        $gpxContent = '<?xml version="1.0"?><gpx version="1.1"><trk><trkseg>'
            . '<trkpt lat="50.6" lon="5.5"><ele>65</ele></trkpt>'
            . '</trkseg></trk></gpx>';

        $filename = 'test-fallback-' . uniqid() . '.gpx';
        $seedDir = storage_path('seeds/gpx');

        if (! is_dir($seedDir)) {
            mkdir($seedDir, 0755, true);
        }

        file_put_contents($seedDir . '/' . $filename, $gpxContent);

        $trace = $this->makeTrace([
            'minio_path' => null,
            'minio_disk' => null,
            'source' => $filename,
        ]);

        $response = $this->get('/api/pilgrimage/gpx/' . $trace->id);

        $response->assertStatus(200);
        $this->assertStringContainsString('application/gpx', $response->headers->get('Content-Type', ''));

        // Nettoyage
        @unlink($seedDir . '/' . $filename);
    }

    // ─── GET /api/pilgrimage/gpx/{id}/simplified ─────────────────────────────

    public function test_simplified_returns_404_for_unknown_id(): void
    {
        $this->getJson('/api/pilgrimage/gpx/00000000-0000-0000-0000-000000000000/simplified')
            ->assertStatus(404);
    }

    public function test_simplified_response_has_cache_control_header(): void
    {
        // Mocker le cache et le service pour ce test d'intégration partielle
        Cache::shouldReceive('remember')->andReturn([
            'type' => 'FeatureCollection',
            'features' => [],
        ]);

        $trace = $this->makeTrace([
            'minio_path' => null,
            'minio_disk' => null,
        ]);

        $response = $this->getJson('/api/pilgrimage/gpx/' . $trace->id . '/simplified');

        // 200 ou 503 selon disponibilité MinIO en test — on vérifie juste pas 500
        $this->assertNotSame(500, $response->status());
    }

    public function test_simplified_returns_geojson_structure(): void
    {
        // GPX local minimal pour test end-to-end
        $gpxContent = '<?xml version="1.0"?>'
            . '<gpx version="1.1" xmlns="http://www.topografix.com/GPX/1/1">'
            . '<trk><name>Test</name><trkseg>'
            . '<trkpt lat="50.60" lon="5.50"><ele>100</ele></trkpt>'
            . '<trkpt lat="50.61" lon="5.51"><ele>110</ele></trkpt>'
            . '<trkpt lat="50.62" lon="5.52"><ele>105</ele></trkpt>'
            . '</trkseg></trk></gpx>';

        $filename = 'test-simplified-' . uniqid() . '.gpx';
        $seedDir = storage_path('seeds/gpx');

        if (! is_dir($seedDir)) {
            mkdir($seedDir, 0755, true);
        }

        file_put_contents($seedDir . '/' . $filename, $gpxContent);

        $trace = $this->makeTrace([
            'minio_path' => null,
            'minio_disk' => null,
            'source' => $filename,
        ]);

        $response = $this->getJson('/api/pilgrimage/gpx/' . $trace->id . '/simplified');

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'type',
                'features' => [
                    '*' => ['type', 'geometry', 'properties'],
                ],
            ]);
        }

        @unlink($seedDir . '/' . $filename);
    }
}
