<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum PackCategory: string
{
    case Portage = 'portage';
    case Sleeping = 'sleeping';
    case Cooking = 'cooking';
    case Water = 'water';
    case Clothing = 'clothing';
    case Hygiene = 'hygiene';
    case Health = 'health';
    case Navigation = 'navigation';
    case Misc = 'misc';

    public function label(): string
    {
        return match ($this) {
            self::Portage => 'Portage',
            self::Sleeping => 'Couchage',
            self::Cooking => 'Cuisine',
            self::Water => 'Eau',
            self::Clothing => 'Vêtements',
            self::Hygiene => 'Hygiène',
            self::Health => 'Santé',
            self::Navigation => 'Navigation',
            self::Misc => 'Divers',
        };
    }
}
