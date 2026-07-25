<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\RouteResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\RouteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoute extends CreateRecord
{
    protected static string $resource = RouteResource::class;
}
