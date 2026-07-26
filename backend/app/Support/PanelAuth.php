<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Helper de lecture du contexte SSO session (Filament admin).
 * Pattern identique à Oikotaku — conservé en attendant migration Spatie Permission.
 */
class PanelAuth
{
    /** @return array<string, mixed>|null */
    public static function authUser(): ?array
    {
        $user = session('auth_service_user');

        return is_array($user) ? $user : null;
    }

    /** @return array<string, mixed>|null */
    public static function panelAccess(): ?array
    {
        $access = session('auth_panel_access');

        return is_array($access) ? $access : null;
    }

    public static function canAccess(): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        return (bool) (self::panelAccess()['can_access'] ?? false);
    }

    public static function isPending(): bool
    {
        return (self::panelAccess()['status'] ?? null) === 'pending';
    }

    public static function role(): ?string
    {
        return self::panelAccess()['role'] ?? null;
    }

    public static function isSuperAdmin(): bool
    {
        return (bool) (self::authUser()['is_super_admin'] ?? false);
    }

    /**
     * Retourne le pilgrim_id SSO si disponible en session.
     * Injecté après auto-création du Pilgrim au callback.
     */
    public static function pilgrimId(): ?string
    {
        $pilgrimId = session('auth_pilgrim_id');

        return is_string($pilgrimId) ? $pilgrimId : null;
    }
}
