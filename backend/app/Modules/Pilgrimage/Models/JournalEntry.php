<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\JournalMood;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use Database\Factories\JournalEntryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ULTREIA-50 — Entrée du carnet de voyage.
 *
 * ADR-U04 : local_id = UUID v4 client, idempotence par UNIQUE PARTIAL INDEX.
 * RG-03    : visibilité contrôlée par JournalEntryPolicy.
 */
class JournalEntry extends Model
{
    /** @use HasFactory<JournalEntryFactory> */
    use HasFactory;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'trip_id',
        'pilgrim_id',
        'stage_id',
        'title',
        'body',
        'entry_date',
        'latitude',
        'longitude',
        'visibility',
        'mood',
        'km_walked_today',
        'is_synced',
        'local_id',
    ];

    /** @var array<string, string|class-string> */
    protected $casts = [
        'visibility'      => JournalVisibility::class,
        'mood'            => JournalMood::class,
        'entry_date'      => 'date',
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
        'km_walked_today' => 'decimal:2',
        'is_synced'       => 'boolean',
    ];

    protected static function newFactory(): JournalEntryFactory
    {
        return JournalEntryFactory::new();
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class, 'pilgrim_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(JournalPhoto::class, 'journal_entry_id')
            ->orderBy('sort_order');
    }
}
