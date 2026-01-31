<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\User;
use App\Models\DartGame;
use App\Services\Dart\AroundTheClockEngine;
use App\Services\Dart\DartGameEngineService;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AroundTheClockEngineTest extends TestCase
{
    use RefreshDatabase;

    private AroundTheClockEngine $engine;
    private DartGame $game;
    private User $player;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(AroundTheClockEngine::class);

        $this->player = User::factory()->create();

        $this->game = DartGame::factory()->create([
            'type' => DartGameType::aroundTheClock,
            'status' => DartGameStatus::CREATED,
        ]);

        $this->game->users()->attach(
            $this->player->id,
            ['position' => 0, 'status' => 'accepted']
        );
    }

    public function test_start_game(): void
    {
        $state = $this->engine->start($this->game);

        $this->assertEquals(DartGameStatus::RUNNING, $this->game->fresh()->status);
        $this->assertArrayHasKey('players', $state);
    }

    public function test_player_progression_through_targets(): void
    {
        $this->engine->start($this->game);

        for ($target = 1; $target <= 5; $target++) {
            $payload = ['field' => $target, 'ring' => 'S', 'x' => null, 'y' => null];
            $state = $this->engine->submitThrow($this->game, $this->player, $payload);
            $playerState = $state['players']->firstWhere('id', $this->player->id);
            $this->assertEquals($target, $playerState['progress']);
        }
    }

    public function test_player_hits_all_targets_and_finishes(): void
    {
        $this->engine->start($this->game);

        // Hit all 21 targets (1-20 + Bull)
        $sequence = array_merge(range(1, 20), [25]);

        foreach ($sequence as $target) {
            $payload = ['field' => $target, 'ring' => 'S', 'x' => null, 'y' => null];
            $this->engine->submitThrow($this->game, $this->player, $payload);
        }

        $this->assertTrue($this->engine->isFinished($this->game));
        $this->assertEquals(DartGameStatus::DONE, $this->game->fresh()->status);
    }

    public function test_atc_progression(): void
    {
        $this->engine->start($this->game);
        $this->engine->submitThrow($this->game, $this->player, [
            'field' => 1,
            'ring' => 'S',
        ]);

        $state = $this->engine->getState($this->game);
        $this->assertEquals(1, $state['players'][0]['progress']);
    }

    public function test_atc_win(): void
    {
        $this->engine->start($this->game);

        $sequence = array_merge(range(1, 20), [25]);
        foreach ($sequence as $target) {
            $this->engine->submitThrow($this->game, $this->player, [
                'field' => $target,
                'ring' => 'S',
            ]);
        }

        $this->assertTrue($this->game->fresh()->isDone());
    }
}
