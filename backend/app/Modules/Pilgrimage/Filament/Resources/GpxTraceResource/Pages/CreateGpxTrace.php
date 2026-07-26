<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\GpxTraceResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\GpxTraceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGpxTrace extends CreateRecord
{
    protected static string $resource = GpxTraceResource::class;
}
