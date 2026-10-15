<?php

namespace Tests\Unit\Minecraft;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use App\Minecraft\AnvilPlanner;
use App\Minecraft\EnchantmentData;
use App\Minecraft\Experience;

class AnvilPlannerTest extends TestCase
{
    private const FULL_SWORD = ['sharpness' => 5, 'knockback' => 2, 'fire_aspect' => 2, 'looting' => 3, 'sweeping_edge' => 3, 'unbreaking' => 3, 'mending' => 1];
    private const FULL_BOOTS = ['protection' => 4, 'feather_falling' => 4, 'depth_strider' => 3, 'soul_speed' => 3, 'unbreaking' => 3, 'mending' => 1];
    private const MAXED_BOW = ['power' => 5, 'punch' => 2, 'flame' => 1, 'infinity' => 1, 'unbreaking' => 3];
    private const MACE = ['density' => 5, 'wind_burst' => 3, 'fire_aspect' => 2, 'unbreaking' => 3, 'mending' => 1];
    private const PICKAXE = ['efficiency' => 5, 'fortune' => 3, 'unbreaking' => 3, 'mending' => 1];

    private function planner(string $version): AnvilPlanner
    {
        return new AnvilPlanner(EnchantmentData::load($version));
    }

    /**
     * Known optimal plans. Step costs are in execution order; totals were
     * cross-checked against the exhaustive search below.
     */
    public static function knownPlans(): array
    {
        $plans = [];
        foreach (['1.21', '26.3'] as $version) {
            $plans["{$version} full diamond sword"] = [$version, 'diamond_sword', self::FULL_SWORD, 49, [4, 2, 10, 2, 11, 3, 17]];
            $plans["{$version} full diamond boots"] = [$version, 'diamond_boots', self::FULL_BOOTS, 49, [6, 2, 8, 3, 5, 25]];
            $plans["{$version} maxed bow"] = [$version, 'bow', self::MAXED_BOW, 29, [4, 2, 9, 3, 11]];
            $plans["{$version} mace"] = [$version, 'mace', self::MACE, 31, [5, 2, 8, 3, 13]];
            $plans["{$version} diamond pickaxe"] = [$version, 'diamond_pickaxe', self::PICKAXE, 23, [5, 7, 2, 9]];
        }

        return $plans;
    }

    #[DataProvider('knownPlans')]
    public function test_known_optimal_plans(string $version, string $item, array $wanted, int $total, array $stepCosts): void
    {
        $plan = $this->planner($version)->plan($item, $wanted);

        $this->assertTrue($plan['possible']);
        $this->assertSame($total, $plan['total_levels']);
        $this->assertSame($stepCosts, array_column($plan['steps'], 'cost'));
        $this->assertSame(count($wanted), $plan['books']);
        $this->assertCount(count($wanted) - 0, $plan['steps'], 'n books need n anvil steps');
        $this->assertNull($plan['failing_step']);

        foreach ($plan['steps'] as $step)
            $this->assertLessThanOrEqual(39, $step['cost']);

        // the final result carries every wanted enchantment at its level
        $final = array_column($plan['result']['enchantments'], 'level', 'id');
        ksort($wanted);
        $this->assertSame($wanted, $final);
        $this->assertTrue($plan['result']['is_item']);

        // the total XP is the sum of the steps, each counted from level 0
        $this->assertSame(
            array_sum(array_map([Experience::class, 'pointsForLevel'], $stepCosts)),
            $plan['total_xp_points']
        );
    }

    public function test_step_costs_follow_the_anvil_rules(): void
    {
        $plan = $this->planner('26.3')->plan('diamond_sword', self::FULL_SWORD);

        foreach ($plan['steps'] as $step) {
            $penalty = (2 ** $step['left']['prior_work'] - 1) + (2 ** $step['right']['prior_work'] - 1);
            $this->assertSame($penalty, $step['penalty_cost']);
            $this->assertSame($step['penalty_cost'] + $step['enchantment_cost'], $step['cost']);
            $this->assertSame(max($step['left']['prior_work'], $step['right']['prior_work']) + 1, $step['result']['prior_work']);
            $this->assertFalse($step['right']['is_item'], 'the item never goes into the right slot');
        }
    }

