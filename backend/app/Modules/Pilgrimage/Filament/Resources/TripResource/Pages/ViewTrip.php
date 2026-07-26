<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\TripResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\TripResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTrip extends ViewRecord
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
