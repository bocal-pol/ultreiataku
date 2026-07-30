<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\GuideSectionResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\GuideSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuideSection extends EditRecord
{
    protected static string $resource = GuideSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
