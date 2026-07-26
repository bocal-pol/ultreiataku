<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum TripConfiguration: string
{
    case Solo = 'solo';
    case Duo = 'duo';
    case Group = 'group';
}
