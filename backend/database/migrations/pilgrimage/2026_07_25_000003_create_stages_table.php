<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('route_id')->constrained('routes')->cascadeOnDelete();
            $table->string('code', 10)->unique();
            $table->json('name')->comment('i18n fr/nl/de');
            $table->integer('day_number');
            $table->foreignUuid('start_waypoint_id')->constrained('waypoints');
            $table->foreignUuid('end_waypoint_id')->constrained('waypoints');
            $table->decimal('distance_km', 6, 2);
            $table->integer('elevation_gain_m')->default(0);
            $table->integer('elevation_loss_m')->default(0);
            $table->decimal('estimated_duration_h', 4, 1)->default(0);
            $table->string('difficulty', 20)->default('moderate');
            $table->string('accommodation_type_default', 20)->nullable();
            $table->json('notes')->nullable()->comment('i18n fr/nl/de');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['route_id', 'day_number']);
            $table->index('route_id');
            $table->index('difficulty');
            $table->index(['route_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
