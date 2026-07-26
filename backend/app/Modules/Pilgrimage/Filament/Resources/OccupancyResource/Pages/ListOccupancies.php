<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\OccupancyResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\OccupancyResource;
use Filament\Resources\Pages\ListRecords;

class ListOccupancies extends ListRecords
{
    protected static string $resource = OccupancyResource::class;

    protected function getHeaderActions(): array
    {
        // bug_rule_004 : pas de CreateAction (table read-only ADR-U03)
        return [];
    }
}
