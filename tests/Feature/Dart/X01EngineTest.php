<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\User;
use App\Models\DartGame;
use App\Services\Dart\DartGameEngineService;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;
use App\Enums\DartGameUserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class X01EngineTest extends TestCase
{
    use RefreshDatabase;

    protected DartGameEngineService $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(DartGameEngineService::class);
    }

    public function test_x01_game_starts_correctly(): void
    {
        $user = User::factory()->create();

        $game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 301,
        ]);

        $game->users()->attach($user->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);

        $state = $this->engine->startGame($game);

        $this->assertEquals(
            DartGameStatus::RUNNING,
            $game->fresh()->status
        );

        $this->assertArrayHasKey('game_id', $state);
    }

    public function test_x01_simple_scoring(): void
    {
        $user = User::factory()->create();

        $game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 301,
        ]);

        $game->users()->attach($user->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);

        $this->engine->startGame($game);

        $this->engine->submitThrow($game, $user, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'T',
        ]);

        $this->assertDatabaseHas('dart_throws', [
            'user_id' => $user->id,
            'value' => 60,
        ]);

        $this->assertEquals(241, $game->remainingPointsByUser($user));
    }

    public function test_x01_bust_resets_turn(): void
    {
        $user = User::factory()->create();

        $game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 40,
        ]);

        $game->users()->attach($user->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);

        $this->engine->startGame($game);

        // 60 Punkte → Bust
        $this->engine->submitThrow($game, $user, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'T',
        ]);

        $this->assertEquals(40, $game->remainingPointsByUser($user));

        $this->assertDatabaseMissing('dart_throws', [
            'user_id' => $user->id,
            'value' => 60,
        ]);
    }

    public function test_x01_double_out_win(): void
    {
        $user = User::factory()->create();

        $game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 40,
            'doubleOut' => true,
        ]);

        $game->users()->attach($user->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);

        $this->engine->startGame($game);

        // Double 20 = 40 => Win
        $this->engine->submitThrow($game, $user, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'D',
        ]);

        $this->assertEquals(
            DartGameStatus::DONE,
            $game->fresh()->status
        );
    }
}
