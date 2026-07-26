<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\WaypointResource;
use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WaypointController extends Controller
{
    /**
     * GET /api/pilgrimage/waypoints
     * Liste des waypoints actifs, filtrable par type et poi_category.
     * Supports: ?type=city, ?type=poi, ?poi_category=religious, ?per_page=15.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Waypoint::query()->where('is_active', true);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('poi_category')) {
            $query->where('poi_category', $request->input('poi_category'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $waypoints = $query->orderBy('slug')->paginate($perPage);

        return WaypointResource::collection($waypoints);
    }

    /**
     * GET /api/pilgrimage/waypoints/{slug}
     * Détail waypoint.
     */
    public function show(string $slug): WaypointResource|JsonResponse
    {
        $waypoint = Waypoint::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($waypoint === null) {
            return response()->json([
                'type' => 'https://ultreiataku.example/errors/not-found',
                'title' => 'Waypoint not found',
                'status' => 404,
                'detail' => "Le waypoint '{$slug}' n'existe pas ou n'est pas actif.",
            ], 404);
        }

        return new WaypointResource($waypoint);
    }
}
