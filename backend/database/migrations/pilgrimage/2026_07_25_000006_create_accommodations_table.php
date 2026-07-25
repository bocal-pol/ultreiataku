<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stage_id')->nullable()->constrained('stages')->cascadeOnDelete();
            $table->foreignUuid('waypoint_id')->nullable()->constrained('waypoints')->nullOnDelete();
            $table->json('name')->comment('i18n fr/nl/de');
            $table->string('type', 20)->default('gite');
            $table->string('address', 500)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->decimal('price_min_eur', 6, 2)->nullable();
            $table->decimal('price_max_eur', 6, 2)->nullable();
            $table->boolean('is_donativo')->default(false);
            $table->integer('capacity')->nullable();
            $table->boolean('has_shower')->default(false);
            $table->boolean('has_kitchen')->default(false);
            $table->boolean('has_wifi')->default(false);
            $table->boolean('stamps_credencial')->default(false);
            $table->boolean('pilgrim_friendly')->default(true);
            $table->boolean('booking_required')->default(false);
            $table->integer('booking_notice_days')->nullable();
            $table->boolean('bivouac_legal')->default(false);
            $table->json('bivouac_notes')->nullable()->comment('i18n fr/nl/de');
            $table->boolean('is_primary')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('notes')->nullable()->comment('i18n fr/nl/de');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // Index clés selon architecture doc
            $table->index(['stage_id', 'is_primary', 'sort_order'], 'accom_stage_primary_sort');
            $table->index('verified_at', 'accom_verified_at'); // widget RG-08
            $table->index('type', 'accom_type');
            $table->index('bivouac_legal', 'accom_bivouac_legal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
