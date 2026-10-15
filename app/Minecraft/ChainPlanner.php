<?php

namespace App\Minecraft;

use InvalidArgumentException;

/**
 * Farm chain planner: a Factorio-style production calculator for Minecraft.
 *
 * Given a target item and a rate (items per hour) it walks the recipe tree
 * down to raw resources and returns per node the recipe used, items and
 * crafts per hour, furnaces and fuel (cooking) or villagers (trading), per
 * raw resource the farm design and the module count ceil(rate / module rate),
 * and per edge the hopper lines the flow needs.
 *
 * Each item has options: its recipes and 'source' (take it as a raw resource,
 * from a farm design of the farm catalogue or by hand). The default option is
 * the one with the fewest raw items per item (villager trades only when
 * nothing else makes the item); 'source' is a default only for items without
 * a recipe or with a farm design. In automatic mode ChainSolver
 * picks the options that need the fewest farm modules.
 *
 * With the options fixed the chain is a linear system p = d + A·p (p = items
 * per hour per item, A = items consumed per item made), solved directly, so
 * loops that pay back (dried kelp blocks fuelling the kelp smelters) work; a
 * loop that does not reach a raw resource is reported as ChainLoopException.
 *
 * The class only takes the version data; it has no link to game files, the
 * client or a server.
 */
final class ChainPlanner
{
    public const SOURCE = 'source';

    public const MODES = ['manual', 'auto'];

    /** Items per hour of one furnace, blast furnace or smoker at 20 TPS (10 s / 5 s per item). */
    public const FURNACE_RATES = ['smelting' => 360.0, 'blasting' => 720.0, 'smoking' => 720.0];

    public const MACHINES = ['smelting' => 'furnace', 'blasting' => 'blast_furnace', 'smoking' => 'smoker', 'trading' => 'villager'];

    /** Upper limit of tree rows, so a deep chain cannot blow up the response. */
    public const MAX_TREE_ROWS = 400;

    public const MAX_RATE = 10_000_000;

    private const EPS = 1e-9;

    /** @var array<string, array<string, float>> raw costs per fuel */
    private array $costCache = [];

    /** @var array<string, array<string, string>> default choices per fuel */
    private array $choiceCache = [];

    public function __construct(
        private readonly RecipeData $recipes,
        private readonly FarmCatalog $farms,
    ) {
    }

