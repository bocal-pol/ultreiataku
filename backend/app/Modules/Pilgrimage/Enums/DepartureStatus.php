<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum DepartureStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Abandoned = 'abandoned';
}
