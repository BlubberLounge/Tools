<?php
namespace App\Services\Dart;

use App\Enums\DartGameStatus;
use App\Enums\DartGameType;
use App\Models\DartGame;
use App\Models\DartThrow;
use App\Models\User;

class StatisticsService
{
    public function getLiveStats(DartGame $game): array
    {
        return [];
    }

    /**
     * Get comprehensive statistics for a user, optionally filtered by game type.
     */
    public function getUserStatistics(User $user, ?DartGameType $gameType = null): array
    {
        $gameIds = $this->getCompletedGameIds($user, $gameType);

        if ($gameIds->isEmpty()) {
            return $this->emptyStats();
        }

        $throws = DartThrow::whereIn('dart_game_id', $gameIds)
            ->where('user_id', $user->id)
            ->get();

        if ($throws->isEmpty()) {
            return $this->emptyStats();
        }

        $gamesPlayed = $gameIds->count();
        $wins = $this->countWins($user, $gameIds);
        $totalDarts = $throws->count();
        $totalPoints = $throws->sum('value');

        return [
            'games_played' => $gamesPlayed,
            'wins' => $wins,
            'win_rate' => $gamesPlayed > 0 ? round($wins / $gamesPlayed * 100, 1) : 0,
            'total_darts' => $totalDarts,
            'average_per_dart' => $totalDarts > 0 ? round($totalPoints / $totalDarts, 2) : 0,
            'average_per_turn' => $this->calculateTurnAverage($throws),
            'highest_turn' => $this->calculateHighestTurn($throws),
            'one_eighties' => $this->count180s($throws),
            'ton_plus' => $this->countTonPlus($throws),
            'checkout_percentage' => $this->calculateCheckoutPercentage($user, $gameIds),
            'favourite_field' => $this->getFavouriteField($throws),
            'miss_rate' => $totalDarts > 0 ? round($throws->where('ring', 'O')->count() / $totalDarts * 100, 1) : 0,
        ];
    }

    /**
     * Get leaderboard ranked by a specific metric.
     */
    public function getLeaderboard(string $metric, ?DartGameType $gameType = null, int $limit = 20): array
    {
        $users = User::whereHas('dartGames', function ($q) {
            $q->whereIn('status', [
                DartGameStatus::DONE,
                DartGameStatus::PLAYER_WON,
                DartGameStatus::FINISHED,
            ]);
        })->get();

        $entries = [];
        foreach ($users as $user) {
            $stats = $this->getUserStatistics($user, $gameType);
            if ($stats['games_played'] === 0) continue;

            $entries[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'value' => $stats[$metric] ?? 0,
                'games_played' => $stats['games_played'],
            ];
        }

        usort($entries, fn($a, $b) => $b['value'] <=> $a['value']);

        $ranked = [];
        foreach (array_slice($entries, 0, $limit) as $i => $entry) {
            $entry['rank'] = $i + 1;
            $ranked[] = $entry;
        }

        return $ranked;
    }

    private function getCompletedGameIds(User $user, ?DartGameType $gameType = null)
    {
        $query = $user->dartGames()
            ->whereIn('dart_games.status', [
                DartGameStatus::DONE,
                DartGameStatus::PLAYER_WON,
                DartGameStatus::FINISHED,
            ]);

        if ($gameType) {
            $query->where('dart_games.type', $gameType);
        }

        return $query->pluck('dart_games.id');
    }

    private function countWins(User $user, $gameIds): int
    {
        return $user->dartGames()
            ->whereIn('dart_games.id', $gameIds)
            ->wherePivot('place', 1)
            ->count();
    }

    private function calculateTurnAverage($throws): float
    {
        $turnGroups = $throws->groupBy(fn($t) => $t->dart_game_id . '-' . $t->turn);

        if ($turnGroups->isEmpty()) return 0;

        $turnSums = $turnGroups->map(fn($group) => $group->sum('value'));

        return round($turnSums->average(), 2);
    }

    private function calculateHighestTurn($throws): int
    {
        $turnGroups = $throws->groupBy(fn($t) => $t->dart_game_id . '-' . $t->turn);

        if ($turnGroups->isEmpty()) return 0;

        return (int) $turnGroups->map(fn($group) => $group->sum('value'))->max();
    }

    private function count180s($throws): int
    {
        $turnGroups = $throws->groupBy(fn($t) => $t->dart_game_id . '-' . $t->turn);

        return $turnGroups->filter(function ($group) {
            return $group->count() === 3 && $group->sum('value') === 180;
        })->count();
    }

    private function countTonPlus($throws): int
    {
        $turnGroups = $throws->groupBy(fn($t) => $t->dart_game_id . '-' . $t->turn);

        return $turnGroups->filter(function ($group) {
            return $group->count() === 3 && $group->sum('value') >= 100;
        })->count();
    }

    private function calculateCheckoutPercentage(User $user, $gameIds): float
    {
        $won = $user->dartGames()
            ->whereIn('dart_games.id', $gameIds)
            ->wherePivot('place', 1)
            ->count();

        $total = $gameIds->count();

        return $total > 0 ? round($won / $total * 100, 1) : 0;
    }

    private function getFavouriteField($throws): ?int
    {
        $fieldCounts = $throws->where('ring', '!=', 'O')
            ->groupBy('field')
            ->map(fn($group) => $group->count());

        if ($fieldCounts->isEmpty()) return null;

        return (int) $fieldCounts->sortDesc()->keys()->first();
    }

    private function emptyStats(): array
    {
        return [
            'games_played' => 0,
            'wins' => 0,
            'win_rate' => 0,
            'total_darts' => 0,
            'average_per_dart' => 0,
            'average_per_turn' => 0,
            'highest_turn' => 0,
            'one_eighties' => 0,
            'ton_plus' => 0,
            'checkout_percentage' => 0,
            'favourite_field' => null,
            'miss_rate' => 0,
        ];
    }
}
