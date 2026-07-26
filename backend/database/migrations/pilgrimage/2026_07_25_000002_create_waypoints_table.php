<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waypoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 150)->unique();
            $table->json('name')->comment('i18n fr/nl/de');
            $table->string('type', 30)->default('city');
            $table->string('poi_category', 30)->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('detour_type', 20)->nullable();
            $table->decimal('detour_distance_km', 5, 2)->nullable();
            $table->integer('detour_duration_min')->nullable();
            $table->integer('visit_duration_min')->nullable();
            $table->decimal('entry_cost_eur', 6, 2)->nullable();
            $table->boolean('booking_required')->default(false);
            $table->string('booking_contact', 255)->nullable();
            $table->json('opening_notes')->nullable()->comment('i18n fr/nl/de');
            $table->json('description')->nullable()->comment('i18n fr/nl/de');
            $table->boolean('is_active')->default(true);
            $table->date('active_from')->nullable();
            $table->date('active_until')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('poi_category');
            $table->index('is_active');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waypoints');
    }
};
