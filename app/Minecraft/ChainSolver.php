<?php

namespace App\Minecraft;

/**
 * Automatic mode of the farm chain planner: a linear program that picks the
 * recipes for a whole chain so the total number of farm modules is as small
 * as possible.
 *
 * Variables: crafts per hour of every recipe that can take part in the chain
 * and items per hour taken from every raw resource. For every item the
 * production minus the consumption (inputs and fuel) must cover the demand
 * (the target rate for the target, zero for the rest). The objective is the
 * sum of raw items per hour divided by the module rate of their farm design,
 * i.e. the fractional number of farm modules.
 *
 * The LP is solved with a dense two-phase simplex using Bland's rule, so it
 * cannot cycle; an iteration cap still guards against bad input. Loops in the
 * recipes are fine for the LP: a loop without a way down to a raw resource
 * makes phase 1 infeasible and is reported, not looped over.
 */
final class ChainSolver
{
    public const OPTIMAL = 'optimal';
    public const INFEASIBLE = 'infeasible';
    public const UNBOUNDED = 'unbounded';
    public const ITERATION_LIMIT = 'iteration_limit';

    /** Used for raw resources from a farm whose module rate is not published. */
    public const FALLBACK_MODULE_RATE = 1000.0;

    /**
     * Used for raw resources no farm design makes (gathered, mined or traded
     * by hand): a heavy weight, so the solver only uses them when nothing else
     * makes the item.
     */
    public const BY_HAND_MODULE_RATE = 1.0;

    private const EPS = 1e-9;

    private const MAX_ITERATIONS = 5000;

    /**
     * Minimise c·x subject to A·x >= b and x >= 0, with b >= 0.
     *
     * @param float[] $c objective per variable
     * @param array<int, float[]> $a constraint rows
     * @param float[] $b right-hand sides (not negative)
     * @return array{status: string, x: float[], objective: float|null, iterations: int}
     */
    public static function minimize(array $c, array $a, array $b): array
    {
        $n = count($c);
        $m = count($a);

        // Rows with b = 0 become -A·x + e = 0 with e as the basic slack. Rows with
        // b > 0 become A·x - s + r = b with an artificial r as the basic variable.
        $artificialRows = [];
        foreach ($b as $i => $value)
            if ($value > self::EPS)
                $artificialRows[] = $i;

        // columns: x (n), one slack/surplus per row (m), artificials (k), then rhs
        $k = count($artificialRows);
        $width = $n + $m + $k;
        $tableau = [];
        $basis = [];
        foreach ($a as $i => $row) {
            $line = array_fill(0, $width + 1, 0.0);
            $positive = $b[$i] > self::EPS;
            for ($j = 0; $j < $n; $j++)
                $line[$j] = $positive ? (float) $row[$j] : -(float) $row[$j];
            $line[$n + $i] = $positive ? -1.0 : 1.0;
            if ($positive) {
                $col = $n + $m + array_search($i, $artificialRows, true);
                $line[$col] = 1.0;
                $basis[$i] = $col;
            } else {
                $basis[$i] = $n + $i;
            }
            $line[$width] = $positive ? (float) $b[$i] : 0.0;
            $tableau[$i] = $line;
        }

        $iterations = 0;

        // phase 1: minimise the sum of the artificials
        if ($k > 0) {
            $phase1 = array_fill(0, $width, 0.0);
            for ($col = $n + $m; $col < $width; $col++)
                $phase1[$col] = 1.0;
            $status = self::run($tableau, $basis, $phase1, $width, $width, $iterations);
            if ($status !== self::OPTIMAL)
                return self::result($status, [], null, $iterations);
            if (self::objectiveValue($tableau, $basis, $phase1, $width) > 1e-7)
                return self::result(self::INFEASIBLE, [], null, $iterations);

            // drive artificials still in the basis (at zero) out where possible
            foreach ($basis as $i => $col) {
                if ($col < $n + $m)
                    continue;
                for ($j = 0; $j < $n + $m; $j++) {
                    if (abs($tableau[$i][$j]) > self::EPS) {
                        self::pivot($tableau, $basis, $i, $j, $width);
                        break;
                    }
                }
            }
        }

        // phase 2: real objective, artificial columns may not enter
        $cost = array_fill(0, $width, 0.0);
        for ($j = 0; $j < $n; $j++)
            $cost[$j] = (float) $c[$j];
        $status = self::run($tableau, $basis, $cost, $width, $n + $m, $iterations);
        if ($status !== self::OPTIMAL)
            return self::result($status, [], null, $iterations);

        $x = array_fill(0, $n, 0.0);
        foreach ($basis as $i => $col)
            if ($col < $n)
                $x[$col] = max(0.0, $tableau[$i][$width]);

        $objective = 0.0;
        for ($j = 0; $j < $n; $j++)
            $objective += $c[$j] * $x[$j];

        return self::result(self::OPTIMAL, $x, $objective, $iterations);
    }

