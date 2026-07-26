<?php

declare(strict_types=1);

namespace App\Modules\Vault\Providers;

use App\Modules\Vault\Services\VaultClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * VaultServiceProvider — DOIT être enregistré EN PREMIER dans bootstrap/providers.php.
 *
 * Lit le secret MinIO d'Ultreiataku depuis Vault (secret/ultreiataku/minio — ADR-U02)
 * et override la config filesystems avant que les providers suivants n'ouvrent les disks.
 *
 * Fallback .env si Vault est down ou VAULT_ENABLED=false.
 * Jamais d'exception fatale.
 */
final class VaultServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VaultClient::class, function (): VaultClient {
            return new VaultClient(
                address: (string) config('vault.address', ''),
                token: (string) config('vault.token', ''),
                skipVerify: (bool) config('vault.skip_verify', false),
            );
        });
    }

    public function boot(): void
    {
        $enabled = (bool) config('vault.enabled', false);

        if (! $enabled) {
            Log::info('Vault integration disabled', ['reason' => 'VAULT_ENABLED is false']);

            return;
        }

        try {
            $this->applyVaultOverrides();
        } catch (Throwable $e) {
            Log::warning('Vault overrides failed, fallback .env', [
                'error' => $e->getMessage(),
                'class' => $e::class,
            ]);
        }
    }

    private function applyVaultOverrides(): void
    {
        /** @var VaultClient $client */
        $client = $this->app->make(VaultClient::class);

        // ─── DB ────────────────────────────────────────────────────────────────
        $dbSecret = $client->readSecret('sitev26/db');

        if ($dbSecret !== []) {
            $this->overrideIfPresent('database.connections.pgsql.host', $dbSecret['host'] ?? null);
            $this->overrideIfPresent('database.connections.pgsql.port', $dbSecret['port'] ?? null);
            $this->overrideIfPresent('database.connections.pgsql.database', $dbSecret['database'] ?? null);
            $this->overrideIfPresent('database.connections.pgsql.username', $dbSecret['username'] ?? null);
            $this->overrideIfPresent('database.connections.pgsql.password', $dbSecret['password'] ?? null);
        } else {
            Log::warning('Vault DB secret unavailable, keeping .env', ['path' => 'sitev26/db']);
        }

        // ─── MinIO Ultreiataku (ADR-U02) ───────────────────────────────────────
        // Secret: secret/ultreiataku/minio (provisionnement ops : svc-vault)
        $minioSecret = $client->readSecret('ultreiataku/minio');

        if ($minioSecret !== []) {
            // Disk minio_gpx
            $this->overrideIfPresent('filesystems.disks.minio_gpx.key', $minioSecret['key'] ?? null);
            $this->overrideIfPresent('filesystems.disks.minio_gpx.secret', $minioSecret['secret'] ?? null);
            // Disk minio_journal
            $this->overrideIfPresent('filesystems.disks.minio_journal.key', $minioSecret['key'] ?? null);
            $this->overrideIfPresent('filesystems.disks.minio_journal.secret', $minioSecret['secret'] ?? null);
            // Disk minio_images
            $this->overrideIfPresent('filesystems.disks.minio_images.key', $minioSecret['key'] ?? null);
            $this->overrideIfPresent('filesystems.disks.minio_images.secret', $minioSecret['secret'] ?? null);
        } else {
            Log::warning('Vault MinIO secret unavailable, keeping .env', ['path' => 'ultreiataku/minio']);
        }

        // ─── Redis ─────────────────────────────────────────────────────────────
        $redisSecret = $client->readSecret('sitev26/redis');

        if ($redisSecret !== []) {
            $this->overrideIfPresent('database.redis.default.host', $redisSecret['host'] ?? null);
            $this->overrideIfPresent('database.redis.default.password', $redisSecret['password'] ?? null);
            $this->overrideIfPresent('database.redis.cache.host', $redisSecret['host'] ?? null);
            $this->overrideIfPresent('database.redis.cache.password', $redisSecret['password'] ?? null);
        } else {
            Log::warning('Vault Redis secret unavailable, keeping .env', ['path' => 'sitev26/redis']);
        }
    }

    private function overrideIfPresent(string $configKey, mixed $value): void
    {
        if ($value === null || (is_string($value) && $value === '')) {
            return;
        }

        config([$configKey => $value]);
    }
}
