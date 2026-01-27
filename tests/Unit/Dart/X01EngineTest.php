<?php

namespace Tests\Unit\Dart;

use Tests\TestCase;
use App\Services\Dart\X01Engine;

/**
 * Unit tests for X01Engine - fast, isolated tests.
 * For database-dependent tests, see Feature\Dart\X01EngineTest and Feature\Dart\X01EngineFullTest
 */
class X01EngineTest extends TestCase
{
    private X01Engine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(X01Engine::class);
    }

    public function test_service_is_instantiable(): void
    {
        $this->assertInstanceOf(X01Engine::class, $this->engine);
    }
}
