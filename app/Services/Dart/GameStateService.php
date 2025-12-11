<?php
namespace App\Services\Dart;

use App\Models\DartGame;
use App\Enums\DartGameStatus;
use App\Http\Resources\DartGameDartThrowResource;
use App\Http\Resources\DartGameUserResource;
use App\Models\User;
use Illuminate\Support\Collection;

class GameStateService
{
    private const DARTS_PER_TURN = 3;
    private const INITIAL_SET = 1;
    private const INITIAL_LEG = 1;
    private const INITIAL_TURN = 1;

    public function startGame(DartGame $game): void
    {
        $game->update(['status' => DartGameStatus::RUNNING]);
    }

    public function abortGame(DartGame $game): void
    {
        $game->update(['status' => DartGameStatus::ABORTED]);
    }

    public function finishGame(DartGame $game): void
    {
        $game->update(['status' => DartGameStatus::DONE]);
    }

    /**
     * Returns the full game state, including players, current points,
     * remaining points, active player, and throws of the current turn.
     */
    public function getFullState(DartGame $game): array
    {
        $game->load(['users', 'dartThrows']);

        $currentTurn = $this->determineCurrentTurn($game);

        return [
            'game_id' => $game->id,
            'status' => $game->status,
            // 'active_player_id' => $currentTurn['player_id'],
            'set' => $currentTurn['set'],
            'leg' => $currentTurn['leg'],
            'turn' => $currentTurn['turn'],
            'players' => $this->buildPlayerStates($game, $currentTurn),
        ];
    }

    /**
     * Build state information for all players.
     */
    private function buildPlayerStates(DartGame $game, array $currentTurn): Collection
    {
        return $game->users->map(function (User $user) use ($game, $currentTurn) {
            $throws = $this->getCurrentTurnThrows($game, $user, $currentTurn['turn']);
            $isActivePlayer = $currentTurn['player_id'] === $user->id;

            return [
                'player' => new DartGameUserResource($user),
                'currentPoints' => $game->currentPointsByUser($user),
                'remainingPoints' => $game->remainingPointsByUser($user),
                'throws' => DartGameDartThrowResource::collection($throws),
                'remainingDartsInTurn' => max(0, self::DARTS_PER_TURN - $throws->count()),
                'isActive' => $isActivePlayer,
            ];
        });
    }

    /**
     * Get throws for the current turn of a specific player.
     */
    private function getCurrentTurnThrows(DartGame $game, User $user, int $turn): Collection
    {
        return $game->dartThrowsByUser($user)
            ->where('turn', $turn)
            ->orderBy('throw')
            ->get();
    }

    /**
     * Determines the current turn in the game.
     * If a player is specified, returns that player's current turn state.
     * Otherwise, returns the global active turn state.
     */
    public function determineCurrentTurn(DartGame $game, ?User $player = null): array
    {
        $players = $this->getOrderedPlayers($game);

        if ($this->isGameJustStarted($game)) {
            return $this->getInitialTurnState($player, $players);
        }

        $lastThrow = $this->getLastThrow($game);

        if ($player !== null) {
            return $this->getPlayerTurnState($game, $player, $lastThrow);
        }

        return $this->getNextTurnState($game, $players, $lastThrow);
    }

    /**
     * Get players ordered by their position.
     */
    private function getOrderedPlayers(DartGame $game): Collection
    {
        return $game->users()->orderBy('pivot_position')->get();
    }

    /**
     * Check if the game has just started (no throws yet).
     */
    private function isGameJustStarted(DartGame $game): bool
    {
        return $game->dartThrows()->count() === 0;
    }

    /**
     * Get the initial turn state for a new game.
     */
    private function getInitialTurnState(?User $player, Collection $players): array
    {
        $firstPlayerId = $player?->id ?? $players->first()->id;

        return [
            'set' => self::INITIAL_SET,
            'leg' => self::INITIAL_LEG,
            'turn' => self::INITIAL_TURN,
            'player_id' => $firstPlayerId,
        ];
    }

