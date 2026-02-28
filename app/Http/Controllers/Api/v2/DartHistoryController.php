<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Http\Resources\DartGameDetailResource;
use App\Enums\DartGameStatus;
use App\Enums\DartGameType;
use App\Models\DartGame;
use App\Models\DartLocalPlayer;
use App\Models\DartThrow;
use App\Models\DartUserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DartHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $limit = $this->getLimit($request);

        $games = $user->dartGames()
            ->whereIn('dart_games.status', [
                DartGameStatus::DONE,
                DartGameStatus::PLAYER_WON,
                DartGameStatus::FINISHED,
            ])
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

    public function sync(Request $request)
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
            'games.*.users.*' => ['integer', 'exists:users,id'],
            'games.*.placements' => ['nullable', 'array'],
            'games.*.placements.*.user_id' => ['required', 'integer'],
            'games.*.placements.*.place' => ['required', 'integer'],
            'games.*.throws' => ['nullable', 'array'],
            'games.*.throws.*.user_id' => ['required', 'integer'],
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

        DB::transaction(function () use ($validated, $user, &$results) {
            foreach ($validated['games'] as $gameData) {
                // Idempotent: skip if already synced
                $existing = DartGame::where('client_game_id', $gameData['client_game_id'])->first();
                if ($existing) {
                    $results[] = [
                        'client_game_id' => $gameData['client_game_id'],
                        'status' => 'skipped',
                        'game_id' => $existing->id,
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
                $game->options = $gameData['options'] ?? null;
                $game->save();

                // Attach users
                if (!empty($gameData['users'])) {
                    $placements = collect($gameData['placements'] ?? [])
                        ->keyBy('user_id');

                    foreach ($gameData['users'] as $position => $userId) {
                        $pivotData = [
                            'status' => 'accepted',
                            'position' => $position,
                        ];
                        if ($placements->has($userId)) {
                            $pivotData['place'] = $placements[$userId]['place'];
                        }
                        $game->users()->attach($userId, $pivotData);
                    }
                }

                // Create throws
                if (!empty($gameData['throws'])) {
                    foreach ($gameData['throws'] as $throwData) {
                        $game->dartThrows()->create([
                            'user_id' => $throwData['user_id'],
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

        return response()->json([
            'synced' => count($results),
            'results' => $results,
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();

        $completedStatuses = [
            DartGameStatus::DONE,
            DartGameStatus::PLAYER_WON,
            DartGameStatus::FINISHED,
        ];

        $gamesCount = $user->dartGames()
            ->whereIn('dart_games.status', $completedStatuses)
            ->count();

        $playersCount = DartLocalPlayer::where('user_id', $user->id)->count();

        $lastGameAt = $user->dartGames()
            ->whereIn('dart_games.status', $completedStatuses)
            ->max('dart_games.created_at');

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
