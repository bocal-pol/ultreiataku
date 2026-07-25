<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\AccommodationType;
use App\Modules\Pilgrimage\Enums\StageDifficulty;
use Database\Factories\StageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Stage extends Model
{
    /** @use HasFactory<StageFactory> */
    use HasFactory;
    use HasTranslations;
    use HasUuids;

    /** @var list<string> */
    public array $translatable = ['name', 'notes'];

    /** @var list<string> */
    protected $fillable = [
        'route_id',
        'code',
        'name',
        'day_number',
        'start_waypoint_id',
        'end_waypoint_id',
        'distance_km',
        'elevation_gain_m',
        'elevation_loss_m',
        'estimated_duration_h',
        'difficulty',
        'accommodation_type_default',
        'notes',
        'sort_order',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'difficulty' => StageDifficulty::class,
        'accommodation_type_default' => AccommodationType::class,
        'distance_km' => 'decimal:2',
        'elevation_gain_m' => 'integer',
        'elevation_loss_m' => 'integer',
        'estimated_duration_h' => 'decimal:1',
        'day_number' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function newFactory(): StageFactory
    {
        return StageFactory::new();
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(PilgrimageRoute::class, 'route_id');
    }

    public function startWaypoint(): BelongsTo
    {
        return $this->belongsTo(Waypoint::class, 'start_waypoint_id');
    }

    public function endWaypoint(): BelongsTo
    {
        return $this->belongsTo(Waypoint::class, 'end_waypoint_id');
    }

    public function waypoints(): BelongsToMany
    {
        return $this->belongsToMany(Waypoint::class, 'stage_waypoint')
            ->withPivot(['sort_order', 'is_highlight'])
            ->orderByPivot('sort_order');
    }

    public function gpxTraces(): HasMany
    {
        return $this->hasMany(GpxTrace::class, 'stage_id');
    }

    public function mainGpxTrace(): HasMany
    {
        return $this->hasMany(GpxTrace::class, 'stage_id')
            ->where('trace_type', 'stage_main');
    }
}
