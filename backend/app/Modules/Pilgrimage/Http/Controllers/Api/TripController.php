<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Pilgrimage\Enums\JournalVisibility;
use App\Modules\Pilgrimage\Enums\TripMemberRole;
use App\Modules\Pilgrimage\Http\Resources\DepartureResource;
use App\Modules\Pilgrimage\Http\Resources\OccupancyResource;
use App\Modules\Pilgrimage\Http\Resources\TripResource;
use App\Modules\Pilgrimage\Jobs\RebuildOccupancyForTripJob;
use App\Modules\Pilgrimage\Mail\TripInvitationMail;
use App\Modules\Pilgrimage\Models\Departure;
use App\Modules\Pilgrimage\Models\JournalEntry;
use App\Modules\Pilgrimage\Models\Occupancy;
use App\Modules\Pilgrimage\Models\Pilgrim;
use App\Modules\Pilgrimage\Models\Stage;
use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * ULTREIA-35 — API REST Trips.
 *
 * Routes :
 *   GET    /api/pilgrimage/trips                          (B-01)
 *   POST   /api/pilgrimage/trips
 *   GET    /api/pilgrimage/trips/{id}
 *   POST   /api/pilgrimage/trips/{id}/members
 *   DELETE /api/pilgrimage/trips/{id}/members/{pilgrimId}
 *   DELETE /api/pilgrimage/trips/{id}/membership          (RGPD-R02 self-leave)
 *   POST   /api/pilgrimage/trips/{id}/departures
 *   GET    /api/pilgrimage/trips/{id}/occupancy
 *   POST   /api/pilgrimage/trips/{id}/invite-token
 *   DELETE /api/pilgrimage/trips/{id}/invite-token
 *   POST   /api/pilgrimage/trips/join/{token}
 *   POST   /api/pilgrimage/trips/join-observer/{token}    (ULTREIA-VIS-01 — lien partage lecture seule)
 */
