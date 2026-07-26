<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\GpxTraceResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\GpxTraceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGpxTrace extends EditRecord
{
    protected static string $resource = GpxTraceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
