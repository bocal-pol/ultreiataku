<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum PilgrimConfiguration: string
{
    case Solo = 'solo';
    case Duo = 'duo';
}
