<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\GpxTraceResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\GpxTraceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGpxTraces extends ListRecords
{
    protected static string $resource = GpxTraceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => GpxTraceResource::canCreate()),
        ];
    }
}