    /**
     * Get the last throw made in the game.
     */
    private function getLastThrow(DartGame $game)
    {
        return $game->dartThrows()
            ->orderBy('set', 'DESC')
            ->orderBy('leg', 'DESC')
            ->orderBy('turn', 'DESC')
            ->orderBy('throw', 'DESC')
            ->first();
    }

    /**
     * Get turn state for a specific player.
     */
    private function getPlayerTurnState(DartGame $game, User $player, $lastThrow): array
    {
        $playerLastTurn = $game->dartThrowsByUser($player)->max('turn') ?? self::INITIAL_TURN;

        return [
            'set' => $lastThrow->set,
            'leg' => $lastThrow->leg,
            'turn' => $playerLastTurn,
            'player_id' => $player->id,
        ];
    }

    /**
     * Get the next turn state based on the last throw.
     */
    private function getNextTurnState(DartGame $game, Collection $players, $lastThrow): array
    {
        $lastPlayerId = $lastThrow->user_id;
        $currentSet = $lastThrow->set;
        $currentLeg = $lastThrow->leg;
        $currentTurn = $lastThrow->turn;

        // Check if the last player completed their turn (threw 3 darts)
        $lastPlayerThrows = $this->countPlayerThrowsInTurn(
            $game,
            $lastPlayerId,
            $currentSet,
            $currentLeg,
            $currentTurn
        );

        // If current player hasn't finished their turn, they're still active
        if ($lastPlayerThrows < self::DARTS_PER_TURN) {
            return [
                'set' => $currentSet,
                'leg' => $currentLeg,
                'turn' => $currentTurn,
                'player_id' => $lastPlayerId,
            ];
        }

        // Current player finished their turn, move to next player
        $lastPlayerIndex = $this->findPlayerIndex($players, $lastPlayerId);
        $nextPlayerIndex = ($lastPlayerIndex + 1) % $players->count();
        $nextPlayer = $players[$nextPlayerIndex];

        // Check if all players have completed this turn
        $playersCompletedThisTurn = $this->countPlayersCompletedTurn(
            $game,
            $players,
            $currentSet,
            $currentLeg,
            $currentTurn
        );

        // If all players finished, advance to next turn
        if ($playersCompletedThisTurn === $players->count()) {
            return [
                'set' => $currentSet,
                'leg' => $currentLeg,
                'turn' => $currentTurn + 1,
                'player_id' => $nextPlayer->id,
            ];
        }

        // Some players haven't thrown yet in this turn
        return [
            'set' => $currentSet,
            'leg' => $currentLeg,
            'turn' => $currentTurn,
            'player_id' => $nextPlayer->id,
        ];
    }

    /**
     * Find the index of a player in the ordered players collection.
     */
    private function findPlayerIndex(Collection $players, int $userId): int
    {
        return $players->search(fn($player) => $player->id === $userId);
    }

    /**
     * Count how many darts a specific player has thrown in a specific turn.
     */
    private function countPlayerThrowsInTurn(
        DartGame $game,
        int $userId,
        int $set,
        int $leg,
        int $turn
    ): int {
        return $game->dartThrows()
            ->where('user_id', $userId)
            ->where('set', $set)
            ->where('leg', $leg)
            ->where('turn', $turn)
            ->count();
    }

    /**
     * Count how many players have completed their turn (thrown 3 darts).
     */
    private function countPlayersCompletedTurn(
        DartGame $game,
        Collection $players,
        int $set,
        int $leg,
        int $turn
    ): int {
        $completed = 0;

        foreach ($players as $player) {
            $throwCount = $this->countPlayerThrowsInTurn(
                $game,
                $player->id,
                $set,
                $leg,
                $turn
            );

            if ($throwCount >= self::DARTS_PER_TURN) {
                $completed++;
            }
        }

        return $completed;
    }
}