    /**
     * Plan a chain. Options (all optional):
     *  - mode: 'manual' (default) or 'auto' (LP solver, choices are ignored)
     *  - choices: [item => recipe id | 'source']
     *  - designs: [item => farm design id] for raw resources
     *  - module_rates: [item => items per hour of one module] overrides
     *  - furnace_rates: ['smelting'|'blasting'|'smoking' => items per hour] overrides
     *  - restocks_per_day: villager restocks per Minecraft day (default from the data, 2)
     *  - fuel: fuel item for cooking (default from the data, coal)
     *
     * @throws InvalidArgumentException on unknown items, recipes, designs or bad numbers
     * @throws ChainLoopException when the chosen recipes loop without a raw resource
     */
    public function plan(string $target, float $rate, array $options = []): array
    {
        if (!$this->recipes->hasItem($target))
            throw new InvalidArgumentException("Unknown item '{$target}'.");
        if (!($rate > 0) || $rate > self::MAX_RATE)
            throw new InvalidArgumentException('The rate must be between 0 and '.self::MAX_RATE.' items per hour.');

        $mode = $options['mode'] ?? 'manual';
        if (!in_array($mode, self::MODES, true))
            throw new InvalidArgumentException("Unknown mode '{$mode}'.");

        $defaults = $this->recipes->defaults();
        $fuel = $options['fuel'] ?? $defaults['fuel'];
        if (!isset($this->recipes->fuels()[$fuel]))
            throw new InvalidArgumentException("Unknown fuel '{$fuel}'.");

        $restocks = (float) ($options['restocks_per_day'] ?? $defaults['restocks_per_day']);
        if (!($restocks > 0))
            throw new InvalidArgumentException('Restocks per day must be positive.');

        $furnaceRates = self::FURNACE_RATES;
        foreach ($options['furnace_rates'] ?? [] as $type => $value) {
            if (!isset($furnaceRates[$type]) || !is_numeric($value) || $value <= 0)
                throw new InvalidArgumentException("Invalid furnace rate for '{$type}'.");
            $furnaceRates[$type] = (float) $value;
        }

        $designs = $options['designs'] ?? [];
        foreach ($designs as $item => $designId) {
            if (!in_array($designId, array_column($this->farms->designsProducing((string) $item), 'id'), true))
                throw new InvalidArgumentException("Farm design '{$designId}' does not make '{$item}'.");
        }

        $moduleRates = [];
        foreach ($options['module_rates'] ?? [] as $item => $value) {
            if (!$this->recipes->hasItem((string) $item) || !is_numeric($value) || $value <= 0)
                throw new InvalidArgumentException("Invalid module rate for '{$item}'.");
            $moduleRates[$item] = (float) $value;
        }

        $choices = $options['choices'] ?? [];
        foreach ($choices as $item => $choice)
            if (!$this->recipes->hasItem((string) $item) || !$this->isOption((string) $item, (string) $choice))
                throw new InvalidArgumentException("'{$choice}' is not a way to make '{$item}'.");

        $defaultChoices = $this->defaultChoices($fuel);

        $solver = null;
        if ($mode === 'auto') {
            $solver = $this->solve($target, $rate, $fuel, $designs, $moduleRates);
            $choices = $solver['choices'];
        }

        $choose = fn (string $item) => $choices[$item] ?? $defaultChoices[$item] ?? self::SOURCE;

        // nodes reachable from the target with the chosen options
        $order = [];
        $queue = [$target];
        while ($queue !== []) {
            $item = array_shift($queue);
            if (isset($order[$item]))
                continue;
            $order[$item] = count($order);
            $recipe = $this->chosenRecipe($item, $choose($item));
            if ($recipe === null)
                continue;
            foreach ($recipe['inputs'] as $input)
                $queue[] = $input['item'];
            if (RecipeData::isCooking($recipe))
                $queue[] = $fuel;
        }

        // A[i][j]: items of i used per item of j made
        $fuelShare = 1 / $this->recipes->fuels()[$fuel];
        $usage = [];
        foreach ($order as $item => $j) {
            $recipe = $this->chosenRecipe($item, $choose($item));
            if ($recipe === null)
                continue;
            $per = 1 / $recipe['output']['count'];
            foreach ($recipe['inputs'] as $input)
                $usage[$input['item']][$item] = ($usage[$input['item']][$item] ?? 0) + $input['count'] * $per;
            if (RecipeData::isCooking($recipe))
                $usage[$fuel][$item] = ($usage[$fuel][$item] ?? 0) + $fuelShare * $per;
        }

        $perHour = $this->solveRates($order, $usage, $target, $rate);

        // nodes
        $nodes = [];
        $sources = [];
        $edges = [];
        $totals = ['modules' => 0, 'modules_complete' => true, 'furnaces' => 0, 'villagers' => 0, 'hopper_lines' => 0];
        foreach ($order as $item => $j) {
            $choice = $choose($item);
            $recipe = $this->chosenRecipe($item, $choice);
            $itemsPerHour = $perHour[$item];

            $node = [
                'item' => $item,
                'name' => $this->recipes->itemName($item),
                'kind' => $recipe === null ? 'source' : 'recipe',
                'choice' => $choice,
                'default_choice' => $defaultChoices[$item] ?? self::SOURCE,
                'chosen_by' => $mode === 'auto' ? 'solver' : (isset($choices[$item]) && $choices[$item] !== ($defaultChoices[$item] ?? self::SOURCE) ? 'user' : 'default'),
                'options' => $this->options($item, $fuel),
                'items_per_hour' => self::round($itemsPerHour),
                'recipe' => null,
                'crafts_per_hour' => null,
                'machine' => null,
                'fuel' => null,
            ];

            if ($recipe !== null) {
                $crafts = $itemsPerHour / $recipe['output']['count'];
                $node['recipe'] = $this->presentRecipe($recipe);
                $node['crafts_per_hour'] = self::round($crafts);

                if (RecipeData::isCooking($recipe)) {
                    $perMachine = $furnaceRates[$recipe['type']];
                    $count = self::ceil($crafts / $perMachine);
                    $node['machine'] = [
                        'type' => self::MACHINES[$recipe['type']],
                        'count' => $count,
                        'per_machine' => self::round($perMachine),
                        'overridden' => $perMachine !== self::FURNACE_RATES[$recipe['type']],
                    ];
                    $node['fuel'] = [
                        'item' => $fuel,
                        'name' => $this->recipes->itemName($fuel),
                        'per_hour' => self::round($crafts * $fuelShare),
                    ];
                    $totals['furnaces'] += $count;
                } elseif ($recipe['type'] === 'trading') {
                    // max uses per restock, restocks per Minecraft day, three days per hour
                    $perMachine = $recipe['max_uses'] * $restocks * (60 / RecipeData::DAY_MINUTES);
                    $count = self::ceil($crafts / $perMachine);
                    $node['machine'] = [
                        'type' => 'villager',
                        'count' => $count,
                        'per_machine' => self::round($perMachine),
                        'overridden' => $restocks !== (float) $defaults['restocks_per_day'],
                    ];
                    $totals['villagers'] += $count;
                }
            } else {
                $source = $this->source($item, $itemsPerHour, $designs[$item] ?? null, $moduleRates[$item] ?? null);
                $sources[] = $source;
                if ($source['modules'] === null)
                    $totals['modules_complete'] = false;
                else
                    $totals['modules'] += $source['modules'];
            }

            $nodes[] = $node;
        }

        // edges: input -> consumer with the flow and the hopper lines it needs
        $hopperRate = (float) $defaults['hopper_items_per_hour'];
        foreach ($usage as $input => $consumers) {
            foreach ($consumers as $consumer => $coefficient) {
                $flow = $coefficient * $perHour[$consumer];
                if ($flow <= self::EPS)
                    continue;
                $lines = self::ceil($flow / $hopperRate);
                $edges[] = [
                    'from' => $input,
                    'to' => $consumer,
                    'items_per_hour' => self::round($flow),
                    'hopper_lines' => $lines,
                    'fuel' => $input === $fuel && $this->isFuelOnly($input, $consumer, $choose),
                ];
                $totals['hopper_lines'] += $lines;
            }
        }

        usort($sources, fn (array $a, array $b) => $b['items_per_hour'] <=> $a['items_per_hour']);

        return [
            'version' => $this->recipes->version(),
            'target' => ['item' => $target, 'name' => $this->recipes->itemName($target), 'items_per_hour' => self::round($rate)],
            'mode' => $mode,
            'fuel' => $fuel,
            'settings' => [
                'furnace_rates' => $furnaceRates,
                'furnace_defaults' => self::FURNACE_RATES,
                'restocks_per_day' => $restocks,
                'restocks_default' => (float) $defaults['restocks_per_day'],
                'hopper_items_per_hour' => $hopperRate,
            ],
            'nodes' => $nodes,
            'sources' => $sources,
            'edges' => $edges,
            'tree' => $this->tree($target, $perHour, $usage, $choose),
            'totals' => $totals,
            'solver' => $solver === null ? null : [
                'status' => $solver['status'],
                'modules' => $solver['modules'] === null ? null : self::round($solver['modules']),
                'estimated' => $solver['estimated'],
                'fallback_module_rate' => ChainSolver::FALLBACK_MODULE_RATE,
                'by_hand_module_rate' => ChainSolver::BY_HAND_MODULE_RATE,
            ],
        ];
    }

