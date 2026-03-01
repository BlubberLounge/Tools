<?php

namespace App\Services\Dart;

use App\Models\DartGame;
use App\Models\User;
use App\Enums\DartGameStatus;
use App\Models\DartThrow;
use Illuminate\Http\Exceptions\HttpResponseException;

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

            $sum = $game->dartThrowsByUser($user)->sum('value');
            $remaining = $game->points - $sum;
            $checkoutType = $this->getCheckoutType($game);

            if ($this->isBustCondition($remaining, $dartThrow, $checkoutType)) {
                $this->handleBust($game, $user);
                return $this->getState($game, true);
            }

            // Checkout (win) condition
            if ($this->isCheckoutCondition($remaining, $dartThrow, $checkoutType)) {
                $this->stateService->finishGame($game);
            }

            $state = $this->getState($game);
            // $this->broadcastState($game, 'throw.submitted');

            return $state;
        });
    }

    public function getState(DartGame $game, $isBust = false): array
    {
        $state = $this->stateService->getFullState($game);

        $activePlayerId = $state['active_player_id'];
        $checkoutType = $this->getCheckoutType($game);

        $state['players'] = $state['players']->map(function ($player) use ($game, $activePlayerId, $checkoutType, $isBust, $state) {
            $user = $game->users->firstWhere('id', $player['id']);
            $isActivePlayer = $player['id'] === $activePlayerId;


            $currentTurnThrows = $this->getCurrentTurnThrows($game, $user);
            // $player['is_bust'] = ($isBust && $isActivePlayer) ? true : $this->checkIfBust($game, $user, $currentTurnThrows, $state);
            $player['possible_checkouts'] = [];

            if ($isActivePlayer) {
                $remainingPoints = $game->remainingPointsByUser($user);
                $dartsThrown = $currentTurnThrows->count();
                $player['can_checkout'] = $this->canCheckout($remainingPoints, $dartsThrown, $checkoutType);
                $player['checkout_type'] = $checkoutType;
            } else {
                $player['can_checkout'] = false;
                $player['checkout_type'] = $checkoutType;
            }

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
     * Check if current state is a bust.
     */
    protected function isBustCondition(int $remaining, DartThrow $dartThrow, string $checkoutType): bool
    {
        if ($remaining < 0) {
            return true;
        }

        // For double/master checkout: remaining=1 is unreachable (minimum double is 2)
        if (in_array($checkoutType, ['double', 'master']) && $remaining === 1) {
            return true;
        }

        // Hit zero but invalid checkout dart → bust
        if ($remaining === 0 && !$this->isValidCheckout($dartThrow, $checkoutType)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the throw is a valid checkout.
     */
    protected function isCheckoutCondition(int $remaining, DartThrow $dartThrow, string $checkoutType): bool
    {
        if ($remaining !== 0) {
            return false;
        }

        return $this->isValidCheckout($dartThrow, $checkoutType);
    }

    /**
     * Validate if a dart throw meets the checkout type requirements.
     */
    protected function isValidCheckout(DartThrow $dartThrow, string $checkoutType): bool
    {
        $ring = $dartThrow->ring instanceof \App\Enums\DartRingType
            ? $dartThrow->ring->value
            : (string) $dartThrow->ring;

        return match ($checkoutType) {
            'single' => true,
            'double' => $ring === 'D',
            'triple' => $ring === 'T',
            'master' => in_array($ring, ['D', 'T']),
            default => false,
        };
    }

    /**
     * Determine the checkout type for this game.
     */
    protected function getCheckoutType(DartGame $game): string
    {
        // Check settings JSON first (new format)
        $checkout = $game->getSetting('checkout');
        if ($checkout) {
            return match ($checkout) {
                'any', 'single' => 'single',
                'double' => 'double',
                'triple' => 'triple',
                'master' => 'master',
                default => 'single',
            };
        }

        // Legacy fallback: check boolean fields
        if ($game->doubleOut && !$game->singleOut && !$game->trippleOut) {
            return 'double';
        }
        if ($game->trippleOut && !$game->singleOut && !$game->doubleOut) {
            return 'triple';
        }

        return 'single';
    }

    /**
     * Check if player is currently in bust state (for state response).
     *
     * Rules:
     *  - If ANY throw in the current turn is soft-deleted → bust.
     *  - If a new round has started AND previous turn has soft-deleted throws → bust.
     *  - Otherwise fallback to classic bust (score/checkout rules).
     */
    protected function checkIfBust(DartGame $game, User $user, $currentTurnThrows, array $currentTurnState): bool
    {
        if ($currentTurnThrows->isEmpty()) {
            return false;
        }

        $currentTurn = $currentTurnState['turn'];
        $previousTurn = $currentTurn - 1;


        if ($currentTurnThrows->contains(fn ($t) => $t->trashed())) {
            return true;
        }

        if ($previousTurn > 0) {
            $previousTurnThrows = $game->dartThrowsByUser($user)
                ->withTrashed()
                ->where('turn', $previousTurn)
                ->orderBy('throw')
                ->get();

            $previousTurnHasSoftDeleted = $previousTurnThrows->contains(fn ($t) => $t->trashed());

            $newRound = $this->hasNewRoundStarted($game, $currentTurnState);

            if ($newRound && $previousTurnHasSoftDeleted) {
                return true;
            }
        }

        $sum = $game->dartThrowsByUser($user)->sum('value');
        $remaining = $game->points - $sum;
        $checkoutType = $this->getCheckoutType($game);
        $lastThrow = $currentTurnThrows->last();

        return $this->isBustCondition($remaining, $lastThrow, $checkoutType);
    }

    /**
     * A new round has started if:
     * - The last recorded turn in the game < the currentTurnState['turn']
     *
     * That means the engine advanced the global turn counter.
     */
    private function hasNewRoundStarted(DartGame $game, array $currentTurnState): bool
    {
        $lastThrow = $game->dartThrows()
            ->withTrashed()
            ->orderByDesc('set')
            ->orderByDesc('leg')
            ->orderByDesc('turn')
            ->orderByDesc('throw')
            ->first();

        if (!$lastThrow) {
            return false;
        }

        return $currentTurnState['turn'] > $lastThrow->turn;
    }

    /**
     * Check if a player can checkout with remaining darts.
     */
    public function canCheckout(int $remainingPoints, int $dartsThrown, string $checkoutType): bool
    {
        $dartsLeft = 3 - $dartsThrown;

        if ($remainingPoints < 1 || $dartsLeft === 0) {
            return false;
        }

        switch ($checkoutType) {
            case 'single':
                return $this->canCheckoutSingle($remainingPoints, $dartsLeft);
            case 'double':
                return $this->canCheckoutDouble($remainingPoints, $dartsLeft);
            case 'triple':
                return $this->canCheckoutTriple($remainingPoints, $dartsLeft);
            default:
                return false;
        }
    }

    /**
     * Check if single out is possible.
     */
    protected function canCheckoutSingle(int $points, int $darts): bool
    {
        // Maximum per dart: T20 = 60
        return $points <= ($darts * 60);
    }

    /**
     * Check if double out is possible.
     */
    protected function canCheckoutDouble(int $points, int $darts): bool
    {
        if ($points < 2 || $points > 170) {
            return false;
        }

        if ($darts === 1) {
            // Doubles: D1-D20 (2,4,...,40) or D25 (bull = 50)
            return $points === 50 || ($points % 2 === 0 && $points <= 40);
        }

        if ($darts === 2) {
            // Maximum: T20 (60) + D20 (40) = 100, or T20 (60) + Bull (50) = 110
            return $points <= 110;
        }

        // 3 darts: Maximum checkout is 170 (T20, T20, Bull)
        return $points <= 170;
    }

    /**
     * Check if triple out is possible.
     */
    protected function canCheckoutTriple(int $points, int $darts): bool
    {
        if ($darts === 1) {
            // Only triples: T1 to T20
            return $points % 3 === 0 && $points >= 3 && $points <= 60;
        }

        if ($darts === 2) {
            // Maximum: T20 (60) + T20 (60) = 120
            return $points <= 120;
        }

        // 3 darts: Maximum is T20, T20, T20 = 180
        return $points <= 180;
    }

    /**
     * Get current turn throws for a user.
     */
    protected function getCurrentTurnThrows(DartGame $game, User $user)
    {
        $currentTurn = $this->stateService->determineCurrentTurn($game, $user);

        return $game->dartThrowsByUser($user)
            ->where('set', $currentTurn['set'])
            ->where('leg', $currentTurn['leg'])
            ->where('turn', $currentTurn['turn'])
            ->orderBy('throw')
            ->get();
    }

    /**
     * Simple bust handling: lösche aktuelle turn-throws
     */
    protected function handleBust(DartGame $game, User $user): void
    {
        $turn = $this->stateService->determineCurrentTurn($game, $user);
        $game->dartThrows()
            ->where('user_id', $user->id)
            ->where('turn', $turn)
            ->delete();
    }

    private function validateThrow(DartGame $game, User $user, array $data): void
    {
        $this->validateCommonThrow($game, $user, $data);
    }

    /**
     * Determines set, leg, turn, and throw number for the next incoming throw.
     * Turn is based on the current game state, not just the player's last throw.
     */
    private function determineThrowPosition(DartGame $game, User $user): array
    {
        $lastThrow = $game->getLastThrowByUser($user);

        if (!$lastThrow || $lastThrow->trashed()) {
            // Game just started - use initial state from game state service
            $currentState = $this->stateService->determineCurrentTurn($game);
            return [
                'set'   => $currentState['set'],
                'leg'   => $currentState['leg'],
                'turn'  => $currentState['turn'],
                'throw' => 1,
            ];
        }

        // Get the current game state to determine the turn
        $currentState = $this->stateService->determineCurrentTurn($game);

        $next = [
            'set'   => $currentState['set'],
            'leg'   => $currentState['leg'],
            'turn'  => $currentState['turn'],
            'throw' => $lastThrow->throw,
        ];
        // dump($lastThrow, $currentState);
        if ($next['throw'] < 3) {
            $next['throw']++;
        } else {
            $next['throw'] = 1;
            // $next['turn']++;
        }

        // dd($next);
        return $next;
    }
}
