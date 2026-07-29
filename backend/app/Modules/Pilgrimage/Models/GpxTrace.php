<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\GpxPrecision;
use App\Modules\Pilgrimage\Enums\GpxTraceType;
use Database\Factories\GpxTraceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpxTrace extends Model
{
    /** @use HasFactory<GpxTraceFactory> */
    use HasFactory;

    use HasUuids;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'stage_id',
        'waypoint_id',
        'trace_type',
        'name',
        'minio_path',
        'minio_disk',
        'source',
        'distance_km',
        'elevation_gain_m',
        'elevation_loss_m',
        'track_points_count',
        'precision',
        'imported_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'trace_type' => GpxTraceType::class,
        'precision' => GpxPrecision::class,
        'distance_km' => 'decimal:3',
        'elevation_gain_m' => 'integer',
        'elevation_loss_m' => 'integer',
        'track_points_count' => 'integer',
        'imported_at' => 'datetime',
    ];

    protected static function newFactory(): GpxTraceFactory
    {
        return GpxTraceFactory::new();
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function waypoint(): BelongsTo
    {
        return $this->belongsTo(Waypoint::class, 'waypoint_id');
    }
}
