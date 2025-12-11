<?php

namespace Tests\Unit\Dart;

use Tests\TestCase;
use App\Models\DartGame;
use App\Services\Dart\DartGameEngineService;
use App\Enums\DartGameType;

class DartGameEngineServiceTest extends TestCase
{
    public function test_engine_resolution_by_type(): void
    {
        $engine = app(DartGameEngineService::class);

        $game = new DartGame(['type' => DartGameType::X01]);
        $resolved = $engine->engineFor($game);

        $this->assertInstanceOf(\App\Services\Dart\X01Engine::class, $resolved);
    }
}
