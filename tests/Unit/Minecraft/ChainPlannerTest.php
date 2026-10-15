<?php

namespace Tests\Unit\Minecraft;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use App\Minecraft\ChainLoopException;
use App\Minecraft\ChainPlanner;
use App\Minecraft\FarmCatalog;
use App\Minecraft\RecipeData;

/**
 * Pins known chains per version and the planner rules (defaults, overrides,
 * machines, hopper lines, loops, timing).
 */
class ChainPlannerTest extends TestCase
{
    use ChainTestData;

    public static function versions(): array
    {
        return ['Java 1.21' => ['1.21'], 'Java 26.3' => ['26.3']];
    }

    private function planner(string $version): ChainPlanner
    {
        return new ChainPlanner(RecipeData::load($version), FarmCatalog::load($version));
    }

    private static function nodes(array $plan): array
    {
        return array_column($plan['nodes'], null, 'item');
    }

    private static function sources(array $plan): array
    {
        return array_column($plan['sources'], null, 'item');
    }

    private static function edge(array $plan, string $from, string $to): array
    {
        foreach ($plan['edges'] as $edge)
            if ($edge['from'] === $from && $edge['to'] === $to)
                return $edge;

        self::fail("no edge {$from} -> {$to}");
    }

    #[DataProvider('versions')]
    public function test_1000_books_per_hour(string $version): void
    {
        $plan = $this->planner($version)->plan('book', 1000);
        $nodes = self::nodes($plan);
        $sources = self::sources($plan);

        $this->assertSame(['book', 'paper', 'leather', 'sugar_cane'], array_keys($nodes));
        $this->assertSame('book', $nodes['book']['choice']);
        $this->assertEquals(1000, $nodes['book']['crafts_per_hour']);
        $this->assertEquals(3000, $nodes['paper']['items_per_hour']);
        $this->assertEquals(1000, $nodes['paper']['crafts_per_hour']);

        // down to sugar cane and leather
        $this->assertSame(['sugar_cane', 'leather'], array_keys($sources));
        $this->assertEquals(3000, $sources['sugar_cane']['items_per_hour']);
        $this->assertSame('kmond-flying-machine-sugar-cane', $sources['sugar_cane']['design']['id']);
        $this->assertEquals(1700, $sources['sugar_cane']['module_rate']);
        $this->assertSame(2, $sources['sugar_cane']['modules']);
        $this->assertEquals(1000, $sources['leather']['items_per_hour']);
        $this->assertSame('autumn-hoglin-farm', $sources['leather']['design']['id']);
        $this->assertSame(1, $sources['leather']['modules']);
        $this->assertSame(3, $plan['totals']['modules']);
        $this->assertTrue($plan['totals']['modules_complete']);

        $this->assertEquals(3000, self::edge($plan, 'paper', 'book')['items_per_hour']);
        $this->assertSame(1, self::edge($plan, 'sugar_cane', 'paper')['hopper_lines']);

        // tree: book -> paper -> sugar cane, book -> leather
        $tree = $plan['tree'];
        $this->assertSame('book', $tree['item']);
        $children = array_column($tree['children'], null, 'item');
        $this->assertEquals(3000, $children['paper']['items_per_hour']);
        $this->assertEquals(1000, $children['leather']['items_per_hour']);
        $this->assertSame('sugar_cane', $children['paper']['children'][0]['item']);
        $this->assertSame('source', $children['leather']['kind']);
    }

    #[DataProvider('versions')]
    public function test_500_tnt_per_hour(string $version): void
    {
        $plan = $this->planner($version)->plan('tnt', 500);
        $sources = self::sources($plan);

        $this->assertEquals(2500, $sources['gunpowder']['items_per_hour']);
        $this->assertSame('fireblox-creeper-farm-33k', $sources['gunpowder']['design']['id']);
        $this->assertSame(1, $sources['gunpowder']['modules']);

        // no sand farm in the catalogue: the need without a module count
        $this->assertEquals(2000, $sources['sand']['items_per_hour']);
        $this->assertTrue($sources['sand']['no_farm']);
        $this->assertTrue($sources['sand']['rate_unknown']);
        $this->assertNull($sources['sand']['modules']);
        $this->assertFalse($plan['totals']['modules_complete']);

        $this->assertSame(1, self::edge($plan, 'gunpowder', 'tnt')['hopper_lines']);
        $this->assertSame(1, self::edge($plan, 'sand', 'tnt')['hopper_lines']);

        // 50000 TNT per hour need more than one hopper line per edge
        $big = $this->planner($version)->plan('tnt', 5000);
        $this->assertSame(3, self::edge($big, 'gunpowder', 'tnt')['hopper_lines']); // 25000 / 9000
        $this->assertSame(3, self::edge($big, 'sand', 'tnt')['hopper_lines']);      // 20000 / 9000
    }

