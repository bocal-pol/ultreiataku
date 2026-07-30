<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\GuideSectionResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\GuideSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuideSections extends ListRecords
{
    protected static string $resource = GuideSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => GuideSectionResource::canCreate()),
        ];
    }
}
