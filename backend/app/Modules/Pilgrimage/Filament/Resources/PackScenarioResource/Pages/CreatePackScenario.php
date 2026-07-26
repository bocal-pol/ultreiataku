<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\PackScenarioResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\PackScenarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePackScenario extends CreateRecord
{
    protected static string $resource = PackScenarioResource::class;
}
