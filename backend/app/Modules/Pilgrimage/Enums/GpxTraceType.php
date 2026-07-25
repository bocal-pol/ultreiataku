<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum GpxTraceType: string
{
    case StageMain = 'stage_main';
    case Detour = 'detour';
    case Variant = 'variant';

    public function label(): string
    {
        return match ($this) {
            GpxTraceType::StageMain => 'Trace principale de l\'étape',
            GpxTraceType::Detour => 'Détour',
            GpxTraceType::Variant => 'Variante',
        };
    }

    public function color(): string
    {
        return match ($this) {
            GpxTraceType::StageMain => '#2563EB',
            GpxTraceType::Detour => '#EA580C',
            GpxTraceType::Variant => '#7C3AED',
        };
    }
}
