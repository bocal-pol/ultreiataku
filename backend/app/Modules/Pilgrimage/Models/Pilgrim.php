<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\PilgrimConfiguration;
use Database\Factories\PilgrimFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pilgrim extends Model
{
    /** @use HasFactory<PilgrimFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'display_name',
        'avatar_url',
        'preferred_locale',
        'configuration',
        'target_base_weight_kg',
        'target_daily_kcal',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'configuration' => PilgrimConfiguration::class,
        'target_base_weight_kg' => 'decimal:2',
        'target_daily_kcal' => 'integer',
        'user_id' => 'integer',
    ];

    protected static function newFactory(): PilgrimFactory
    {
        return PilgrimFactory::new();
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function organizedTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'organizer_id');
    }

    /**
     * Tous les Trips dont le Pilgrim est membre (via pivot trip_members).
     */
    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_members', 'pilgrim_id', 'trip_id')
            ->withPivot(['role', 'joined_at', 'invited_by'])
            ->withTimestamps();
    }

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class, 'pilgrim_id');
    }

    // ─── Vague 1d — Sac ──────────────────────────────────────────────────────

    public function packScenarios(): HasMany
    {
        return $this->hasMany(PackScenario::class, 'pilgrim_id');
    }

    public function itemAssignments(): HasMany
    {
        return $this->hasMany(ItemAssignment::class, 'assigned_to_pilgrim_id');
    }

    // ─── Vague 1e — Journal ───────────────────────────────────────────────────

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'pilgrim_id');
    }
}
