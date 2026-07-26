<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\ItemAssignmentResource;
use App\Modules\Pilgrimage\Http\Resources\PackItemResource;
use App\Modules\Pilgrimage\Http\Resources\PackScenarioResource;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\ItemAssignment;
use App\Modules\Pilgrimage\Models\PackItem;
use App\Modules\Pilgrimage\Models\PackScenario;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * ULTREIA-43 — API REST Sac (PackScenario, PackItem, ItemAssignment).
 *
 * Routes :
 *   GET    /api/pilgrimage/pilgrims/{pilgrimId}/pack-scenarios
 *   GET    /api/pilgrimage/pack-scenarios/{id}
 *   POST   /api/pilgrimage/pack-scenarios
 *   PUT    /api/pilgrimage/pack-scenarios/{id}
 *   POST   /api/pilgrimage/pack-scenarios/{id}/items
 *   POST   /api/pilgrimage/departures/{id}/assignments
 */
class PackScenarioController extends Controller
{
    // ─── GET /api/pilgrimage/pilgrims/{pilgrimId}/pack-scenarios ──────────────

    public function indexForPilgrim(Request $request, string $pilgrimId): JsonResponse
    {
        $targetPilgrim = Pilgrim::query()->findOrFail($pilgrimId);

        $currentPilgrim = Pilgrim::query()->where('user_id', $request->user()->id)->firstOrFail();

        // Restriction : seul le propriétaire ou un organizer d'un Trip commun peut voir
        $canView = $targetPilgrim->id === $currentPilgrim->id
            || $this->isOrganizerOfTripWithPilgrim($currentPilgrim, $targetPilgrim->id);

        if (! $canView) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $scenarios = PackScenario::query()
            ->where('pilgrim_id', $pilgrimId)
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => PackScenarioResource::collection($scenarios),
        ]);
    }

    // ─── GET /api/pilgrimage/pack-scenarios/{id} ──────────────────────────────

    public function show(Request $request, string $scenarioId): JsonResponse
    {
        $scenario = PackScenario::query()
            ->with(['items' => fn ($q) => $q->orderBy('category')->orderBy('sort_order')])
            ->findOrFail($scenarioId);

        $this->authorize('view', $scenario);

        return response()->json([
            'data' => new PackScenarioResource($scenario),
        ]);
    }

    // ─── POST /api/pilgrimage/pack-scenarios ──────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', PackScenario::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'target_base_weight_kg' => 'nullable|numeric|min:1|max:30',
            'configuration' => 'nullable|in:solo,duo',
            'season' => 'nullable|in:spring,summer,autumn,winter',
        ], [
            'name.required' => 'Le nom du scénario est obligatoire.',
            'configuration.in' => 'La configuration doit être solo ou duo.',
            'season.in' => 'La saison doit être spring, summer, autumn ou winter.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pilgrim = Pilgrim::query()->where('user_id', $request->user()->id)->firstOrFail();

        $scenario = DB::transaction(function () use ($validator, $pilgrim): PackScenario {
            return PackScenario::query()->create(array_merge(
                $validator->validated(),
                ['pilgrim_id' => $pilgrim->id],
            ));
        });

        Log::info('PackScenario created', [
            'scenario_id' => $scenario->id,
            'pilgrim_id' => $pilgrim->id,
            'name' => $scenario->name,
        ]);

        return response()->json(['data' => new PackScenarioResource($scenario)], 201);
    }

    // ─── PUT /api/pilgrimage/pack-scenarios/{id} ──────────────────────────────

    public function update(Request $request, string $scenarioId): JsonResponse
    {
        $scenario = PackScenario::query()->findOrFail($scenarioId);

        $this->authorize('update', $scenario);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'target_base_weight_kg' => 'nullable|numeric|min:1|max:30',
            'configuration' => 'nullable|in:solo,duo',
            'season' => 'nullable|in:spring,summer,autumn,winter',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($scenario, $validator): void {
            $scenario->update($validator->validated());
        });

        $scenario->load('items');

        return response()->json(['data' => new PackScenarioResource($scenario)]);
    }

    // ─── POST /api/pilgrimage/pack-scenarios/{id}/items ───────────────────────

    public function addItem(Request $request, string $scenarioId): JsonResponse
    {
        $scenario = PackScenario::query()->findOrFail($scenarioId);

        $this->authorize('addItem', $scenario);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'category' => 'required|in:portage,sleeping,cooking,water,clothing,hygiene,health,navigation,misc',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'weight_g' => 'required|integer|min:1',
            'is_shared' => 'nullable|boolean',
            'is_consumable' => 'nullable|boolean',
            'replacement_km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Le nom de l\'item est obligatoire.',
            'category.required' => 'La catégorie est obligatoire.',
            'category.in' => 'Catégorie invalide.',
            'weight_g.required' => 'Le poids est obligatoire.',
            'weight_g.min' => 'Le poids doit être supérieur à 0.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = DB::transaction(function () use ($scenario, $validator): PackItem {
            return PackItem::query()->create(array_merge(
                $validator->validated(),
                ['pack_scenario_id' => $scenario->id],
            ));
        });

        Log::info('PackItem added', [
            'item_id' => $item->id,
            'scenario_id' => $scenario->id,
            'category' => $item->category->value,
            'weight_g' => $item->weight_g,
        ]);

        return response()->json(['data' => new PackItemResource($item)], 201);
    }

    // ─── POST /api/pilgrimage/departures/{id}/assignments ─────────────────────

    public function addAssignment(Request $request, string $departureId): JsonResponse
    {
        $departure = Departure::query()->with('trip')->findOrFail($departureId);

        $this->authorize('create', [ItemAssignment::class, $departure]);

        $validator = Validator::make($request->all(), [
            'pack_item_id' => 'required|uuid|exists:pack_items,id',
            'assigned_to_pilgrim_id' => 'required|uuid|exists:pilgrims,id',
            'from_stage_id' => 'nullable|uuid|exists:stages,id',
            'to_stage_id' => 'nullable|uuid|exists:stages,id',
            'notes' => 'nullable|string',
        ], [
            'pack_item_id.exists' => 'L\'item de sac est introuvable.',
            'assigned_to_pilgrim_id.exists' => 'Le pèlerin est introuvable.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $assignment = DB::transaction(function () use ($departure, $validator): ItemAssignment {
            return ItemAssignment::query()->create(array_merge(
                $validator->validated(),
                ['departure_id' => $departure->id],
            ));
        });

        Log::info('ItemAssignment created', [
            'assignment_id' => $assignment->id,
            'departure_id' => $departure->id,
            'pack_item_id' => $assignment->pack_item_id,
            'assigned_to_pilgrim_id' => $assignment->assigned_to_pilgrim_id,
        ]);

        $assignment->load('packItem');

        return response()->json(['data' => new ItemAssignmentResource($assignment)], 201);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function isOrganizerOfTripWithPilgrim(Pilgrim $viewer, string $targetPilgrimId): bool
    {
        return Trip::query()
            ->where('organizer_id', $viewer->id)
            ->whereHas('members', function ($q) use ($targetPilgrimId): void {
                $q->where('pilgrim_id', $targetPilgrimId);
            })
            ->exists();
    }
}
