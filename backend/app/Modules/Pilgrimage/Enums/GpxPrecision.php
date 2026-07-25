<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum GpxPrecision: string
{
    case Exact = 'exact';
    case Approximate = 'approximate';

    public function label(): string
    {
        return match ($this) {
            GpxPrecision::Exact => 'Exacte (GPS terrain)',
            GpxPrecision::Approximate => 'Approximative (calculée)',
        };
    }
}
