<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\DepartureResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\DepartureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeparture extends ViewRecord
{
    protected static string $resource = DepartureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
