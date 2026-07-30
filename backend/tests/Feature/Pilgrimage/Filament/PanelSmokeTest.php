<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Filament;

use App\Models\User;
use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\GpxTrace;
use App\Modules\Pilgrimage\Models\GuideSection;
use App\Modules\Pilgrimage\Models\ItemAssignment;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\Meal;
use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use App\Modules\Pilgrimage\Models\Waypoint;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Smoke test du panel admin Filament — monte CHAQUE page List/Edit de chaque
 * Resource pour détecter les erreurs de rendu (API Filament 3→4, policies,
 * enums) que les tests unitaires ne voient pas.
 *
 * C'est le filet qui manquait : les 500 « Class Filament\Tables\Actions\… not
 * found » / « Forms\Components\Section not found » ne se révèlent qu'au montage.
 *
 * Session PanelAuth simulée en super_admin pour que les policies à bypass admin
 * (Trip, Departure, PackScenario, ItemAssignment, JournalEntry) autorisent l'accès.
 * Pas de Gate::before : on teste les VRAIES policies via InteractsWithPanelAuth.
 */
class PanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        // Un Pilgrim lié : les policies résolvent le pèlerin courant (roleOf, etc.)
        Pilgrim::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user, 'web');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        // Simule la session SSO panel admin (super_admin) pour que
        // InteractsWithPanelAuth::isAdmin() retourne true dans les policies.
        // Sans ça, TripPolicy::update(), DeparturePolicy::update(), etc. refusent
        // car le User de test n'est pas organizer des records créés par factory.
        session([
            'auth_service_user' => ['is_super_admin' => true],
            'auth_panel_access' => ['can_access' => true, 'role' => 'super-admin'],
        ]);
    }

    public function test_all_list_pages_mount(): void
    {
        $ns = 'App\\Modules\\Pilgrimage\\Filament\\Resources\\';
        $pages = [
            $ns . 'RouteResource\\Pages\\ListRoutes',
            $ns . 'StageResource\\Pages\\ListStages',
            $ns . 'WaypointResource\\Pages\\ListWaypoints',
            $ns . 'GpxTraceResource\\Pages\\ListGpxTraces',
            $ns . 'AccommodationResource\\Pages\\ListAccommodations',
            $ns . 'MealResource\\Pages\\ListMeals',
            $ns . 'TripResource\\Pages\\ListTrips',
            $ns . 'PilgrimResource\\Pages\\ListPilgrims',
            $ns . 'DepartureResource\\Pages\\ListDepartures',
            $ns . 'OccupancyResource\\Pages\\ListOccupancies',
            $ns . 'PackScenarioResource\\Pages\\ListPackScenarios',
            $ns . 'PackItemResource\\Pages\\ListPackItems',
            $ns . 'ItemAssignmentResource\\Pages\\ListItemAssignments',
            $ns . 'JournalEntryResource\\Pages\\ListJournalEntries',
            $ns . 'GuideSectionResource\\Pages\\ListGuideSections',
        ];

        foreach ($pages as $page) {
            Livewire::test($page)->assertOk();
            $this->assertTrue(true, $page . ' monte');
        }
    }

    public function test_edit_pages_mount(): void
    {
        // Crée un enregistrement minimal par entité via factory (pas de seed
        // complet : l'index partiel (route_id, day_number) WHERE is_variant=false
        // n'est pas répliqué par SQLite, ce qui fait échouer le seed massif — le
        // schéma Postgres réel est correct).
        $ns = 'App\\Modules\\Pilgrimage\\Filament\\Resources\\';

        // ─── Ressources sans policy enregistrée (toujours éditables) ──────────
        $route = PilgrimageRoute::factory()->create();
        $waypoint = Waypoint::factory()->create();
        $stage = Stage::factory()->create(['route_id' => $route->id]);
        $accommodation = Accommodation::factory()->create(['stage_id' => $stage->id]);
        $meal = Meal::factory()->create(['stage_id' => $stage->id]);
        $gpxTrace = GpxTrace::factory()->create(['stage_id' => $stage->id]);
        $guideSection = GuideSection::factory()->create();

        // ─── Ressources avec policy (bypass admin via session PanelAuth) ───────
        $pilgrim = Pilgrim::factory()->create();
        $trip = Trip::factory()->create([
            'route_id' => $route->id,
            'organizer_id' => $pilgrim->id,
        ]);
        $departure = Departure::factory()->create([
            'trip_id' => $trip->id,
            'pilgrim_id' => $pilgrim->id,
            'start_stage_id' => $stage->id,
            'end_stage_id' => $stage->id,
        ]);
        $packScenario = PackScenario::factory()->create(['pilgrim_id' => $pilgrim->id]);
        $packItem = PackItem::factory()->create(['pack_scenario_id' => $packScenario->id]);
        $itemAssignment = ItemAssignment::factory()->create([
            'departure_id' => $departure->id,
            'pack_item_id' => $packItem->id,
            'assigned_to_pilgrim_id' => $pilgrim->id,
        ]);
        $journalEntry = JournalEntry::factory()->create([
            'trip_id' => $trip->id,
            'pilgrim_id' => $pilgrim->id,
        ]);

        $cases = [
            // Ressources sans policy
            [$ns . 'RouteResource\\Pages\\EditRoute', $route->getKey()],
            [$ns . 'StageResource\\Pages\\EditStage', $stage->getKey()],
            [$ns . 'WaypointResource\\Pages\\EditWaypoint', $waypoint->getKey()],
            [$ns . 'AccommodationResource\\Pages\\EditAccommodation', $accommodation->getKey()],
            [$ns . 'MealResource\\Pages\\EditMeal', $meal->getKey()],
            [$ns . 'GpxTraceResource\\Pages\\EditGpxTrace', $gpxTrace->getKey()],
            [$ns . 'GuideSectionResource\\Pages\\EditGuideSection', $guideSection->getKey()],
            // Ressources avec policy (bypass admin actif via session super_admin)
            [$ns . 'TripResource\\Pages\\EditTrip', $trip->getKey()],
            [$ns . 'PilgrimResource\\Pages\\EditPilgrim', $pilgrim->getKey()],
            [$ns . 'DepartureResource\\Pages\\EditDeparture', $departure->getKey()],
            [$ns . 'PackScenarioResource\\Pages\\EditPackScenario', $packScenario->getKey()],
            [$ns . 'PackItemResource\\Pages\\EditPackItem', $packItem->getKey()],
            [$ns . 'ItemAssignmentResource\\Pages\\EditItemAssignment', $itemAssignment->getKey()],
            [$ns . 'JournalEntryResource\\Pages\\EditJournalEntry', $journalEntry->getKey()],
        ];

        foreach ($cases as [$page, $key]) {
            Livewire::test($page, ['record' => $key])->assertOk();
            $this->assertTrue(true, $page . ' monte');
        }
    }
}
