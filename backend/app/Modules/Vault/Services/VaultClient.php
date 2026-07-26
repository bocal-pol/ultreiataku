<?php

declare(strict_types=1);

namespace App\Modules\Vault\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Client HTTP pour HashiCorp Vault (KV v2).
 * Copie conforme du pattern Oikotaku — timeout 2s, fallback silencieux.
 * Secret MinIO provisionné : secret/ultreiataku/minio (ADR-U02).
 */
final class VaultClient
{
    private const TIMEOUT_SECONDS = 2;

    private const KV_V2_MOUNT = 'secret';

    public function __construct(
        private readonly string $address,
        private readonly string $token,
        private readonly bool $skipVerify = false,
    ) {}

    /**
     * Lit un secret KV v2 — retourne [] si indisponible (jamais d'exception).
     *
     * @return array<string, mixed>
     */
    public function readSecret(string $path): array
    {
        $path = trim($path, '/');

        if ($path === '' || $this->address === '' || $this->token === '') {
            return [];
        }

        $url = sprintf('%s/v1/%s/data/%s', rtrim($this->address, '/'), self::KV_V2_MOUNT, $path);

        try {
            $request = Http::withHeaders([
                'X-Vault-Token' => $this->token,
                'Accept' => 'application/json',
            ])
                ->timeout(self::TIMEOUT_SECONDS)
                ->connectTimeout(self::TIMEOUT_SECONDS)
                ->acceptJson();

            if ($this->skipVerify) {
                $request = $request->withoutVerifying();
            }

            $response = $request->get($url);

            if (! $response->successful()) {
                Log::warning('Vault read failed', ['path' => $path, 'status' => $response->status()]);

                return [];
            }

            /** @var array<string, mixed> $payload */
            $payload = (array) $response->json();
            $data = $payload['data']['data'] ?? null;

            if (! is_array($data)) {
                Log::warning('Vault unexpected payload shape', ['path' => $path]);

                return [];
            }

            /** @var array<string, mixed> $data */
            return $data;
        } catch (ConnectionException $e) {
            Log::warning('Vault connection failed', ['path' => $path, 'error' => $e->getMessage()]);

            return [];
        } catch (RequestException $e) {
            Log::warning('Vault request failed', ['path' => $path, 'error' => $e->getMessage()]);

            return [];
        } catch (Throwable $e) {
            Log::warning('Vault unexpected exception', ['path' => $path, 'error' => $e->getMessage()]);

            return [];
        }
    }

    public function isHealthy(): bool
    {
        if ($this->address === '') {
            return false;
        }

        try {
            $request = Http::timeout(self::TIMEOUT_SECONDS)->connectTimeout(self::TIMEOUT_SECONDS)->acceptJson();

            if ($this->skipVerify) {
                $request = $request->withoutVerifying();
            }

            $response = $request->get(sprintf('%s/v1/sys/health', rtrim($this->address, '/')));

            return $response->status() === 200 || $response->status() === 429;
        } catch (Throwable) {
            return false;
        }
    }
}
