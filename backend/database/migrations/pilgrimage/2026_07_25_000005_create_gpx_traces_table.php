<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gpx_traces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->foreignUuid('waypoint_id')->nullable()->constrained('waypoints')->nullOnDelete();
            $table->string('trace_type', 30)->default('stage_main');
            $table->string('name', 200)->nullable()->comment('Nom lisible de la trace');
            $table->string('minio_path', 500)->nullable()->comment('null si MinIO indisponible au seed');
            $table->string('minio_disk', 50)->nullable()->default('minio_gpx');
            $table->string('source', 200)->nullable()->comment('Nom du fichier source d\'origine');
            $table->decimal('distance_km', 8, 3)->nullable();
            $table->integer('elevation_gain_m')->nullable();
            $table->integer('elevation_loss_m')->nullable();
            $table->integer('track_points_count')->nullable();
            $table->string('precision', 20)->default('approximate');
            $table->timestamp('imported_at')->nullable();

            $table->index(['stage_id', 'trace_type']);
            $table->index('waypoint_id');
            $table->index('trace_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gpx_traces');
    }
};
