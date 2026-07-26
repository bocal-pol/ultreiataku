<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum PackSeason: string
{
    case Spring = 'spring';
    case Summer = 'summer';
    case Autumn = 'autumn';
    case Winter = 'winter';

    public function label(): string
    {
        return match ($this) {
            self::Spring => 'Printemps',
            self::Summer => 'Été',
            self::Autumn => 'Automne',
            self::Winter => 'Hiver',
        };
    }
}
