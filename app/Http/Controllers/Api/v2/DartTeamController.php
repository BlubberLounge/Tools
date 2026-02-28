<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Http\Requests\StoreDartTeamRequest;
use App\Http\Requests\UpdateDartTeamRequest;
use App\Http\Requests\SetDartTeamPlayersRequest;
use App\Http\Resources\DartTeamResource;
use App\Models\DartGame;
use App\Models\DartGameUser;
use App\Models\DartTeam;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DartTeamController extends Controller
{
    /**
     * List all teams for a game.
     *
     * GET /api/v2/dart/{game}/teams
     */
    public function index(DartGame $game): JsonResponse
    {
        $teams = $game->teams()->with('users')->get();

        return response()->json([
            'teams' => DartTeamResource::collection($teams),
        ]);
    }

    /**
     * Create a new team for a game.
     *
     * POST /api/v2/dart/{game}/teams
     */
    public function store(DartGame $game, StoreDartTeamRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $team = $game->teams()->create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'position' => $game->teams()->count(),
        ]);

        // Optionally assign players
        if (!empty($validated['players'])) {
            DartGameUser::where('dart_game_id', $game->id)
                ->whereIn('user_id', $validated['players'])
                ->whereNull('deleted_at')
                ->update(['dart_team_id' => $team->id]);
        }

        $team->load('users');

        return response()->json([
            'team' => new DartTeamResource($team),
        ], 201);
    }

    /**
     * Update a team's name or color.
     *
     * PUT /api/v2/dart/{game}/teams/{team}
     */
    public function update(DartGame $game, DartTeam $team, UpdateDartTeamRequest $request): JsonResponse
    {
        abort_if($team->dart_game_id !== $game->id, 404, 'Team does not belong to this game.');

        $team->update($request->validated());
        $team->load('users');

        return response()->json([
            'team' => new DartTeamResource($team),
        ]);
    }

    /**
     * Delete a team from a game.
     *
     * DELETE /api/v2/dart/{game}/teams/{team}
     */
    public function destroy(DartGame $game, DartTeam $team): JsonResponse
    {
        abort_if($team->dart_game_id !== $game->id, 404, 'Team does not belong to this game.');

        // Unassign players from this team
        DartGameUser::where('dart_game_id', $game->id)
            ->where('dart_team_id', $team->id)
            ->whereNull('deleted_at')
            ->update(['dart_team_id' => null]);

        $team->delete();

        return response()->json(null, 204);
    }

    /**
     * Add a single player to a team.
     *
     * POST /api/v2/dart/{game}/teams/{team}/players
     */
    public function addPlayer(DartGame $game, DartTeam $team, Request $request): JsonResponse
    {
        abort_if($team->dart_game_id !== $game->id, 404, 'Team does not belong to this game.');

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        DartGameUser::where('dart_game_id', $game->id)
            ->where('user_id', $validated['user_id'])
            ->whereNull('deleted_at')
            ->update(['dart_team_id' => $team->id]);

        $team->load('users');

        return response()->json([
            'team' => new DartTeamResource($team),
        ]);
    }

    /**
     * Remove a player from a team.
     *
     * DELETE /api/v2/dart/{game}/teams/{team}/players/{user}
     */
    public function removePlayer(DartGame $game, DartTeam $team, User $user): JsonResponse
    {
        abort_if($team->dart_game_id !== $game->id, 404, 'Team does not belong to this game.');

        DartGameUser::where('dart_game_id', $game->id)
            ->where('user_id', $user->id)
            ->where('dart_team_id', $team->id)
            ->whereNull('deleted_at')
            ->update(['dart_team_id' => null]);

        return response()->json(null, 204);
    }

    /**
     * Set all players for a team (bulk assignment).
     *
     * PUT /api/v2/dart/{game}/teams/{team}/players
     */
    public function setPlayers(DartGame $game, DartTeam $team, SetDartTeamPlayersRequest $request): JsonResponse
    {
        abort_if($team->dart_game_id !== $game->id, 404, 'Team does not belong to this game.');

        $playerIds = $request->validated('players');

        // Remove current assignments for this team
        DartGameUser::where('dart_game_id', $game->id)
            ->where('dart_team_id', $team->id)
            ->whereNull('deleted_at')
            ->update(['dart_team_id' => null]);

        // Assign new players
        DartGameUser::where('dart_game_id', $game->id)
            ->whereIn('user_id', $playerIds)
            ->whereNull('deleted_at')
            ->update(['dart_team_id' => $team->id]);

        $team->load('users');

        return response()->json([
            'team' => new DartTeamResource($team),
        ]);
    }
}
