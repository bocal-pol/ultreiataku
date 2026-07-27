<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RGPD-U02 — SoftDeletes sur les tables données personnelles pèlerin.
 *
 * Décision produit (2026-07-27) : rétention ILLIMITÉE par choix produit explicite.
 * Pas de purge automatique ni de TTL. La suppression est uniquement manuelle
 * (droit à l'oubli Art. 17 sur demande via DELETE /api/pilgrimage/me).
 *
 * Le SoftDelete garantit :
 *   - traçabilité (corbeille admin récupérable)
 *   - atomicité de la suppression RGPD (purge MinIO async via PurgePilgrimAssetsJob)
 *   - possibilité de restauration administrative avant purge physique
 *
 * Tables concernées : journal_entries, journal_photos, pack_scenarios, departures.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── journal_entries ──────────────────────────────────────────────────
        Schema::table('journal_entries', function (Blueprint $table): void {
            $table->softDeletes()->after('updated_at');
            $table->index('deleted_at');
        });

        // ─── journal_photos ───────────────────────────────────────────────────
        Schema::table('journal_photos', function (Blueprint $table): void {
            $table->softDeletes()->after('updated_at');
            $table->index('deleted_at');
        });

        // ─── pack_scenarios ───────────────────────────────────────────────────
        Schema::table('pack_scenarios', function (Blueprint $table): void {
            $table->softDeletes()->after('updated_at');
            $table->index('deleted_at');
        });

        // ─── departures ───────────────────────────────────────────────────────
        Schema::table('departures', function (Blueprint $table): void {
            $table->softDeletes()->after('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('journal_photos', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('pack_scenarios', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('departures', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
