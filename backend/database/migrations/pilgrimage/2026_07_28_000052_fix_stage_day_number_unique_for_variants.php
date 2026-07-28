<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remplace la contrainte unique(route_id, day_number) par un index partiel
 * qui s'applique uniquement aux étapes principales (is_variant = false).
 *
 * Les variantes partagent le day_number de leur étape parente par conception
 * — la contrainte globale empêchait leur insertion.
 *
 * PostgreSQL uniquement : WHERE clause sur les index.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Supprimer la contrainte unique globale
        Schema::table('stages', function ($table) {
            $table->dropUnique(['route_id', 'day_number']);
        });

        // Recréer comme index partiel (étapes principales seulement)
        DB::statement(
            'CREATE UNIQUE INDEX stages_route_day_main_unique '
            . 'ON stages (route_id, day_number) WHERE is_variant = false',
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS stages_route_day_main_unique');

        Schema::table('stages', function ($table) {
            $table->unique(['route_id', 'day_number']);
        });
    }
};