    #[DataProvider('versions')]
    public function test_emerald_trade_chain(string $version): void
    {
        $planner = $this->planner($version);

        // default: emeralds from the trading hall, rate not published
        $default = $planner->plan('emerald', 100);
        $source = self::sources($default)['emerald'];
        $this->assertSame('source', self::nodes($default)['emerald']['choice']);
        $this->assertTrue($source['rate_unknown']);
        $this->assertNull($source['modules']);

        // librarians buying paper: 24 paper per emerald, 16 trades, 2 restocks per 20-minute day
        $plan = $planner->plan('emerald', 100, ['choices' => ['emerald' => 'trade_librarian_paper_emerald']]);
        $nodes = self::nodes($plan);
        $this->assertSame('user', $nodes['emerald']['chosen_by']);
        $this->assertEquals(100, $nodes['emerald']['crafts_per_hour']);
        $this->assertSame('villager', $nodes['emerald']['machine']['type']);
        $this->assertEquals(96, $nodes['emerald']['machine']['per_machine']);
        $this->assertSame(2, $nodes['emerald']['machine']['count']);
        $this->assertEquals(2400, $nodes['paper']['items_per_hour']);
        $sources = self::sources($plan);
        $this->assertEquals(2400, $sources['sugar_cane']['items_per_hour']);
        $this->assertSame(2, $sources['sugar_cane']['modules']);
        $this->assertSame(2, $plan['totals']['villagers']);

        // trade cooldown override: four restocks a day halve the villagers
        $faster = $planner->plan('emerald', 100, ['choices' => ['emerald' => 'trade_librarian_paper_emerald'], 'restocks_per_day' => 4]);
        $emerald = self::nodes($faster)['emerald'];
        $this->assertSame(1, $emerald['machine']['count']);
        $this->assertTrue($emerald['machine']['overridden']);

        // spending emeralds: bottles o' enchanting from clerics, emeralds from farmers buying wheat
        $xp = $planner->plan('experience_bottle', 60, ['choices' => ['emerald' => 'trade_farmer_wheat_emerald']]);
        $nodes = self::nodes($xp);
        $this->assertEquals(180, $nodes['emerald']['items_per_hour']);
        $this->assertEquals(3600, $nodes['wheat']['items_per_hour']);
        $this->assertSame(1, $nodes['experience_bottle']['machine']['count']); // 60 / (12 * 2 * 3)
        $this->assertSame(2, $nodes['emerald']['machine']['count']);           // 180 / 96
    }

    #[DataProvider('versions')]
    public function test_module_rate_override_and_design_choice(string $version): void
    {
        $planner = $this->planner($version);

        $plan = $planner->plan('hopper', 100);
        $iron = self::sources($plan)['iron_ingot'];
        $this->assertEquals(500, $iron['items_per_hour']);
        $this->assertSame('fireblox-4-module-iron-farm', $iron['design']['id']);
        $this->assertSame(1, $iron['modules']);
        $this->assertFalse($iron['module_rate_overridden']);

        $plan = $planner->plan('hopper', 100, ['module_rates' => ['iron_ingot' => 400]]);
        $iron = self::sources($plan)['iron_ingot'];
        $this->assertEquals(400, $iron['module_rate']);
        $this->assertSame(2, $iron['modules']);
        $this->assertTrue($iron['module_rate_overridden']);

        $plan = $planner->plan('hopper', 100, ['designs' => ['iron_ingot' => 'shulkercraft-reliable-iron-farm']]);
        $this->assertEquals(600, self::sources($plan)['iron_ingot']['module_rate']);

        // an unknown rate gets a module count once the user enters one
        $plan = $planner->plan('emerald', 100, ['module_rates' => ['emerald' => 30]]);
        $this->assertSame(4, self::sources($plan)['emerald']['modules']);

        $this->expectException(InvalidArgumentException::class);
        $planner->plan('hopper', 100, ['designs' => ['iron_ingot' => 'kmond-flying-machine-sugar-cane']]);
    }

    #[DataProvider('versions')]
    public function test_cooking_nodes_count_furnaces_and_fuel(string $version): void
    {
        $planner = $this->planner($version);

        $plan = $planner->plan('glass', 1000);
        $glass = self::nodes($plan)['glass'];
        $this->assertSame('furnace', $glass['machine']['type']);
        $this->assertSame(3, $glass['machine']['count']); // 1000 / 360
        $this->assertSame('coal', $glass['fuel']['item']);
        $this->assertEquals(125, $glass['fuel']['per_hour']);
        $this->assertEquals(125, self::sources($plan)['coal']['items_per_hour']);
        $this->assertTrue(self::edge($plan, 'coal', 'glass')['fuel']);

        // blast furnaces are twice as fast and use the same fuel per item
        $plan = $planner->plan('iron_ingot', 1440, ['choices' => ['iron_ingot' => 'iron_ingot_from_blasting_raw_iron']]);
        $ingot = self::nodes($plan)['iron_ingot'];
        $this->assertSame('blast_furnace', $ingot['machine']['type']);
        $this->assertSame(2, $ingot['machine']['count']);
        $this->assertEquals(180, $ingot['fuel']['per_hour']);

        // furnace speed override
        $plan = $planner->plan('glass', 1000, ['furnace_rates' => ['smelting' => 100]]);
        $glass = self::nodes($plan)['glass'];
        $this->assertSame(10, $glass['machine']['count']);
        $this->assertTrue($glass['machine']['overridden']);
    }

