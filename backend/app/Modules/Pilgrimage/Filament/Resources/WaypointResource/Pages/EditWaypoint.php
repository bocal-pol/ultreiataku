<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\WaypointResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\WaypointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWaypoint extends EditRecord
{
    protected static string $resource = WaypointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
