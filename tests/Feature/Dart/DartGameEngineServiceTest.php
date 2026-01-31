<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\DartGame;
use App\Models\User;
use App\Services\Dart\DartGameEngineService;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DartGameEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    private DartGameEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DartGameEngineService::class);
    }

    public function test_new_game_creates_dart_game(): void
    {
        $user = User::factory()->create();

        $result = $this->service->newGame($user->id, [
            'type' => 'X01',
            'points' => 501,
            'title' => 'Test Game',
        ]);

        $game = is_array($result) ? ($result['game'] ?? null) : $result;

        $this->assertInstanceOf(DartGame::class, $game);
        $this->assertEquals($user->id, $game->created_by);
        $this->assertEquals(DartGameType::X01, $game->type);
    }

    public function test_start_game_changes_status(): void
    {
        $user = User::factory()->create();
        $game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'status' => DartGameStatus::CREATED,
        ]);
        $game->users()->attach($user->id, ['position' => 0, 'status' => 'accepted']);

        $this->service->startGame($game);

        $this->assertEquals(DartGameStatus::RUNNING, $game->fresh()->status);
    }

    public function test_submit_throw(): void
    {
        $user = User::factory()->create();
        $game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 501,
            'status' => DartGameStatus::RUNNING,
        ]);
        $game->users()->attach($user->id, ['position' => 0, 'status' => 'accepted']);

        $state = $this->service->submitThrow($game, $user, [
            'field' => 20,
            'ring' => 'T',
        ]);

        $this->assertIsArray($state);
        $this->assertArrayHasKey('game_id', $state);
    }

    public function test_get_state(): void
    {
        $user = User::factory()->create();
        $game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'status' => DartGameStatus::RUNNING,
        ]);
        $game->users()->attach($user->id, ['position' => 0, 'status' => 'accepted']);

        $state = $this->service->getState($game);

        $this->assertIsArray($state);
        $this->assertArrayHasKey('game_id', $state);
        $this->assertArrayHasKey('players', $state);
    }

    public function test_add_users_to_game(): void
    {
        $game = DartGame::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $result = $this->service->addUsers($game, [$user1->id, $user2->id]) ?? [];

        $this->assertArrayHasKey('attached', $result);
        $this->assertCount(2, $game->users);
    }

    public function test_remove_user_from_game(): void
    {
        $game = DartGame::factory()->create();
        $user = User::factory()->create();
        $game->users()->attach($user->id);

        $this->service->removeUser($game, $user->id);

        $this->assertFalse($game->fresh()->users->contains($user));
    }

    public function test_end_games_for_users(): void
    {
        $user = User::factory()->create();
        $game1 = DartGame::factory()->create(['status' => DartGameStatus::RUNNING]);
        $game1->users()->attach($user->id, ['status' => 'accepted']);

        $result = $this->service->endGamesForUsers([$user->id], DartGameStatus::ABORTED);

        $this->assertArrayHasKey('ended_game_ids', $result);
        $this->assertEquals(DartGameStatus::ABORTED, $game1->fresh()->status);
    }
}
