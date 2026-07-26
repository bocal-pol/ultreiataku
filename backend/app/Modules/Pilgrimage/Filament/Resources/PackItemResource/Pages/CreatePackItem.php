<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\PackItemResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\PackItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePackItem extends CreateRecord
{
    protected static string $resource = PackItemResource::class;
}
