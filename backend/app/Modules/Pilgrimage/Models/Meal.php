<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\MealContext;
use App\Modules\Pilgrimage\Enums\MealType;
use Database\Factories\MealFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Meal extends Model
{
    /** @use HasFactory<MealFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUuids;

    /** @var list<string> */
    public array $translatable = ['name', 'description', 'notes'];

    /** @var list<string> */
    protected $fillable = [
        'stage_id',
        'waypoint_id',
        'meal_type',
        'name',
        'description',
        'meal_context',
        'restaurant_name',
        'restaurant_address',
        'price_estimate_eur',
        'kcal_estimate',
        'weight_g',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'meal_type' => MealType::class,
        'meal_context' => MealContext::class,
        'price_estimate_eur' => 'decimal:2',
        'kcal_estimate' => 'integer',
        'weight_g' => 'integer',
    ];

    protected static function newFactory(): MealFactory
    {
        return MealFactory::new();
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
