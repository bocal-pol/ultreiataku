<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use Database\Factories\ItemAssignmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ULTREIA-41 — Assignation d'un PackItem à un pèlerin pour un Departure.
 *
 * Règle : un PackItem marqué is_shared = true peut avoir plusieurs
 * ItemAssignments pour le même Departure (un porteur par tronçon).
 *
 * @property string $id
 * @property string $pack_item_id
 * @property string $departure_id
 * @property string $assigned_to_pilgrim_id
 * @property string|null $from_stage_id
 * @property string|null $to_stage_id
 * @property string|null $notes
 */
class ItemAssignment extends Model
{
    /** @use HasFactory<ItemAssignmentFactory> */
    use HasFactory;

    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'pack_item_id',
        'departure_id',
        'assigned_to_pilgrim_id',
        'from_stage_id',
        'to_stage_id',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [];

    protected static function newFactory(): ItemAssignmentFactory
    {
        return ItemAssignmentFactory::new();
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function packItem(): BelongsTo
    {
        return $this->belongsTo(PackItem::class, 'pack_item_id');
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class, 'departure_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class, 'assigned_to_pilgrim_id');
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'to_stage_id');
    }
}