    /**
     * Pick the recipes for a chain with the fewest farm modules.
     *
     * $moduleRates gives the items per hour of one module for raw resources
     * (null = unknown: FALLBACK_MODULE_RATE for farm items, BY_HAND_MODULE_RATE
     * for items no farm makes). Only items in $sourceItems may be taken as raw
     * resources; the value says whether a farm design makes the item. The
     * reported module count only sums raw resources with a known rate.
     *
     * @param array<string, float|null> $moduleRates
     * @param array<string, bool> $sourceItems item => made by a farm design
     * @return array{status: string, choices: array<string, string>, modules: float|null, estimated: string[], message: string|null}
     */
    public static function solveChain(RecipeData $data, string $target, float $rate, string $fuel, array $moduleRates, array $sourceItems): array
    {
        $fuelShare = 1 / $data->fuels()[$fuel];

        // everything that can take part: walk the inputs of every recipe backwards from the target
        $items = [];
        $recipes = [];
        $queue = [$target];
        while ($queue !== []) {
            $item = array_shift($queue);
            if (isset($items[$item]))
                continue;
            $items[$item] = count($items);
            foreach ($data->recipesFor($item) as $recipe) {
                $recipes[$recipe['id']] = $recipe;
                foreach ($recipe['inputs'] as $input)
                    $queue[] = $input['item'];
                if (RecipeData::isCooking($recipe))
                    $queue[] = $fuel;
            }
        }

        // variables: recipes, then sources
        $columns = [];
        foreach ($recipes as $id => $recipe)
            $columns[] = ['recipe', $id];
        $estimated = [];
        foreach ($items as $item => $row) {
            if (!array_key_exists($item, $sourceItems))
                continue;
            $columns[] = ['source', $item];
        }

        $known = [];
        $m = count($items);
        $a = array_fill(0, $m, array_fill(0, count($columns), 0.0));
        $c = array_fill(0, count($columns), 0.0);
        $b = array_fill(0, $m, 0.0);
        $b[$items[$target]] = $rate;

        foreach ($columns as $j => [$kind, $key]) {
            if ($kind === 'recipe') {
                $recipe = $recipes[$key];
                $a[$items[$recipe['output']['item']]][$j] += $recipe['output']['count'];
                foreach ($recipe['inputs'] as $input)
                    $a[$items[$input['item']]][$j] -= $input['count'];
                if (RecipeData::isCooking($recipe))
                    $a[$items[$fuel]][$j] -= $fuelShare;
            } else {
                $a[$items[$key]][$j] = 1.0;
                $moduleRate = $moduleRates[$key] ?? null;
                if ($moduleRate === null || $moduleRate <= 0) {
                    $estimated[] = $key;
                    $c[$j] = 1 / ($sourceItems[$key] ? self::FALLBACK_MODULE_RATE : self::BY_HAND_MODULE_RATE);
                } else {
                    $known[$j] = 1 / $moduleRate;
                    $c[$j] = $known[$j];
                }
            }
        }

        $solution = self::minimize($c, $a, $b);
        if ($solution['status'] !== self::OPTIMAL) {
            return [
                'status' => $solution['status'],
                'choices' => [],
                'modules' => null,
                'estimated' => $estimated,
                'message' => match ($solution['status']) {
                    self::INFEASIBLE => 'The recipes form a loop that never reaches a raw resource.',
                    self::UNBOUNDED => 'The recipes form a loop that makes items out of nothing.',
                    default => 'The solver did not finish.',
                },
            ];
        }

        $modules = 0.0;
        foreach ($known as $j => $weight)
            $modules += $weight * $solution['x'][$j];

        // per item the option that delivers most of it (without caps the optimum uses one option per item)
        $best = [];
        foreach ($columns as $j => [$kind, $key]) {
            $value = $solution['x'][$j];
            if ($value <= self::EPS)
                continue;
            $item = $kind === 'recipe' ? $recipes[$key]['output']['item'] : $key;
            $amount = $kind === 'recipe' ? $value * $recipes[$key]['output']['count'] : $value;
            if (!isset($best[$item]) || $amount > $best[$item][1] + self::EPS)
                $best[$item] = [$kind === 'recipe' ? $key : ChainPlanner::SOURCE, $amount];
        }

        return [
            'status' => self::OPTIMAL,
            'choices' => array_map(fn (array $entry) => $entry[0], $best),
            'modules' => $modules,
            'estimated' => array_values(array_intersect($estimated, array_keys($best))),
            'message' => null,
        ];
    }

