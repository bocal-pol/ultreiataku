<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\ItemAssignmentResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\ItemAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemAssignments extends ListRecords
{
    protected static string $resource = ItemAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => ItemAssignmentResource::canCreate()),
        ];
    }
}
