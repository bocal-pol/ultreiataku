<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

/**
 * ULTREIA-50 — Visibilité d'une entrée de journal.
 *
 * RG-03 — Matrice :
 *   private  → auteur seul
 *   members  → auteur + participants + organizer
 *   public   → auteur + participants + organizer + observers (si Trip is_public)
 */
enum JournalVisibility: string
{
    case Private = 'private';
    case Members = 'members';
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Privée',
            self::Members => 'Membres',
            self::Public => 'Publique',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Private => 'gray',
            self::Members => 'info',
            self::Public => 'success',
        };
    }
}
