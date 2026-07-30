<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Guide — Table guide_sections.
 *
 * Les sections de préparation du pèlerinage (forme physique, santé/pieds,
 * credencial, budget, météo, faune) deviennent des entités administrables
 * plutôt que du contenu en dur dans le frontend.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 100)->unique();
            $table->string('category', 50)->index();
            $table->json('title');
            $table->string('icon', 100)->default('heroicon-o-book-open');
            $table->json('content');
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_sections');
    }
};