    public function test_mace_and_spear_only_where_the_version_has_them(): void
    {
        $plan = $this->planner('26.3')->plan('netherite_spear', ['sharpness' => 5, 'lunge' => 3, 'unbreaking' => 3, 'mending' => 1]);
        $this->assertTrue($plan['possible']);

        $this->expectException(InvalidArgumentException::class);
        $this->planner('1.21')->plan('netherite_spear', ['lunge' => 3]);
    }

    public function test_present_enchantments_are_not_bought_again(): void
    {
        $plan = $this->planner('26.3')->plan(
            'diamond_sword',
            ['sharpness' => 5, 'looting' => 3, 'mending' => 1],
            ['mending' => 1, 'looting' => 2]
        );

        $this->assertSame(2, $plan['books']);
        $this->assertSame(['looting', 'sharpness'], array_column($plan['book_list'], 'id'));
        $this->assertSame(12, $plan['total_levels']);
        $this->assertSame(['looting' => 3, 'mending' => 1, 'sharpness' => 5], array_column($plan['result']['enchantments'], 'level', 'id'));
    }

    public function test_nothing_to_do_when_everything_is_present(): void
    {
        $plan = $this->planner('26.3')->plan('diamond_sword', ['mending' => 1], ['mending' => 1]);

        $this->assertTrue($plan['possible']);
        $this->assertSame([], $plan['steps']);
        $this->assertSame(0, $plan['total_levels']);
        $this->assertSame(0, $plan['books']);
    }

    public function test_impossible_selection_names_the_failing_step(): void
    {
        // item already went through the anvil five times: penalty 31 before any book
        $plan = $this->planner('26.3')->plan('diamond_sword', ['sharpness' => 5, 'looting' => 3], [], 5);

        $this->assertFalse($plan['possible']);
        $this->assertSame(2, $plan['failing_step']);
        $this->assertSame([5, 43], array_column($plan['steps'], 'cost'));
        $this->assertTrue($plan['steps'][1]['too_expensive']);
        $this->assertFalse($plan['steps'][0]['too_expensive']);
        $this->assertSame(48, $plan['total_levels']);
    }

