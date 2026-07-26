<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\PilgrimResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\PilgrimResource;
use Filament\Resources\Pages\ListRecords;

class ListPilgrims extends ListRecords
{
    protected static string $resource = PilgrimResource::class;

    protected function getHeaderActions(): array
    {
        // Pilgrims créés automatiquement au login SSO — pas de création manuelle
        return [];
    }
}
