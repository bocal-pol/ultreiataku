<?php

declare(strict_types=1);

namespace Tests\Unit\Pilgrimage;

use App\Models\User;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use App\Modules\Pilgrimage\Policies\TripPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests ULTREIA-33 — TripPolicy : matrice rôles 3 niveaux.
 *
 * Matrice (specs §4.3) :
 *   organizer   → CRUD complet, invitations, membres, occupancy
 *   participant → view, createDeparture, viewOccupancy
 *   observer    → view uniquement, NO occupancy, NO departure
 */
class TripPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TripPolicy $policy;

    private Trip $trip;

    private User $organizerUser;

    private Pilgrim $organizerPilgrim;

    private User $participantUser;

    private Pilgrim $participantPilgrim;

    private User $observerUser;

    private Pilgrim $observerPilgrim;

    private User $outsiderUser;

    private Pilgrim $outsiderPilgrim;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new TripPolicy();
        $route = PilgrimageRoute::factory()->create();

        $this->organizerUser = User::factory()->create();
        $this->organizerPilgrim = Pilgrim::factory()->create(['user_id' => $this->organizerUser->id]);

        $this->participantUser = User::factory()->create();
        $this->participantPilgrim = Pilgrim::factory()->create(['user_id' => $this->participantUser->id]);

        $this->observerUser = User::factory()->create();
        $this->observerPilgrim = Pilgrim::factory()->create(['user_id' => $this->observerUser->id]);

        $this->outsiderUser = User::factory()->create();
        $this->outsiderPilgrim = Pilgrim::factory()->create(['user_id' => $this->outsiderUser->id]);

        $this->trip = Trip::factory()->create([
            'route_id' => $route->id,
            'organizer_id' => $this->organizerPilgrim->id,
        ]);
        $this->trip->members()->attach($this->organizerPilgrim->id, ['role' => 'organizer', 'joined_at' => now()]);
        $this->trip->members()->attach($this->participantPilgrim->id, ['role' => 'participant', 'joined_at' => now()]);
        $this->trip->members()->attach($this->observerPilgrim->id, ['role' => 'observer', 'joined_at' => now()]);
    }

    // ─── view ─────────────────────────────────────────────────────────────────

    public function test_view_organizer(): void
    {
        $this->assertTrue($this->policy->view($this->organizerUser, $this->trip));
    }

    public function test_view_participant(): void
    {
        $this->assertTrue($this->policy->view($this->participantUser, $this->trip));
    }

    public function test_view_observer(): void
    {
        $this->assertTrue($this->policy->view($this->observerUser, $this->trip));
    }

    public function test_view_outsider_denied(): void
    {
        $this->assertFalse($this->policy->view($this->outsiderUser, $this->trip));
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function test_update_organizer(): void
    {
        $this->assertTrue($this->policy->update($this->organizerUser, $this->trip));
    }

    public function test_update_participant_denied(): void
    {
        $this->assertFalse($this->policy->update($this->participantUser, $this->trip));
    }

    public function test_update_observer_denied(): void
    {
        $this->assertFalse($this->policy->update($this->observerUser, $this->trip));
    }

    // ─── invite / manageMember ────────────────────────────────────────────────

    public function test_invite_organizer(): void
    {
        $this->assertTrue($this->policy->invite($this->organizerUser, $this->trip));
    }

    public function test_invite_participant_denied(): void
    {
        $this->assertFalse($this->policy->invite($this->participantUser, $this->trip));
    }

    public function test_manage_member_observer_denied(): void
    {
        $this->assertFalse($this->policy->manageMember($this->observerUser, $this->trip));
    }

    // ─── viewOccupancy ────────────────────────────────────────────────────────

    public function test_view_occupancy_organizer(): void
    {
        $this->assertTrue($this->policy->viewOccupancy($this->organizerUser, $this->trip));
    }

    public function test_view_occupancy_participant(): void
    {
        $this->assertTrue($this->policy->viewOccupancy($this->participantUser, $this->trip));
    }

    public function test_view_occupancy_observer_denied(): void
    {
        $this->assertFalse($this->policy->viewOccupancy($this->observerUser, $this->trip));
    }

    public function test_view_occupancy_outsider_denied(): void
    {
        $this->assertFalse($this->policy->viewOccupancy($this->outsiderUser, $this->trip));
    }

    // ─── createDeparture ──────────────────────────────────────────────────────

    public function test_create_departure_organizer(): void
    {
        $this->assertTrue($this->policy->createDeparture($this->organizerUser, $this->trip));
    }

    public function test_create_departure_participant(): void
    {
        $this->assertTrue($this->policy->createDeparture($this->participantUser, $this->trip));
    }

    public function test_create_departure_observer_denied(): void
    {
        $this->assertFalse($this->policy->createDeparture($this->observerUser, $this->trip));
    }

    public function test_create_departure_outsider_denied(): void
    {
        $this->assertFalse($this->policy->createDeparture($this->outsiderUser, $this->trip));
    }
}
