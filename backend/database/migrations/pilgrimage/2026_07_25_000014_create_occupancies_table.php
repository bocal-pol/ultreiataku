<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR-U03 — Table matérialisée pour l'Occupancy.
     * Peuplée par OccupancyObserver ; en lecture seule depuis le frontend.
     * Index unique (accommodation_id, date, trip_id) pour updateOrCreate idempotent.
     */
    public function up(): void
    {
        Schema::create('occupancies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('accommodation_id');
            $table->date('date');
            $table->uuid('trip_id');
            $table->integer('count')->default(0);
            $table->timestamps();

            $table->foreign('accommodation_id')->references('id')->on('accommodations')->onDelete('cascade');
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');

            // Index unique pour l'idempotence (updateOrCreate)
            $table->unique(['accommodation_id', 'date', 'trip_id'], 'occupancies_unique');

            // Index de lecture (détail hébergement + tableau de bord Trip)
            $table->index(['accommodation_id', 'date']);
            $table->index(['trip_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupancies');
    }
};
