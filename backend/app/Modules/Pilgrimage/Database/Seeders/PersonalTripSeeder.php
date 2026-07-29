<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\PilgrimageRoute;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Seed personnel bocal — RÈGLE UTILISATEUR.
 *
 * Crée (ou retrouve) le Pilgrim `bocal` lié au compte SSO user_id=1,
 * crée le Trip « Liège → Santiago » (Via Mosana, status: planned)
 * avec bocal comme ORGANIZER, tof (user_id=3) et mike (user_id=4) comme
 * PARTICIPANTS, et un Departure planifié BE-01 → BE-12 pour chacun.
 *
 * Les comptes SSO tof/mike sont créés dans le projet Auth central
 * (UltreiatakuApplicationSeeder / DevFriendsSeeder). Ici on crée leurs
 * Pilgrims et on les rattache au Trip (RÈGLE UTILISATEUR : bocal + 2 amis).
 *
 * Idempotent : updateOrCreate sur les identifiants métier.
 * Ne génère pas d'invite_token (l'organisateur le générera depuis le frontend).
 */
class PersonalTripSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== PersonalTripSeeder — bocal ===');

        DB::transaction(function (): void {
            // ─── 1. Pilgrim bocal (user_id = 1, SSO bocal@police.belgium.eu) ──
            /** @var Pilgrim $bocal */
            $bocal = Pilgrim::query()->updateOrCreate(
                ['user_id' => 1],
                [
                    'display_name' => 'bocal',
                    'preferred_locale' => 'fr',
                    'configuration' => 'solo',
                    'target_base_weight_kg' => 8.50,
                    'target_daily_kcal' => 3200,
                ],
            );

            $this->command->info("Pilgrim bocal : {$bocal->id}");

            // ─── 2. Route Via Mosana Belgique ─────────────────────────────────
            /** @var PilgrimageRoute|null $route */
            $route = PilgrimageRoute::query()
                ->where('slug', 'via-mosana-belgique')
                ->first();

            if ($route === null) {
                $this->command->warn('Route via-mosana-belgique introuvable — RouteSeeder à exécuter d\'abord.');
                Log::warning('PersonalTripSeeder: route via-mosana-belgique not found — run RouteSeeder first.');

                return;
            }

            // ─── 3. Trip « Liège → Santiago » ────────────────────────────────
            /** @var Trip $trip */
            $trip = Trip::query()->updateOrCreate(
                [
                    'organizer_id' => $bocal->id,
                    'name' => 'Liège → Santiago',
                ],
                [
                    'route_id' => $route->id,
                    'description' => 'Mon carnet de route — Via Mosana, étapes BE-01 à BE-12 puis vers Compostelle.',
                    'status' => 'planned',
                    'configuration' => 'solo',
                    'is_public' => false,
                    'estimated_start_date' => '2027-05-10',
                    'estimated_end_date' => '2027-05-24',
                ],
            );

            $this->command->info("Trip : {$trip->id} ({$trip->name})");

            // ─── 4. bocal ORGANIZER dans trip_members ─────────────────────────
            if (! $trip->hasMember($bocal->id)) {
                $trip->members()->attach($bocal->id, [
                    'role' => 'organizer',
                    'joined_at' => now(),
                    'invited_by' => null,
                ]);
                $this->command->info('bocal ajouté comme ORGANIZER du Trip.');
            } else {
                $this->command->info('bocal déjà membre du Trip.');
            }

            // ─── 5. Departure planifié BE-01 → BE-12 ─────────────────────────
            $startStage = Stage::query()->where('code', 'BE-01')->first();
            $endStage = Stage::query()->where('code', 'BE-12')->first();

            if ($startStage === null || $endStage === null) {
                $this->command->warn('Stages BE-01/BE-12 introuvables — StageSeeder à exécuter d\'abord.');
                Log::warning('PersonalTripSeeder: stages BE-01 or BE-12 not found.');

                return;
            }

            Departure::query()->updateOrCreate(
                [
                    'trip_id' => $trip->id,
                    'pilgrim_id' => $bocal->id,
                    'start_stage_id' => $startStage->id,
                    'end_stage_id' => $endStage->id,
                ],
                [
                    'planned_start_date' => '2027-05-10',
                    'planned_end_date' => '2027-05-24',
                    'status' => 'planned',
                    'notes' => 'Première traversée complète de la Via Mosana belge.',
                ],
            );

            $this->command->info('Departure BE-01 → BE-12 créé / mis à jour.');

            // ─── 6. tof (user_id=3) et mike (user_id=4) — amis PARTICIPANTS ────
            // Comptes SSO créés dans Auth (DevFriendsSeeder). On crée leurs
            // Pilgrims et on les rattache au Trip comme participants.
            $friends = [
                3 => 'tof',
                4 => 'mike',
            ];

            foreach ($friends as $userId => $name) {
                /** @var Pilgrim $friend */
                $friend = Pilgrim::query()->updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'display_name' => $name,
                        'preferred_locale' => 'fr',
                        'configuration' => 'solo',
                    ],
                );

                if (! $trip->hasMember($friend->id)) {
                    $trip->members()->attach($friend->id, [
                        'role' => 'participant',
                        'joined_at' => now(),
                        'invited_by' => $bocal->id,
                    ]);
                    $this->command->info("{$name} ajouté comme PARTICIPANT du Trip.");
                }

                Departure::query()->updateOrCreate(
                    [
                        'trip_id' => $trip->id,
                        'pilgrim_id' => $friend->id,
                        'start_stage_id' => $startStage->id,
                        'end_stage_id' => $endStage->id,
                    ],
                    [
                        'planned_start_date' => '2027-05-10',
                        'planned_end_date' => '2027-05-24',
                        'status' => 'planned',
                        'notes' => "Départ de {$name} — Via Mosana avec bocal.",
                    ],
                );
            }

            // Trip à 3 → configuration group
            if ($trip->configuration !== 'group') {
                $trip->update(['configuration' => 'group']);
                $this->command->info('Trip passé en configuration GROUP (bocal + tof + mike).');
            }
        });

        $this->command->info('=== PersonalTripSeeder — terminé ===');
    }
}
