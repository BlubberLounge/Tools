<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\DartGame;
use App\Models\User;
use App\Services\Dart\X01Engine;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class X01EngineFullTest extends TestCase
{
    use RefreshDatabase;

    private X01Engine $engine;
    private DartGame $game;
    private User $player1;
    private User $player2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(X01Engine::class);

        $this->player1 = User::factory()->create(['name' => 'Player 1']);
        $this->player2 = User::factory()->create(['name' => 'Player 2']);

        $this->game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 501,
            'status' => DartGameStatus::CREATED,
        ]);

        $this->game->users()->attach([
            $this->player1->id => ['position' => 0, 'status' => 'accepted'],
            $this->player2->id => ['position' => 1, 'status' => 'accepted'],
        ]);
    }

    public function test_start_game_returns_state_and_changes_status(): void
    {
        $state = $this->engine->start($this->game);

        $this->assertIsArray($state);
        $this->assertArrayHasKey('game_id', $state);
        $this->assertEquals(DartGameStatus::RUNNING, $this->game->fresh()->status);
    }

    public function test_submit_throw_creates_dart_throw(): void
    {
        $this->engine->start($this->game);
        $this->engine->submitThrow($this->game, $this->player1, ['field' => 20, 'ring' => 'T']);

        $this->assertDatabaseHas('dart_throws', [
            'user_id' => $this->player1->id,
            'value' => 60,
        ]);
    }

    public function test_submit_throw_returns_state(): void
    {
        $this->engine->start($this->game);
        $state = $this->engine->submitThrow($this->game, $this->player1, ['field' => 20, 'ring' => 'S']);

        $this->assertIsArray($state);
        $this->assertArrayHasKey('game_id', $state);
        $this->assertArrayHasKey('players', $state);
    }

    public function test_get_state_returns_full_state(): void
    {
        $this->engine->start($this->game);
        $state = $this->engine->getState($this->game);

        $this->assertIsArray($state);
        $this->assertArrayHasKey('game_id', $state);
        $this->assertArrayHasKey('status', $state);
        $this->assertArrayHasKey('active_player_id', $state);
        $this->assertArrayHasKey('players', $state);
    }

    public function test_is_finished_for_running_and_done_game(): void
    {
        $this->engine->start($this->game);
        $this->assertFalse($this->engine->isFinished($this->game));

        $this->game->update(['status' => DartGameStatus::DONE]);
        $this->assertTrue($this->engine->isFinished($this->game));
    }

    public function test_finish_game_changes_status(): void
    {
        $this->engine->start($this->game);
        $this->engine->finish($this->game);

        $this->assertEquals(DartGameStatus::DONE, $this->game->fresh()->status);
    }

    public function test_submit_throw_with_invalid_field_throws_exception(): void
    {
        $this->engine->start($this->game);
        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);
        $this->engine->submitThrow($this->game, $this->player1, ['field' => 99, 'ring' => 'S']);
    }

    public function test_submit_throw_with_invalid_ring_throws_exception(): void
    {
        $this->engine->start($this->game);
        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);
        $this->engine->submitThrow($this->game, $this->player1, ['field' => 20, 'ring' => 'INVALID']);
    }

    public function test_submit_throw_when_not_running_throws_exception(): void
    {
        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);
        $this->engine->submitThrow($this->game, $this->player1, ['field' => 20, 'ring' => 'S']);
    }

    public function test_submit_throw_by_non_active_player_throws_exception(): void
    {
        $this->engine->start($this->game);
        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);
        $this->engine->submitThrow($this->game, $this->player2, ['field' => 20, 'ring' => 'S']);
    }

    public function test_submit_throw_by_player_not_in_game_throws_exception(): void
    {
        $this->engine->start($this->game);
        $outsider = User::factory()->create();

        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);
        $this->engine->submitThrow($this->game, $outsider, ['field' => 20, 'ring' => 'S']);
    }

    public function test_submit_throw_increments_throw_number(): void
    {
        $this->engine->start($this->game);
        $this->engine->submitThrow($this->game, $this->player1, ['field' => 20, 'ring' => 'S']);
        $this->engine->submitThrow($this->game, $this->player1, ['field' => 19, 'ring' => 'S']);

        $throws = $this->game->dartThrowsByUser($this->player1)->get();
        $this->assertEquals(1, $throws[0]->throw);
        $this->assertEquals(2, $throws[1]->throw);
    }

    public function test_can_checkout_calculations(): void
    {
        $this->assertTrue($this->engine->canCheckout(20, 0, 'single'));
        $this->assertTrue($this->engine->canCheckout(40, 0, 'double'));
        $this->assertTrue($this->engine->canCheckout(60, 0, 'triple'));
    }
}