    /**
     * Default option per item: the fewest raw items per item made. Ties keep
     * 'source'; an option only wins when it is strictly cheaper, so the
     * defaults never loop. Villager trades are only a default when no other
     * option reaches raw resources.
     *
     * @return array<string, string>
     */
    public function defaultChoices(string $fuel): array
    {
        if (isset($this->choiceCache[$fuel]))
            return $this->choiceCache[$fuel];

        $all = array_values($this->recipes->recipes());
        $withoutTrades = array_values(array_filter($all, fn (array $recipe) => $recipe['type'] !== 'trading'));
        $sourceCost = fn (string $item) => $this->defaultSourceCost($item);

        $first = RecipeData::cheapest($withoutTrades, $sourceCost, $fuel, $this->recipes->fuels());
        $second = RecipeData::cheapest($all, $sourceCost, $fuel, $this->recipes->fuels());

        $choices = [];
        foreach (array_keys($this->recipes->items()) as $item) {
            if (is_finite($first['costs'][$item] ?? $sourceCost($item)))
                $choices[$item] = $first['choices'][$item] ?? self::SOURCE;
            else
                $choices[$item] = $second['choices'][$item] ?? self::SOURCE;
        }

        return $this->choiceCache[$fuel] = $choices;
    }

    /**
     * Raw items per item made, cheapest way, for every item.
     *
     * @return array<string, float>
     */
    public function rawCosts(string $fuel): array
    {
        $costs = RecipeData::rawCosts(
            array_values($this->recipes->recipes()),
            fn (string $item) => $this->defaultSourceCost($item),
            $fuel,
            $this->recipes->fuels(),
        );

        foreach (array_keys($this->recipes->items()) as $item)
            $costs[$item] ??= $this->defaultSourceCost($item);

        return $costs;
    }

