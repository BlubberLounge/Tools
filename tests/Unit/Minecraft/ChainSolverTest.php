<?php

namespace Tests\Unit\Minecraft;

use Tests\TestCase;

use App\Minecraft\ChainLoopException;
use App\Minecraft\ChainPlanner;
use App\Minecraft\ChainSolver;
use App\Minecraft\FarmCatalog;
use App\Minecraft\RecipeData;

/**
 * The LP mode of the chain planner: the simplex itself and the recipe choice
 * with the fewest farm modules.
 */
class ChainSolverTest extends TestCase
{
    use ChainTestData;

    public function test_simplex_finds_the_optimum(): void
    {
        // min 2x + 3y  s.t.  x + y >= 4,  x + 3y >= 6  ->  x = 3, y = 1, cost 9
        $result = ChainSolver::minimize([2, 3], [[1, 1], [1, 3]], [4, 6]);

        $this->assertSame(ChainSolver::OPTIMAL, $result['status']);
        $this->assertEqualsWithDelta(3, $result['x'][0], 1e-9);
        $this->assertEqualsWithDelta(1, $result['x'][1], 1e-9);
        $this->assertEqualsWithDelta(9, $result['objective'], 1e-9);
    }

    public function test_simplex_reports_infeasible_and_unbounded(): void
    {
        // -x >= 1 has no solution with x >= 0
        $this->assertSame(ChainSolver::INFEASIBLE, ChainSolver::minimize([1], [[-1]], [1])['status']);

        // min -x with x >= 1: x can grow forever
        $this->assertSame(ChainSolver::UNBOUNDED, ChainSolver::minimize([-1], [[1]], [1])['status']);
    }

    public function test_solver_picks_the_recipe_with_fewer_modules(): void
    {
        // widget: 2 alpha (100 per module) or 1 beta (10 per module)
        $recipes = [
            self::craft('widget_from_alpha', 'widget', 1, ['alpha' => 2]),
            self::craft('widget_from_beta', 'widget', 1, ['beta' => 1]),
        ];
        $planner = new ChainPlanner(
            $this->recipeData($recipes, ['widget', 'alpha', 'beta']),
            $this->farmCatalog(['alpha' => 100.0, 'beta' => 10.0, 'coal' => 1000.0]),
        );

        // default: fewest raw items -> beta, 10 modules
        $manual = $planner->plan('widget', 100);
        $this->assertSame('widget_from_beta', $manual['nodes'][0]['choice']);
        $this->assertSame(10, $manual['totals']['modules']);

        // solver: alpha, 200 per hour = 2 modules
        $auto = $planner->plan('widget', 100, ['mode' => 'auto']);
        $this->assertSame('widget_from_alpha', $auto['nodes'][0]['choice']);
        $this->assertSame('solver', $auto['nodes'][0]['chosen_by']);
        $this->assertSame(2, $auto['totals']['modules']);
        $this->assertSame(ChainSolver::OPTIMAL, $auto['solver']['status']);
        $this->assertEqualsWithDelta(2, $auto['solver']['modules'], 1e-6);

        // a module rate override turns the result around
        $override = $planner->plan('widget', 100, ['mode' => 'auto', 'module_rates' => ['beta' => 1000]]);
        $this->assertSame('widget_from_beta', $override['nodes'][0]['choice']);
    }

    public function test_solver_handles_real_loops(): void
    {
        $planner = new ChainPlanner(RecipeData::load('26.3'), FarmCatalog::load('26.3'));

        // bone meal: the moss farm beats skeletons, though bones give three each
        $manual = $planner->plan('bone_block', 100);
        $auto = $planner->plan('bone_block', 100, ['mode' => 'auto']);
        $this->assertSame('bone_meal', array_column($manual['nodes'], 'choice', 'item')['bone_meal']);
        $this->assertSame('source', array_column($auto['nodes'], 'choice', 'item')['bone_meal']);

        // ingot <-> block and the kelp fuel loop do not trip the solver
        $auto = $planner->plan('iron_block', 50, ['mode' => 'auto']);
        $this->assertSame('source', array_column($auto['nodes'], 'choice', 'item')['iron_ingot']);
        $auto = $planner->plan('glass', 1000, ['mode' => 'auto', 'fuel' => 'dried_kelp_block']);
        $this->assertSame(ChainSolver::OPTIMAL, $auto['solver']['status']);
    }

    public function test_solver_reports_a_loop_without_raw_resource(): void
    {
        // nothing may be taken as raw resource: phase 1 cannot reach the demand
        $data = RecipeData::load('26.3');
        $result = ChainSolver::solveChain($data, 'iron_block', 10, 'coal', [], []);

        $this->assertSame(ChainSolver::INFEASIBLE, $result['status']);
        $this->assertSame([], $result['choices']);
        $this->assertNotNull($result['message']);

        $this->assertTrue(is_subclass_of(ChainLoopException::class, \RuntimeException::class));
    }
}
