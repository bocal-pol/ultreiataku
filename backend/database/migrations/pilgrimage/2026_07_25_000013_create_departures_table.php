<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departures', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('trip_id');
            $table->uuid('pilgrim_id');
            $table->uuid('start_stage_id');
            $table->uuid('end_stage_id');
            $table->date('planned_start_date');
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->enum('status', ['planned', 'active', 'paused', 'completed', 'abandoned'])->default('planned');
            $table->uuid('pack_scenario_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('pilgrim_id')->references('id')->on('pilgrims')->onDelete('cascade');
            $table->foreign('start_stage_id')->references('id')->on('stages')->onDelete('restrict');
            $table->foreign('end_stage_id')->references('id')->on('stages')->onDelete('restrict');

            $table->index('trip_id');
            $table->index('pilgrim_id');
            $table->index('status');
            $table->index(['trip_id', 'pilgrim_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departures');
    }
};
