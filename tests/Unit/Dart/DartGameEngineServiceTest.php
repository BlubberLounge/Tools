<?php

namespace Tests\Unit\Dart;

use Tests\TestCase;
use App\Models\DartGame;
use App\Services\Dart\DartGameEngineService;
use App\Services\Dart\X01Engine;
use App\Services\Dart\AroundTheClockEngine;
use App\Enums\DartGameType;

/**
 * Unit tests for DartGameEngineService - engine resolution only.
 * For database-dependent tests, see Feature\Dart\DartGameEngineServiceTest
 */
class DartGameEngineServiceTest extends TestCase
{
    private DartGameEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DartGameEngineService::class);
    }

    public function test_engine_resolution_for_x01(): void
    {
        $game = new DartGame(['type' => DartGameType::X01]);
        $resolved = $this->service->engineFor($game);

        $this->assertInstanceOf(X01Engine::class, $resolved);
    }

    public function test_engine_resolution_for_around_the_clock(): void
    {
        $game = new DartGame(['type' => DartGameType::aroundTheClock]);
        $resolved = $this->service->engineFor($game);

        $this->assertInstanceOf(AroundTheClockEngine::class, $resolved);
    }
}
