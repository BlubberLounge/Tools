<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\User;
use App\Models\DartGame;
use App\Services\Dart\TurnService;
use App\Enums\DartGameType;
use App\Enums\DartGameUserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class TurnServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TurnService $turnService;
    protected DartGame $game;
    protected User $player1;
    protected User $player2;
    protected User $player3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->turnService = app(TurnService::class);

        // Create a game with 3 players
        $this->game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 301,
        ]);

        $this->player1 = User::factory()->create(['name' => 'Player 1']);
        $this->player2 = User::factory()->create(['name' => 'Player 2']);
        $this->player3 = User::factory()->create(['name' => 'Player 3']);

        // Attach players with positions
        $this->game->users()->attach($this->player1->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);
        $this->game->users()->attach($this->player2->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 2,
        ]);
        $this->game->users()->attach($this->player3->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 3,
        ]);
    }

    /**
     * Test: getCurrentTurn returns 1 when no throws exist
     */
    public function test_get_current_turn_no_throws(): void
    {
        $turn = $this->turnService->getCurrentTurn($this->game, $this->player1);

        $this->assertEquals(1, $turn);
    }

    /**
     * Test: getCurrentTurn returns correct turn number
     */
    public function test_get_current_turn_with_throws(): void
    {
        // Create throws for player 1, turn 1
        $this->game->dartThrows()->createMany([
            [
                'user_id' => $this->player1->id,
                'set' => 1,
                'leg' => 1,
                'turn' => 1,
                'throw' => 1,
                'field' => 20,
                'ring' => 'S',
                'value' => 20,
            ],
            [
                'user_id' => $this->player1->id,
                'set' => 1,
                'leg' => 1,
                'turn' => 1,
                'throw' => 2,
                'field' => 20,
                'ring' => 'D',
                'value' => 40,
            ],
        ]);

        $turn = $this->turnService->getCurrentTurn($this->game, $this->player1);

        $this->assertEquals(1, $turn);
    }

    /**
     * Test: getCurrentTurn increments to next turn
     */
    public function test_get_current_turn_increments(): void
    {
        // Create throws for turn 1, 2, 3
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 2, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 3, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $turn = $this->turnService->getCurrentTurn($this->game, $this->player1);

        $this->assertEquals(3, $turn);
    }

    /**
     * Test: handleTurnProgress does nothing on first throw
     */
    public function test_handle_turn_progress_first_throw(): void
    {
        $this->game->dartThrows()->create([
            'user_id' => $this->player1->id,
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
            'value' => 20,
        ]);

        // Cache should not be set yet
        $this->assertNull(Cache::get("dart:active:{$this->game->id}"));

        $this->turnService->handleTurnProgress($this->game, $this->player1);

        // Still null - only 1 throw
        $this->assertNull(Cache::get("dart:active:{$this->game->id}"));
    }

    /**
     * Test: handleTurnProgress does nothing on second throw
     */
    public function test_handle_turn_progress_second_throw(): void
    {
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'D', 'value' => 40],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player1);

        // Cache should not be set yet - only 2 throws
        $this->assertNull(Cache::get("dart:active:{$this->game->id}"));
    }

    /**
     * Test: handleTurnProgress switches to next player on third throw
     */
    public function test_handle_turn_progress_switches_to_next_player(): void
    {
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player1);

        // Should set active player to player 2
        $this->assertEquals($this->player2->id, Cache::get("dart:active:{$this->game->id}"));
    }

    /**
     * Test: handleTurnProgress cycles through all players
     */
    public function test_handle_turn_progress_cycles_through_players(): void
    {
        // Player 1 completes 3 throws
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player1);
        $this->assertEquals($this->player2->id, Cache::get("dart:active:{$this->game->id}"));

        // Player 2 completes 3 throws
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player2->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player2->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player2->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player2);
        $this->assertEquals($this->player3->id, Cache::get("dart:active:{$this->game->id}"));

        // Player 3 completes 3 throws
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player3->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player3->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player3->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player3);
        // Should cycle back to player 1
        $this->assertEquals($this->player1->id, Cache::get("dart:active:{$this->game->id}"));
    }

    /**
     * Test: handleTurnProgress with two players
     */
    public function test_handle_turn_progress_two_players(): void
    {
        // Remove player 3
        $this->game->users()->detach($this->player3->id);

        // Player 1 completes 3 throws
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player1);
        $this->assertEquals($this->player2->id, Cache::get("dart:active:{$this->game->id}"));

        // Player 2 completes 3 throws
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player2->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player2->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player2->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player2);
        // Should cycle back to player 1
        $this->assertEquals($this->player1->id, Cache::get("dart:active:{$this->game->id}"));
    }

    /**
     * Test: handleTurnProgress with single player
     */
    public function test_handle_turn_progress_single_player(): void
    {
        // Remove player 2 and 3, keep only player 1
        $this->game->users()->detach($this->player2->id);
        $this->game->users()->detach($this->player3->id);

        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player1);

        // Should stay on player 1 (cycles to themselves)
        $this->assertEquals($this->player1->id, Cache::get("dart:active:{$this->game->id}"));
    }

    /**
     * Test: getCurrentTurn returns different turns per player
     */
    public function test_get_current_turn_per_player(): void
    {
        // Player 1: turn 5
        for ($i = 1; $i <= 5; $i++) {
            $this->game->dartThrows()->create([
                'user_id' => $this->player1->id,
                'turn' => $i,
                'throw' => 1,
                'field' => 20,
                'ring' => 'S',
                'value' => 20,
            ]);
        }

        // Player 2: turn 3
        for ($i = 1; $i <= 3; $i++) {
            $this->game->dartThrows()->create([
                'user_id' => $this->player2->id,
                'turn' => $i,
                'throw' => 1,
                'field' => 20,
                'ring' => 'S',
                'value' => 20,
            ]);
        }

        $this->assertEquals(5, $this->turnService->getCurrentTurn($this->game, $this->player1));
        $this->assertEquals(3, $this->turnService->getCurrentTurn($this->game, $this->player2));
        $this->assertEquals(1, $this->turnService->getCurrentTurn($this->game, $this->player3));
    }

    /**
     * Test: handleTurnProgress respects position order
     */
    public function test_handle_turn_progress_respects_position_order(): void
    {
        // Verify positions are 1, 2, 3
        $players = $this->game->users()->orderBy('pivot_position')->pluck('id')->toArray();

        $this->assertEquals([
            $this->player1->id,
            $this->player2->id,
            $this->player3->id,
        ], $players);

        // Complete player 1's turn
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player1->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $this->turnService->handleTurnProgress($this->game, $this->player1);

        // Next should be player 2
        $activeId = Cache::get("dart:active:{$this->game->id}");
        $this->assertEquals($this->player2->id, $activeId);
    }
}
