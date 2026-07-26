<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum MealContext: string
{
    case Restaurant = 'restaurant';
    case BivouacCooking = 'bivouac_cooking';
    case Grocery = 'grocery';
    case LocalSpecialty = 'local_specialty';

    public function label(): string
    {
        return match ($this) {
            MealContext::Restaurant => 'Restaurant',
            MealContext::BivouacCooking => 'Cuisine bivouac',
            MealContext::Grocery => 'Épicerie / boulangerie',
            MealContext::LocalSpecialty => 'Spécialité locale',
        };
    }
}
