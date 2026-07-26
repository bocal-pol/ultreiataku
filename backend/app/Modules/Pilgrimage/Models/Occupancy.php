<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ADR-U03 — Table matérialisée occupancies.
 * Jamais saisie directement — peuplée par OccupancyObserver uniquement.
 */
class Occupancy extends Model
{
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'accommodation_id',
        'date',
        'trip_id',
        'count',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'count' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }
}
