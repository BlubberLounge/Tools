<?php

namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\DartThrow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class AbstractGameEngine implements GameEngineInterface
{
    protected GameStateService $stateService;
    protected ScoringService $scoringService;
    protected StatisticsService $statisticsService;
    protected WebhookBroadcaster $broadcaster;

    public function __construct(
        GameStateService $stateService,
        ScoringService $scoringService,
        StatisticsService $statisticsService,
        WebhookBroadcaster $broadcaster
    ) {
        $this->stateService = $stateService;
        $this->scoringService = $scoringService;
        $this->statisticsService = $statisticsService;
        $this->broadcaster = $broadcaster;
    }

    /**
     * Hilfs-Funktion: Broadcasten des aktuellen States an registrierte Webhooks.
     */
    protected function broadcastState(DartGame $game, string $event = 'state.updated'): void
    {
        try {
            $payload = $this->stateService->getFullState($game);
            $this->broadcaster->broadcast($game, $event, $payload);
        } catch (\Throwable $e) {
            Log::warning('Broadcast failed: '.$e->getMessage());
        }
    }

    /**
     * Utility: sichere Transaktion beim Anwenden von Server-Änderungen.
     */
    protected function transaction(callable $fn)
    {
        return DB::transaction($fn);
    }

    /**
     * Undo the last throw for a user. Default implementation soft-deletes the most recent throw.
     */
    public function undoThrow(DartGame $game, User $user): array
    {
        $lastThrow = $game->dartThrowsByUser($user)
            ->orderBy('set', 'DESC')
            ->orderBy('leg', 'DESC')
            ->orderBy('turn', 'DESC')
            ->orderBy('throw', 'DESC')
            ->first();

        if ($lastThrow) {
            $lastThrow->delete();
        }

        return $this->getState($game);
    }
}
