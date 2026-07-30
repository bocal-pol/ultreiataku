<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Filament\Resources\GuideSectionResource\Pages;

use App\Modules\Pilgrimage\Filament\Resources\GuideSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuideSection extends CreateRecord
{
    protected static string $resource = GuideSectionResource::class;
}
