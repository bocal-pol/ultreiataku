<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\PackItemResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\PackItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPackItem extends EditRecord
{
    protected static string $resource = PackItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
