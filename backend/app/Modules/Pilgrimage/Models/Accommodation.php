<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\AccommodationType;
use Database\Factories\AccommodationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Accommodation extends Model
{
    /** @use HasFactory<AccommodationFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUuids;

    /** @var list<string> */
    public array $translatable = ['name', 'bivouac_notes', 'notes'];

    /** @var list<string> */
    protected $fillable = [
        'stage_id',
        'waypoint_id',
        'name',
        'type',
        'address',
        'phone',
        'website',
        'email',
        'price_min_eur',
        'price_max_eur',
        'is_donativo',
        'capacity',
        'has_shower',
        'has_kitchen',
        'has_wifi',
        'stamps_credencial',
        'pilgrim_friendly',
        'booking_required',
        'booking_notice_days',
        'bivouac_legal',
        'bivouac_notes',
        'is_primary',
        'sort_order',
        'notes',
        'verified_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type' => AccommodationType::class,
        'is_donativo' => 'boolean',
        'has_shower' => 'boolean',
        'has_kitchen' => 'boolean',
        'has_wifi' => 'boolean',
        'stamps_credencial' => 'boolean',
        'pilgrim_friendly' => 'boolean',
        'booking_required' => 'boolean',
        'bivouac_legal' => 'boolean',
        'is_primary' => 'boolean',
        'capacity' => 'integer',
        'booking_notice_days' => 'integer',
        'sort_order' => 'integer',
        'price_min_eur' => 'decimal:2',
        'price_max_eur' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    protected static function newFactory(): AccommodationFactory
    {
        return AccommodationFactory::new();
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function waypoint(): BelongsTo
    {
        return $this->belongsTo(Waypoint::class, 'waypoint_id');
    }

    /**
     * RG-08 : vrai si verified_at est null ou > 6 mois.
     */
    public function isObsolete(): bool
    {
        if ($this->verified_at === null) {
            return true;
        }

        return $this->verified_at->lt(now()->subMonths(6));
    }
}
