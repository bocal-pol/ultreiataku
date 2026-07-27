<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\DepartureStatus;
use Database\Factories\DepartureFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * RGPD-U02 — SoftDeletes activé par décision produit (2026-07-27).
 * Rétention ILLIMITÉE : pas de purge automatique, pas de TTL.
 * La suppression est uniquement sur demande (Art. 17, DELETE /api/pilgrimage/me).
 */
class Departure extends Model
{
    /** @use HasFactory<DepartureFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'trip_id',
        'pilgrim_id',
        'start_stage_id',
        'end_stage_id',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'status',
        'pack_scenario_id',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'status' => DepartureStatus::class,
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
    ];

    protected static function newFactory(): DepartureFactory
    {
        return DepartureFactory::new();
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

    public function startStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'start_stage_id');
    }

    public function endStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'end_stage_id');
    }

    // ─── Vague 1d — Sac ──────────────────────────────────────────────────────

    public function packScenario(): BelongsTo
    {
        return $this->belongsTo(PackScenario::class, 'pack_scenario_id');
    }

    public function itemAssignments(): HasMany
    {
        return $this->hasMany(ItemAssignment::class, 'departure_id');
    }
}
