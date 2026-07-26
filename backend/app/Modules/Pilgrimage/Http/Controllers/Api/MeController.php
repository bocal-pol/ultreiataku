<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Http\Resources\PilgrimResource;
use App\Modules\Pilgrimage\Models\Pilgrim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Utilisateur courant + son profil Pilgrim (créé au premier accès si absent,
     * même logique que SsoCallbackController pour les logins API sans passage Filament).
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $pilgrim = Pilgrim::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->name,
                'preferred_locale' => 'fr',
                'configuration' => 'solo',
            ],
        );

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'pilgrim' => new PilgrimResource($pilgrim),
        ]);
    }
}