    /**
     * Simplex iterations on the tableau for the given cost vector. Only
     * columns below $enterLimit may enter the basis.
     */
    private static function run(array &$tableau, array &$basis, array $cost, int $width, int $enterLimit, int &$iterations): string
    {
        $m = count($tableau);
        while (true) {
            if (++$iterations > self::MAX_ITERATIONS)
                return self::ITERATION_LIMIT;

            // reduced costs; Bland's rule: lowest index with negative reduced cost enters
            $entering = null;
            for ($j = 0; $j < $enterLimit; $j++) {
                if (in_array($j, $basis, true))
                    continue;
                $reduced = $cost[$j];
                for ($i = 0; $i < $m; $i++)
                    $reduced -= $cost[$basis[$i]] * $tableau[$i][$j];
                if ($reduced < -self::EPS) {
                    $entering = $j;
                    break;
                }
            }
            if ($entering === null)
                return self::OPTIMAL;

            // ratio test; ties broken by the lowest basic column (Bland)
            $leaving = null;
            $bestRatio = INF;
            for ($i = 0; $i < $m; $i++) {
                $coefficient = $tableau[$i][$entering];
                if ($coefficient <= self::EPS)
                    continue;
                $ratio = $tableau[$i][$width] / $coefficient;
                if ($ratio < $bestRatio - self::EPS
                    || (abs($ratio - $bestRatio) <= self::EPS && $basis[$i] < $basis[$leaving])) {
                    $bestRatio = $ratio;
                    $leaving = $i;
                }
            }
            if ($leaving === null)
                return self::UNBOUNDED;

            self::pivot($tableau, $basis, $leaving, $entering, $width);
        }
    }

    private static function pivot(array &$tableau, array &$basis, int $row, int $col, int $width): void
    {
        $pivot = $tableau[$row][$col];
        for ($j = 0; $j <= $width; $j++)
            $tableau[$row][$j] /= $pivot;

        foreach ($tableau as $i => $line) {
            if ($i === $row)
                continue;
            $factor = $line[$col];
            if (abs($factor) <= 1e-15)
                continue;
            for ($j = 0; $j <= $width; $j++)
                $tableau[$i][$j] -= $factor * $tableau[$row][$j];
        }

        $basis[$row] = $col;
    }

    private static function objectiveValue(array $tableau, array $basis, array $cost, int $width): float
    {
        $value = 0.0;
        foreach ($basis as $i => $col)
            $value += $cost[$col] * $tableau[$i][$width];

        return $value;
    }

    private static function result(string $status, array $x, ?float $objective, int $iterations): array
    {
        return ['status' => $status, 'x' => $x, 'objective' => $objective, 'iterations' => $iterations];
    }
}
