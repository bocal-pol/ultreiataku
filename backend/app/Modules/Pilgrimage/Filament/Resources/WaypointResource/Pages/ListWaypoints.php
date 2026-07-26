<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\WaypointResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\WaypointResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaypoints extends ListRecords
{
    protected static string $resource = WaypointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => WaypointResource::canCreate()),
        ];
    }
}
