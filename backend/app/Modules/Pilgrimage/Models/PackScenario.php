<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use App\Modules\Pilgrimage\Enums\PackCategory;
use App\Modules\Pilgrimage\Enums\PackSeason;
use App\Modules\Pilgrimage\Enums\PilgrimConfiguration;
use Database\Factories\PackScenarioFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ULTREIA-40 — Scénario de sac appartenant à un Pilgrim.
 *
 * RG-01 : baseWeightG() = SUM(weight_g WHERE is_consumable = false)
 *         totalWeightG() = SUM(weight_g) (tous les items)
 *
 * RGPD-U02 — SoftDeletes activé par décision produit (2026-07-27).
 * Rétention ILLIMITÉE : pas de purge automatique, pas de TTL.
 * La suppression est uniquement sur demande (Art. 17, DELETE /api/pilgrimage/me).
 *
 * @property string $id
 * @property string $pilgrim_id
 * @property string $name
 * @property string|null $description
 * @property float|null $target_base_weight_kg
 * @property PilgrimConfiguration|null $configuration
 * @property PackSeason|null $season
 */
class PackScenario extends Model
{
    /** @use HasFactory<PackScenarioFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'pilgrim_id',
        'name',
        'description',
        'target_base_weight_kg',
        'configuration',
        'season',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'configuration' => PilgrimConfiguration::class,
        'season' => PackSeason::class,
        'target_base_weight_kg' => 'decimal:2',
    ];

    protected static function newFactory(): PackScenarioFactory
    {
        return PackScenarioFactory::new();
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class, 'pilgrim_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackItem::class, 'pack_scenario_id');
    }

    public function itemAssignments(): HasMany
    {
        return $this->hasMany(ItemAssignment::class, 'pack_scenario_id');
    }

    // ─── RG-01 — Calcul poids ─────────────────────────────────────────────────

    /**
     * Poids de base en grammes (sans consommables).
     * RG-01 : SUM(weight_g WHERE is_consumable = false)
     */
    public function baseWeightG(): int
    {
        return (int) $this->items()
            ->where('is_consumable', false)
            ->sum('weight_g');
    }

    /**
     * Poids total en grammes (avec consommables).
     */
    public function totalWeightG(): int
    {
        return (int) $this->items()->sum('weight_g');
    }

    /**
     * Indicateur RG-01 : green / orange / red selon objectif.
     *
     * - green  : base_weight_kg <= target
     * - orange : base_weight_kg <= target + 1.0 kg
     * - red    : base_weight_kg > target + 1.0 kg
     *
     * Si target_base_weight_kg est null → retourne 'unknown'.
     */
    public function weightIndicator(): string
    {
        if ($this->target_base_weight_kg === null) {
            return 'unknown';
        }

        $baseKg = $this->baseWeightG() / 1000;
        $target = (float) $this->target_base_weight_kg;

        if ($baseKg <= $target) {
            return 'green';
        }

        if ($baseKg <= $target + 1.0) {
            return 'orange';
        }

        return 'red';
    }

    /**
     * Items groupés par catégorie avec totaux de poids.
     *
     * @return array<string, array{label: string, items: Collection<int, PackItem>, total_g: int}>
     */
    public function itemsByCategory(): array
    {
        $grouped = [];

        foreach (PackCategory::cases() as $category) {
            $categoryItems = $this->items()
                ->where('category', $category->value)
                ->orderBy('sort_order')
                ->get();

            if ($categoryItems->isNotEmpty()) {
                $grouped[$category->value] = [
                    'label' => $category->label(),
                    'items' => $categoryItems,
                    'total_g' => (int) $categoryItems->sum('weight_g'),
                ];
            }
        }

        return $grouped;
    }
}
