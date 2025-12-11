<?php
namespace App\Services\Dart;

use App\Models\DartGame;

class StatisticsService
{
    public function getLiveStats(DartGame $game): array
    {
        return [
            'highestThrow' => $game->getHighestThrowOfTurn(),
            'lowestThrow' => $game->getLowestThrowOfTurn(),
            'longestStreak' => $game->getLongestStreak(),
            'misthrows' => $game->getMostMisthrows(),
        ];
    }
}