    /**
     * Options of an item for the client: recipes plus 'source', each with its
     * raw items per item made.
     */
    public function options(string $item, string $fuel): array
    {
        $costs = $this->costCache[$fuel] ??= $this->rawCosts($fuel);
        $fuelShare = 1 / $this->recipes->fuels()[$fuel];

        $options = [];
        foreach ($this->recipes->recipesFor($item) as $recipe) {
            $cost = $this->recipeCost($recipe, $costs, $fuel, $fuelShare);
            $options[] = [
                'id' => $recipe['id'],
                'type' => $recipe['type'],
                'label' => $this->recipeLabel($recipe),
                'raw_items' => is_finite($cost) ? self::round($cost) : null,
            ];
        }
        $options[] = [
            'id' => self::SOURCE,
            'type' => self::SOURCE,
            'label' => $this->farms->designsProducing($item) === [] ? 'raw resource' : 'farm',
            'raw_items' => 1.0,
        ];

        return $options;
    }

    private function solve(string $target, float $rate, string $fuel, array $designs, array $moduleRates): array
    {
        $rates = [];
        $sourceItems = [];
        foreach (array_keys($this->recipes->items()) as $item) {
            if (!is_finite($this->defaultSourceCost($item)))
                continue;
            $sourceItems[$item] = $this->farms->designsProducing($item) !== [];
            $rates[$item] = $moduleRates[$item] ?? $this->designRate($item, $designs[$item] ?? null);
        }

        $result = ChainSolver::solveChain($this->recipes, $target, $rate, $fuel, $rates, $sourceItems);
        if ($result['status'] !== ChainSolver::OPTIMAL)
            throw new ChainLoopException($result['message'], [$target], $result['status']);

        return $result;
    }

    /**
     * Solve (I - A)·p = d with Gaussian elimination (partial pivoting).
     *
     * @return array<string, float> items per hour per item
     * @throws ChainLoopException when the system has no non-negative solution
     */
    private function solveRates(array $order, array $usage, string $target, float $rate): array
    {
        $items = array_keys($order);
        $n = count($items);
        $matrix = [];
        foreach ($items as $i => $item) {
            $row = array_fill(0, $n + 1, 0.0);
            $row[$i] = 1.0;
            foreach ($usage[$item] ?? [] as $consumer => $coefficient)
                $row[$order[$consumer]] -= $coefficient;
            $row[$n] = $item === $target ? $rate : 0.0;
            $matrix[$i] = $row;
        }

        for ($col = 0; $col < $n; $col++) {
            $pivot = $col;
            for ($row = $col + 1; $row < $n; $row++)
                if (abs($matrix[$row][$col]) > abs($matrix[$pivot][$col]))
                    $pivot = $row;
            if (abs($matrix[$pivot][$col]) < 1e-12)
                throw new ChainLoopException('The chosen recipes form a loop that never reaches a raw resource.', $this->loopItems($order, $usage));
            if ($pivot !== $col)
                [$matrix[$col], $matrix[$pivot]] = [$matrix[$pivot], $matrix[$col]];

            for ($row = 0; $row < $n; $row++) {
                if ($row === $col)
                    continue;
                $factor = $matrix[$row][$col] / $matrix[$col][$col];
                if ($factor == 0.0)
                    continue;
                for ($k = $col; $k <= $n; $k++)
                    $matrix[$row][$k] -= $factor * $matrix[$col][$k];
            }
        }

        $rates = [];
        foreach ($items as $i => $item) {
            $value = $matrix[$i][$n] / $matrix[$i][$i];
            if (!is_finite($value) || $value < -1e-6 || $value > self::MAX_RATE * 1e6)
                throw new ChainLoopException('The chosen recipes form a loop that uses up more than it makes.', $this->loopItems($order, $usage));
            $rates[$item] = max(0.0, $value);
        }

        return $rates;
    }

