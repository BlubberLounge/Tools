<?php

namespace Tests\Unit\Dart;

use Tests\TestCase;
use App\Services\Dart\AroundTheClockEngine;

/**
 * Unit tests for AroundTheClockEngine - fast, isolated tests.
 * For database-dependent tests, see Feature\Dart\AroundTheClockEngineTest
 */
class AroundTheClockEngineTest extends TestCase
{
    private AroundTheClockEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(AroundTheClockEngine::class);
    }

    public function test_service_is_instantiable(): void
    {
        $this->assertInstanceOf(AroundTheClockEngine::class, $this->engine);
    }
}
