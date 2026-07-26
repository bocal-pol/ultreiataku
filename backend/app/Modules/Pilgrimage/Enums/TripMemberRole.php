<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum TripMemberRole: string
{
    case Organizer = 'organizer';
    case Participant = 'participant';
    case Observer = 'observer';
}
