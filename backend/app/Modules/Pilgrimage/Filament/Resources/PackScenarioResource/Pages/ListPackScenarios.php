<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\PackScenarioResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\PackScenarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPackScenarios extends ListRecords
{
    protected static string $resource = PackScenarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => PackScenarioResource::canCreate()),
        ];
    }
}
