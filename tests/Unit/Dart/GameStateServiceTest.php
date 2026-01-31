<?php

namespace Tests\Unit\Dart;

use Tests\TestCase;
use App\Services\Dart\GameStateService;

/**
 * Unit tests for GameStateService - fast, isolated tests.
 * For database-dependent tests, see Feature\Dart\GameStateServiceTest
 */
class GameStateServiceTest extends TestCase
{
    private GameStateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GameStateService::class);
    }

    /**
     * Placeholder: GameStateService is heavily database-dependent.
     * See Feature\Dart\GameStateServiceTest for comprehensive integration tests.
     */
    public function test_service_is_instantiable(): void
    {
        $this->assertInstanceOf(GameStateService::class, $this->service);
    }
}
