<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_members', function (Blueprint $table): void {
            $table->uuid('trip_id');
            $table->uuid('pilgrim_id');
            $table->enum('role', ['organizer', 'participant', 'observer'])->default('participant');
            $table->timestamp('joined_at')->useCurrent();
            $table->uuid('invited_by')->nullable();

            $table->primary(['trip_id', 'pilgrim_id']);
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('pilgrim_id')->references('id')->on('pilgrims')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('pilgrims')->nullOnDelete();

            $table->index('trip_id');
            $table->index('pilgrim_id');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_members');
    }
};
