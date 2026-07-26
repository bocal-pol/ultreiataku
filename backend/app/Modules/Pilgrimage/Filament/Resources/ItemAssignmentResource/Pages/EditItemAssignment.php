<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\ItemAssignmentResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\ItemAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemAssignment extends EditRecord
{
    protected static string $resource = ItemAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
