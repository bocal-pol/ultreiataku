<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\StageResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\StageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStages extends ListRecords
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => StageResource::canCreate()),
        ];
    }
}
