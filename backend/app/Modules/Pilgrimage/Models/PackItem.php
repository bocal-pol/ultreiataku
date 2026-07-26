<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\PackCategory;
use Database\Factories\PackItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ULTREIA-40 — Item portable d'un PackScenario.
 *
 * @property string $id
 * @property string $pack_scenario_id
 * @property string $name
 * @property PackCategory $category
 * @property string|null $brand
 * @property string|null $model
 * @property int $weight_g
 * @property bool $is_shared
 * @property bool $is_consumable
 * @property int|null $replacement_km
 * @property string|null $notes
 * @property int $sort_order
 */
class PackItem extends Model
{
    /** @use HasFactory<PackItemFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'pack_scenario_id',
        'name',
        'category',
        'brand',
        'model',
        'weight_g',
        'is_shared',
        'is_consumable',
        'replacement_km',
        'notes',
        'sort_order',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'category' => PackCategory::class,
        'weight_g' => 'integer',
        'is_shared' => 'boolean',
        'is_consumable' => 'boolean',
        'replacement_km' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function newFactory(): PackItemFactory
    {
        return PackItemFactory::new();
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function packScenario(): BelongsTo
    {
        return $this->belongsTo(PackScenario::class, 'pack_scenario_id');
    }

    public function itemAssignments(): HasMany
    {
        return $this->hasMany(ItemAssignment::class, 'pack_item_id');
    }
}
