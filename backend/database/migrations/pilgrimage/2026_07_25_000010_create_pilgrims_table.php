<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilgrims', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->unique()->comment('FK users (SSO)');
            $table->string('display_name', 100);
            $table->string('avatar_url', 500)->nullable();
            $table->enum('preferred_locale', ['fr', 'nl', 'de'])->default('fr');
            $table->enum('configuration', ['solo', 'duo'])->default('solo');
            $table->decimal('target_base_weight_kg', 4, 2)->nullable();
            $table->integer('target_daily_kcal')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilgrims');
    }
};
