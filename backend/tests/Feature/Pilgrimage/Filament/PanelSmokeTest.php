<?php

declare(strict_types=1);

namespace Tests\Feature\Pilgrimage\Filament;

use App\Models\User;
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
 */
class PanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        // Autoriser toutes les policies pour ce smoke test : on teste le RENDU
        // des pages (API Filament 3→4), pas l'autorisation métier (testée ailleurs).
        \Illuminate\Support\Facades\Gate::before(fn () => true);
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

        $route = \App\Modules\Pilgrimage\Models\PilgrimageRoute::factory()->create();
        $waypoint = \App\Modules\Pilgrimage\Models\Waypoint::factory()->create();
        $stage = \App\Modules\Pilgrimage\Models\Stage::factory()->create(['route_id' => $route->id]);
        $accommodation = \App\Modules\Pilgrimage\Models\Accommodation::factory()->create(['stage_id' => $stage->id]);
        $meal = \App\Modules\Pilgrimage\Models\Meal::factory()->create(['stage_id' => $stage->id]);

        $cases = [
            [$ns . 'RouteResource\\Pages\\EditRoute', $route->getKey()],
            [$ns . 'StageResource\\Pages\\EditStage', $stage->getKey()],
            [$ns . 'WaypointResource\\Pages\\EditWaypoint', $waypoint->getKey()],
            [$ns . 'AccommodationResource\\Pages\\EditAccommodation', $accommodation->getKey()],
            [$ns . 'MealResource\\Pages\\EditMeal', $meal->getKey()],
        ];

        foreach ($cases as [$page, $key]) {
            Livewire::test($page, ['record' => $key])->assertOk();
            $this->assertTrue(true, $page . ' monte');
        }
    }
}
