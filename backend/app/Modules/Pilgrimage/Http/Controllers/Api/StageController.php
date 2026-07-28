<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\StageResource;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StageController extends Controller
{
    /**
     * GET /api/pilgrimage/stages
     * Liste des stages, filtrable par route_id, difficulty, country.
     * Supports: ?route_id=uuid, ?difficulty=easy, ?include=waypoints,accommodations,meals, ?per_page=15.
     *
     * BUG-P1-001 : tri par route_id puis sort_order pour éviter l'entremêlement
     * des étapes de deux routes partageant les mêmes valeurs sort_order (1..N).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Stage::query()->with(['startWaypoint', 'endWaypoint', 'route']);

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->input('route_id'));
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->input('difficulty'));
        }

        if ($request->filled('country')) {
            $query->whereHas('route', function ($q) use ($request): void {
                $q->where('country', strtoupper((string) $request->input('country')));
            });
        }

        $includes = $this->parseIncludes($request);

        if (in_array('waypoints', $includes, true)) {
            $query->with('waypoints');
        }

        if (in_array('gpx_traces', $includes, true)) {
            $query->with('gpxTraces');
        }

        if (in_array('accommodations', $includes, true)) {
            $query->with('accommodations');
        }

        if (in_array('meals', $includes, true)) {
            $query->with('meals');
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $stages = $query
            ->orderBy('route_id')
            ->orderBy('sort_order')
            ->paginate($perPage);

        return StageResource::collection($stages);
    }

    /**
     * GET /api/pilgrimage/stages/{code}
     * Détail stage. Supports: ?include=waypoints,gpx_traces,accommodations,meals.
     * Les accommodations sont triées is_primary first (RG-02).
     */
    public function show(Request $request, string $code): StageResource|JsonResponse
    {
        $includes = $this->parseIncludes($request);

        $with = ['route', 'startWaypoint', 'endWaypoint'];

        if (in_array('waypoints', $includes, true)) {
            $with[] = 'waypoints';
        }

        if (in_array('gpx_traces', $includes, true)) {
            $with[] = 'gpxTraces';
        }

        if (in_array('accommodations', $includes, true)) {
            $with[] = 'accommodations';
        }

        if (in_array('meals', $includes, true)) {
            $with[] = 'meals';
        }

        $stage = Stage::where('code', strtoupper($code))
            ->with($with)
            ->first();

        if ($stage === null) {
            return response()->json([
                'type' => 'https://ultreiataku.example/errors/not-found',
                'title' => 'Stage not found',
                'status' => 404,
                'detail' => "L'étape '{$code}' n'existe pas.",
            ], 404);
        }

        return new StageResource($stage);
    }

    /** @return list<string> */
    private function parseIncludes(Request $request): array
    {
        $include = (string) $request->input('include', '');

        /** @var list<string> */
        return array_values(array_filter(array_map('trim', explode(',', $include))));
    }
}
