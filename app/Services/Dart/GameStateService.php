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
            'type' => $game->type,
            'points' => $game->points,
            'set' => $currentTurn['set'],
            'leg' => $currentTurn['leg'],
            'turn' => $currentTurn['turn'],
            'maxThrows' => self::DARTS_PER_TURN,
            'active_player_id' => $currentTurn['player_id'],
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

            return [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->pivot->status->value ?? $user->pivot->status,
                'score' => $game->currentPointsByUser($user),
                'average' => $this->calculatePlayerAverage($game, $user),

                // 'remainingPoints' => $game->remainingPointsByUser($user),
                'throws' => DartGameDartThrowResource::collection($throws),
                // 'remainingDartsInTurn' => max(0, self::DARTS_PER_TURN - $throws->count()),
            ];
        });
    }

    /**
     * Calculate the average score per dart for a player.
     */
    private function calculatePlayerAverage(DartGame $game, User $user): float
    {
        $allThrows = $game->dartThrowsByUser($user)->get();

        if ($allThrows->isEmpty()) {
            return 0.0;
        }

        $totalPoints = $allThrows->sum('value');
        $totalDarts = $allThrows->count();

        return round($totalPoints / $totalDarts, 2);
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
    /**
     * @param array $excludePlayerIds Player IDs to skip in turn rotation (e.g. eliminated players).
     */
    public function determineCurrentTurn(DartGame $game, ?User $player = null, array $excludePlayerIds = []): array
    {
        $players = $this->getOrderedPlayers($game);

        // Filter out excluded players for turn determination
        $activePlayers = empty($excludePlayerIds)
            ? $players
            : $players->filter(fn($p) => !in_array($p->id, $excludePlayerIds))->values();

        if ($this->isGameJustStarted($game)) {
            return $this->getInitialTurnState($player, $activePlayers);
        }

        $lastThrow = $this->getLastThrow($game);

        if ($player !== null) {
            return $this->getPlayerTurnState($game, $player, $lastThrow);
        }

        return $this->getNextTurnState($game, $activePlayers, $lastThrow);
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
     * Get the last throw made in the game (including soft-deleted throws).
     */
    private function getLastThrow(DartGame $game)
    {
        return $game->dartThrows()
            ->withTrashed()
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
        $playerLastTurn = $game->dartThrowsByUser($player)->withTrashed()->max('turn') ?? self::INITIAL_TURN;

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

        // Check if the last player completed their turn (threw 3 darts or busted)
        // Include trashed throws to account for busts
        $lastPlayerThrows = $this->countPlayerThrowsInTurn(
            $game,
            $lastPlayerId,
            $currentSet,
            $currentLeg,
            $currentTurn,
            true // Include trashed throws
        );

        $playerBusted = $this->playerBustedInTurn(
            $game,
            $lastPlayerId,
            $currentSet,
            $currentLeg,
            $currentTurn
        );

        if (!$playerBusted && $lastPlayerThrows < self::DARTS_PER_TURN) {
            return [
                'set' => $currentSet,
                'leg' => $currentLeg,
                'turn' => $currentTurn,
                'player_id' => $lastPlayerId,
            ];
        }

        // Last player finished their turn, determine next active player
        $lastPlayerIndex = $this->findPlayerIndex($players, $lastPlayerId);

        // Look for the next player who hasn't completed this turn yet
        for ($i = 1; $i <= $players->count(); $i++) {
            $nextPlayerIndex = ($lastPlayerIndex + $i) % $players->count();
            $candidatePlayer = $players[$nextPlayerIndex];

            // Check if this player has completed their turn (including busts)
            $candidateThrows = $this->countPlayerThrowsInTurn(
                $game,
                $candidatePlayer->id,
                $currentSet,
                $currentLeg,
                $currentTurn,
                true // Include trashed throws
            );

            $candidateBusted = $this->playerBustedInTurn(
                $game,
                $candidatePlayer->id,
                $currentSet,
                $currentLeg,
                $currentTurn
            );

            // Player can still throw if they did NOT bust AND have fewer than 3 throws
            if (!$candidateBusted && $candidateThrows < self::DARTS_PER_TURN) {
                return [
                    'set' => $currentSet,
                    'leg' => $currentLeg,
                    'turn' => $currentTurn,
                    'player_id' => $candidatePlayer->id,
                ];
            }
        }

        // All players have completed this turn, advance to next turn
        return [
            'set' => $currentSet,
            'leg' => $currentLeg,
            'turn' => $currentTurn + 1,
            'player_id' => $players->first()->id,
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
        int $turn,
        bool $includeTrashed = false
    ): int {
        $query = $game->dartThrows()
            ->where('user_id', $userId)
            ->where('set', $set)
            ->where('leg', $leg)
            ->where('turn', $turn);

        if ($includeTrashed) {
            $query->withTrashed();
        }

        return $query->count();
    }

    private function playerBustedInTurn(
        DartGame $game,
        int $userId,
        int $set,
        int $leg,
        int $turn
    ): bool {
        return $game->dartThrows()
            ->onlyTrashed()
            ->where('user_id', $userId)
            ->where('set', $set)
            ->where('leg', $leg)
            ->where('turn', $turn)
            ->exists();
    }
}
