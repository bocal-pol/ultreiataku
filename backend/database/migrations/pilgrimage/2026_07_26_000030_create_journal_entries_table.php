<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ULTREIA-50 — Table journal_entries.
     *
     * ADR-U04 : local_id UNIQUE PARTIAL INDEX (WHERE local_id IS NOT NULL)
     * pour l'idempotence offline sans contraindre les entrées online (local_id = null).
     *
     * Indices : trip_id + pilgrim_id (lecture journal), visibility (filtre RG-03),
     * entry_date (tri chronologique), is_synced (queue sync offline).
     */
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id');
            $table->uuid('pilgrim_id');
            $table->uuid('stage_id')->nullable();
            $table->string('title', 300)->nullable();
            $table->text('body')->nullable();
            $table->date('entry_date');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('visibility', 20)->default('private');
            $table->string('mood', 20)->nullable();
            $table->decimal('km_walked_today', 6, 2)->nullable();
            $table->boolean('is_synced')->default(true);
            $table->string('local_id', 36)->nullable();
            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('pilgrim_id')->references('id')->on('pilgrims')->onDelete('cascade');
            $table->foreign('stage_id')->references('id')->on('stages')->onDelete('set null');

            // Indices de lecture
            $table->index(['trip_id', 'pilgrim_id']);
            $table->index(['trip_id', 'visibility']);
            $table->index(['trip_id', 'entry_date']);
            $table->index('pilgrim_id');
            $table->index('is_synced');
        });

        // ADR-U04 — UNIQUE PARTIAL INDEX : local_id unique uniquement quand non-null.
        // Postgres : WHERE local_id IS NOT NULL permet plusieurs NULL sans conflit.
        // SQLite (tests :memory:) : supporte le partial index depuis 3.8.9 — syntaxe identique.
        DB::statement(
            'CREATE UNIQUE INDEX journal_entries_local_id_unique '
            . 'ON journal_entries (local_id) WHERE local_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
