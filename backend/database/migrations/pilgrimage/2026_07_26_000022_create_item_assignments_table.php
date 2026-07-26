<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ULTREIA-41 — Table item_assignments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('pack_item_id');
            $table->uuid('departure_id');
            $table->uuid('assigned_to_pilgrim_id');
            $table->uuid('from_stage_id')->nullable();
            $table->uuid('to_stage_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('pack_item_id')->references('id')->on('pack_items')->onDelete('cascade');
            $table->foreign('departure_id')->references('id')->on('departures')->onDelete('cascade');
            $table->foreign('assigned_to_pilgrim_id')->references('id')->on('pilgrims')->onDelete('cascade');
            $table->foreign('from_stage_id')->references('id')->on('stages')->onDelete('set null');
            $table->foreign('to_stage_id')->references('id')->on('stages')->onDelete('set null');

            $table->index('pack_item_id');
            $table->index('departure_id');
            $table->index('assigned_to_pilgrim_id');
            $table->index(['departure_id', 'assigned_to_pilgrim_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_assignments');
    }
};
