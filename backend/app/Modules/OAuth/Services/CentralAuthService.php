<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ULTREIA-03 — Client du SSO central SiteV26 (Passport OAuth2).
 *
 * Pattern identique à Oikotaku/CentralAuthService :
 *   - Circuit breaker (3 failures / 60s → ouvert 30s)
 *   - exchangeCode (TKN-P0-2)
 *   - verifyToken avec X-App-ID: ultreiataku
 *   - appSlug() = 'ultreiataku' (AUTH_APP_ID .env)
 */
class CentralAuthService
{
    private const BREAKER_FAILURES_KEY = 'central_auth.breaker.failures';

    private const BREAKER_OPEN_KEY = 'central_auth.breaker.open';

    private const BREAKER_WINDOW_SECONDS = 60;

    private const BREAKER_OPEN_DURATION_SECONDS = 30;

    private const BREAKER_FAILURE_THRESHOLD = 3;

    /**
     * @return array{user: array<string, mixed>, panel_access: array<string, mixed>|null}|null
     */
    public function verifyToken(string $token): ?array
    {
        if ($this->breakerIsOpen()) {
            Log::warning('central_auth.breaker_open_short_circuit');

            return null;
        }

        try {
            $response = Http::timeout(120)
                ->acceptJson()
                ->withToken($token)
                ->withHeaders(['X-App-ID' => $this->appId()])
                ->post((string) config('services.auth.verify_url'), [
                    'panel' => config('app.panel'),
                ]);
        } catch (\Throwable $e) {
            Log::warning('central_auth.verify_transport_error', ['error' => $e->getMessage()]);
            $this->recordFailure();

            return null;
        }

        if (! $response->ok() || ! $response->json('valid')) {
            if ($response->status() >= 500) {
                $this->recordFailure();
            }

            return null;
        }

        $this->recordSuccess();

        $user = $response->json('user');

        if (! is_array($user)) {
            return null;
        }

        return [
            'user' => $user,
            'panel_access' => $user['app_access'] ?? $user['panel_access'] ?? null,
        ];
    }

    /**
     * TKN-P0-2 — Échange un code éphémère contre un access token Passport.
     */
    public function exchangeCode(string $code): ?string
    {
        $url = $this->exchangeUrl();

        try {
            $response = Http::timeout(120)
                ->acceptJson()
                ->asJson()
                ->withHeaders(['X-App-ID' => $this->appId()])
                ->post($url, ['code' => $code]);
        } catch (\Throwable $e) {
            Log::warning('sso.exchange.transport_error', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->ok()) {
            Log::info('sso.exchange.rejected', ['status' => $response->status()]);

            return null;
        }

        $token = $response->json('token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function loginUrl(): string
    {
        return (string) config('services.auth.login_url');
    }

    public function appSlug(): string
    {
        return $this->appId();
    }

    public function callbackUrl(): string
    {
        return rtrim((string) config('app.url'), '/') . '/admin/sso/callback';
    }

    private function exchangeUrl(): string
    {
        $configured = (string) config('services.auth.exchange_url', '');

        return $configured !== ''
            ? $configured
            : rtrim((string) config('services.auth.api_url', 'http://auth-app'), '/') . '/api/auth/sso/exchange';
    }

    private function appId(): string
    {
        $configured = (string) config('services.auth.app_id', '');

        if ($configured !== '') {
            return $configured;
        }

        $legacy = (string) config('app.panel', '');

        return $legacy !== '' ? $legacy : 'ultreiataku';
    }

    private function breakerIsOpen(): bool
    {
        try {
            return (bool) Cache::get(self::BREAKER_OPEN_KEY, false);
        } catch (\Throwable) {
            return false;
        }
    }

    private function recordFailure(): void
    {
        try {
            $count = (int) Cache::get(self::BREAKER_FAILURES_KEY, 0);
            $count++;
            Cache::put(self::BREAKER_FAILURES_KEY, $count, self::BREAKER_WINDOW_SECONDS);

            if ($count >= self::BREAKER_FAILURE_THRESHOLD) {
                Cache::put(self::BREAKER_OPEN_KEY, true, self::BREAKER_OPEN_DURATION_SECONDS);
                Cache::forget(self::BREAKER_FAILURES_KEY);
                Log::error('central_auth.breaker_opened');
            }
        } catch (\Throwable) {
            Log::warning('central_auth.breaker_cache_unavailable');
        }
    }

    private function recordSuccess(): void
    {
        try {
            Cache::forget(self::BREAKER_FAILURES_KEY);
        } catch (\Throwable) {
        }
    }
}