class TripController extends Controller
{
    // ─── GET /api/pilgrimage/trips ────────────────────────────────────────────
    // B-01 : Retourne tous les Trips dont le pèlerin courant est organisateur OU membre.

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Trip::class);

        $pilgrim = Pilgrim::query()->where('user_id', $request->user()->id)->firstOrFail();

        $trips = Trip::query()
            ->with(['organizer', 'members', 'route'])
            ->whereHas('members', function ($q) use ($pilgrim): void {
                $q->where('pilgrim_id', $pilgrim->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return TripResource::collection($trips);
    }

    // ─── POST /api/pilgrimage/trips ───────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Trip::class);

        $validator = Validator::make($request->all(), [
            'route_id' => 'required|uuid|exists:routes,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'status' => 'nullable|in:planned,active,completed,cancelled',
            'configuration' => 'nullable|in:solo,duo,group',
            'is_public' => 'nullable|boolean',
            'estimated_start_date' => 'nullable|date',
            'estimated_end_date' => 'nullable|date|after_or_equal:estimated_start_date',
        ], [
            'route_id.exists' => 'La route sélectionnée n\'existe pas.',
            'estimated_end_date.after_or_equal' => 'La date de fin doit être après la date de début.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pilgrim = Pilgrim::query()->where('user_id', $request->user()->id)->firstOrFail();

        $trip = DB::transaction(function () use ($validator, $pilgrim): Trip {
            $data = array_merge($validator->validated(), [
                'organizer_id' => $pilgrim->id,
                'status' => $validator->validated()['status'] ?? 'planned',
                'configuration' => $validator->validated()['configuration'] ?? 'solo',
            ]);

            /** @var Trip $trip */
            $trip = Trip::query()->create($data);

            // L'organisateur est automatiquement ajouté comme membre avec le rôle organizer
            $trip->members()->attach($pilgrim->id, [
                'role' => 'organizer',
                'joined_at' => now(),
                'invited_by' => null,
            ]);

            Log::info('trip.created', [
                'trip_id' => $trip->id,
                'organizer_id' => $pilgrim->id,
            ]);

            return $trip;
        });

        return (new TripResource($trip->load(['organizer', 'members'])))
            ->response()
            ->setStatusCode(201);
    }

    // ─── GET /api/pilgrimage/trips/{id} ───────────────────────────────────────

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->with(['organizer', 'members', 'route'])->findOrFail($id);

        $this->authorize('view', $trip);

        return (new TripResource($trip))->response();
    }

    // ─── POST /api/pilgrimage/trips/{id}/members ──────────────────────────────

    public function addMember(Request $request, string $id): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->findOrFail($id);

        $this->authorize('manageMember', $trip);

        $validator = Validator::make($request->all(), [
            'pilgrim_id' => 'required|uuid|exists:pilgrims,id',
            'role' => 'required|in:participant,observer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organizerPilgrim = Pilgrim::query()->where('user_id', $request->user()->id)->firstOrFail();

        if ($trip->hasMember($request->string('pilgrim_id')->toString())) {
            return response()->json(['message' => 'Ce pèlerin est déjà membre du Trip.'], 409);
        }

        DB::transaction(function () use ($trip, $request, $organizerPilgrim): void {
            $trip->members()->attach($request->string('pilgrim_id')->toString(), [
                'role' => $request->string('role')->toString(),
                'joined_at' => now(),
                'invited_by' => $organizerPilgrim->id,
            ]);

            // Recalcul occupancy après ajout de membre
            RebuildOccupancyForTripJob::dispatch($trip->id);
        });

        Log::info('trip.member_added', [
            'trip_id' => $trip->id,
            'pilgrim_id' => $request->string('pilgrim_id'),
        ]);

        return response()->json(['message' => 'Membre ajouté au Trip.'], 201);
    }

    // ─── DELETE /api/pilgrimage/trips/{id}/members/{pilgrimId} ───────────────
    // B-03 : Interdire d'éjecter l'organisateur du Trip.
    // RGPD-U03 : Le membre partant choisit le sort de ses entrées journal.

    public function removeMember(Request $request, string $id, string $pilgrimId): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->findOrFail($id);

        $this->authorize('manageMember', $trip);

        // B-03 — Garde : impossible de retirer l'organisateur du pivot
        if ($pilgrimId === $trip->organizer_id) {
            return response()->json([
                'message' => 'Impossible de retirer l\'organisateur du Trip. Transférez d\'abord le rôle d\'organisateur.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'journal_action' => 'nullable|in:keep,remove',
        ], [
            'journal_action.in' => 'La valeur de journal_action doit être "keep" ou "remove".',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Par défaut : keep (les entrées restent visibles au Trip)
        $journalAction = $request->input('journal_action', 'keep');

        DB::transaction(function () use ($trip, $pilgrimId, $journalAction): void {
            // RGPD-U03 — Traitement des entrées journal selon le choix du membre
            if ($journalAction === 'remove') {
                // "remove" : les entrées du membre partant passent en visibilité "private"
                // afin qu'elles ne soient plus visibles aux autres membres du Trip.
                // Elles ne sont PAS supprimées (soft-delete) : le pèlerin garde accès
                // à ses propres entrées s'il rejoint un autre Trip ou exporte ses données.
                JournalEntry::query()
                    ->where('trip_id', $trip->id)
                    ->where('pilgrim_id', $pilgrimId)
                    ->whereNot('visibility', JournalVisibility::Private->value)
                    ->update(['visibility' => JournalVisibility::Private->value]);

                Log::info('trip.member_removed.journal_masked', [
                    'trip_id' => $trip->id,
                    'pilgrim_id' => $pilgrimId,
                ]);
            }
            // "keep" : les entrées restent avec leur visibilité courante (aucune modification)

            $trip->members()->detach($pilgrimId);
            RebuildOccupancyForTripJob::dispatch($trip->id);
        });

        Log::info('trip.member_removed', [
            'trip_id' => $trip->id,
            'pilgrim_id' => $pilgrimId,
            'journal_action' => $journalAction,
        ]);

        return response()->json(['message' => 'Membre retiré du Trip.']);
    }

    // ─── DELETE /api/pilgrimage/trips/{id}/membership ────────────────────────
    // RGPD-R02 : Self-leave — un pèlerin quitte un Trip de lui-même.
    // L'organizer ne peut pas self-leaver (doit transférer/supprimer le Trip d'abord).

    public function selfLeave(Request $request, string $id): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->findOrFail($id);

        $this->authorize('selfLeave', $trip);

        $validator = Validator::make($request->all(), [
            'journal_action' => 'nullable|in:keep,remove',
        ], [
            'journal_action.in' => 'La valeur de journal_action doit être "keep" ou "remove".',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pilgrim = Pilgrim::query()->where('user_id', $request->user()->id)->firstOrFail();

        // Garde supplémentaire explicite : l'organizer reçoit un 422 clair
        // (la Policy retourne déjà false → 403, mais on préfère un message métier).
        if ($trip->organizer_id === $pilgrim->id) {
            return response()->json([
                'message' => 'L\'organisateur ne peut pas quitter son propre Trip. Transférez d\'abord l\'organisation ou supprimez le Trip.',
            ], 422);
        }

        $journalAction = $request->input('journal_action', 'keep');

        DB::transaction(function () use ($trip, $pilgrim, $journalAction): void {
            // RGPD-R02 — Même sémantique que removeMember
            if ($journalAction === 'remove') {
                JournalEntry::query()
                    ->where('trip_id', $trip->id)
                    ->where('pilgrim_id', $pilgrim->id)
                    ->whereNot('visibility', JournalVisibility::Private->value)
                    ->update(['visibility' => JournalVisibility::Private->value]);

                Log::info('trip.self_leave.journal_masked', [
                    'trip_id' => $trip->id,
                    'pilgrim_id' => $pilgrim->id,
                ]);
            }

            $trip->members()->detach($pilgrim->id);
            RebuildOccupancyForTripJob::dispatch($trip->id);
        });

        Log::info('trip.self_leave', [
            'trip_id' => $trip->id,
            'pilgrim_id' => $pilgrim->id,
            'journal_action' => $journalAction,
        ]);

        return response()->json(['message' => 'Vous avez quitté le Trip.']);
    }

    // ─── POST /api/pilgrimage/trips/{id}/departures ───────────────────────────
    // I-07 + P1-02 : Vérifier membership + ownership pour les participants.

    public function addDeparture(Request $request, string $id): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->findOrFail($id);

        $this->authorize('createDeparture', $trip);

        $validator = Validator::make($request->all(), [
            'pilgrim_id' => 'required|uuid|exists:pilgrims,id',
            'start_stage_id' => 'required|uuid|exists:stages,id',
            'end_stage_id' => 'required|uuid|exists:stages,id',
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // I-07 — RG : le pilgrim_id doit être membre du Trip
        $targetPilgrimId = $request->string('pilgrim_id')->toString();
        if (! $trip->hasMember($targetPilgrimId)) {
            return response()->json([
                'message' => 'Le pèlerin sélectionné n\'est pas membre de ce Trip.',
            ], 403);
        }

        // P1-02 — IDOR : un participant ne peut créer une departure que pour lui-même.
        // Seul un organisateur peut créer une departure pour n'importe quel membre.
        // Un observer n'a pas le droit de créer des departures (géré par DeparturePolicy).
        $currentPilgrim = Pilgrim::query()->where('user_id', $request->user()->id)->firstOrFail();
        $currentRole = $trip->roleOf($currentPilgrim->id);

        if ($currentRole === TripMemberRole::Participant && $currentPilgrim->id !== $targetPilgrimId) {
            Log::warning('trip.departure.idor_attempt', [
                'trip_id' => $trip->id,
                'current_pilgrim_id' => $currentPilgrim->id,
                'target_pilgrim_id' => $targetPilgrimId,
            ]);

            return response()->json([
                'message' => 'Un participant ne peut créer une étape que pour lui-même.',
            ], 403);
        }

        // Vérifier RG : end_stage.day_number >= start_stage.day_number
        $startStage = Stage::query()->findOrFail($request->string('start_stage_id'));
        $endStage = Stage::query()->findOrFail($request->string('end_stage_id'));

        if ($endStage->day_number < $startStage->day_number) {
            return response()->json([
                'errors' => ['end_stage_id' => ['L\'étape finale doit avoir un numéro de jour >= à l\'étape initiale.']],
            ], 422);
        }

        if ($startStage->route_id !== $endStage->route_id) {
            return response()->json([
                'errors' => ['end_stage_id' => ['Les étapes doivent appartenir à la même route.']],
            ], 422);
        }

        $departure = DB::transaction(function () use ($validator, $trip): Departure {
            /** @var Departure $departure */
            $departure = Departure::query()->create(array_merge(
                $validator->validated(),
                ['trip_id' => $trip->id],
            ));

            return $departure;
        });

        Log::info('trip.departure_added', [
            'trip_id' => $trip->id,
            'departure_id' => $departure->id,
            'pilgrim_id' => $departure->pilgrim_id,
        ]);

        return (new DepartureResource($departure))
            ->response()
            ->setStatusCode(201);
    }

    // ─── GET /api/pilgrimage/trips/{id}/occupancy ─────────────────────────────

    public function occupancy(Request $request, string $id): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->findOrFail($id);

        $this->authorize('viewOccupancy', $trip);

        $occupancies = Occupancy::query()
            ->where('trip_id', $id)
            ->orderBy('date')
            ->get();

        return OccupancyResource::collection($occupancies)->response();
    }

    // ─── POST /api/pilgrimage/trips/{id}/invite-token ─────────────────────────

    public function generateInviteToken(Request $request, string $id): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->findOrFail($id);

        $this->authorize('invite', $trip);

        $token = $trip->generateInviteToken();

        Log::info('trip.invite_token_generated', ['trip_id' => $trip->id]);

        return response()->json(['invite_token' => $token]);
    }

    // ─── DELETE /api/pilgrimage/trips/{id}/invite-token ───────────────────────

    public function revokeInviteToken(Request $request, string $id): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->findOrFail($id);

        $this->authorize('invite', $trip);

        $trip->revokeInviteToken();

        Log::info('trip.invite_token_revoked', ['trip_id' => $trip->id]);

        return response()->json(['message' => 'Token d\'invitation révoqué.']);
    }

    // ─── POST /api/pilgrimage/trips/join/{token} ──────────────────────────────

    /**
     * RG-07 — Rejoindre un Trip via token d'invitation.
     * Le pèlerin est auto-créé si inexistant (premier login SSO).
     */
    public function joinByToken(Request $request, string $token): JsonResponse
    {
        $trip = Trip::query()->where('invite_token', $token)->first();

        if ($trip === null) {
            return response()->json(['message' => 'Token d\'invitation invalide ou révoqué.'], 404);
        }

        $user = $request->user();
        $pilgrim = Pilgrim::query()->where('user_id', $user->id)->firstOrFail();

        if ($trip->hasMember($pilgrim->id)) {
            return response()->json(['message' => 'Vous êtes déjà membre de ce Trip.'], 409);
        }

        DB::transaction(function () use ($trip, $pilgrim): void {
            $trip->members()->attach($pilgrim->id, [
                'role' => 'participant',
                'joined_at' => now(),
                'invited_by' => null,
            ]);

            RebuildOccupancyForTripJob::dispatch($trip->id);
        });

        Log::info('trip.joined_via_token', [
            'trip_id' => $trip->id,
            'pilgrim_id' => $pilgrim->id,
        ]);

        return response()->json(['message' => 'Vous avez rejoint le Trip avec succès.'], 200);
    }

    // ─── POST /api/pilgrimage/trips/join-observer/{token} ────────────────────

    /**
     * ULTREIA-VIS-01 — Rejoindre un Trip en lecture seule (observer) via token d'invitation.
     *
     * Utilise le même invite_token que joinByToken, mais attache le pèlerin avec
     * le rôle observer. L'organizer partage ce lien distinct pour permettre un accès
     * en lecture au Trip et aux entrées journal publiques, sans capacité de créer
     * des departures ou des entrées journal.
     */
    public function joinByTokenAsObserver(Request $request, string $token): JsonResponse
    {
        $trip = Trip::query()->where('invite_token', $token)->first();

        if ($trip === null) {
            return response()->json(['message' => 'Token d\'invitation invalide ou révoqué.'], 404);
        }

        $user = $request->user();
        $pilgrim = Pilgrim::query()->where('user_id', $user->id)->firstOrFail();

        if ($trip->hasMember($pilgrim->id)) {
            return response()->json(['message' => 'Vous êtes déjà membre de ce Trip.'], 409);
        }

        DB::transaction(function () use ($trip, $pilgrim): void {
            $trip->members()->attach($pilgrim->id, [
                'role' => TripMemberRole::Observer->value,
                'joined_at' => now(),
                'invited_by' => null,
            ]);
        });

        Log::info('trip.joined_as_observer_via_token', [
            'trip_id' => $trip->id,
            'pilgrim_id' => $pilgrim->id,
        ]);

        return response()->json(['message' => 'Vous observez ce Trip en lecture seule.'], 200);
    }

    // ─── POST /api/pilgrimage/trips/{id}/invite-email ─────────────────────────

    public function sendInvitationEmail(Request $request, string $id): JsonResponse
    {
        /** @var Trip $trip */
        $trip = Trip::query()->with('organizer')->findOrFail($id);

        $this->authorize('invite', $trip);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'locale' => 'nullable|in:fr,nl,de',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($trip->invite_token === null) {
            $trip->generateInviteToken();
            $trip->refresh();
        }

        $locale = $request->string('locale')->toString() ?: 'fr';

        Mail::to($request->string('email')->toString())
            ->queue(new TripInvitationMail($trip, $locale));

        Log::info('trip.invitation_email_sent', [
            'trip_id' => $trip->id,
            'locale' => $locale,
        ]);

        return response()->json(['message' => 'Invitation envoyée.']);
    }
}
