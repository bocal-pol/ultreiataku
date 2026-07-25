<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum Country: string
{
    case BE = 'BE';
    case FR = 'FR';
    case ES = 'ES';

    public function label(): string
    {
        return match ($this) {
            Country::BE => 'Belgique',
            Country::FR => 'France',
            Country::ES => 'Espagne',
        };
    }
}
