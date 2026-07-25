<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\WaypointResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\WaypointResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaypoint extends CreateRecord
{
    protected static string $resource = WaypointResource::class;
}
