<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum PoiCategory: string
{
    case Archaeology = 'archaeology';
    case Religious = 'religious';
    case Fortress = 'fortress';
    case Nature = 'nature';
    case Gastronomy = 'gastronomy';
    case View = 'view';

    public function label(): string
    {
        return match ($this) {
            PoiCategory::Archaeology => 'Archéologie',
            PoiCategory::Religious => 'Religieux',
            PoiCategory::Fortress => 'Forteresse',
            PoiCategory::Nature => 'Nature',
            PoiCategory::Gastronomy => 'Gastronomie',
            PoiCategory::View => 'Panorama',
        };
    }
}
