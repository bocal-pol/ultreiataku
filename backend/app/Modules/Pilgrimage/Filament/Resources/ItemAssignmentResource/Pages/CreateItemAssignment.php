<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\ItemAssignmentResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\ItemAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItemAssignment extends CreateRecord
{
    protected static string $resource = ItemAssignmentResource::class;
}
