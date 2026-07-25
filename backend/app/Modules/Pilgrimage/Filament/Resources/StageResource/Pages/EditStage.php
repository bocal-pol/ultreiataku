<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\StageResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\StageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStage extends EditRecord
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
