<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\AccommodationResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\AccommodationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccommodations extends ListRecords
{
    protected static string $resource = AccommodationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => AccommodationResource::canCreate()),
        ];
    }
}
