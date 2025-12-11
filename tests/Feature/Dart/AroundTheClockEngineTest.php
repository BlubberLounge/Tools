<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\User;
use App\Models\DartGame;
use App\Services\Dart\DartGameEngineService;
use App\Enums\DartGameType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AroundTheClockEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_atc_progression(): void
    {
        $user = User::factory()->create();

        $game = DartGame::factory()->create([
            'type' => DartGameType::aroundTheClock,
            'start' => 1,
            'end' => 3,
        ]);

        $game->users()->attach($user->id);

        $engine = app(DartGameEngineService::class);
        $engine->startGame($game);

        $engine->submitThrow($game, $user, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 1,
            'ring' => 'S',
        ]);

        $state = $engine->getState($game);

        $this->assertEquals(2, $state['atc_players'][0]['position']);
    }

    public function test_atc_win(): void
    {
        $user = User::factory()->create();

        $game = DartGame::factory()->create([
            'type' => DartGameType::aroundTheClock,
            'start' => 1,
            'end' => 1,
        ]);

        $game->users()->attach($user->id);

        $engine = app(DartGameEngineService::class);
        $engine->startGame($game);

        $engine->submitThrow($game, $user, [
            'set' => 1,
            'leg' => 1,
            'turn' => 1,
            'throw' => 1,
            'field' => 1,
            'ring' => 'S',
        ]);

        $this->assertTrue($game->fresh()->isDone());
    }
}
