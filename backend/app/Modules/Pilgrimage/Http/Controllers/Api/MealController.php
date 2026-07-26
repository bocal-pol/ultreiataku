<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\MealResource;
use App\Modules\Pilgrimage\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MealController extends Controller
{
    /**
     * GET /api/pilgrimage/meals
     * Filtrable : stage_id, meal_type.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Meal::query()->with(['stage', 'waypoint']);

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->input('stage_id'));
        }

        if ($request->filled('meal_type')) {
            $query->where('meal_type', $request->input('meal_type'));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $meals = $query->orderBy('meal_type')->paginate($perPage);

        return MealResource::collection($meals);
    }

    /**
     * GET /api/pilgrimage/meals/{id}
     */
    public function show(string $id): MealResource|JsonResponse
    {
        $meal = Meal::with(['stage', 'waypoint'])->find($id);

        if ($meal === null) {
            return response()->json([
                'type' => 'https://ultreiataku.example/errors/not-found',
                'title' => 'Meal not found',
                'status' => 404,
                'detail' => "Le repas '{$id}' n'existe pas.",
            ], 404);
        }

        return new MealResource($meal);
    }
}
