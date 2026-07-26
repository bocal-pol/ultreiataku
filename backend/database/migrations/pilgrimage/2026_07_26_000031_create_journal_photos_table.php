<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ULTREIA-50 — Table journal_photos.
     *
     * ADR-U02 : disk minio_journal, bucket ultreiataku-journal.
     * Jamais d'URL directe MinIO — proxy backend /api/pilgrimage/journal/photos/{id}.
     * Strip EXIF sensible à l'upload (SAUF coords si keep_location = true).
     */
    public function up(): void
    {
        Schema::create('journal_photos', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('journal_entry_id');
            $table->string('minio_path', 500);
            $table->string('minio_disk', 50)->default('minio_journal');
            $table->string('alt_text', 500)->nullable();
            $table->string('caption', 500)->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('file_size_bytes')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_synced')->default(true);
            $table->timestamps();

            $table->foreign('journal_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->onDelete('cascade');

            // Indices
            $table->index(['journal_entry_id', 'sort_order']);
            $table->index('is_synced');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_photos');
    }
};
