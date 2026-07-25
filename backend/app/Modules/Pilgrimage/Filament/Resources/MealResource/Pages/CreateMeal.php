<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\MealResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\MealResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeal extends CreateRecord
{
    protected static string $resource = MealResource::class;
}
