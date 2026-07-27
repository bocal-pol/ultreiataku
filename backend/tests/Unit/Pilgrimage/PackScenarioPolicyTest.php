<?php

declare(strict_types=1);

namespace Tests\Unit\Pilgrimage;

use App\Models\User;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Trip;
use App\Modules\Pilgrimage\Policies\PackScenarioPolicy;
use App\Modules\Pilgrimage\Services\TripAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ULTREIA-4T — Tests policy PackScenario.
 */
class PackScenarioPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PackScenarioPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        // I-02 : PackScenarioPolicy requiert TripAuthorizationService depuis le refacto themis
        $this->policy = new PackScenarioPolicy(new TripAuthorizationService);
    }

    private function makeUserWithPilgrim(): array
    {
        $user = User::factory()->create();
        $pilgrim = Pilgrim::factory()->create(['user_id' => $user->id]);

        return [$user, $pilgrim];
    }

    public function test_owner_can_view_own_scenario(): void
    {
        [$user, $pilgrim] = $this->makeUserWithPilgrim();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $pilgrim->id]);

        $this->assertTrue($this->policy->view($user, $scenario));
    }

    public function test_other_pilgrim_cannot_view_scenario(): void
    {
        [$user, $pilgrim] = $this->makeUserWithPilgrim();
        [$otherUser, $otherPilgrim] = $this->makeUserWithPilgrim();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $otherPilgrim->id]);

        $this->assertFalse($this->policy->view($user, $scenario));
    }

    public function test_organizer_of_shared_trip_can_view_member_scenario(): void
    {
        [$user, $pilgrim] = $this->makeUserWithPilgrim();
        [$otherUser, $otherPilgrim] = $this->makeUserWithPilgrim();

        $route = PilgrimageRoute::factory()->create();
        $trip = Trip::factory()->create([
            'organizer_id' => $pilgrim->id,
            'route_id' => $route->id,
        ]);
        $trip->members()->attach($pilgrim->id, ['role' => TripMemberRole::Organizer->value, 'joined_at' => now()]);
        $trip->members()->attach($otherPilgrim->id, ['role' => TripMemberRole::Participant->value, 'joined_at' => now()]);

        $scenario = PackScenario::factory()->create(['pilgrim_id' => $otherPilgrim->id]);

        $this->assertTrue($this->policy->view($user, $scenario));
    }

    public function test_owner_can_update_own_scenario(): void
    {
        [$user, $pilgrim] = $this->makeUserWithPilgrim();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $pilgrim->id]);

        $this->assertTrue($this->policy->update($user, $scenario));
    }

    public function test_other_pilgrim_cannot_update_scenario(): void
    {
        [$user, $pilgrim] = $this->makeUserWithPilgrim();
        [$otherUser, $otherPilgrim] = $this->makeUserWithPilgrim();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $otherPilgrim->id]);

        $this->assertFalse($this->policy->update($user, $scenario));
    }

    public function test_owner_can_add_item_to_own_scenario(): void
    {
        [$user, $pilgrim] = $this->makeUserWithPilgrim();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $pilgrim->id]);

        $this->assertTrue($this->policy->addItem($user, $scenario));
    }

    public function test_owner_can_delete_own_scenario(): void
    {
        [$user, $pilgrim] = $this->makeUserWithPilgrim();
        $scenario = PackScenario::factory()->create(['pilgrim_id' => $pilgrim->id]);

        $this->assertTrue($this->policy->delete($user, $scenario));
    }

    public function test_any_pilgrim_can_create(): void
    {
        [$user] = $this->makeUserWithPilgrim();

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_pilgrim_cannot_create(): void
    {
        $userWithoutPilgrim = User::factory()->create();

        $this->assertFalse($this->policy->create($userWithoutPilgrim));
    }
}
