<?php

namespace Tests\Unit\Dart;

use Tests\TestCase;
use App\Services\Dart\ScoringService;

class ScoringServiceTest extends TestCase
{
    private ScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ScoringService::class);
    }

    /**
     * @dataProvider ringValuesProvider
     */
    public function test_calculate_value_by_ring_and_field(string $field, string $ring, int $expected): void
    {
        $this->assertEquals($expected, $this->service->calculateValue($field, $ring));
    }

    public function ringValuesProvider(): array
    {
        return [
            // Single ring values
            ['1', 'S', 1],
            ['5', 'S', 5],
            ['20', 'S', 20],
            // Double ring values
            ['1', 'D', 2],
            ['5', 'D', 10],
            ['20', 'D', 40],
            // Triple ring values
            ['1', 'T', 3],
            ['5', 'T', 15],
            ['20', 'T', 60],
            // Bull values
            ['0', 'BULL', 50],
            ['0', 'SBULL', 25],
            // Invalid ring
            ['10', 'INVALID', 0],
            ['5', 'X', 0],
        ];
    }

    public function test_calculate_value_all_fields_and_rings(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $this->assertEquals($i, $this->service->calculateValue((string)$i, 'S'));
            $this->assertEquals($i * 2, $this->service->calculateValue((string)$i, 'D'));
            $this->assertEquals($i * 3, $this->service->calculateValue((string)$i, 'T'));
        }
    }

    public function test_calculate_value_maximum_single_throw(): void
    {
        $this->assertEquals(60, $this->service->calculateValue('20', 'T'));
    }
}
