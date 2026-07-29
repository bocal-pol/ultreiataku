<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Resources;

use App\Modules\Pilgrimage\Models\JournalPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ULTREIA-54 — Resource API JournalPhoto.
 *
 * ADR-U02 : jamais d'URL MinIO directe.
 * proxy_url = /api/pilgrimage/journal/photos/{id} — le client utilise ce chemin.
 * minio_path et minio_disk ne sont PAS exposés au client.
 *
 * @mixin JournalPhoto
 */
class JournalPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'journal_entry_id' => $this->journal_entry_id,
            'proxy_url' => route('api.pilgrimage.journal.photos.stream', ['id' => $this->id]),
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'taken_at' => $this->taken_at?->toIso8601String(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'file_size_bytes' => $this->file_size_bytes,
            'mime_type' => $this->mime_type,
            'sort_order' => $this->sort_order,
            'is_synced' => $this->is_synced,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
