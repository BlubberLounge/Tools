<?php

namespace App\Services\Dart;

use App\Enums\DartGameStatus;
use App\Models\DartGame;
use App\Models\DartThrow;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
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
     * Convert polar coordinates to cartesian if present in payload.
     */
    protected function normalizeCoordinates(array &$payload): void
    {
        if (isset($payload['r'], $payload['theta']) && !isset($payload['x'])) {
            $cartesian = ScoringService::polarToCartesian((float) $payload['r'], (float) $payload['theta']);
            $payload['x'] = $cartesian['x'];
            $payload['y'] = $cartesian['y'];
        }
    }

    /**
     * Common throw validation shared by all engines.
     * Validates game state, active player, user participation, and field+ring legality.
     */
    protected function validateCommonThrow(DartGame $game, User $user, array $data): void
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

        $activePlayerId = $this->stateService->determineCurrentTurn($game)['player_id'];
        if ($user->id !== $activePlayerId) {
            throw new HttpResponseException(response()->json([
                'error' => 'not_active_player',
                'message' => 'It is not this player\'s turn.',
                'active_player_id' => $activePlayerId,
            ], 403));
        }

        $this->validateFieldRing($data['field'] ?? null, $data['ring'] ?? null);
    }

    /**
     * Validate field+ring combination against real dartboard rules.
     */
    protected function validateFieldRing($field, $ring): void
    {
        $field = (int) $field;

        // Valid rings
        if (!in_array($ring, ['O', 'S', 'D', 'T'])) {
            throw new HttpResponseException(response()->json([
                'error' => 'invalid_ring',
                'message' => "Ring must be O, S, D, or T. Got: {$ring}",
                'ring' => $ring,
            ], 422));
        }

        // Valid fields: 0-20, 25
        if ($field < 0 || ($field > 20 && $field !== 25)) {
            throw new HttpResponseException(response()->json([
                'error' => 'invalid_field',
                'message' => "Field must be 0-20 or 25. Got: {$field}",
                'field' => $field,
            ], 422));
        }

        // Bull (field 25) has no triple ring
        if ($field === 25 && $ring === 'T') {
            throw new HttpResponseException(response()->json([
                'error' => 'invalid_field_ring',
                'message' => 'Triple is not possible on bullseye (field 25).',
                'field' => $field,
                'ring' => $ring,
            ], 422));
        }
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
