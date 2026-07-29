<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\TripConfiguration;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Enums\TripStatus;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Trip extends Model
{
    /** @use HasFactory<TripFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'route_id',
        'organizer_id',
        'name',
        'description',
        'status',
        'estimated_start_date',
        'estimated_end_date',
        'configuration',
        'is_public',
        'invite_token',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'status' => TripStatus::class,
        'configuration' => TripConfiguration::class,
        'is_public' => 'boolean',
        'estimated_start_date' => 'date',
        'estimated_end_date' => 'date',
    ];

    protected static function newFactory(): TripFactory
    {
        return TripFactory::new();
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function route(): BelongsTo
    {
        return $this->belongsTo(PilgrimageRoute::class, 'route_id');
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class, 'organizer_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Pilgrim::class, 'trip_members', 'trip_id', 'pilgrim_id')
            ->withPivot(['role', 'joined_at', 'invited_by']);
    }

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class, 'trip_id');
    }

    public function occupancies(): HasMany
    {
        return $this->hasMany(Occupancy::class, 'trip_id');
    }

    /**
     * ULTREIA-50 — Entrées du carnet de voyage.
     */
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'trip_id');
    }

    // ─── Business methods ─────────────────────────────────────────────────────

    /**
     * RG-07 — Génère un token d'invitation UUID v4 et le persiste.
     */
    public function generateInviteToken(): string
    {
        $token = Str::uuid()->toString();
        $this->update(['invite_token' => $token]);

        return $token;
    }

    /**
     * RG-07 — Révoque le token d'invitation (met à null).
     */
    public function revokeInviteToken(): void
    {
        $this->update(['invite_token' => null]);
    }

    /**
     * Retourne true si le Pilgrim donné est membre du Trip.
     */
    public function hasMember(string $pilgrimId): bool
    {
        return $this->members()->where('pilgrims.id', $pilgrimId)->exists();
    }

    /**
     * Retourne le rôle du Pilgrim dans ce Trip, ou null s'il n'en fait pas partie.
     */
    public function roleOf(string $pilgrimId): ?TripMemberRole
    {
        $member = $this->members()
            ->where('pilgrims.id', $pilgrimId)
            ->first();

        if ($member === null) {
            return null;
        }

        $role = $member->pivot->role;

        return TripMemberRole::tryFrom($role);
    }
}
