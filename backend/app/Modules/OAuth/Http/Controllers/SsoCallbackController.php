<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\OAuth\Services\CentralAuthService;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Support\PanelAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ULTREIA-03 — Callback SSO Filament (ADR-U06).
 *
 * Flux :
 *   RedirectToCentralAuth (middleware) → Auth frontend SSO
 *   → GET /admin/sso/callback?code=...&state=...
 *   → exchange code → verify token → createOrUpdate User
 *   → auto-create Pilgrim (ULTREIA-03 : premier login SSO)
 *   → Auth::login() session Filament → redirect /admin
 *
 * Règles anti-régression contrat SSO :
 *   - Vérification CSRF state (hash_equals timing-safe)
 *   - Support code éphémère (TKN-P0-2) + legacy token
 *   - PanelAuth::canAccess() avant redirection /admin
 */
class SsoCallbackController extends Controller
{
    public function __invoke(Request $request, CentralAuthService $centralAuth): RedirectResponse
    {
        // ─── CSRF state ────────────────────────────────────────────────────────
        $incomingState = $request->string('state')->toString();
        $sessionState = (string) ($request->session()->pull('oauth_state', '') ?? '');

        // P1-01 — Le state MUST être présent des deux côtés.
        // Ancienne condition : `$incomingState !== '' || $sessionState !== ''`
        //   → si les deux sont '' (session expirée + callback sans state), le check était bypassé.
        // Nouvelle condition : on exige `$sessionState !== ''` ET `hash_equals()`.
        // Si $sessionState est vide (session expirée / premier callback / cookie absent),
        // on rejette immédiatement — pas de fallback silencieux.
        if (! hash_equals($sessionState, $incomingState) || $sessionState === '') {
            Log::warning('sso.callback.csrf_state_mismatch', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_state_empty' => $sessionState === '',
            ]);

            return $this->redirectToLogin($centralAuth, 'csrf_mismatch');
        }

        // ─── Exchange code / token ─────────────────────────────────────────────
        $code = $request->string('code')->toString();
        $token = '';

        if ($code !== '') {
            $token = (string) ($centralAuth->exchangeCode($code) ?? '');

            if ($token === '') {
                return $this->redirectToLogin($centralAuth, 'invalid_code');
            }
        } else {
            $token = $request->string('token')->toString();

            if ($token !== '') {
                Log::warning('sso.callback.legacy_token_query', [
                    'ip' => $request->ip(),
                    'note' => 'TKN-P0-2 deprecation: query-string token will be removed.',
                ]);
            }
        }

        if ($token === '') {
            return $this->redirectToLogin($centralAuth, 'missing_token');
        }

        // ─── Vérification token ────────────────────────────────────────────────
        $verified = $centralAuth->verifyToken($token);

        if ($verified === null) {
            return $this->redirectToLogin($centralAuth, 'invalid_token');
        }

        $authUser = $verified['user'];
        $panelAccess = $verified['panel_access'];

        // ─── Sync User local (email unique, jamais de mot de passe réel) ───────
        /** @var User $user */
        $user = DB::transaction(function () use ($authUser): User {
            /** @var User $user */
            $user = User::query()->updateOrCreate(
                ['email' => $authUser['email']],
                [
                    'name' => $authUser['name'],
                    'password' => Str::password(32),
                    'email_verified_at' => now(),
                ],
            );

            // ULTREIA-03 — Auto-création du Pilgrim au premier login SSO
            Pilgrim::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $authUser['name'],
                    'preferred_locale' => $authUser['locale'] ?? 'fr',
                    'configuration' => 'solo',
                ],
            );

            return $user;
        });

        // ─── Session Filament ──────────────────────────────────────────────────
        Auth::login($user, remember: false);
        $request->session()->regenerate(); // prévient la fixation de session

        $request->session()->put('auth_service_user', $authUser);

        // P1-03 — Token SSO NON persisté en session.
        // L'ancien `$request->session()->put('auth_service_token', $token)` stockait
        // le Bearer token en session Redis (SESSION_ENCRYPT=true mais REDIS_PASSWORD=null).
        // Ce stockage est inutile fonctionnellement : seuls auth_service_user et
        // auth_panel_access sont utilisés pour l'UX Filament. Supprimé.

        // Stocke le pilgrim_id en session (utilisé par PanelAuth::pilgrimId())
        $pilgrim = Pilgrim::query()->where('user_id', $user->id)->first();

        if ($pilgrim !== null) {
            $request->session()->put('auth_pilgrim_id', $pilgrim->id);
        }

        if (is_array($panelAccess)) {
            $request->session()->put('auth_panel_access', $panelAccess);
        } elseif (($authUser['is_super_admin'] ?? false) === true) {
            $request->session()->put('auth_panel_access', [
                'panel' => config('app.panel'),
                'role' => 'super-admin',
                'status' => 'approved',
                'can_access' => true,
                'can_manage_users' => true,
            ]);
        }

        Log::info('sso.callback.login_success', [
            'user_email' => $user->email,
            'pilgrim_id' => $pilgrim?->id,
        ]);

        if (! PanelAuth::canAccess()) {
            // Pas d'accès Filament → redirection vers le frontend (pas de page admin access)
            $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

            return redirect()->away("{$frontendUrl}?error=forbidden_admin");
        }

        return redirect()->intended('/admin');
    }

    private function redirectToLogin(CentralAuthService $centralAuth, string $error): RedirectResponse
    {
        $loginUrl = $centralAuth->loginUrl()
            . '?return=' . urlencode($centralAuth->callbackUrl())
            . '&error=' . $error;

        return redirect()->away($loginUrl);
    }
}
