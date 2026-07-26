<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\RouteResource;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RouteController extends Controller
{
    /**
     * GET /api/pilgrimage/routes
     * Liste des routes actives, filtrable par country.
     * Supports: ?country=BE, ?include=stages, ?per_page=15.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PilgrimageRoute::query()->where('is_active', true);

        if ($request->filled('country')) {
            $query->where('country', strtoupper((string) $request->input('country')));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $routes = $query->orderBy('sort_order')->paginate($perPage);

        if ($request->filled('include')) {
            $includes = explode(',', (string) $request->input('include'));
            if (in_array('stages', $includes, true)) {
                $routes->load(['stages']);
            }
        }

        return RouteResource::collection($routes);
    }

    /**
     * GET /api/pilgrimage/routes/{slug}
     * Détail route. Supports: ?include=stages.
     */
    public function show(Request $request, string $slug): RouteResource|JsonResponse
    {
        $query = PilgrimageRoute::where('slug', $slug)->where('is_active', true);

        if ($request->filled('include') && str_contains((string) $request->input('include'), 'stages')) {
            $query->with(['stages' => function ($q): void {
                $q->orderBy('sort_order')->with(['startWaypoint', 'endWaypoint']);
            }]);
        }

        $route = $query->first();

        if ($route === null) {
            return response()->json([
                'type' => 'https://ultreiataku.example/errors/not-found',
                'title' => 'Route not found',
                'status' => 404,
                'detail' => "La route '{$slug}' n'existe pas ou n'est pas active.",
            ], 404);
        }

        return new RouteResource($route);
    }
}
