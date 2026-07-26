<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ULTREIA-40 — Table pack_items.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pack_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('pack_scenario_id');
            $table->string('name', 200);
            $table->enum('category', [
                'portage',
                'sleeping',
                'cooking',
                'water',
                'clothing',
                'hygiene',
                'health',
                'navigation',
                'misc',
            ]);
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedInteger('weight_g');
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_consumable')->default(false);
            $table->unsignedInteger('replacement_km')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('pack_scenario_id')->references('id')->on('pack_scenarios')->onDelete('cascade');

            $table->index('pack_scenario_id');
            $table->index('category');
            $table->index(['pack_scenario_id', 'category']);
            $table->index(['pack_scenario_id', 'is_consumable']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_items');
    }
};
