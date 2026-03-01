<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Http\Requests\CreateDartGameRequest;
use App\Http\Requests\UpdateDartGameStatusRequest;
use App\Http\Resources\DartGameDetailResource;
use App\Http\Resources\UserResource;
use App\Enums\DartGameStatus;
use App\Models\DartGame;
use App\Models\DartQueue;
use App\Models\User;
use App\Models\DartGameUser;
use App\Services\Dart\DartGameEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DartEngineController extends Controller
{
    public function availablePlayers(Request $request)
    {
        $user = $request->user();

        $inGameUserIds = DartGameUser::activeUserIds();

        $queueData = DartQueue::with('parentUser')
            ->get()
            ->unique('parent_user_id')
            ->sortBy('created_at')
            ->filter(fn($q) => !in_array($q->parent_user_id, $inGameUserIds));

        $queueUserIds = $queueData->pluck('parent_user_id')->toArray();

        $queuePayload = $queueData->map(function($queueItem, $index) use ($inGameUserIds) {
            return [
                'order' => $index + 1,
                // 'id' => $queueItem->id,
                'parentUser' => new UserResource($queueItem->parentUser),
                'childUsers' => UserResource::collection(
                    $queueItem->parentUser->dartQueueChilds()
                        ->get()
                        ->filter(fn($c) => !in_array($c->id, $inGameUserIds))
                ),
            ];
        })->values();

        $excludeIds = array_merge($queueUserIds, $inGameUserIds);

        $otherUsers = User::whereNotIn('id', $excludeIds)
            ->where('id', '!=', $user->id)
            ->get();

        return response()->json([
            'queue' => $queuePayload,
            'other' => UserResource::collection($otherUsers),
        ]);
    }

    public function store(CreateDartGameRequest $request, DartGameEngineService $engine)
    {
        $game = $engine->newGame(Auth::id(), $request->validated());

        if (is_array($game)) {
            $created = $game['game'] ?? null;
            $conflicts = $game['conflicts'] ?? [];

            $conflicts = array_map(function ($userId) {
                $u = User::find($userId);
                $name = $u ? $u->name : 'Benutzer #' . $userId;
                return [
                    'id' => $userId,
                    'name' => $name,
                    'message' => "Der Benutzer {$name} (ID: {$userId}) ist bereits in einem anderen aktiven Dartspiel.",
                ];
            }, $conflicts);

            return response()->json([
                'conflicts' => $conflicts,
            ], 409);
        }

        return response()->json($engine->getState($game), 201);
    }

    public function activeGameState(Request $request, DartGameEngineService $engine)
    {
        $user = $request->user();
        $game = $user->ActiveDartGame();

        if (!$game) {
            return response()->json([
                'success' => false,
                'message' => 'No active game found for this user.',
            ], 404);
        }

        $this->authorize('view', $game);
        return response()->json($engine->getState($game));
    }

    public function pastGames(Request $request)
    {
        $user = $request->user();
        $limit = $this->getLimit($request);

        $games = $user->DartGames()
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $games,
            'count' => $games->count(),
        ]);
    }

    public function state(DartGame $game, DartGameEngineService $engine)
    {
        $this->authorize('view', $game);
        return response()->json($engine->getState($game));
    }

    public function start(DartGame $game, DartGameEngineService $engine)
    {
        $this->authorize('update', $game);
        return response()->json($engine->startGame($game));
    }

    public function submitThrow(DartGame $game, User $user, Request $request, DartGameEngineService $engine)
    {
        $this->authorize('update', $game);

        $payload = $request->validate([
            'field' => ['required', 'integer', 'min:0', 'max:25'],
            'ring' => 'required|string|in:S,D,T,O',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'r' => 'nullable|numeric|min:0',
            'theta' => 'nullable|numeric',
        ]);

        return response()->json($engine->submitThrow($game, $user, $payload));
    }

    public function endGamesByUser(Request $request, User $user, DartGameEngineService $engine)
    {
        $this->authorize('update', DartGame::class);

        $result = $engine->endGamesForUsers([$user->id]);

        return response()->json($result);
    }

    /**
     * Undo the last throw for a player in a game.
     *
     * POST /api/v2/dart/{game}/user/{user}/undo
     */
    public function undoThrow(DartGame $game, User $user, DartGameEngineService $engine)
    {
        $this->authorize('update', $game);

        return response()->json($engine->undoThrow($game, $user));
    }

    /**
     * Update game status (e.g., abort a game).
     *
     * PATCH /api/v2/dart/{game}/status
     */
    public function updateStatus(DartGame $game, UpdateDartGameStatusRequest $request, DartGameEngineService $engine)
    {
        $this->authorize('update', $game);

        $game->update(['status' => DartGameStatus::from($request->validated('status'))]);

        return response()->json($engine->getState($game));
    }

    /**
     * Complete a game with final results.
     *
     * POST /api/v2/dart/{game}/complete
     */
    public function complete(DartGame $game, Request $request, DartGameEngineService $engine)
    {
        $this->authorize('update', $game);

        if (!in_array($game->status, [DartGameStatus::RUNNING, DartGameStatus::DONE, DartGameStatus::PLAYER_WON])) {
            return response()->json([
                'error' => 'Game cannot be completed in its current state.',
                'status' => $game->status,
            ], 422);
        }

        $validated = $request->validate([
            'winner_id' => ['nullable', 'integer', 'exists:users,id'],
            'placements' => ['nullable', 'array'],
            'placements.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'placements.*.place' => ['required', 'integer', 'min:1'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'stats' => ['nullable', 'array'],
        ]);

        // Update placements in pivot
        if (!empty($validated['placements'])) {
            foreach ($validated['placements'] as $placement) {
                $game->users()->updateExistingPivot($placement['user_id'], [
                    'place' => $placement['place'],
                ]);
            }
        }

        // Store completion data in settings
        $settings = $game->settings ? $game->settings->toArray() : [];
        if (isset($validated['winner_id'])) {
            $settings['winner_id'] = $validated['winner_id'];
        }
        if (isset($validated['duration'])) {
            $settings['duration'] = $validated['duration'];
        }
        if (isset($validated['stats'])) {
            $settings['final_stats'] = $validated['stats'];
        }

        $game->update([
            'settings' => $settings,
            'status' => DartGameStatus::FINISHED,
            'finished_at' => now(),
        ]);

        return response()->json($engine->getState($game));
    }

    /**
     * Get full game detail including all throws (for replay/stats).
     *
     * GET /api/v2/dart/{game}/detail
     */
    public function detail(DartGame $game)
    {
        $this->authorize('view', $game);

        $game->load(['users', 'dartThrows', 'teams.users']);

        return response()->json(new DartGameDetailResource($game));
    }
}
