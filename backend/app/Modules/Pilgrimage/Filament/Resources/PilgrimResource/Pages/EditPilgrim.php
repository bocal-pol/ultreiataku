<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\PilgrimResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\PilgrimResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPilgrim extends EditRecord
{
    protected static string $resource = PilgrimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
