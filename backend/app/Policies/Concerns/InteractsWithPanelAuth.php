<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Support\PanelAuth;

/**
 * Centralise la lecture du rôle session pour les Policies Filament.
 * Pattern identique à Oikotaku/AdminOnlyPolicy.
 */
trait InteractsWithPanelAuth
{
    protected function isSuperAdmin(): bool
    {
        return PanelAuth::isSuperAdmin();
    }

    protected function role(): string
    {
        return strtolower((string) (PanelAuth::role() ?? ''));
    }

    protected function isAdmin(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($this->role(), ['admin', 'super-admin'], true);
    }
}
