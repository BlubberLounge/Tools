<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Models\User;
use App\Models\DartGame;
use App\Services\Dart\StatisticsService;
use App\Enums\DartGameType;
use App\Enums\DartGameStatus;
use App\Enums\DartGameUserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatisticsService $statisticsService;
    protected DartGame $game;
    protected User $player;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = app(StatisticsService::class);

        $this->game = DartGame::factory()->create([
            'type' => DartGameType::X01,
            'points' => 301,
            'status' => DartGameStatus::RUNNING,
        ]);

        $this->player = User::factory()->create();

        $this->game->users()->attach($this->player->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 1,
        ]);
    }

    /**
     * Test: getLiveStats returns array with expected keys
     */
    public function test_get_live_stats_returns_array(): void
    {
        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('highestThrow', $stats);
        $this->assertArrayHasKey('lowestThrow', $stats);
        $this->assertArrayHasKey('longestStreak', $stats);
        $this->assertArrayHasKey('misthrows', $stats);
    }

    /**
     * Test: getLiveStats with no throws
     */
    public function test_get_live_stats_no_throws(): void
    {
        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertIsArray($stats);
        // All stats should be null or 0 (depending on implementation)
        $this->assertNull($stats['highestThrow']);
    }

    /**
     * Test: getLiveStats highestThrow
     */
    public function test_get_live_stats_highest_throw(): void
    {
        // Create throws with different values
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'D', 'value' => 40],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'T', 'value' => 60],
        ]);

        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertEquals(60, $stats['highestThrow']);
    }

    /**
     * Test: getLiveStats lowestThrow (excluding misses)
     */
    public function test_get_live_stats_lowest_throw(): void
    {
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'D', 'value' => 40],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'T', 'value' => 60],
        ]);

        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertEquals(20, $stats['lowestThrow']);
    }

    /**
     * Test: getLiveStats with mix of hits and misses
     */
    public function test_get_live_stats_with_misses(): void
    {
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 2, 'field' => 0, 'ring' => 'O', 'value' => 0],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'D', 'value' => 40],
        ]);

        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertIsArray($stats);
        $this->assertNotNull($stats['highestThrow']);
    }

    /**
     * Test: getLiveStats longestStreak
     */
    public function test_get_live_stats_longest_streak(): void
    {
        // Create consecutive non-zero throws
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'D', 'value' => 40],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'T', 'value' => 60],
        ]);

        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertIsArray($stats);
        $this->assertIsNumeric($stats['longestStreak']);
    }

    /**
     * Test: getLiveStats misthrows (user with most misses)
     */
    public function test_get_live_stats_misthrows(): void
    {
        // Player 1 with 2 misses
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 1, 'field' => 0, 'ring' => 'O', 'value' => 0],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 2, 'field' => 0, 'ring' => 'O', 'value' => 0],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 3, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertIsArray($stats);
        // misthrows should indicate user with most misses
    }

    /**
     * Test: getLiveStats multiple players
     */
    public function test_get_live_stats_multiple_players(): void
    {
        $player2 = User::factory()->create();
        $this->game->users()->attach($player2->id, [
            'status' => DartGameUserStatus::ACCEPTED,
            'position' => 2,
        ]);

        // Player 1 throws
        $this->game->dartThrows()->createMany([
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'S', 'value' => 20],
            ['user_id' => $this->player->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'D', 'value' => 40],
        ]);

        // Player 2 throws
        $this->game->dartThrows()->createMany([
            ['user_id' => $player2->id, 'turn' => 1, 'throw' => 1, 'field' => 20, 'ring' => 'T', 'value' => 60],
            ['user_id' => $player2->id, 'turn' => 1, 'throw' => 2, 'field' => 20, 'ring' => 'S', 'value' => 20],
        ]);

        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertIsArray($stats);
        // highestThrow should be 60 (from player 2)
        $this->assertEquals(60, $stats['highestThrow']);
    }

    /**
     * Test: getLiveStats with bull (50 points)
     */
    public function test_get_live_stats_with_bull(): void
    {
        $this->game->dartThrows()->create([
            'user_id' => $this->player->id,
            'turn' => 1,
            'throw' => 1,
            'field' => 50,
            'ring' => 'BULL',
            'value' => 50,
        ]);

        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertIsArray($stats);
        $this->assertEquals(50, $stats['highestThrow']);
    }

    /**
     * Test: getLiveStats with triple 20 (60 points - maximum)
     */
    public function test_get_live_stats_maximum_throw(): void
    {
        $this->game->dartThrows()->create([
            'user_id' => $this->player->id,
            'turn' => 1,
            'throw' => 1,
            'field' => 20,
            'ring' => 'T',
            'value' => 60,
        ]);

        $stats = $this->statisticsService->getLiveStats($this->game);

        $this->assertEquals(60, $stats['highestThrow']);
    }
}
