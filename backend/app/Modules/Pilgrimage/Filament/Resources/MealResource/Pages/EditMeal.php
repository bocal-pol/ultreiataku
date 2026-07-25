<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\MealResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\MealResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeal extends EditRecord
{
    protected static string $resource = MealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
