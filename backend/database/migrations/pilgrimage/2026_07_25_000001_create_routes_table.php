<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 100)->unique();
            $table->json('name')->comment('i18n fr/nl/de');
            $table->json('description')->nullable()->comment('i18n fr/nl/de');
            $table->string('country', 2)->default('BE');
            $table->decimal('total_distance_km', 8, 2)->default(0);
            $table->integer('total_elevation_gain_m')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('country');
            $table->index('is_active');
            $table->index(['country', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
