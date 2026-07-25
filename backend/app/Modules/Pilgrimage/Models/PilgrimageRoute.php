<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\Country;
use Database\Factories\PilgrimageRouteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class PilgrimageRoute extends Model
{
    /** @use HasFactory<PilgrimageRouteFactory> */
    use HasFactory;
    use HasTranslations;
    use HasUuids;

    protected $table = 'routes';

    /** @var list<string> */
    public array $translatable = ['name', 'description'];

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'country',
        'total_distance_km',
        'total_elevation_gain_m',
        'is_active',
        'sort_order',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'country' => Country::class,
        'total_distance_km' => 'decimal:2',
        'total_elevation_gain_m' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function newFactory(): PilgrimageRouteFactory
    {
        return PilgrimageRouteFactory::new();
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class, 'route_id')->orderBy('sort_order');
    }
}
