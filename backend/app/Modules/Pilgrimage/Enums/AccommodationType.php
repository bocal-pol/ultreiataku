<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum AccommodationType: string
{
    case Gite = 'gite';
    case Camping = 'camping';
    case Hostel = 'hostel';
    case Hotel = 'hotel';
    case Abbey = 'abbey';
    case Donativo = 'donativo';
    case Bivouac = 'bivouac';

    public function label(): string
    {
        return match ($this) {
            AccommodationType::Gite => 'Gîte',
            AccommodationType::Camping => 'Camping',
            AccommodationType::Hostel => 'Auberge de jeunesse',
            AccommodationType::Hotel => 'Hôtel',
            AccommodationType::Abbey => 'Abbaye',
            AccommodationType::Donativo => 'Donativo',
            AccommodationType::Bivouac => 'Bivouac',
        };
    }
}
