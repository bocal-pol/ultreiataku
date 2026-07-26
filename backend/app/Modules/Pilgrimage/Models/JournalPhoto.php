<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Models;

use Database\Factories\JournalPhotoFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ULTREIA-50 — Photo d'une entrée de journal.
 *
 * ADR-U02 : disk minio_journal, bucket ultreiataku-journal.
 * Jamais d'URL directe MinIO : proxy GET /api/pilgrimage/journal/photos/{id}.
 * Strip EXIF sensible à l'upload (SAUF coords si keep_location = true).
 */
class JournalPhoto extends Model
{
    /** @use HasFactory<JournalPhotoFactory> */
    use HasFactory;
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'journal_entry_id',
        'minio_path',
        'minio_disk',
        'alt_text',
        'caption',
        'taken_at',
        'latitude',
        'longitude',
        'file_size_bytes',
        'mime_type',
        'sort_order',
        'is_synced',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'taken_at'        => 'datetime',
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
        'file_size_bytes' => 'integer',
        'sort_order'      => 'integer',
        'is_synced'       => 'boolean',
    ];

    protected static function newFactory(): JournalPhotoFactory
    {
        return JournalPhotoFactory::new();
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
