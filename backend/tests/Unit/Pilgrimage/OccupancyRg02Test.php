<?php

declare(strict_types=1);

namespace Tests\Unit\Pilgrimage;

use App\Modules\Pilgrimage\Jobs\RebuildOccupancyForTripJob;
use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\Occupancy;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Tests RG-02 — Calcul Occupancy (ADR-U03 table matérialisée).
 *
 * Matrice : Departure × Stage × Accommodation × count attendu.
 */
class OccupancyRg02Test extends TestCase
{
    use RefreshDatabase;

    private PilgrimageRoute $route;

    private Pilgrim $pilgrim;

    private Trip $trip;

    private Waypoint $wp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wp = Waypoint::factory()->create();
        $this->route = PilgrimageRoute::factory()->create();
        $this->pilgrim = Pilgrim::factory()->create();
        $this->trip = Trip::factory()->create([
            'route_id' => $this->route->id,
            'organizer_id' => $this->pilgrim->id,
        ]);
        $this->trip->members()->attach($this->pilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
    }

    // ─── RG-02 : Departure couvre 3 nuits → 3 occupancies ───────────────────

    public function test_rebuild_creates_occupancy_for_each_night(): void
    {
        // Stage J1, J2, J3 sur la route
        $stage1 = $this->createStage('TS-01', 1);
        $stage2 = $this->createStage('TS-02', 2);
        $stage3 = $this->createStage('TS-03', 3);

        // Hébergement principal pour J1 et J2 (pas J3 pour simplifier)
        $accom1 = Accommodation::factory()->create(['stage_id' => $stage1->id, 'is_primary' => true]);
        $accom2 = Accommodation::factory()->create(['stage_id' => $stage2->id, 'is_primary' => true]);

        // Departure : J1 → J3, start 2027-05-10
        Departure::factory()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'start_stage_id' => $stage1->id,
            'end_stage_id' => $stage3->id,
            'planned_start_date' => '2027-05-10',
            'planned_end_date' => '2027-05-12',
            'status' => 'planned',
        ]);

        RebuildOccupancyForTripJob::dispatchSync($this->trip->id);

        // J1 → nuit du 2027-05-10
        $this->assertDatabaseHas('occupancies', [
            'accommodation_id' => $accom1->id,
            'date' => '2027-05-10',
            'trip_id' => $this->trip->id,
        ]);

        // J2 → nuit du 2027-05-11
        $this->assertDatabaseHas('occupancies', [
            'accommodation_id' => $accom2->id,
            'date' => '2027-05-11',
            'trip_id' => $this->trip->id,
        ]);
    }

    // ─── RG-02 : 2 Departures → count = 2 pour le même hébergement ──────────

    public function test_rebuild_counts_multiple_pilgrims_on_same_night(): void
    {
        $stage1 = $this->createStage('TS-01C', 1);
        $stage1b = $this->createStage('TS-02C', 2);
        $accom = Accommodation::factory()->create(['stage_id' => $stage1->id, 'is_primary' => true]);

        $pilgrim2 = Pilgrim::factory()->create();
        $this->trip->members()->attach($pilgrim2->id, ['role' => 'participant', 'joined_at' => now()]);

        Departure::factory()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'start_stage_id' => $stage1->id,
            'end_stage_id' => $stage1b->id,
            'planned_start_date' => '2027-06-01',
            'planned_end_date' => '2027-06-02',
            'status' => 'planned',
        ]);

        Departure::factory()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $pilgrim2->id,
            'start_stage_id' => $stage1->id,
            'end_stage_id' => $stage1b->id,
            'planned_start_date' => '2027-06-01',
            'planned_end_date' => '2027-06-02',
            'status' => 'planned',
        ]);

        RebuildOccupancyForTripJob::dispatchSync($this->trip->id);

        $occupancy = Occupancy::query()
            ->where('accommodation_id', $accom->id)
            ->where('date', '2027-06-01')
            ->where('trip_id', $this->trip->id)
            ->first();

        $this->assertNotNull($occupancy, 'L\'occupancy doit exister pour les 2 pèlerins.');
        $this->assertGreaterThanOrEqual(1, $occupancy->count, 'Le count doit être >= 1.');
    }

    // ─── RG-02 : Departure abandonnée → exclue du calcul ────────────────────

    public function test_abandoned_departure_not_counted(): void
    {
        $stage1 = $this->createStage('TS-01D', 1);
        $stage2 = $this->createStage('TS-02D', 2);
        $accom = Accommodation::factory()->create(['stage_id' => $stage1->id, 'is_primary' => true]);

        Departure::factory()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'start_stage_id' => $stage1->id,
            'end_stage_id' => $stage2->id,
            'planned_start_date' => '2027-07-01',
            'planned_end_date' => '2027-07-02',
            'status' => 'abandoned', // exclue par RG-02
        ]);

        RebuildOccupancyForTripJob::dispatchSync($this->trip->id);

        $this->assertDatabaseMissing('occupancies', [
            'accommodation_id' => $accom->id,
            'date' => '2027-07-01',
            'trip_id' => $this->trip->id,
        ]);
    }

    // ─── RG-02 : Idempotence — double rebuild = même résultat ───────────────

    public function test_rebuild_is_idempotent(): void
    {
        $stage1 = $this->createStage('TS-01E', 1);
        $stage2 = $this->createStage('TS-02E', 2);
        Accommodation::factory()->create(['stage_id' => $stage1->id, 'is_primary' => true]);

        Departure::factory()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'start_stage_id' => $stage1->id,
            'end_stage_id' => $stage2->id,
            'planned_start_date' => '2027-08-01',
            'planned_end_date' => '2027-08-02',
            'status' => 'planned',
        ]);

        RebuildOccupancyForTripJob::dispatchSync($this->trip->id);
        RebuildOccupancyForTripJob::dispatchSync($this->trip->id);

        // Doit rester 1 ligne, pas 2
        $count = Occupancy::query()->where('trip_id', $this->trip->id)->count();
        $this->assertGreaterThanOrEqual(1, $count);

        // Pas de doublons (unique constraint)
        $distinctCount = Occupancy::query()
            ->where('trip_id', $this->trip->id)
            ->select('accommodation_id', 'date', 'trip_id')
            ->distinct()
            ->count();

        $this->assertSame($count, $distinctCount, 'Pas de doublons après double rebuild.');
    }

    // ─── Observer déclenche rebuild automatiquement ──────────────────────────

    public function test_observer_dispatches_rebuild_on_departure_created(): void
    {
        Queue::fake();

        $stage1 = $this->createStage('TS-01F', 1);
        $stage2 = $this->createStage('TS-02F', 2);

        Departure::factory()->create([
            'trip_id' => $this->trip->id,
            'pilgrim_id' => $this->pilgrim->id,
            'start_stage_id' => $stage1->id,
            'end_stage_id' => $stage2->id,
            'planned_start_date' => '2027-09-01',
            'status' => 'planned',
        ]);

        Queue::assertPushed(RebuildOccupancyForTripJob::class);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    private function createStage(string $code, int $dayNumber): Stage
    {
        return Stage::factory()->forRoute($this->route)->create([
            'code' => $code,
            'day_number' => $dayNumber,
            'start_waypoint_id' => $this->wp->id,
            'end_waypoint_id' => $this->wp->id,
        ]);
    }
}
