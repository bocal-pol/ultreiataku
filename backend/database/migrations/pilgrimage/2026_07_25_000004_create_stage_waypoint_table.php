<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_waypoint', function (Blueprint $table) {
            $table->foreignUuid('stage_id')->constrained('stages')->cascadeOnDelete();
            $table->foreignUuid('waypoint_id')->constrained('waypoints')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_highlight')->default(false);

            $table->primary(['stage_id', 'waypoint_id']);
            $table->index('waypoint_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_waypoint');
    }
};
