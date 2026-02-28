<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Http\Requests\StoreDartTournamentRequest;
use App\Http\Requests\UpdateDartTournamentRequest;
use App\Http\Resources\DartTournamentResource;
use App\Http\Resources\DartTournamentDetailResource;
use App\Http\Resources\DartTournamentMatchResource;
use App\Http\Resources\DartTournamentStandingsResource;
use App\Enums\DartTournamentStatus;
use App\Enums\DartTournamentMatchStatus;
use App\Enums\DartTournamentRoundStatus;
use App\Models\DartTournament;
use App\Models\DartTournamentRound;
use App\Models\DartTournamentMatch;
use App\Models\User;
use App\Services\Dart\DartGameEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DartTournamentController extends Controller
{
    /**
     * List tournaments for the authenticated user.
     */
    public function index(Request $request)
    {
        $limit = $this->getLimit($request);

        $tournaments = DartTournament::where('created_by', Auth::id())
            ->orWhereHas('participants', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->withCount('participants')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => DartTournamentResource::collection($tournaments),
            'count' => $tournaments->count(),
        ]);
    }

    /**
     * Create a new tournament.
     */
    public function store(StoreDartTournamentRequest $request)
    {
        $validated = $request->validated();

        $tournament = DartTournament::create([
            'created_by' => Auth::id(),
            'title' => $validated['title'],
            'format' => $validated['format'],
            'game_mode' => $validated['game_mode'],
            'game_options' => $validated['game_options'] ?? null,
            'seed_type' => $validated['seed_type'] ?? 'manual',
            'best_of' => $validated['best_of'] ?? 1,
            'status' => 'created',
        ]);

        if (!empty($validated['participants'])) {
            $tournament->participants()->attach($validated['participants']);
        }

        $tournament->load('participants');
        $tournament->loadCount('participants');

        return response()->json(new DartTournamentResource($tournament), 201);
    }

    /**
     * Get full tournament detail with rounds, matches, and participants.
     */
    public function show(DartTournament $tournament)
    {
        $tournament->load([
            'participants',
            'winner',
            'rounds.matches.player1',
            'rounds.matches.player2',
            'rounds.matches.winner',
        ]);

        return response()->json(new DartTournamentDetailResource($tournament));
    }

    /**
     * Update tournament settings (only before running).
     */
    public function update(DartTournament $tournament, UpdateDartTournamentRequest $request)
    {
        abort_if(
            !in_array($tournament->status, [DartTournamentStatus::CREATED, DartTournamentStatus::SEEDED]),
            422,
            'Cannot update a tournament that has already started.'
        );

        $tournament->update($request->validated());

        return response()->json(new DartTournamentResource($tournament));
    }

    /**
     * Soft-delete a tournament.
     */
    public function destroy(DartTournament $tournament)
    {
        $tournament->delete();

        return response()->json(['success' => true, 'message' => 'Tournament deleted.']);
    }

    /**
     * Add participants to the tournament.
     */
    public function addParticipants(DartTournament $tournament, Request $request)
    {
        abort_if(
            !in_array($tournament->status, [DartTournamentStatus::CREATED]),
            422,
            'Cannot add participants after seeding has started.'
        );

        $request->validate([
            'participants' => ['required', 'array'],
            'participants.*' => ['integer', 'exists:users,id'],
        ]);

        $tournament->participants()->syncWithoutDetaching($request->input('participants'));

        $tournament->load('participants');

        return response()->json([
            'success' => true,
            'participant_count' => $tournament->participants->count(),
            'participants' => $tournament->participants->map(fn($u) => ['id' => $u->id, 'name' => $u->name]),
        ]);
    }

    /**
     * Remove a participant from the tournament.
     */
    public function removeParticipant(DartTournament $tournament, User $user)
    {
        abort_if(
            !in_array($tournament->status, [DartTournamentStatus::CREATED]),
            422,
            'Cannot remove participants after seeding has started.'
        );

        $tournament->participants()->detach($user->id);

        return response()->json(['success' => true, 'message' => 'Participant removed.']);
    }

    /**
     * Generate bracket/schedule (stub — seeding logic to be implemented).
     */
    public function seed(DartTournament $tournament)
    {
        abort_if(
            $tournament->status !== DartTournamentStatus::CREATED,
            422,
            'Tournament can only be seeded from created status.'
        );

        abort_if(
            $tournament->participants()->count() < 2,
            422,
            'At least 2 participants are required to seed a tournament.'
        );

        // TODO: Implement seeding logic (bracket generation for single_elimination, schedule for round_robin)

        $tournament->update(['status' => 'seeded']);

        $tournament->load(['participants', 'rounds.matches']);

        return response()->json(new DartTournamentDetailResource($tournament));
    }

    /**
     * Start the tournament.
     */
    public function start(DartTournament $tournament)
    {
        abort_if(
            $tournament->status !== DartTournamentStatus::SEEDED,
            422,
            'Tournament must be seeded before starting.'
        );

        $tournament->update(['status' => 'running']);

        // Set first round to running
        $firstRound = $tournament->rounds()->orderBy('round_number')->first();
        if ($firstRound) {
            $firstRound->update(['status' => 'running']);
        }

        return response()->json(new DartTournamentResource($tournament));
    }

    /**
     * Update tournament status (abort/finish).
     */
    public function updateStatus(DartTournament $tournament, Request $request)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:done,aborted'],
        ]);

        $tournament->update(['status' => $request->input('status')]);

        return response()->json(new DartTournamentResource($tournament));
    }

    /**
     * List all matches for a tournament.
     */
    public function matches(DartTournament $tournament)
    {
        $matches = $tournament->matches()
            ->with(['player1', 'player2', 'winner', 'round'])
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => DartTournamentMatchResource::collection($matches),
            'count' => $matches->count(),
        ]);
    }

    /**
     * Get a specific round with its matches.
     */
    public function round(DartTournament $tournament, DartTournamentRound $round)
    {
        abort_if($round->dart_tournament_id !== $tournament->id, 404);

        $round->load(['matches.player1', 'matches.player2', 'matches.winner']);

        return response()->json([
            'id' => $round->id,
            'round_number' => $round->round_number,
            'name' => $round->name,
            'status' => $round->status,
            'matches' => DartTournamentMatchResource::collection($round->matches),
        ]);
    }

    /**
     * Start a match — create a DartGame and link it.
     */
    public function startMatch(DartTournament $tournament, DartTournamentMatch $match, DartGameEngineService $engine)
    {
        abort_if($match->dart_tournament_id !== $tournament->id, 404);
        abort_if($match->status !== DartTournamentMatchStatus::READY, 422, 'Match is not ready to start.');
        abort_if(!$match->player1_id || !$match->player2_id, 422, 'Both players must be assigned.');

        $gameData = [
            'type' => $tournament->game_mode->value,
            'game_type' => 'tournament',
            'title' => $tournament->title . ' - Match ' . $match->match_number,
            'users' => [$match->player1_id, $match->player2_id],
        ];

        // Merge tournament game options
        if ($tournament->game_options) {
            $gameData = array_merge($gameData, $tournament->game_options->toArray());
        }

        $game = $engine->newGame(Auth::id(), $gameData);

        if (is_array($game) && isset($game['conflicts'])) {
            return response()->json(['conflicts' => $game['conflicts']], 409);
        }

        $match->update([
            'dart_game_id' => $game->id,
            'status' => 'running',
        ]);

        return response()->json($engine->getState($game));
    }

    /**
     * Complete a match — record winner and update standings.
     */
    public function completeMatch(DartTournament $tournament, DartTournamentMatch $match, Request $request)
    {
        abort_if($match->dart_tournament_id !== $tournament->id, 404);
        abort_if($match->status !== DartTournamentMatchStatus::RUNNING, 422, 'Match is not running.');

        $request->validate([
            'winner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $winnerId = $request->input('winner_id');

        abort_if(
            !in_array($winnerId, [$match->player1_id, $match->player2_id]),
            422,
            'Winner must be one of the match players.'
        );

        $match->update([
            'winner_id' => $winnerId,
            'status' => 'done',
        ]);

        // Update participant stats
        $loserId = $winnerId == $match->player1_id ? $match->player2_id : $match->player1_id;

        $tournament->participants()->updateExistingPivot($winnerId, [
            'wins' => \DB::raw('wins + 1'),
        ]);
        $tournament->participants()->updateExistingPivot($loserId, [
            'losses' => \DB::raw('losses + 1'),
        ]);

        // TODO: Advance winner to next round (single elimination), check round completion

        return response()->json(new DartTournamentMatchResource($match->load(['player1', 'player2', 'winner'])));
    }

    /**
     * Get tournament standings (round robin) or bracket progress (single elimination).
     */
    public function standings(DartTournament $tournament)
    {
        $participants = $tournament->participants()
            ->orderByPivot('wins', 'desc')
            ->orderByPivot('points_for', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => DartTournamentStandingsResource::collection($participants),
        ]);
    }
}
