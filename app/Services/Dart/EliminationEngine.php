<?php

namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\User;
use App\Enums\DartGameStatus;
use App\Models\DartThrow;
use Illuminate\Http\Exceptions\HttpResponseException;

class EliminationEngine extends AbstractGameEngine
{
    private const DEFAULT_LIVES = 3;
    private const DEFAULT_ROOKIE_TURNS = 2;

    public function start(DartGame $game): array
    {
        return $this->transaction(function () use ($game) {
            $this->initializePlayerStates($game);
            $this->stateService->startGame($game);
            return $this->getState($game);
        });
    }

    public function submitThrow(DartGame $game, User $user, array $payload): array
    {
        return $this->transaction(function () use ($game, $user, $payload) {
            $this->normalizeCoordinates($payload);

            $position = $this->determineThrowPosition($game, $user);
            $payload = array_merge($payload, $position);

            $this->validateThrow($game, $user, $payload);

            $value = $this->scoringService->calculateValue($payload['field'], $payload['ring']);

            $dartThrow = $game->dartThrows()->make();
            $dartThrow->user()->associate($user);
            $dartThrow->set = $payload['set'];
            $dartThrow->leg = $payload['leg'];
            $dartThrow->turn = $payload['turn'];
            $dartThrow->throw = $payload['throw'];
            $dartThrow->field = $payload['field'];
            $dartThrow->ring = $payload['ring'];
            $dartThrow->value = $value;
            $dartThrow->x = $payload['x'] ?? null;
            $dartThrow->y = $payload['y'] ?? null;
            $dartThrow->r = $payload['r'] ?? null;
            $dartThrow->theta = $payload['theta'] ?? null;
            $dartThrow->save();

            // Check if the round is complete (all active players have thrown 3 darts)
            if ($this->isRoundComplete($game, $payload['turn'])) {
                $this->evaluateRound($game, $payload['turn']);
            }

            // Check if game is over (only 1 player left)
            if ($this->isGameOver($game)) {
                $this->stateService->finishGame($game);
            }

            return $this->getState($game);
        });
    }

    public function getState(DartGame $game): array
    {
        $state = $this->stateService->getFullState($game);
        $playerStates = $this->getPlayerStates($game);
        $eliminatedIds = $this->getEliminatedPlayerIds($game);

        // Override active_player_id to skip eliminated players
        if (in_array($state['active_player_id'], $eliminatedIds)) {
            $correctedTurn = $this->stateService->determineCurrentTurn($game, null, $eliminatedIds);
            $state['active_player_id'] = $correctedTurn['player_id'];
        }

        $state['players'] = $state['players']->map(function ($player) use ($game, $playerStates) {
            $userId = (string) $player['id'];
            $ps = $playerStates[$userId] ?? [
                'lives' => $this->getStartingLives($game),
                'is_eliminated' => false,
            ];

            $player['lives'] = $ps['lives'];
            $player['is_eliminated'] = $ps['is_eliminated'];
            $player['is_rookie_protected'] = $this->isRookieProtected($game, $player['id']);

            return $player;
        });

        return $state;
    }

    public function isFinished(DartGame $game): bool
    {
        return $game->isDone();
    }

    public function finish(DartGame $game): void
    {
        $this->stateService->finishGame($game);
        $this->broadcastState($game, 'game.finished');
    }

    /**
     * Initialize player states with starting lives.
     */
    private function initializePlayerStates(DartGame $game): void
    {
        $lives = $this->getStartingLives($game);
        $states = [];

        foreach ($game->users as $user) {
            $states[(string) $user->id] = [
                'lives' => $lives,
                'is_eliminated' => false,
            ];
        }

        $settings = $game->settings ? $game->settings->toArray() : [];
        $settings['player_states'] = $states;
        $game->update(['settings' => $settings]);
    }

    /**
     * Get player states from game settings.
     */
    private function getPlayerStates(DartGame $game): array
    {
        $game->refresh();
        return $game->getSetting('player_states', []);
    }

    /**
     * Save player states to game settings.
     */
    private function savePlayerStates(DartGame $game, array $states): void
    {
        $settings = $game->settings ? $game->settings->toArray() : [];
        $settings['player_states'] = $states;
        $game->update(['settings' => $settings]);
    }

    /**
     * Get starting lives from game options.
     */
    private function getStartingLives(DartGame $game): int
    {
        return (int) $game->getSetting('lives', self::DEFAULT_LIVES);
    }

    /**
     * Check if a player is still under rookie protection.
     */
    private function isRookieProtected(DartGame $game, int $userId): bool
    {
        if (!$game->getSetting('rookie_protection', false)) {
            return false;
        }

        $rookieTurns = (int) $game->getSetting('rookie_turns', self::DEFAULT_ROOKIE_TURNS);
        $user = $game->users->firstWhere('id', $userId);
        if (!$user) return false;

        $maxTurn = $game->dartThrowsByUser($user)->max('turn') ?? 0;
        return $maxTurn <= $rookieTurns;
    }

