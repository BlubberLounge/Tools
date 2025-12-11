<?php

namespace Tests\Feature\Dart;

use Tests\TestCase;
use App\Services\Dart\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoringService = app(ScoringService::class);
    }

    /**
     * Test: calculateValue for single (S) ring
     */
    public function test_calculate_value_single(): void
    {
        $this->assertEquals(1, $this->scoringService->calculateValue('1', 'S'));
        $this->assertEquals(5, $this->scoringService->calculateValue('5', 'S'));
        $this->assertEquals(20, $this->scoringService->calculateValue('20', 'S'));
    }

    /**
     * Test: calculateValue for double (D) ring
     */
    public function test_calculate_value_double(): void
    {
        $this->assertEquals(2, $this->scoringService->calculateValue('1', 'D'));
        $this->assertEquals(10, $this->scoringService->calculateValue('5', 'D'));
        $this->assertEquals(40, $this->scoringService->calculateValue('20', 'D'));
    }

    /**
     * Test: calculateValue for triple (T) ring
     */
    public function test_calculate_value_triple(): void
    {
        $this->assertEquals(3, $this->scoringService->calculateValue('1', 'T'));
        $this->assertEquals(15, $this->scoringService->calculateValue('5', 'T'));
        $this->assertEquals(60, $this->scoringService->calculateValue('20', 'T'));
    }

    /**
     * Test: calculateValue for bull (BULL) - inner bull / bullseye
     */
    public function test_calculate_value_bull(): void
    {
        $this->assertEquals(50, $this->scoringService->calculateValue('50', 'BULL'));
        // Field is ignored for bull
        $this->assertEquals(50, $this->scoringService->calculateValue('25', 'BULL'));
        $this->assertEquals(50, $this->scoringService->calculateValue('0', 'BULL'));
    }

    /**
     * Test: calculateValue for single bull (SBULL) - outer bull
     */
    public function test_calculate_value_single_bull(): void
    {
        $this->assertEquals(25, $this->scoringService->calculateValue('25', 'SBULL'));
        // Field is ignored for SBULL
        $this->assertEquals(25, $this->scoringService->calculateValue('50', 'SBULL'));
        $this->assertEquals(25, $this->scoringService->calculateValue('0', 'SBULL'));
    }

    /**
     * Test: calculateValue for outer bull (O) - miss
     */
    public function test_calculate_value_outer_miss(): void
    {
        $this->assertEquals(0, $this->scoringService->calculateValue('0', 'O'));
        $this->assertEquals(0, $this->scoringService->calculateValue('1', 'O'));
        $this->assertEquals(0, $this->scoringService->calculateValue('20', 'O'));
    }

    /**
     * Test: calculateValue for invalid ring returns 0
     */
    public function test_calculate_value_invalid_ring(): void
    {
        $this->assertEquals(0, $this->scoringService->calculateValue('20', 'X'));
        $this->assertEquals(0, $this->scoringService->calculateValue('20', 'INVALID'));
        $this->assertEquals(0, $this->scoringService->calculateValue('20', ''));
    }

    /**
     * Test: calculateValue with all dart board numbers (1-20)
     */
    public function test_calculate_value_all_numbers(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $single = $this->scoringService->calculateValue((string)$i, 'S');
            $double = $this->scoringService->calculateValue((string)$i, 'D');
            $triple = $this->scoringService->calculateValue((string)$i, 'T');

            $this->assertEquals($i, $single);
            $this->assertEquals($i * 2, $double);
            $this->assertEquals($i * 3, $triple);
        }
    }

    /**
     * Test: calculateValue maximum single (T20)
     */
    public function test_calculate_value_maximum_single(): void
    {
        $max = $this->scoringService->calculateValue('20', 'T');
        $this->assertEquals(60, $max);
    }

    /**
     * Test: calculateValue with string numbers
     */
    public function test_calculate_value_with_string_numbers(): void
    {
        $this->assertEquals(20, $this->scoringService->calculateValue('20', 'S'));
        $this->assertEquals(40, $this->scoringService->calculateValue('20', 'D'));
        $this->assertEquals(60, $this->scoringService->calculateValue('20', 'T'));
    }

    /**
     * Test: calculateValue special fields (25, 50)
     */
    public function test_calculate_value_special_fields(): void
    {
        // Field 25 with different rings
        $this->assertEquals(25, $this->scoringService->calculateValue('25', 'S'));
        $this->assertEquals(50, $this->scoringService->calculateValue('25', 'D'));
        $this->assertEquals(75, $this->scoringService->calculateValue('25', 'T'));

        // Field 50 with different rings
        $this->assertEquals(50, $this->scoringService->calculateValue('50', 'S'));
        $this->assertEquals(100, $this->scoringService->calculateValue('50', 'D'));
        $this->assertEquals(150, $this->scoringService->calculateValue('50', 'T'));
    }

    /**
     * Test: calculateValue case sensitivity
     */
    public function test_calculate_value_case_sensitivity(): void
    {
        // Should work with uppercase (as per implementation)
        $this->assertEquals(50, $this->scoringService->calculateValue('50', 'BULL'));
        $this->assertEquals(25, $this->scoringService->calculateValue('25', 'SBULL'));

        // Lowercase should return 0 (no match)
        $this->assertEquals(0, $this->scoringService->calculateValue('50', 'bull'));
        $this->assertEquals(0, $this->scoringService->calculateValue('25', 'sbull'));
    }
}
