<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étend la colonne code de varchar(20) à varchar(25)
 * pour accueillir les codes de variantes plus longs (ex: BE-07V-CHATEAU-THIERRY = 21 chars).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->string('code', 25)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->string('code', 20)->change();
        });
    }
};
