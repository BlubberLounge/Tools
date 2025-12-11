<?php

namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\User;
use App\Enums\DartGameStatus;
use App\Models\DartThrow;

class X01Engine extends AbstractGameEngine
{
    public function start(DartGame $game): array
    {
        return $this->transaction(function () use ($game) {
            $this->stateService->startGame($game);
            // setze initiale Werte, z.B. turn=1, position aus pivot_position
            // cache()->put("dart:turn:{$game->id}", 1);
            // $this->broadcastState($game, 'game.started');

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

            $sum = $game->dartThrowsByUser($user)->sum('value');
            $remaining = $game->points - $sum;

            if ($remaining < 0) {
                // Bust: Lösche die letzten drei Würfe dieses Zuges (vereinfachte Variante)
                $this->handleBust($game, $user);
            } elseif ($remaining === 0) {
                if ($game->doubleOut && in_array($payload['ring'], ['D', 'BULL'])) {
                    $this->stateService->finishGame($game);
                } else {
                    $this->handleBust($game, $user);
                }
            }

            // Turn-Progress
            $this->turnService->handleTurnProgress($game, $user);

            $state = $this->getState($game);
            // $this->broadcastState($game, 'throw.submitted');

            return $state;
        });
    }

    public function getState(DartGame $game): array
    {
        return $this->stateService->getFullState($game);
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
     * Simple bust handling: lösche aktuelle turn-throws
     */
    protected function handleBust(DartGame $game, User $user): void
    {
        $turn = $this->turnService->getCurrentTurn($game, $user);
        $game->dartThrows()
            ->where('user_id', $user->id)
            ->where('turn', $turn)
            ->delete();
    }

    private function validateThrow(DartGame $game, User $user, array $data): void
    {
        abort_if($game->status !== DartGameStatus::RUNNING, 403, 'Spiel läuft nicht.');

        $activePlayerId = $this->stateService->determineCurrentTurn($game)['player_id'];
        abort_if($user->id !== $activePlayerId, 403, 'Nicht der aktive Spieler.');

        abort_if(!$game->users->contains($user), 403);

        abort_if(($data['field'] < 0 || $data['field'] > 20) && !in_array($data['field'], ['25', '50']), 422);

        abort_if(!in_array($data['ring'], ['S', 'D', 'T', 'O', 'BULL', 'SBULL']), 422);
    }



    /**
     * Determines set, leg, turn, and throw number for the next incoming throw.
     */
    private function determineThrowPosition(DartGame $game, User $user): array
    {
        $lastThrow = $game->getLastThrowByUser($user);

        if (!$lastThrow) {
            return [
                'set'   => 1,
                'leg'   => 1,
                'turn'  => 1,
                'throw' => 1,
            ];
        }

        $next = [
            'set'   => $lastThrow->set,
            'leg'   => $lastThrow->leg,
            'turn'  => $lastThrow->turn,
            'throw' => $lastThrow->throw,
        ];

        if ($next['throw'] < 3) {
            $next['throw']++;
        } else {
            $next['throw'] = 1;
            $next['turn']++;
        }

        return $next;
    }
}