    /**
     * Items on a cycle of the chosen recipes (Tarjan's strongly connected components).
     *
     * @return string[]
     */
    private function loopItems(array $order, array $usage): array
    {
        $graph = [];
        foreach ($usage as $input => $consumers)
            foreach (array_keys($consumers) as $consumer)
                $graph[$consumer][] = $input;

        $index = 0;
        $stack = [];
        $indices = [];
        $low = [];
        $onStack = [];
        $loops = [];
        $visit = function (string $item) use (&$visit, &$index, &$stack, &$indices, &$low, &$onStack, &$loops, $graph) {
            $indices[$item] = $low[$item] = $index++;
            $stack[] = $item;
            $onStack[$item] = true;
            foreach ($graph[$item] ?? [] as $next) {
                if (!isset($indices[$next])) {
                    $visit($next);
                    $low[$item] = min($low[$item], $low[$next]);
                } elseif ($onStack[$next] ?? false) {
                    $low[$item] = min($low[$item], $indices[$next]);
                }
            }
            if ($low[$item] === $indices[$item]) {
                $component = [];
                do {
                    $member = array_pop($stack);
                    $onStack[$member] = false;
                    $component[] = $member;
                } while ($member !== $item);
                if (count($component) > 1)
                    $loops = array_merge($loops, $component);
            }
        };
        foreach (array_keys($order) as $item)
            if (!isset($indices[$item]))
                $visit($item);

        return $loops;
    }

    /**
     * Indented tree from the target down to raw resources. A node's share of
     * an item is split over its inputs in the same ratio as the whole chain;
     * an item that is already on the path is shown as a loop leaf.
     */
    private function tree(string $target, array $perHour, array $usage, callable $choose): array
    {
        $rows = 0;
        $build = function (string $item, float $amount, array $path) use (&$build, &$rows, $perHour, $usage, $choose): array {
            $rows++;
            $recipe = $this->chosenRecipe($item, $choose($item));
            $node = [
                'item' => $item,
                'name' => $this->recipes->itemName($item),
                'items_per_hour' => self::round($amount),
                'kind' => $recipe === null ? 'source' : 'recipe',
                'recipe_type' => $recipe['type'] ?? null,
                'loop' => false,
                'children' => [],
            ];
            if ($recipe === null)
                return $node;

            $path[$item] = true;
            $share = $perHour[$item] > self::EPS ? $amount / $perHour[$item] : 0.0;
            foreach ($usage as $input => $consumers) {
                if (!isset($consumers[$item]))
                    continue;
                $childAmount = $share * $consumers[$item] * $perHour[$item];
                if (isset($path[$input]) || $rows >= self::MAX_TREE_ROWS) {
                    $rows++;
                    $node['children'][] = [
                        'item' => $input,
                        'name' => $this->recipes->itemName($input),
                        'items_per_hour' => self::round($childAmount),
                        'kind' => 'loop',
                        'recipe_type' => null,
                        'loop' => true,
                        'children' => [],
                    ];
                    continue;
                }
                $node['children'][] = $build($input, $childAmount, $path);
            }

            return $node;
        };

        return $build($target, $perHour[$target], []);
    }

