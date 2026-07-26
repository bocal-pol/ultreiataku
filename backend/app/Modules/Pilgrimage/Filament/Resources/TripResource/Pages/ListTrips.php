<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\TripResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\TripResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrips extends ListRecords
{
    protected static string $resource = TripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // bug_rule_004 — visible() explicite pour respecter canCreate()
            Actions\CreateAction::make()
                ->visible(fn () => TripResource::canCreate()),
        ];
    }
}