    #[DataProvider('versions')]
    public function test_fuel_loop_with_dried_kelp_blocks(string $version): void
    {
        // the dried kelp smelters burn part of their own output
        $plan = $this->planner($version)->plan('glass', 1000, ['fuel' => 'dried_kelp_block']);
        $nodes = self::nodes($plan);

        $fuelForGlass = 1000 / 20.005;
        $blocks = $fuelForGlass / (1 - 9 / 20.005);
        $this->assertEqualsWithDelta($blocks, $nodes['dried_kelp_block']['items_per_hour'], 0.01);
        $this->assertEqualsWithDelta(9 * $blocks, $nodes['dried_kelp']['items_per_hour'], 0.01);
        $this->assertSame('dried_kelp_from_smelting', $nodes['dried_kelp']['choice']);
        $this->assertEqualsWithDelta(9 * $blocks, self::sources($plan)['kelp']['items_per_hour'], 0.01);

        // the tree marks the way back to the fuel as a loop
        $rows = [];
        $walk = function (array $node) use (&$walk, &$rows) {
            $rows[] = $node;
            foreach ($node['children'] as $child)
                $walk($child);
        };
        $walk($plan['tree']);
        $this->assertNotEmpty(array_filter($rows, fn ($row) => $row['loop'] && $row['item'] === 'dried_kelp_block'));
    }

    #[DataProvider('versions')]
    public function test_default_choice_is_fewest_raw_items(string $version): void
    {
        $planner = $this->planner($version);
        $defaults = $planner->defaultChoices('coal');

        // a farm makes iron ingots: no detour through iron blocks or nuggets
        $this->assertSame('source', $defaults['iron_ingot']);
        // one bone makes three bone meal
        $this->assertSame('bone_meal', $defaults['bone_meal']);
        // glass from sand, not from librarians (trades only when nothing else works)
        $this->assertSame('glass', $defaults['glass']);
        $this->assertSame('dried_kelp_from_smelting', $defaults['dried_kelp']);

        $options = array_column($planner->options('torch', 'coal'), null, 'id');
        $this->assertArrayHasKey('torch', $options);
        $this->assertArrayHasKey('torch_from_charcoal', $options);
        $this->assertArrayHasKey('source', $options);
    }

    #[DataProvider('versions')]
    public function test_recipe_loops_are_reported(string $version): void
    {
        try {
            $this->planner($version)->plan('iron_block', 10, ['choices' => ['iron_ingot' => 'iron_ingot_from_iron_block']]);
            $this->fail('loop not reported');
        } catch (ChainLoopException $e) {
            $this->assertEqualsCanonicalizing(['iron_ingot', 'iron_block'], $e->items);
        }
    }

    public function test_invalid_input_is_rejected(): void
    {
        $planner = $this->planner('26.3');

        foreach ([
            fn () => $planner->plan('nope', 10),
            fn () => $planner->plan('book', 0),
            fn () => $planner->plan('book', 10, ['choices' => ['book' => 'paper']]),
            fn () => $planner->plan('book', 10, ['fuel' => 'book']),
            fn () => $planner->plan('book', 10, ['mode' => 'magic']),
            fn () => $planner->plan('book', 10, ['furnace_rates' => ['campfire' => 10]]),
            fn () => $planner->plan('book', 10, ['module_rates' => ['leather' => -1]]),
        ] as $i => $call) {
            try {
                $call();
                $this->fail("case {$i} was accepted");
            } catch (InvalidArgumentException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function test_a_30_node_chain_is_planned_within_300_ms(): void
    {
        // item_0 <- 2 item_1 + raw_1, item_1 <- 2 item_2 + raw_2 ... (30 crafted nodes plus raw items)
        $recipes = [];
        $items = [];
        $farms = [];
        for ($i = 0; $i < 30; $i++) {
            $items[] = "item_{$i}";
            $items[] = "raw_{$i}";
            $inputs = $i < 29 ? ["item_".($i + 1) => 2, "raw_{$i}" => 1] : ["raw_{$i}" => 3];
            $recipes[] = self::craft("make_{$i}", "item_{$i}", 2, $inputs);
            $farms["raw_{$i}"] = 1000.0;
        }
        $planner = new ChainPlanner($this->recipeData($recipes, $items), $this->farmCatalog($farms));

        $start = microtime(true);
        $plan = $planner->plan('item_0', 1000);
        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertCount(60, $plan['nodes']);
        $this->assertCount(30, array_filter($plan['nodes'], fn ($n) => $n['kind'] === 'recipe'));
        $this->assertLessThan(300, $elapsed, "planning took {$elapsed} ms");

        // the real data: a long redstone chain, also in manual and automatic mode
        $real = $this->planner('26.3');
        foreach (['manual', 'auto'] as $mode) {
            $start = microtime(true);
            $real->plan('activator_rail', 600, ['mode' => $mode]);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->assertLessThan(300, $elapsed, "{$mode}: {$elapsed} ms");
        }
    }
}
