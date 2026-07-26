<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

enum StageDifficulty: string
{
    case Easy = 'easy';
    case Moderate = 'moderate';
    case Hard = 'hard';

    public function label(): string
    {
        return match ($this) {
            StageDifficulty::Easy => 'Facile',
            StageDifficulty::Moderate => 'Modéré',
            StageDifficulty::Hard => 'Difficile',
        };
    }

    public function color(): string
    {
        return match ($this) {
            StageDifficulty::Easy => 'success',
            StageDifficulty::Moderate => 'warning',
            StageDifficulty::Hard => 'danger',
        };
    }
}