    /**
     * Check if all active (non-eliminated) players have completed their throws for a turn.
     */
    private function isRoundComplete(DartGame $game, int $turn): bool
    {
        $playerStates = $this->getPlayerStates($game);

        foreach ($game->users as $user) {
            $userId = (string) $user->id;
            $ps = $playerStates[$userId] ?? null;

            // Skip eliminated players
            if ($ps && $ps['is_eliminated']) {
                continue;
            }

            $throwCount = $game->dartThrowsByUser($user)
                ->where('turn', $turn)
                ->count();

            if ($throwCount < 3) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a completed round - the player with the lowest score loses a life.
     */
    private function evaluateRound(DartGame $game, int $turn): void
    {
        $playerStates = $this->getPlayerStates($game);
        $roundScores = [];

        foreach ($game->users as $user) {
            $userId = (string) $user->id;
            $ps = $playerStates[$userId] ?? null;

            // Skip eliminated players
            if ($ps && $ps['is_eliminated']) {
                continue;
            }

            // Skip rookie-protected players
            if ($this->isRookieProtected($game, $user->id)) {
                continue;
            }

            $roundScore = $game->dartThrowsByUser($user)
                ->where('turn', $turn)
                ->sum('value');

            $roundScores[$userId] = $roundScore;
        }

        if (empty($roundScores)) {
            return;
        }

        // Target score mode: everyone below target loses a life
        $targetScore = $game->getSetting('target_score');
        if ($targetScore !== null) {
            $losers = array_keys(array_filter($roundScores, fn($s) => $s < (int) $targetScore));
        } else {
            // Default: player(s) with the lowest score lose a life
            $lowestScore = min($roundScores);
            $losers = array_keys(array_filter($roundScores, fn($s) => $s === $lowestScore));
        }

        foreach ($losers as $loserId) {
            if (isset($playerStates[$loserId])) {
                $playerStates[$loserId]['lives'] = max(0, $playerStates[$loserId]['lives'] - 1);
                if ($playerStates[$loserId]['lives'] <= 0) {
                    $playerStates[$loserId]['is_eliminated'] = true;
                }
            }
        }

        $this->savePlayerStates($game, $playerStates);
    }

    /**
     * Get IDs of eliminated players.
     */
    private function getEliminatedPlayerIds(DartGame $game): array
    {
        $playerStates = $this->getPlayerStates($game);
        $eliminated = [];

        foreach ($playerStates as $userId => $state) {
            if ($state['is_eliminated']) {
                $eliminated[] = (int) $userId;
            }
        }

        return $eliminated;
    }

    /**
     * Check if the game is over (only 1 or fewer active players remain).
     */
    private function isGameOver(DartGame $game): bool
    {
        $playerStates = $this->getPlayerStates($game);
        $activePlayers = 0;

        foreach ($playerStates as $state) {
            if (!$state['is_eliminated']) {
                $activePlayers++;
            }
        }

        return $activePlayers <= 1;
    }

    /**
     * Determines set, leg, turn, and throw number for the next incoming throw.
     */
    private function determineThrowPosition(DartGame $game, User $user): array
    {
        $eliminatedIds = $this->getEliminatedPlayerIds($game);
        $lastThrow = $game->getLastThrowByUser($user);

        if (!$lastThrow || $lastThrow->trashed()) {
            $currentState = $this->stateService->determineCurrentTurn($game, null, $eliminatedIds);
            return [
                'set'   => $currentState['set'],
                'leg'   => $currentState['leg'],
                'turn'  => $currentState['turn'],
                'throw' => 1,
            ];
        }

        $currentState = $this->stateService->determineCurrentTurn($game, null, $eliminatedIds);

        $next = [
            'set'   => $currentState['set'],
            'leg'   => $currentState['leg'],
            'turn'  => $currentState['turn'],
            'throw' => $lastThrow->throw,
        ];

        if ($next['throw'] < 3) {
            $next['throw']++;
        } else {
            $next['throw'] = 1;
        }

        return $next;
    }

    private function validateThrow(DartGame $game, User $user, array $data): void
    {
        if ($game->status !== DartGameStatus::RUNNING) {
            throw new HttpResponseException(response()->json([
                'error' => 'game_not_running',
                'message' => 'Game is not in running state.',
                'actual_state' => $game->status,
            ], 409));
        }

        if (!$game->users->contains($user)) {
            throw new HttpResponseException(response()->json([
                'error' => 'player_not_in_game',
                'message' => 'Player is not part of this game.',
            ], 403));
        }

        // Check if player is eliminated
        $playerStates = $this->getPlayerStates($game);
        $userId = (string) $user->id;
        if (isset($playerStates[$userId]) && $playerStates[$userId]['is_eliminated']) {
            throw new HttpResponseException(response()->json([
                'error' => 'player_eliminated',
                'message' => 'Player has been eliminated from this game.',
            ], 403));
        }

        // Active player check using excluded eliminated players
        $eliminatedIds = $this->getEliminatedPlayerIds($game);
        $activePlayerId = $this->stateService->determineCurrentTurn($game, null, $eliminatedIds)['player_id'];
        if ($user->id !== $activePlayerId) {
            throw new HttpResponseException(response()->json([
                'error' => 'not_active_player',
                'message' => 'It is not this player\'s turn.',
                'active_player_id' => $activePlayerId,
            ], 403));
        }

        $this->validateFieldRing($data['field'] ?? null, $data['ring'] ?? null);
    }
}