    public function test_exclusive_enchantments_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->planner('26.3')->plan('diamond_sword', ['sharpness' => 5, 'smite' => 5]);
    }

    public function test_exclusive_with_a_present_enchantment_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->planner('26.3')->plan('bow', ['infinity' => 1], ['mending' => 1]);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $planner = $this->planner('26.3');
        $invalid = [
            fn () => $planner->plan('stick', ['unbreaking' => 3]),
            fn () => $planner->plan('diamond_sword', ['efficiency' => 5]),
            fn () => $planner->plan('diamond_sword', ['sharpness' => 6]),
            fn () => $planner->plan('diamond_sword', ['sharpness' => 0]),
            fn () => $planner->plan('diamond_sword', ['unknown' => 1]),
            fn () => $planner->plan('diamond_sword', ['sharpness' => 5], [], -1),
        ];

        foreach ($invalid as $i => $call) {
            try {
                $call();
                $this->fail("case {$i} was accepted");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_dynamic_program_matches_exhaustive_search(): void
    {
        mt_srand(20261015);

        foreach (['1.21', '26.3'] as $version) {
            $data = EnchantmentData::load($version);
            $planner = new AnvilPlanner($data);
            $items = array_values($data->items());

            for ($run = 0; $run < 25; $run++) {
                $item = $items[mt_rand(0, count($items) - 1)];
                [$wanted, $present] = $this->randomSelection($data, $item, 5);
                $priorWork = mt_rand(0, 3);

                $plan = $planner->plan($item['id'], $wanted, $present, $priorWork);
                $expected = $this->exhaustiveBest($data, $wanted, $present, $priorWork, $data->levelCap());

                $label = "{$version} {$item['id']} ".json_encode($wanted).' present '.json_encode($present)." work {$priorWork}";
                if ($expected === null) {
                    $this->assertFalse($plan['possible'], $label);
                } else {
                    $this->assertTrue($plan['possible'], $label);
                    $this->assertSame($expected, $plan['total_levels'], $label);
                }
            }
        }
    }

    public function test_largest_valid_selection_is_planned_within_200_ms(): void
    {
        foreach (['1.21', '26.3'] as $version) {
            $data = EnchantmentData::load($version);
            $planner = new AnvilPlanner($data);

            // the item with the most enchantments that can be on it at the same time
            $largest = null;
            foreach ($data->items() as $item) {
                $selection = [];
                foreach ($item['enchantments'] as $id) {
                    foreach (array_keys($selection) as $chosen)
                        if (!$data->areCompatible($id, $chosen))
                            continue 2;
                    $selection[$id] = $data->enchantment($id)['max_level'];
                }
                if ($largest === null || count($selection) > count($largest[1]))
                    $largest = [$item['id'], $selection];
            }

            $this->assertGreaterThanOrEqual(9, count($largest[1]));

            $start = hrtime(true);
            $plan = $planner->plan($largest[0], $largest[1]);
            $elapsedMs = (hrtime(true) - $start) / 1e6;

            $this->assertSame(count($largest[1]), $plan['books']);
            $this->assertLessThan(200, $elapsedMs, "{$version} {$largest[0]} took {$elapsedMs} ms");
        }
    }

    public function test_xp_conversion(): void
    {
        $this->assertSame(0, Experience::pointsForLevel(0));
        $this->assertSame(160, Experience::pointsForLevel(10));
        $this->assertSame(352, Experience::pointsForLevel(16));
        $this->assertSame(394, Experience::pointsForLevel(17));
        $this->assertSame(1395, Experience::pointsForLevel(30));
        $this->assertSame(1507, Experience::pointsForLevel(31));
        $this->assertSame(1628, Experience::pointsForLevel(32));
        $this->assertSame(2727, Experience::pointsForLevel(39));

        $plan = $this->planner('26.3')->plan('diamond_sword', self::FULL_SWORD);
        $mobs = array_column($plan['xp_sources']['mobs'], null, 'id');
        $cactus = array_column($plan['xp_sources']['smelting'], null, 'id')['cactus'];

        $this->assertSame(840, $plan['total_xp_points']);
        $this->assertSame(168, $mobs['zombie']['count']);
        $this->assertSame(84, $mobs['blaze']['count']);
        $this->assertSame(840, $cactus['count']);
    }

    /**
     * Random compatible selection for an item: wanted books and enchantments already present.
     */
    private function randomSelection(EnchantmentData $data, array $item, int $maxBooks): array
    {
        $ids = $item['enchantments'];
        shuffle($ids);

        $wanted = [];
        $present = [];
        foreach ($ids as $id) {
            foreach (array_keys($wanted + $present) as $chosen)
                if (!$data->areCompatible($id, $chosen))
                    continue 2;

            $level = mt_rand(1, $data->enchantment($id)['max_level']);
            if (mt_rand(0, 4) === 0)
                $present[$id] = $level;
            elseif (count($wanted) < $maxBooks)
                $wanted[$id] = $level;
        }

        return [$wanted, $present];
    }

    /**
     * Brute force over every merge order: returns the cheapest total or null when every order hits the cap.
     */
    private function exhaustiveBest(EnchantmentData $data, array $wanted, array $present, int $priorWork, int $cap): ?int
    {
        $costs = [];
        foreach ($wanted as $id => $level)
            if (($present[$id] ?? 0) < $level)
                $costs[] = $data->enchantment($id)['book_multiplier'] * $level;

        // node: [isItem, enchantment cost of its books, prior work]
        $nodes = [[1, 0, $priorWork]];
        foreach ($costs as $cost)
            $nodes[] = [0, $cost, 0];

        $memo = [];
        $search = function (array $nodes) use (&$search, &$memo, $cap): ?int {
            if (count($nodes) === 1)
                return 0;

            $sorted = $nodes;
            sort($sorted);
            $key = json_encode($sorted);
            if (array_key_exists($key, $memo))
                return $memo[$key];

            $best = null;
            foreach ($nodes as $i => $left) {
                foreach ($nodes as $j => $right) {
                    if ($i === $j || $right[0] === 1)
                        continue;

                    $step = (2 ** $left[2] - 1) + (2 ** $right[2] - 1) + $right[1];
                    if ($step > $cap)
                        continue;

                    $rest = $nodes;
                    unset($rest[$i], $rest[$j]);
                    $rest[] = [$left[0], $left[1] + $right[1], max($left[2], $right[2]) + 1];

                    $sub = $search(array_values($rest));
                    if ($sub !== null && ($best === null || $step + $sub < $best))
                        $best = $step + $sub;
                }
            }

            return $memo[$key] = $best;
        };

        return $search($nodes);
    }
}
