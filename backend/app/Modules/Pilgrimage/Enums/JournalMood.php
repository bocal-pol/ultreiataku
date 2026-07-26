<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Enums;

/**
 * ULTREIA-50 — Humeur d'une entrée de journal.
 */
enum JournalMood: string
{
    case Great     = 'great';
    case Good      = 'good';
    case Neutral   = 'neutral';
    case Tired     = 'tired';
    case Difficult = 'difficult';

    public function label(): string
    {
        return match ($this) {
            self::Great     => 'Super',
            self::Good      => 'Bien',
            self::Neutral   => 'Neutre',
            self::Tired     => 'Fatigué',
            self::Difficult => 'Difficile',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Great     => '😄',
            self::Good      => '🙂',
            self::Neutral   => '😐',
            self::Tired     => '😴',
            self::Difficult => '😓',
        };
    }
}
