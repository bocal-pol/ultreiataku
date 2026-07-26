<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\DepartureResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\DepartureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartures extends ListRecords
{
    protected static string $resource = DepartureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // bug_rule_004 — visible() explicite pour respecter canCreate()
            Actions\CreateAction::make()
                ->visible(fn () => DepartureResource::canCreate()),
        ];
    }
}
