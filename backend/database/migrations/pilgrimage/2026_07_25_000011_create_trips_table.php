<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('route_id');
            $table->uuid('organizer_id');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->enum('status', ['planned', 'active', 'completed', 'cancelled'])->default('planned');
            $table->date('estimated_start_date')->nullable();
            $table->date('estimated_end_date')->nullable();
            $table->enum('configuration', ['solo', 'duo', 'group'])->default('solo');
            $table->boolean('is_public')->default(false);
            // invite_token : UUID v4, usage multiple, révocable (nullable = révoqué)
            $table->string('invite_token', 64)->unique()->nullable();
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->foreign('organizer_id')->references('id')->on('pilgrims')->onDelete('cascade');

            $table->index('route_id');
            $table->index('organizer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
