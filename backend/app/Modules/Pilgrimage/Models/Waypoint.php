<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\DetourType;
use App\Modules\Pilgrimage\Enums\PoiCategory;
use App\Modules\Pilgrimage\Enums\WaypointType;
use Database\Factories\WaypointFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Waypoint extends Model
{
    /** @use HasFactory<WaypointFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUuids;

    /** @var list<string> */
    public array $translatable = ['name', 'description', 'opening_notes'];

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name',
        'type',
        'poi_category',
        'latitude',
        'longitude',
        'detour_type',
        'detour_distance_km',
        'detour_duration_min',
        'visit_duration_min',
        'entry_cost_eur',
        'booking_required',
        'booking_contact',
        'opening_notes',
        'description',
        'is_active',
        'active_from',
        'active_until',
        'verified_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type' => WaypointType::class,
        'poi_category' => PoiCategory::class,
        'detour_type' => DetourType::class,
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'detour_distance_km' => 'decimal:2',
        'detour_duration_min' => 'integer',
        'visit_duration_min' => 'integer',
        'entry_cost_eur' => 'decimal:2',
        'booking_required' => 'boolean',
        'is_active' => 'boolean',
        'active_from' => 'date',
        'active_until' => 'date',
        'verified_at' => 'datetime',
    ];

    protected static function newFactory(): WaypointFactory
    {
        return WaypointFactory::new();
    }

    public function stages(): BelongsToMany
    {
        return $this->belongsToMany(Stage::class, 'stage_waypoint')
            ->withPivot(['sort_order', 'is_highlight'])
            ->orderByPivot('sort_order');
    }

    public function gpxTraces(): HasMany
    {
        return $this->hasMany(GpxTrace::class, 'waypoint_id');
    }
}
