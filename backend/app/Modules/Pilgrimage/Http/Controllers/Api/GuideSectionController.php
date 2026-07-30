<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\GuideSectionResource;
use App\Modules\Pilgrimage\Models\GuideSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API publique de lecture des sections Guide pèlerin.
 *
 * GET /api/pilgrimage/guides          — liste publiée, groupée par catégorie
 * GET /api/pilgrimage/guides/{slug}   — contenu complet d'une section
 *
 * Aucune authentification requise : contenu de préparation public,
 * accessible avant même la connexion SSO (lecture chemin, esprit du Chemin).
 */
class GuideSectionController extends Controller
{
    /**
     * GET /api/pilgrimage/guides
     *
     * Retourne les sections publiées groupées par catégorie, triées par sort_order.
     * Format : { "data": { "Le Corps": [...], "Pratique": [...] } }
     *
     * Note : on utilise ->toArray($request) sur chaque resource plutôt que
     * GuideSectionResource::collection() pour éviter le double wrapping "data"
     * dans la structure groupée.
     */
    public function index(Request $request): JsonResponse
    {
        $sections = GuideSection::query()
            ->published()
            ->orderBy('sort_order')
            ->get();

        $grouped = $sections
            ->groupBy(fn (GuideSection $s) => $s->category?->value ?? 'Autre')
            ->map(fn ($group) => $group->map(fn (GuideSection $s) => (new GuideSectionResource($s))->toArray($request)));

        return response()->json(['data' => $grouped]);
    }

    /**
     * GET /api/pilgrimage/guides/{slug}
     */
    public function show(string $slug): GuideSectionResource|JsonResponse
    {
        $section = GuideSection::query()
            ->published()
            ->where('slug', $slug)
            ->first();

        if ($section === null) {
            return response()->json([
                'type' => 'https://ultreiataku.example/errors/not-found',
                'title' => 'Guide section not found',
                'status' => 404,
                'detail' => "La section '{$slug}' n'existe pas ou n'est pas publiée.",
            ], 404);
        }

        return new GuideSectionResource($section);
    }
}
