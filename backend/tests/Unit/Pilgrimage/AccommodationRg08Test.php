<?php

declare(strict_types=1);

namespace Tests\Unit\Pilgrimage;

use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires RG-08 — Hébergements obsolètes.
 * ULTREIA-2T.
 */
class AccommodationRg08Test extends TestCase
{
    use RefreshDatabase;

    // ─── isObsolete() ────────────────────────────────────────────────────────

    public function test_is_obsolete_when_verified_at_is_null(): void
    {
        $accommodation = new Accommodation;
        $accommodation->verified_at = null;

        $this->assertTrue($accommodation->isObsolete());
    }

    public function test_is_obsolete_when_verified_at_exactly_6_months_ago(): void
    {
        $accommodation = new Accommodation;
        $accommodation->verified_at = now()->subMonths(6)->subDay();

        $this->assertTrue($accommodation->isObsolete());
    }

    public function test_is_obsolete_when_verified_at_over_6_months_ago(): void
    {
        $accommodation = new Accommodation;
        $accommodation->verified_at = now()->subMonths(12);

        $this->assertTrue($accommodation->isObsolete());
    }

    public function test_is_not_obsolete_when_verified_recently(): void
    {
        $accommodation = new Accommodation;
        $accommodation->verified_at = now()->subMonths(3);

        $this->assertFalse($accommodation->isObsolete());
    }

    public function test_is_not_obsolete_when_verified_today(): void
    {
        $accommodation = new Accommodation;
        $accommodation->verified_at = now();

        $this->assertFalse($accommodation->isObsolete());
    }

    public function test_is_not_obsolete_when_verified_5_months_ago(): void
    {
        $accommodation = new Accommodation;
        $accommodation->verified_at = now()->subMonths(5);

        $this->assertFalse($accommodation->isObsolete());
    }

    // ─── RG-08 scope DB ──────────────────────────────────────────────────────

    public function test_query_finds_accommodations_with_null_verified_at(): void
    {
        $stage = Stage::factory()->create([
            'start_waypoint_id' => Waypoint::factory()->create()->id,
            'end_waypoint_id' => Waypoint::factory()->create()->id,
        ]);

        Accommodation::factory()->forStage($stage)->neverVerified()->create();
        Accommodation::factory()->forStage($stage)->create(['verified_at' => now()->subMonths(2)]);

        $obsolete = Accommodation::where(function ($q) {
            $q->whereNull('verified_at')
                ->orWhere('verified_at', '<', now()->subMonths(6));
        })->get();

        $this->assertCount(1, $obsolete);
        $this->assertNull($obsolete->first()->verified_at);
    }

    public function test_query_finds_accommodations_over_6_months(): void
    {
        $stage = Stage::factory()->create([
            'start_waypoint_id' => Waypoint::factory()->create()->id,
            'end_waypoint_id' => Waypoint::factory()->create()->id,
        ]);

        Accommodation::factory()->forStage($stage)->obsolete()->create();
        Accommodation::factory()->forStage($stage)->create(['verified_at' => now()->subMonths(2)]);

        $obsolete = Accommodation::where(function ($q) {
            $q->whereNull('verified_at')
                ->orWhere('verified_at', '<', now()->subMonths(6));
        })->get();

        $this->assertCount(1, $obsolete);
        $this->assertTrue($obsolete->first()->isObsolete());
    }
}