    /**
     * Raw resource row: chosen farm design, module rate (override or the
     * design's rate) and modules = ceil(rate / module rate), null when the
     * rate is unknown.
     */
    private function source(string $item, float $itemsPerHour, ?string $designId, ?float $override): array
    {
        $designs = $this->farms->designsProducing($item);
        $design = null;
        if ($designId !== null)
            $design = $this->farms->design($designId);
        elseif ($designs !== [])
            $design = $designs[0];

        $designRate = $design === null ? null : $this->farms->rate($design['id'], $item);
        $moduleRate = $override ?? $designRate;

        return [
            'item' => $item,
            'name' => $this->recipes->itemName($item),
            'items_per_hour' => self::round($itemsPerHour),
            'design' => $design === null ? null : [
                'id' => $design['id'],
                'name' => $design['name'],
                'type' => $design['type'],
                'per_hour' => $designRate,
                'basis' => current(array_filter($design['produces'], fn ($p) => $p['item'] === $item))['basis'] ?? 'unknown',
                'source_url' => $design['source']['url'],
            ],
            'designs' => array_map(fn (array $d) => [
                'id' => $d['id'],
                'name' => $d['name'],
                'per_hour' => $this->farms->rate($d['id'], $item),
            ], $designs),
            'module_rate' => $moduleRate,
            'module_rate_overridden' => $override !== null,
            'rate_unknown' => $moduleRate === null,
            'no_farm' => $designs === [],
            'modules' => $moduleRate === null ? null : self::ceil($itemsPerHour / $moduleRate),
        ];
    }

    private function designRate(string $item, ?string $designId): ?float
    {
        if ($designId !== null)
            return $this->farms->rate($designId, $item);

        $designs = $this->farms->designsProducing($item);

        return $designs === [] ? null : $this->farms->rate($designs[0]['id'], $item);
    }

    /**
     * Cost of taking an item as a raw resource in the default choice: one raw
     * item when nothing makes it or a farm design does, otherwise not a
     * default (the user can still pick 'source').
     */
    private function defaultSourceCost(string $item): float
    {
        if ($this->recipes->recipesFor($item) === [] || $this->farms->designsProducing($item) !== [])
            return 1.0;

        return INF;
    }

    private function recipeCost(array $recipe, array $costs, string $fuel, float $fuelShare): float
    {
        $cost = 0.0;
        foreach ($recipe['inputs'] as $input)
            $cost += $input['count'] * ($costs[$input['item']] ?? INF);
        if (RecipeData::isCooking($recipe))
            $cost += $fuelShare * ($costs[$fuel] ?? INF);

        return $cost / $recipe['output']['count'];
    }

    private function isOption(string $item, string $choice): bool
    {
        if ($choice === self::SOURCE)
            return true;

        $recipe = $this->recipes->recipe($choice);

        return $recipe !== null && $recipe['output']['item'] === $item;
    }

    private function chosenRecipe(string $item, string $choice): ?array
    {
        return $choice === self::SOURCE ? null : $this->recipes->recipe($choice);
    }

    /** True when the consumer uses the fuel item only as fuel, not as an ingredient. */
    private function isFuelOnly(string $fuel, string $consumer, callable $choose): bool
    {
        $recipe = $this->chosenRecipe($consumer, $choose($consumer));

        return $recipe !== null && !in_array($fuel, array_column($recipe['inputs'], 'item'), true);
    }

    private function presentRecipe(array $recipe): array
    {
        $named = fn (array $entry) => $entry + ['name' => $this->recipes->itemName($entry['item'])];

        return array_filter([
            'id' => $recipe['id'],
            'type' => $recipe['type'],
            'label' => $this->recipeLabel($recipe),
            'output' => $named($recipe['output']),
            'inputs' => array_map($named, $recipe['inputs']),
            'seconds' => $recipe['seconds'] ?? null,
            'profession' => $recipe['profession'] ?? null,
            'level' => $recipe['level'] ?? null,
            'max_uses' => $recipe['max_uses'] ?? null,
        ], fn ($value) => $value !== null);
    }

    /** "3 Paper + 1 Leather → 1 Book" style label. */
    private function recipeLabel(array $recipe): string
    {
        $parts = array_map(fn (array $input) => $input['count'].' '.$this->recipes->itemName($input['item']), $recipe['inputs']);
        $label = implode(' + ', $parts).' → '.$recipe['output']['count'].' '.$this->recipes->itemName($recipe['output']['item']);

        return match ($recipe['type']) {
            'crafting' => $label,
            'trading' => $label.' ('.$recipe['profession'].')',
            default => $label.' ('.$recipe['type'].')',
        };
    }

    private static function ceil(float $value): int
    {
        // 2.0000000001 is 2 machines, not 3
        return (int) ceil($value - 1e-9);
    }

    private static function round(float $value): float
    {
        return round($value, 3);
    }
}
