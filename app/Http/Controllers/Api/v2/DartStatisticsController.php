<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v2\Controller;
use App\Enums\DartGameType;
use App\Models\User;
use App\Services\Dart\StatisticsService;
use Illuminate\Http\Request;

class DartStatisticsController extends Controller
{
    public function __construct(
        protected StatisticsService $statisticsService,
    ) {
        parent::__construct();
    }

    public function show(Request $request, User $user)
    {
        $gameType = null;
        if ($request->has('game_type')) {
            $gameType = DartGameType::tryFrom($request->query('game_type'));
        }

        $stats = $this->statisticsService->getUserStatistics($user, $gameType);

        return response()->json($stats);
    }

    public function leaderboard(Request $request)
    {
        $metric = $request->query('metric', 'wins');
        $limit = (int) $request->query('limit', 20);

        $gameType = null;
        if ($request->has('game_type')) {
            $gameType = DartGameType::tryFrom($request->query('game_type'));
        }

        $leaderboard = $this->statisticsService->getLeaderboard($metric, $gameType, $limit);

        return response()->json($leaderboard);
    }
}
