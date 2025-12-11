<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\User;
use App\Models\DartGame;
use App\Models\DartThrow;
use App\Services\Dart\DartGameEngineService;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;
use App\Enums\DartGameUserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class X01EngineSubmitThrowTest extends TestCase
{
    use RefreshDatabase;

    protected DartGameEngineService $engine;
    protected User $player;
    protected DartGame $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(DartGameEngineService::class);
        $this->player = User::factory()->create();
    }

    private function createAndStartGame($points = 301, $doubleOut = false): DartGame
    {
        $this->game = DartGame::factory()->create([
            'status' => DartGameStatus::CREATED,
            'type' => DartGameType::X01,
            'points' => $points,
            'doubleOut' => $doubleOut,
        ]);

        $this->game->users()->attach($this->player->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);

        $this->engine->startGame($this->game);

        return $this->game;
    }

    /**
     * Test: Single throw scoring
     */
    public function test_submit_throw_single_scoring(): void
    {
        $this->createAndStartGame(301);

        $state = $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
        ]);

        $this->assertDatabaseHas('dart_throws', [
            'user_id' => $this->player->id,
            'value' => 20,
            'field' => 20,
            'ring' => 'S',
        ]);

        $this->assertEquals(281, $this->game->fresh()->remainingPointsByUser($this->player));
        $this->assertArrayHasKey('players', $state);
    }

    /**
     * Test: Double throw scoring
     */
    public function test_submit_throw_double_scoring(): void
    {
        $this->createAndStartGame(301);

        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'D',
        ]);

        $this->assertDatabaseHas('dart_throws', [
            'user_id' => $this->player->id,
            'value' => 40,
        ]);

        $this->assertEquals(261, $this->game->fresh()->remainingPointsByUser($this->player));
    }

    /**
     * Test: Triple throw scoring
     */
    public function test_submit_throw_triple_scoring(): void
    {
        $this->createAndStartGame(301);

        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'T',
        ]);

        $this->assertDatabaseHas('dart_throws', [
            'user_id' => $this->player->id,
            'value' => 60,
        ]);

        $this->assertEquals(241, $this->game->fresh()->remainingPointsByUser($this->player));
    }

    /**
     * Test: Outer bull (25 points)
     */
    public function test_submit_throw_outer_bull(): void
    {
        $this->createAndStartGame(301);

        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 25,
            'ring' => 'S',
        ]);

        $this->assertDatabaseHas('dart_throws', [
            'user_id' => $this->player->id,
            'value' => 25,
        ]);

        $this->assertEquals(276, $this->game->fresh()->remainingPointsByUser($this->player));
    }

    /**
     * Test: Inner bull / bullseye (50 points)
     */
    public function test_submit_throw_bull(): void
    {
        $this->createAndStartGame(301);

        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 25,
            'ring' => 'D',
        ]);

        $this->assertDatabaseHas('dart_throws', [
            'user_id' => $this->player->id,
            'value' => 50,
        ]);

        $this->assertEquals(251, $this->game->fresh()->remainingPointsByUser($this->player));
    }

    /**
     * Test: Multiple throws in a turn
     */
    public function test_submit_throw_multiple_throws_in_turn(): void
    {
        $this->createAndStartGame(301);

        // First throw
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
        ]);

        // Second throw
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 2,
            'field' => 20,
            'ring' => 'D',
        ]);

        // Third throw
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 3,
            'field' => 5,
            'ring' => 'T',
        ]);

        $totalScored = 20 + 40 + 15; // S20 + D20 + T5
        $this->assertEquals(301 - $totalScored, $this->game->fresh()->remainingPointsByUser($this->player));
    }

    /**
     * Test: Bust - score goes below zero
     */
    public function test_submit_throw_bust_below_zero(): void
    {
        $this->createAndStartGame(40, false);

        // Score 60 which is more than 40 → Bust
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'T',
        ]);

        // After bust, throws should be deleted, remaining should be back to 40
        $this->assertEquals(40, $this->game->fresh()->remainingPointsByUser($this->player));

        $this->assertDatabaseMissing('dart_throws', [
            'user_id' => $this->player->id,
            'value' => 60,
        ]);
    }

    /**
     * Test: Bust - invalid double out (remaining is 0 but last throw is not double)
     */
    public function test_submit_throw_bust_invalid_double_out(): void
    {
        $this->createAndStartGame(40, true);

        // Score exactly 40 with single (not double) → Invalid checkout, should bust
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
        ]);

        // Remaining is 20, not busted yet
        $this->assertEquals(20, $this->game->fresh()->remainingPointsByUser($this->player));

        // Now throw to exactly 0, but not with a double
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 2,
            'field' => 20,
            'ring' => 'S',
        ]);

        // Should bust because doubleOut is required
        $this->assertEquals(20, $this->game->fresh()->remainingPointsByUser($this->player));
    }

    /**
     * Test: Valid double out - game finished
     */
    public function test_submit_throw_valid_double_out_win(): void
    {
        $this->createAndStartGame(40, true);

        // Score exactly 40 with double → Win
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'D',
        ]);

        $this->assertEquals(DartGameStatus::DONE, $this->game->fresh()->status);
        $this->assertEquals(0, $this->game->fresh()->remainingPointsByUser($this->player));
    }

    /**
     * Test: Valid double out with bull
     */
    public function test_submit_throw_valid_bull_out_win(): void
    {
        $this->createAndStartGame(50, true);

        // Score exactly 50 with inner bull → Win
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 50,
            'ring' => 'BULL',
        ]);

        $this->assertEquals(DartGameStatus::DONE, $this->game->fresh()->status);
    }

    /**
     * Test: Single out (no double required) - game finished
     */
    public function test_submit_throw_single_out_win(): void
    {
        $this->createAndStartGame(40, false);

        // Score exactly 40 with single → Win (doubleOut is false)
        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
        ]);

        $this->assertEquals(DartGameStatus::DONE, $this->game->fresh()->status);
    }

    /**
     * Test: Game not running - should throw error
     */
    public function test_submit_throw_game_not_running(): void
    {
        $this->game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 301,
            'status' => DartGameStatus::CREATED,
        ]);

        $this->game->users()->attach($this->player->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);

        $this->expectException(\Exception::class);

        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
        ]);
    }

    /**
     * Test: User not in game - should throw error
     */
    public function test_submit_throw_user_not_in_game(): void
    {
        $otherPlayer = User::factory()->create();
        $this->createAndStartGame(301);

        $this->expectException(\Exception::class);

        $this->engine->submitThrow($this->game, $otherPlayer, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
        ]);
    }

    /**
     * Test: Invalid field (out of range) - should throw error
     */
    public function test_submit_throw_invalid_field(): void
    {
        $this->createAndStartGame(301);

        $this->expectException(\Exception::class);

        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 21, // Invalid: only 0-20, 25, 50 allowed
            'ring' => 'S',
        ]);
    }

    /**
     * Test: Invalid ring - should throw error
     */
    public function test_submit_throw_invalid_ring(): void
    {
        $this->createAndStartGame(301);

        $this->expectException(\Exception::class);

        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'X', // Invalid ring
        ]);
    }

    /**
     * Test: State is returned correctly
     */
    public function test_submit_throw_returns_game_state(): void
    {
        $this->createAndStartGame(301);

        $state = $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
        ]);

        $this->assertIsArray($state);
        $this->assertArrayHasKey('game_id', $state);
        $this->assertArrayHasKey('status', $state);
        $this->assertArrayHasKey('active_player', $state);
        $this->assertArrayHasKey('players', $state);
        $this->assertArrayHasKey('throws', $state);
    }

    /**
     * Test: Miss (0 points) - valid throw
     */
    public function test_submit_throw_miss(): void
    {
        $this->createAndStartGame(301);

        $this->engine->submitThrow($this->game, $this->player, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 0,
            'ring' => 'O',
        ]);

        $this->assertDatabaseHas('dart_throws', [
            'user_id' => $this->player->id,
            'value' => 0,
        ]);

        $this->assertEquals(301, $this->game->fresh()->remainingPointsByUser($this->player));
    }
}
