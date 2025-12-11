<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\User;
use App\Models\DartGame;
use App\Services\Dart\GameStateService;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;
use App\Enums\DartGameUserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameStateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GameStateService $stateService;
    protected DartGame $game;
    protected User $player;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateService = app(GameStateService::class);

        $this->game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 301,
            'status' => DartGameStatus::CREATED,
        ]);

        $this->player = User::factory()->create();

        $this->game->users()->attach($this->player->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);
    }

    /**
     * Test: startGame updates status to RUNNING
     */
    public function test_start_game_updates_status(): void
    {
        $this->assertEquals(DartGameStatus::CREATED, $this->game->status);

        $this->stateService->startGame($this->game);

        $this->assertEquals(DartGameStatus::RUNNING, $this->game->fresh()->status);
    }

    /**
     * Test: abortGame updates status to ABORTED
     */
    public function test_abort_game_updates_status(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $this->stateService->abortGame($this->game);

        $this->assertEquals(DartGameStatus::ABORTED, $this->game->fresh()->status);
    }

    /**
     * Test: finishGame updates status to DONE
     */
    public function test_finish_game_updates_status(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $this->stateService->finishGame($this->game);

        $this->assertEquals(DartGameStatus::DONE, $this->game->fresh()->status);
    }

    /**
     * Test: getFullState returns array with required keys
     */
    public function test_get_full_state_returns_array(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertIsArray($state);
        $this->assertArrayHasKey('game_id', $state);
        $this->assertArrayHasKey('status', $state);
        $this->assertArrayHasKey('active_player', $state);
        $this->assertArrayHasKey('players', $state);
        $this->assertArrayHasKey('throws', $state);
    }

    /**
     * Test: getFullState game_id matches
     */
    public function test_get_full_state_game_id(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertEquals($this->game->id, $state['game_id']);
    }

    /**
     * Test: getFullState status is enum
     */
    public function test_get_full_state_status(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertEquals(DartGameStatus::RUNNING, $state['status']);
    }

    /**
     * Test: getFullState active_player is user resource
     */
    public function test_get_full_state_active_player(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertIsArray($state['active_player']);
        $this->assertArrayHasKey('id', $state['active_player']);
        $this->assertArrayHasKey('name', $state['active_player']);
        $this->assertEquals($this->player->id, $state['active_player']['id']);
    }

    /**
     * Test: getFullState players is collection
     */
    public function test_get_full_state_players(): void
    {
        $user2 = User::factory()->create();
        $this->game->users()->attach($user2->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 2,
        ]);

        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertIsArray($state['players']);
        $this->assertCount(2, $state['players']);
    }

    /**
     * Test: getFullState players include user and accepted status
     */
    public function test_get_full_state_players_include_data(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        $playerData = $state['players'][0];

        $this->assertIsArray($playerData);
        $this->assertArrayHasKey('id', $playerData);
        $this->assertArrayHasKey('name', $playerData);
        $this->assertArrayHasKey('accepted', $playerData);
    }

    /**
     * Test: getFullState throws is array
     */
    public function test_get_full_state_throws(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        // Create a throw
        $this->game->dartThrows()->create([
            'user_id' => $this->player->id,
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'S',
            'value' => 20,
        ]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertIsArray($state['throws']);
        $this->assertCount(1, $state['throws']);
    }

    /**
     * Test: getFullState with multiple players
     */
    public function test_get_full_state_multiple_players(): void
    {
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $this->game->users()->attach($user2->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 2,
        ]);
        $this->game->users()->attach($user3->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 3,
        ]);

        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertCount(3, $state['players']);
        $this->assertIsArray($state['active_player']);
    }

    /**
     * Test: getFullState active player is first by position
     */
    public function test_get_full_state_active_player_first_position(): void
    {
        $user2 = User::factory()->create();
        $this->game->users()->attach($user2->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 2,
        ]);

        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        // Active player should be the one with position 1
        $this->assertEquals($this->player->id, $state['active_player']['id']);
    }

    /**
     * Test: getFullState with no throws
     */
    public function test_get_full_state_no_throws(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertIsArray($state['throws']);
        $this->assertEmpty($state['throws']);
    }

    /**
     * Test: getFullState after game finished
     */
    public function test_get_full_state_after_game_finished(): void
    {
        $this->game->update(['status' => DartGameStatus::DONE]);

        $state = $this->stateService->getFullState($this->game);

        $this->assertEquals(DartGameStatus::DONE, $state['status']);
    }
}
