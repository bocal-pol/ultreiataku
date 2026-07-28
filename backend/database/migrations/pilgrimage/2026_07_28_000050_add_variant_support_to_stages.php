<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->uuid('parent_stage_id')->nullable()->after('route_id');
            $table->boolean('is_variant')->default(false)->after('parent_stage_id');

            $table->foreign('parent_stage_id')
                ->references('id')
                ->on('stages')
                ->nullOnDelete();

            $table->index('parent_stage_id');
            $table->index('is_variant');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropForeign(['parent_stage_id']);
            $table->dropIndex(['parent_stage_id']);
            $table->dropIndex(['is_variant']);
            $table->dropColumn(['parent_stage_id', 'is_variant']);
        });
    }
};
