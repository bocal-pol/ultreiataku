<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\OccupancyResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\OccupancyResource;
use Filament\Resources\Pages\ViewRecord;

class ViewOccupancy extends ViewRecord
{
    protected static string $resource = OccupancyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
