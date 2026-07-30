<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Déconnexion — POST /api/pilgrimage/logout
 *
 * Invalide la session web courante (guard web driver session).
 * Compatible avec le pattern monorepo : session cookie, pas de Bearer token.
 *
 * Étapes :
 *   1. Auth::guard('web')->logout()     — invalide l'auth en session
 *   2. session()->invalidate()          — détruit la session (flash + all)
 *   3. session()->regenerateToken()     — nouveau CSRF token
 *
 * Retourne 200 JSON plutôt que 204 : le SPA peut afficher un message de confirmation.
 * Idempotent : si déjà déconnecté, répond 200 sans erreur.
 */
class LogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('auth.logout', ['user_id' => $userId]);

        return response()->json([
            'message' => 'Bonne route, pèlerin. À bientôt sur le Chemin.',
        ]);
    }
}
