<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\TripResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\TripResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrip extends EditRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
