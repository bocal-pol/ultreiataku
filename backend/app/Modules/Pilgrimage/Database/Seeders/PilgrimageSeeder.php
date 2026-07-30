<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orchestrateur de tous les seeders Pilgrimage.
 * Ordre de dépendance strict :
 *   1.  RouteSeeder                    → routes Belgique
 *   2.  RouteSeederFrance              → routes France (Via Campaniensis + Voie de Vézelay)
 *   3.  RouteSeederEspagne             → routes Espagne (Camino del Norte ES + Module Picos)
 *   4.  WaypointSeeder                 → waypoints Belgique (villes + POI)
 *   5.  WaypointSeederFrance           → waypoints France (41 villes + 11 POI)
 *   6.  WaypointSeederEspagne          → waypoints Espagne (50+ villes + 13 POI + 4 Picos)
 *   7.  StageSeeder                    → étapes principales Belgique
 *   8.  StageSeederFrance              → 40 étapes principales France FR-01→FR-40
 *   9.  StageSeederEspagne             → 39 étapes ES-01→ES-39 + 9 étapes PC-01→PC-09
 *  10.  StageVariantBelgiqueSeeder     → 5 variantes BE + branche Bruxelles
 *  11.  StageVariantFranceSeeder       → variante FR-04V-FAUX-VERZY (Faux de Verzy)
 *  12.  StageVariantEspagneSeeder      → 7 variantes Norte + 2 variantes Picos
 *  13.  StagePOISeeder                 → pivot POI↔étapes Belgique
 *  14.  StagePOISeederFrance           → pivot POI↔étapes France
 *  15.  StagePOISeederEspagne          → pivot POI↔étapes Espagne (ES + PC)
 *  16.  GpxTraceSeeder                 → GPX principales Belgique
 *  17.  GpxTraceVariantBelgiqueSeeder  → GPX variantes Belgique
 *  18.  GpxTraceSeederFrance           → GPX France FR-01→FR-40 + variante Faux de Verzy
 *  19.  GpxTraceSeederEspagne          → GPX ES-01→ES-39 + PC-01→PC-09
 *  20.  GpxTraceVariantEspagneSeeder   → GPX 7 variantes Norte + 2 variantes Picos
 *  21.  AccommodationSeeder            → hébergements Belgique
 *  22.  AccommodationSeederFrance      → hébergements France (clés par étape)
 *  23.  AccommodationSeederEspagne     → hébergements Espagne (Güemes donativo, Miraz, Sobrado…)
 *  24.  MealSeeder                     → repas Belgique
 *  25.  MealSeederFrance               → repas France (7 incontournables + régions)
 *  26.  MealSeederEspagne              → repas Espagne (pintxos, anchois, fabada, pulpo, tarta…)
 *  27.  GuideSectionSeeder             → sections Guide pèlerin (forme, santé, credencial, budget…)
 *  28.  PersonalTripSeeder             → seeds bocal (RÈGLE UTILISATEUR — vague 1c)
 *  29.  PackScenarioSeeder             → scénarios de sac bocal (ULTREIA-42 — vague 1d)
 */
class PilgrimageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Pilgrimage Seeder — démarrage ===');

        $this->call([
            // ── Routes ──────────────────────────────────────────────────────
            RouteSeeder::class,
            RouteSeederFrance::class,
            RouteSeederEspagne::class,

            // ── Waypoints ────────────────────────────────────────────────────
            WaypointSeeder::class,
            WaypointSeederFrance::class,
            WaypointSeederEspagne::class,

            // ── Étapes principales ───────────────────────────────────────────
            StageSeeder::class,
            StageSeederFrance::class,
            StageSeederEspagne::class,

            // ── Variantes ────────────────────────────────────────────────────
            StageVariantBelgiqueSeeder::class,
            StageVariantFranceSeeder::class,
            StageVariantEspagneSeeder::class,

            // ── Pivot POI ────────────────────────────────────────────────────
            StagePOISeeder::class,
            StagePOISeederFrance::class,
            StagePOISeederEspagne::class,

            // ── GPX ──────────────────────────────────────────────────────────
            GpxTraceSeeder::class,
            GpxTraceVariantBelgiqueSeeder::class,
            GpxTraceSeederFrance::class,
            GpxTraceSeederEspagne::class,
            GpxTraceVariantEspagneSeeder::class,

            // ── Hébergements ─────────────────────────────────────────────────
            AccommodationSeeder::class,
            AccommodationSeederFrance::class,
            AccommodationSeederEspagne::class,

            // ── Repas ─────────────────────────────────────────────────────────
            MealSeeder::class,
            MealSeederFrance::class,
            MealSeederEspagne::class,

            // ── Guide pèlerin (sections de préparation) ───────────────────────
            GuideSectionSeeder::class,

            // ── Usage personnel ───────────────────────────────────────────────
            PersonalTripSeeder::class,
            PackScenarioSeeder::class,
        ]);

        $this->command->info('=== Pilgrimage Seeder — terminé ===');
    }
}
