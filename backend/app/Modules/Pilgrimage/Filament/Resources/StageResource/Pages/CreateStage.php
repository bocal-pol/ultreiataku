<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\StageResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\StageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStage extends CreateRecord
{
    protected static string $resource = StageResource::class;
}
