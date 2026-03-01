<?php

namespace App\Services\Dart;

use App\Enums\DartGameStatus;
use App\Models\DartGame;
use App\Enums\DartGameType;
use App\Models\User;
use App\Models\DartGameUser;
use App\Models\DartTeam;
use App\Models\DartThrow;
use App\Enums\DartPlayType;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DartGameEngineService
{
    protected array $engines = [];

    public function __construct(
        protected GameStateService $stateService,
        protected ScoringService $scoringService,
        protected StatisticsService $statisticsService,
        protected WebhookBroadcaster $broadcaster
    ) {
        $this->engines['X01'] = new X01Engine($stateService, $scoringService, $statisticsService, $broadcaster);
        $this->engines['ATC'] = new AroundTheClockEngine($stateService, $scoringService, $statisticsService, $broadcaster);
        $this->engines['ELIMINATION'] = new EliminationEngine($stateService, $scoringService, $statisticsService, $broadcaster);
    }

    /**
     * Liefert die richtige Engine für das Game (basierend auf $game->type).
     */
    public function engineFor(DartGame $game): GameEngineInterface
    {
        $type = match ($game->type) {
            DartGameType::X01 => 'X01',
            DartGameType::cricket => 'CRICKET',
            DartGameType::aroundTheClock => 'ATC',
            DartGameType::highscore => 'HIGHSCORE',
            DartGameType::elimination => 'ELIMINATION',
        };

        return $this->engines[$type]
            ?? throw new InvalidArgumentException("Unknown game type: {$game->type->name}");
    }

    public function newGame(int $userId, array $data): DartGame|array
    {
        $game = new DartGame();

        $game->created_by = $userId;
        $game->type = DartGameType::from($data['type']);
        $game->status = DartGameStatus::CREATED;

        $game->private = $data['private'] ?? false;
        $game->title = $data['title'] ?? null;
        $game->comment = $data['comment'] ?? null;

        $game->points = $data['points'] ?? null;
        $game->start = $data['start'] ?? null;
        $game->end = $data['end'] ?? null;

        $game->singleOut = $data['singleOut'] ?? false;
        $game->doubleOut = $data['doubleOut'] ?? false;
        $game->trippleOut = $data['trippleOut'] ?? false;

        $game->singleIn = $data['singleIn'] ?? true;
        $game->doubleIn = $data['doubleIn'] ?? true;
        $game->trippleIn = $data['trippleIn'] ?? true;

        // New: play type and mode-specific settings
        $game->game_type = $data['game_type'] ?? DartPlayType::STANDARD->value;
        $game->settings = $data['settings'] ?? null;

        $game->save();

        if (isset($data['users']) && is_array($data['users'])) {
            $requested = array_values($data['users']);

            // find active user ids (participating in created/started/running games)
            $active = DartGameUser::activeUserIds();

            $conflicts = array_values(array_intersect($requested, $active));

            if (!empty($conflicts)) {
                // return the created game and the conflicting user ids so caller can decide
                return ['game' => $game, 'conflicts' => $conflicts];
            }

            $this->addUsers($game, $requested);
        }

        // Create teams if this is a team game
        if (isset($data['teams']) && is_array($data['teams'])) {
            foreach ($data['teams'] as $position => $teamData) {
                $team = $game->teams()->create([
                    'name' => $teamData['name'],
                    'color' => $teamData['color'],
                    'position' => $position,
                ]);

                // Assign players to the team via the pivot
                if (isset($teamData['players'])) {
                    DartGameUser::where('dart_game_id', $game->id)
                        ->whereIn('user_id', $teamData['players'])
                        ->whereNull('deleted_at')
                        ->update(['dart_team_id' => $team->id]);
                }
            }
        }

        return $game;
    }

    public function startGame(DartGame $game): array
    {
        return $this->engineFor($game)->start($game);
    }

    public function submitThrow(DartGame $game, User $user, array $payload): array
    {
        return $this->engineFor($game)->submitThrow($game, $user, $payload);
    }

    public function getState(DartGame $game): array
    {
        return $this->engineFor($game)->getState($game);
    }

    /**
     * Undo the last throw for a player in a game.
     * Soft-deletes the most recent non-deleted throw.
     */
    public function undoThrow(DartGame $game, User $user): array
    {
        return $this->engineFor($game)->undoThrow($game, $user);
    }

    /**
     * Add a user to the game.
     *
     * @param DartGame $game
     * @param int $userId
     * @param array $data Additional pivot data (status, position, etc.)
     * @return void
     */
    public function addUser(DartGame $game, int $userId, array $data = []): void
    {
        $user = User::find($userId);
        $autoAccept = $user && (bool) $user->settings->value('dartGameInvitationAutoAccept', true);

        $pivotData = [
            'status' => $autoAccept ? 'accepted' : ($data['status'] ?? 'pending'),
        ];

        if (isset($data['position'])) {
            $pivotData['position'] = $data['position'];
        }

        $game->users()->attach($userId, $pivotData);
    }

    /**
     * Add multiple users to the game.
     *
     * @param DartGame $game
     * @param array $userIds Array of user IDs to add
     * @param array $data Additional pivot data (status, position, etc.)
     * @return void
     */
    public function addUsers(DartGame $game, array $userIds, array $data = []): array
    {
        $requested = array_values($userIds);

        // check for users that are already active in other games
        $active = DartGameUser::activeUserIds();
        $conflicts = array_values(array_intersect($requested, $active));

        if (!empty($conflicts)) {
            return ['conflicts' => $conflicts];
        }

        foreach ($requested as $position => $userId) {
            $userData = array_merge($data, ['position' => $position]);
            $this->addUser($game, $userId, $userData);
        }

        return ['attached' => $requested];
    }

    /**
     * Remove a user from the game.
     *
     * @param DartGame $game
     * @param int $userId
     * @return void
     */
    public function removeUser(DartGame $game, int $userId): void
    {
        $game->users()->detach($userId);
    }

    /**
     * Update user status in the game.
     *
     * @param DartGame $game
     * @param int $userId
     * @param string $status pending|accepted|denied
     * @return void
     */
    public function updateUserStatus(DartGame $game, int $userId, string $status): void
    {
        $game->users()->updateExistingPivot($userId, ['status' => $status]);
    }

    /**
     * End active games for the given users by setting the game status to the provided end status.
     * Returns an array with ended game ids and the mapping of game => affected user ids.
     *
     * @param array $userIds
     * @param DartGameStatus $endStatus
     * @return array
     */
    public function endGamesForUsers(array $userIds, DartGameStatus $endStatus = DartGameStatus::ABORTED): array
    {
        $userIds = array_values($userIds);

        $gameIds = DartGameUser::join('dart_games', 'dart_game_user.dart_game_id', '=', 'dart_games.id')
            ->whereIn('dart_game_user.user_id', $userIds)
            ->whereIn('dart_games.status', ['created', 'started', 'running'])
            ->where('dart_game_user.status', 'accepted')
            ->whereNull('dart_game_user.deleted_at')
            ->pluck('dart_game_user.dart_game_id')
            ->unique()
            ->values()
            ->toArray();

        $affected = [];

        if (!empty($gameIds)) {
            $games = DartGame::whereIn('id', $gameIds)->get();

            foreach ($games as $g) {
                $players = DartGameUser::where('dart_game_id', $g->id)
                    ->whereIn('user_id', $userIds)
                    ->whereNull('deleted_at')
                    ->pluck('user_id')
                    ->toArray();

                $affected[$g->id] = $players;

                $g->status = $endStatus;
                $g->save();
            }
        }

        return ['ended_game_ids' => $gameIds, 'affected' => $affected];
    }

    /**
     * Convenience: remove players by ending the games they are currently in.
     *
     * @param array $userIds
     * @param DartGameStatus $endStatus
     * @return array
     */
    public function removePlayersByEndingGames(array $userIds, DartGameStatus $endStatus = DartGameStatus::ABORTED): array
    {
        return $this->endGamesForUsers($userIds, $endStatus);
    }
}
