<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\OAuth\Services\CentralAuthService;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

/**
 * ULTREIA-03 / ADR-U06 — Middleware Filament SSO.
 *
 * Remplace l'Authenticate natif Filament.
 * Génère un state CSRF avant la redirection (prévient CSRF OAuth RFC 6749 §10.12).
 * Construction de l'URL avec ?app=ultreiataku (scope obligatoire côté Auth Passport).
 */
class RedirectToCentralAuth extends FilamentAuthenticate
{
    protected function redirectTo($request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        /** @var CentralAuthService $centralAuth */
        $centralAuth = app(CentralAuthService::class);

        // State CSRF CSPRNG — stocké en session avant redirect
        $state = bin2hex(random_bytes(32));
        $request->session()->put('oauth_state', $state);

        return $centralAuth->loginUrl()
            . '?app=' . urlencode($centralAuth->appSlug())
            . '&return=' . urlencode($centralAuth->callbackUrl())
            . '&state=' . urlencode($state);
    }
}
