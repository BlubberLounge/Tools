<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Http\Resources\DartGameDetailResource;
use App\Enums\DartGameStatus;
use App\Enums\DartGameType;
use App\Models\DartGame;
use App\Models\DartGameUser;
use App\Models\DartLocalPlayer;
use App\Models\DartUserSettings;
use App\Enums\DartGameUserStatus;
use App\Services\Dart\LocalPlayerResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DartHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $limit = $this->getLimit($request);

        // Include games linked via pivot table OR created by this user (synced games)
        $query = DartGame::where(function ($q) use ($user) {
                $q->where('dart_games.created_by', $user->id)
                  ->orWhereExists(function ($sub) use ($user) {
                      $sub->select(DB::raw(1))
                           ->from('dart_game_user')
                           ->whereColumn('dart_game_user.dart_game_id', 'dart_games.id')
                           ->where('dart_game_user.user_id', $user->id);
                  });
            })
            ->whereIn('dart_games.status', [
                DartGameStatus::DONE,
                DartGameStatus::PLAYER_WON,
                DartGameStatus::FINISHED,
            ]);

        // Optional: only return games created/updated after a given timestamp
        if ($since = $request->query('since')) {
            $query->where('dart_games.created_at', '>', $since);
        }

        $games = $query
            ->with(['users', 'dartThrows', 'teams.users'])
            ->orderByDesc('dart_games.created_at')
            ->paginate($limit);

        return response()->json([
            'data' => DartGameDetailResource::collection($games),
            'meta' => [
                'current_page' => $games->currentPage(),
                'last_page' => $games->lastPage(),
                'per_page' => $games->perPage(),
                'total' => $games->total(),
            ],
        ]);
    }

    public function sync(Request $request, LocalPlayerResolver $resolver)
    {
        $validated = $request->validate([
            'games' => ['required', 'array', 'max:50'],
            'games.*.client_game_id' => ['required', 'string', 'max:255'],
            'games.*.type' => ['required', 'string'],
            'games.*.title' => ['required', 'string'],
            'games.*.status' => ['required', 'string'],
            'games.*.points' => ['nullable', 'integer'],
            'games.*.options' => ['nullable', 'array'],
            'games.*.users' => ['nullable', 'array'],
            'games.*.users.*.user_id' => ['nullable', 'required_without:games.*.users.*.local_player_id', 'integer', 'exists:users,id'],
            'games.*.users.*.local_player_id' => ['nullable', 'required_without:games.*.users.*.user_id', 'string', 'max:255'],
            'games.*.placements' => ['nullable', 'array'],
            'games.*.placements.*.user_id' => ['nullable', 'integer'],
            'games.*.placements.*.local_player_id' => ['nullable', 'string', 'max:255'],
            'games.*.placements.*.place' => ['required', 'integer'],
            'games.*.throws' => ['nullable', 'array'],
            'games.*.throws.*.user_id' => ['nullable', 'required_without:games.*.throws.*.local_player_id', 'integer'],
            'games.*.throws.*.local_player_id' => ['nullable', 'required_without:games.*.throws.*.user_id', 'string', 'max:255'],
            'games.*.throws.*.set' => ['required', 'integer'],
            'games.*.throws.*.leg' => ['required', 'integer'],
            'games.*.throws.*.turn' => ['required', 'integer'],
            'games.*.throws.*.throw' => ['required', 'integer'],
            'games.*.throws.*.field' => ['required'],
            'games.*.throws.*.ring' => ['required', 'string'],
            'games.*.throws.*.value' => ['required', 'integer'],
            'games.*.throws.*.x' => ['nullable', 'numeric'],
            'games.*.throws.*.y' => ['nullable', 'numeric'],
            'games.*.throws.*.r' => ['nullable', 'numeric'],
            'games.*.throws.*.theta' => ['nullable', 'numeric'],
        ]);

        $user = $request->user();
        $results = [];

        // Collect all local_player_ids and batch-resolve them
        $allLocalIds = $this->collectLocalPlayerIds($validated['games']);
        $resolvedMap = $resolver->resolveMany($allLocalIds);

        // Pre-fetch existing games for idempotency check (avoid N+1)
        $clientGameIds = collect($validated['games'])->pluck('client_game_id')->filter();
        $existingGames = DartGame::whereIn('client_game_id', $clientGameIds)
            ->pluck('id', 'client_game_id');

        // Disable auditing for pivot records created during sync (Pivot models
        // don't populate the auto-increment id, causing null auditable_id errors)
        DartGameUser::disableAuditing();

        DB::transaction(function () use ($validated, $user, $resolvedMap, $existingGames, &$results) {
            foreach ($validated['games'] as $gameData) {
                // Idempotent: skip if already synced
                $existingId = $existingGames->get($gameData['client_game_id']);
                if ($existingId) {
                    $results[] = [
                        'client_game_id' => $gameData['client_game_id'],
                        'status' => 'skipped',
                        'game_id' => $existingId,
                    ];
                    continue;
                }

                $game = new DartGame();
                $game->created_by = $user->id;
                $game->client_game_id = $gameData['client_game_id'];
                $game->type = DartGameType::from($gameData['type']);
                $game->status = DartGameStatus::from($gameData['status']);
                $game->title = $gameData['title'];
                $game->points = $gameData['points'] ?? null;
                $game->settings = $gameData['options'] ?? null;
                $game->save();

                // Always link the uploading user to the game
                DartGameUser::firstOrCreate([
                    'dart_game_id' => $game->id,
                    'user_id' => $user->id,
                ], [
                    'status' => DartGameUserStatus::ACCEPTED,
                    'position' => 0,
                ]);

                // Attach users
                if (!empty($gameData['users'])) {
                    $placements = collect($gameData['placements'] ?? []);
                    $placementsByUserId = $placements->whereNotNull('user_id')->keyBy('user_id');
                    $placementsByLocalId = $placements->whereNotNull('local_player_id')->keyBy('local_player_id');

                    foreach ($gameData['users'] as $position => $playerEntry) {
                        $resolved = $this->resolvePlayer($playerEntry, $resolvedMap);

                        // Find placement for this player (by user_id or local_player_id)
                        $placement = null;
                        if ($resolved['user_id']) {
                            $placement = $placementsByUserId->get($resolved['user_id']);
                        }
                        if (!$placement && $resolved['local_player_id']) {
                            $placement = $placementsByLocalId->get($resolved['local_player_id']);
                        }

                        $pivotData = [
                            'dart_game_id' => $game->id,
                            'user_id' => $resolved['user_id'],
                            'local_player_id' => $resolved['local_player_id'],
                            'status' => DartGameUserStatus::ACCEPTED,
                            'position' => $position,
                            'place' => $placement['place'] ?? null,
                        ];

                        DartGameUser::create($pivotData);
                    }
                }

                // Create throws
                if (!empty($gameData['throws'])) {
                    foreach ($gameData['throws'] as $throwData) {
                        $resolved = $this->resolvePlayer($throwData, $resolvedMap);

                        $game->dartThrows()->create([
                            'user_id' => $resolved['user_id'],
                            'local_player_id' => $resolved['local_player_id'],
                            'set' => $throwData['set'],
                            'leg' => $throwData['leg'],
                            'turn' => $throwData['turn'],
                            'throw' => $throwData['throw'],
                            'field' => $throwData['field'],
                            'ring' => $throwData['ring'],
                            'value' => $throwData['value'],
                            'x' => $throwData['x'] ?? null,
                            'y' => $throwData['y'] ?? null,
                            'r' => $throwData['r'] ?? null,
                            'theta' => $throwData['theta'] ?? null,
                        ]);
                    }
                }

                $results[] = [
                    'client_game_id' => $gameData['client_game_id'],
                    'status' => 'created',
                    'game_id' => $game->id,
                ];
            }
        });

        DartGameUser::enableAuditing();

        return response()->json([
            'synced' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * Resolve a player entry to user_id and local_player_id.
     * Accepts both old format (plain integer) and new format ({user_id, local_player_id}).
     */
    private function resolvePlayer(mixed $entry, array $resolvedMap): array
    {
        // Old format: plain integer user_id
        if (is_int($entry) || (is_string($entry) && ctype_digit($entry))) {
            return ['user_id' => (int) $entry, 'local_player_id' => null];
        }

        // New format: {user_id, local_player_id}
        $userId = $entry['user_id'] ?? null;
        $localPlayerId = $entry['local_player_id'] ?? null;

        // If user_id is provided directly, use it
        if ($userId) {
            return ['user_id' => $userId, 'local_player_id' => $localPlayerId];
        }

        // Try to resolve local_player_id to user_id
        if ($localPlayerId && isset($resolvedMap[$localPlayerId])) {
            return ['user_id' => $resolvedMap[$localPlayerId], 'local_player_id' => $localPlayerId];
        }

        // Unresolved local player — store with null user_id for later backfill
        return ['user_id' => null, 'local_player_id' => $localPlayerId];
    }

    /**
     * Collect all local_player_ids from game data for batch resolution.
     */
    private function collectLocalPlayerIds(array $games): array
    {
        $ids = [];
        foreach ($games as $game) {
            foreach ($game['users'] ?? [] as $entry) {
                if (is_array($entry) && !empty($entry['local_player_id'])) {
                    $ids[] = $entry['local_player_id'];
                }
            }
            foreach ($game['throws'] ?? [] as $entry) {
                if (!empty($entry['local_player_id'])) {
                    $ids[] = $entry['local_player_id'];
                }
            }
        }
        return array_unique($ids);
    }

    public function status(Request $request)
    {
        $user = $request->user();

        $completedStatuses = [
            DartGameStatus::DONE,
            DartGameStatus::PLAYER_WON,
            DartGameStatus::FINISHED,
        ];

        $gamesQuery = DartGame::where(function ($q) use ($user) {
                $q->where('dart_games.created_by', $user->id)
                  ->orWhereExists(function ($sub) use ($user) {
                      $sub->select(DB::raw(1))
                           ->from('dart_game_user')
                           ->whereColumn('dart_game_user.dart_game_id', 'dart_games.id')
                           ->where('dart_game_user.user_id', $user->id);
                  });
            })
            ->whereIn('dart_games.status', $completedStatuses);

        $gamesCount = (clone $gamesQuery)->count();

        $playersCount = DartLocalPlayer::where('user_id', $user->id)->count();

        $lastGameAt = (clone $gamesQuery)->max('dart_games.created_at');

        $settings = DartUserSettings::where('user_id', $user->id)->first();
        $lastSyncAt = $settings?->settings?->get('last_sync_at');

        return response()->json([
            'games_count'   => $gamesCount,
            'players_count' => $playersCount,
            'last_game_at'  => $lastGameAt,
            'last_sync_at'  => $lastSyncAt,
        ]);
    }
}
