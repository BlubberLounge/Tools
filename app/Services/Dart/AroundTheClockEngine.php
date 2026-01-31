<?php

namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\User;
use App\Enums\DartGameStatus;
use App\Models\DartThrow;
use Illuminate\Http\Exceptions\HttpResponseException;

class AroundTheClockEngine extends AbstractGameEngine
{
    // Around the Clock sequence: 1-20, then Bull
    private const SEQUENCE = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 25];
    private const SEQUENCE_LENGTH = 21;

    public function start(DartGame $game): array
    {
        return $this->transaction(function () use ($game) {
            $this->stateService->startGame($game);
            return $this->getState($game);
        });
    }

    public function submitThrow(DartGame $game, User $user, array $payload): array
    {
        return $this->transaction(function () use ($game, $user, $payload) {
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
            $dartThrow->save();

            // Check if player hit the current target
            $playerProgress = $this->getPlayerProgress($game, $user);
            $currentTarget = self::SEQUENCE[$playerProgress];
            $hitTarget = $this->hitTarget($payload, $currentTarget);

            if ($hitTarget) {
                // Advance player's progress
                $this->advancePlayerProgress($game, $user);
            }

            // Check if player finished
            if ($this->isPlayerFinished($game, $user)) {
                $this->stateService->finishGame($game);
            }

            return $this->getState($game);
        });
    }

    public function getState(DartGame $game): array
    {
        $state = $this->stateService->getFullState($game);

        $activePlayerId = $state['active_player_id'];

        $state['players'] = $state['players']->map(function ($player) use ($game, $activePlayerId) {
            $user = $game->users->firstWhere('id', $player['id']);
            $isActivePlayer = $player['id'] === $activePlayerId;

            $progress = $this->getPlayerProgress($game, $user);
            $currentTarget = $progress < self::SEQUENCE_LENGTH ? self::SEQUENCE[$progress] : null;
            $isFinished = $progress >= self::SEQUENCE_LENGTH;

            $player['progress'] = $progress;
            $player['current_target'] = $currentTarget;
            $player['is_finished'] = $isFinished;

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
     * Get the current progress for a player (how many targets have been hit).
     */
    private function getPlayerProgress(DartGame $game, User $user): int
    {
        // Count successful hits for this player
        return $game->dartThrowsByUser($user)
            ->where('field', '!=', 0) // Only count non-miss throws
            ->whereNotNull('value')
            ->count();
    }

    /**
     * Check if the thrown dart hit the current target.
     */
    private function hitTarget(array $payload, int $targetField): bool
    {
        // Player must hit the target field (in any ring: S, D, T)
        return intval($payload['field']) === $targetField;
    }

    /**
     * Advance player's progress by marking them as having hit the current target.
     */
    private function advancePlayerProgress(DartGame $game, User $user): void
    {
        // Progress is tracked by counting successful throws
        // The last throw will be recorded in submitThrow
    }

    /**
     * Check if player has finished (hit all 21 targets).
     */
    private function isPlayerFinished(DartGame $game, User $user): bool
    {
        return $this->getPlayerProgress($game, $user) >= self::SEQUENCE_LENGTH;
    }

    /**
     * Determines set, leg, turn, and throw number for the next incoming throw.
     * Turn is based on the current game state, not just the player's last throw.
     */
    private function determineThrowPosition(DartGame $game, User $user): array
    {
        $lastThrow = $game->getLastThrowByUser($user);

        if (!$lastThrow || $lastThrow->trashed()) {
            $currentState = $this->stateService->determineCurrentTurn($game);
            return [
                'set'   => $currentState['set'],
                'leg'   => $currentState['leg'],
                'turn'  => $currentState['turn'],
                'throw' => 1,
            ];
        }

        $currentState = $this->stateService->determineCurrentTurn($game);

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
                'error' => 'Spiel läuft nicht.',
                'actual_state' => $game->status
            ], 403));
        }

        $activePlayerId = $this->stateService->determineCurrentTurn($game)['player_id'];
        if ($user->id !== $activePlayerId) {
            throw new HttpResponseException(response()->json([
                'error' => 'Nicht der aktive Spieler.',
            ], 403));
        }

        if (!$game->users->contains($user)) {
            throw new HttpResponseException(response()->json([
                'error' => 'Spieler ist nicht Teil dieses Spiels.',
            ], 403));
        }

        if (($data['field'] < 0 || $data['field'] > 20) && !in_array($data['field'], ['25'])) {
            throw new HttpResponseException(response()->json([
                'error' => 'Ungültiges Feld.',
                'field' => $data['field'],
            ], 422));
        }

        if (!in_array($data['ring'], ['O', 'S', 'D', 'T'])) {
            throw new HttpResponseException(response()->json([
                'error' => 'Ungültiger Ring.',
                'ring' => $data['ring'],
            ], 422));
        }
    }
}
