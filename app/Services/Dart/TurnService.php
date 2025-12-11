<?php
namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\User;

class TurnService
{
    public function handleTurnProgress(DartGame $game, User $user): void
    {
        $throwsThisTurn = $game->dartThrows()
            ->where('user_id', $user->id)
            ->where('turn', $this->getCurrentTurn($game, $user))
            ->count();

        if ($throwsThisTurn >= 3) {
            $this->nextPlayer($game, $user);
        }
    }

    public function getCurrentTurn(DartGame $game, User $user): int
    {
        return (int) $game->dartThrows()
            ->where('user_id', $user->id)
            ->max('turn') ?? 1;
    }

    private function nextPlayer(DartGame $game, User $currentUser): void
    {
        // Nur Reihenfolge nach pivot_position
        $players = $game->users()->orderBy('pivot_position')->get();

        $index = $players->search(fn($p) => $p->id === $currentUser->id);
        $next = $players[($index + 1) % $players->count()];

        cache()->put("dart:active:{$game->id}", $next->id);
    }
}
