<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\AccommodationResource;
use App\Modules\Pilgrimage\Models\Accommodation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccommodationController extends Controller
{
    /**
     * GET /api/pilgrimage/accommodations
     * Filtrable : stage_id, type, bivouac_legal.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Accommodation::query()->with(['stage', 'waypoint']);

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->input('stage_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('bivouac_legal')) {
            $query->where('bivouac_legal', filter_var($request->input('bivouac_legal'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $accommodations = $query
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->paginate($perPage);

        return AccommodationResource::collection($accommodations);
    }

    /**
     * GET /api/pilgrimage/accommodations/{id}
     */
    public function show(string $id): AccommodationResource|JsonResponse
    {
        $accommodation = Accommodation::with(['stage', 'waypoint'])->find($id);

        if ($accommodation === null) {
            return response()->json([
                'type' => 'https://ultreiataku.example/errors/not-found',
                'title' => 'Accommodation not found',
                'status' => 404,
                'detail' => "L'hébergement '{$id}' n'existe pas.",
            ], 404);
        }

        return new AccommodationResource($accommodation);
    }
}
