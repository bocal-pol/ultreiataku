<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum MealType: string
{
    case Breakfast = 'breakfast';
    case Lunch = 'lunch';
    case Dinner = 'dinner';
    case Snack = 'snack';

    public function label(): string
    {
        return match ($this) {
            MealType::Breakfast => 'Petit-déjeuner',
            MealType::Lunch => 'Déjeuner',
            MealType::Dinner => 'Dîner',
            MealType::Snack => 'Collation',
        };
    }
}
