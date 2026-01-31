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

    public function test_start_game_updates_status(): void
    {
        $this->assertEquals(DartGameStatus::CREATED, $this->game->status);
        $this->stateService->startGame($this->game);
        $this->assertEquals(DartGameStatus::RUNNING, $this->game->fresh()->status);
    }

    public function test_abort_game_updates_status(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);
        $this->stateService->abortGame($this->game);
        $this->assertEquals(DartGameStatus::ABORTED, $this->game->fresh()->status);
    }

    public function test_finish_game_updates_status(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);
        $this->stateService->finishGame($this->game);
        $this->assertEquals(DartGameStatus::DONE, $this->game->fresh()->status);
    }

    public function test_get_full_state_returns_array_with_required_keys(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);
        $state = $this->stateService->getFullState($this->game);

        $this->assertIsArray($state);
        $this->assertArrayHasKey('game_id', $state);
        $this->assertArrayHasKey('status', $state);
        $this->assertArrayHasKey('type', $state);
        $this->assertArrayHasKey('points', $state);
        $this->assertArrayHasKey('set', $state);
        $this->assertArrayHasKey('leg', $state);
        $this->assertArrayHasKey('turn', $state);
        $this->assertArrayHasKey('active_player_id', $state);
        $this->assertArrayHasKey('players', $state);
    }

    public function test_get_full_state_game_id_and_status(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);
        $state = $this->stateService->getFullState($this->game);

        $this->assertEquals($this->game->id, $state['game_id']);
        $this->assertEquals(DartGameStatus::RUNNING, $state['status']);
    }

    public function test_get_full_state_active_player_first_position(): void
    {
        $user2 = User::factory()->create();
        $this->game->users()->attach($user2->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 2,
        ]);

        $this->game->update(['status' => DartGameStatus::RUNNING]);
        $state = $this->stateService->getFullState($this->game);

        $this->assertEquals($this->player->id, $state['active_player_id']);
    }

    public function test_get_full_state_players_collection(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);
        $state = $this->stateService->getFullState($this->game);

        $this->assertCount(1, $state['players']);
    }

    public function test_determine_current_turn_initial_state(): void
    {
        $turn = $this->stateService->determineCurrentTurn($this->game);

        $this->assertEquals(1, $turn['set']);
        $this->assertEquals(1, $turn['leg']);
        $this->assertEquals(1, $turn['turn']);
        $this->assertEquals($this->player->id, $turn['player_id']);
    }

    public function test_determine_current_turn_after_throws(): void
    {
        $this->game->update(['status' => DartGameStatus::RUNNING]);

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

        $turn = $this->stateService->determineCurrentTurn($this->game);
        $this->assertEquals($this->player->id, $turn['player_id']);
        $this->assertEquals(1, $turn['turn']);
    }
}
