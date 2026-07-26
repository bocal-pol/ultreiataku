<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ULTREIA-40 — Table pack_scenarios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pack_scenarios', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('pilgrim_id');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('target_base_weight_kg', 4, 2)->nullable();
            $table->enum('configuration', ['solo', 'duo'])->default('solo');
            $table->enum('season', ['spring', 'summer', 'autumn', 'winter'])->default('spring');
            $table->timestamps();

            $table->foreign('pilgrim_id')->references('id')->on('pilgrims')->onDelete('cascade');

            $table->index('pilgrim_id');
            $table->index('configuration');
            $table->index('season');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_scenarios');
    }
};
