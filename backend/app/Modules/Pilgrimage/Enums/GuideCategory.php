<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum GuideCategory: string
{
    case LeCorps = 'Le Corps';
    case Pratique = 'Pratique';
    case LeVoyage = 'Le Voyage';

    public function label(): string
    {
        return match ($this) {
            self::LeCorps => 'Le Corps',
            self::Pratique => 'Pratique',
            self::LeVoyage => 'Le Voyage',
        };
    }
}
