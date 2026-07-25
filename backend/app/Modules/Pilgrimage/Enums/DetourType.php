<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum DetourType: string
{
    case OnPath = 'on_path';
    case Short = 'short';
    case Medium = 'medium';
    case Long = 'long';

    public function label(): string
    {
        return match ($this) {
            DetourType::OnPath => 'Sur le chemin',
            DetourType::Short => 'Détour court (< 2 km A/R)',
            DetourType::Medium => 'Détour moyen (2-4 km A/R)',
            DetourType::Long => 'Détour long (> 4 km A/R)',
        };
    }
}
