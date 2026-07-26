<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\DepartureResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\DepartureResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeparture extends CreateRecord
{
    protected static string $resource = DepartureResource::class;
}
