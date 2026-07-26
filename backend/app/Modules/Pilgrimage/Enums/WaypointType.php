<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum WaypointType: string
{
    case City = 'city';
    case Poi = 'poi';
    case Water = 'water';
    case Rest = 'rest';
    case Crossroads = 'crossroads';
    case BivouacZone = 'bivouac_zone';

    public function label(): string
    {
        return match ($this) {
            WaypointType::City => 'Ville-étape',
            WaypointType::Poi => 'Point d\'intérêt',
            WaypointType::Water => 'Point d\'eau',
            WaypointType::Rest => 'Pause',
            WaypointType::Crossroads => 'Croisement',
            WaypointType::BivouacZone => 'Zone bivouac',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            WaypointType::City => 'heroicon-o-map-pin',
            WaypointType::Poi => 'heroicon-o-star',
            WaypointType::Water => 'heroicon-o-beaker',
            WaypointType::Rest => 'heroicon-o-pause-circle',
            WaypointType::Crossroads => 'heroicon-o-arrows-right-left',
            WaypointType::BivouacZone => 'heroicon-o-home-modern',
        };
    }
}
