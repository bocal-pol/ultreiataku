<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stage_id')->constrained('stages')->cascadeOnDelete();
            $table->foreignUuid('waypoint_id')->nullable()->constrained('waypoints')->nullOnDelete();
            $table->string('meal_type', 20)->default('dinner');
            $table->json('name')->comment('i18n fr/nl/de');
            $table->json('description')->nullable()->comment('i18n fr/nl/de');
            $table->string('meal_context', 30)->default('restaurant');
            $table->string('restaurant_name', 200)->nullable();
            $table->string('restaurant_address', 300)->nullable();
            $table->decimal('price_estimate_eur', 6, 2)->nullable();
            $table->integer('kcal_estimate')->nullable();
            $table->integer('weight_g')->nullable()->comment('Pour repas bivouac portés');
            $table->json('notes')->nullable()->comment('i18n fr/nl/de');
            $table->timestamps();

            $table->index('stage_id', 'meals_stage_id');
            $table->index('meal_type', 'meals_meal_type');
            $table->index('meal_context', 'meals_meal_context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
