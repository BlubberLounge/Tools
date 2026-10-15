<?php

namespace App\Minecraft;

use InvalidArgumentException;

/**
 * Recipes of one Minecraft Java version for the farm chain planner:
 * crafting, cooking (smelting, blasting, smoking) and villager trades, plus
 * the fuels and the planner defaults.
 *
 * Every supported version has one hand-maintained JSON file in
 * resources/minecraft/recipes/{version}.json. The file holds only the recipes
 * that farm chains need, not the whole recipe book. Item ids are vanilla ids
 * without namespace, the same convention as the farm catalogue.
 */
final class RecipeData
{
    public const TYPES = ['crafting', 'smelting', 'blasting', 'smoking', 'trading'];

    public const COOKING_TYPES = ['smelting', 'blasting', 'smoking'];

    public const TRADE_LEVELS = ['novice', 'apprentice', 'journeyman', 'expert', 'master'];

    /** Ticks per second at the normal tick rate. */
    public const TICKS_PER_SECOND = 20;

    /** Length of a Minecraft day in minutes. */
    public const DAY_MINUTES = 20;

    private const VERSION_PATTERN = '/^\d+(\.\d+){1,2}$/';

    private const ID_PATTERN = '/^[a-z0-9_]+$/';

    /** Below this many raw items per item a loop makes items out of nothing. */
    private const MIN_COST = 1e-6;

    /** @var array<string, self> loaded files, cached per process */
    private static array $cache = [];

    /** @var array<string, array> */
    private array $items = [];

    /** @var array<string, array> */
    private array $recipes = [];

    /** @var array<string, string[]> recipe ids per output item, in file order */
    private array $producers = [];

    /** @var array<string, float> items smelted per fuel item */
    private array $fuels = [];

    private function __construct(private readonly array $data)
    {
        foreach ($data['items'] as $item)
            $this->items[$item['id']] = $item;

        foreach ($data['recipes'] as $recipe) {
            $this->recipes[$recipe['id']] = $recipe;
            $this->producers[$recipe['output']['item']][] = $recipe['id'];
        }

        foreach ($data['fuels'] as $fuel)
            $this->fuels[$fuel['item']] = (float) $fuel['smelts'];
    }

    public static function directory(): string
    {
        return resource_path('minecraft/recipes');
    }

    /**
     * Available version ids, newest first.
     *
     * @return string[]
     */
    public static function versions(): array
    {
        $versions = [];
        foreach (glob(self::directory().'/*.json') ?: [] as $file) {
            $version = basename($file, '.json');
            if (preg_match(self::VERSION_PATTERN, $version))
                $versions[] = $version;
        }

        usort($versions, fn ($a, $b) => version_compare($b, $a));

        return $versions;
    }

    public static function exists(string $version): bool
    {
        return preg_match(self::VERSION_PATTERN, $version) === 1
            && is_file(self::directory()."/{$version}.json");
    }

    /**
     * Load and validate the recipes of a version.
     *
     * @throws InvalidArgumentException when the version is unknown or the file is invalid
     */
    public static function load(string $version): self
    {
        if (isset(self::$cache[$version]))
            return self::$cache[$version];

        if (!self::exists($version))
            throw new InvalidArgumentException("Unknown Minecraft version '{$version}'.");

        $data = json_decode(file_get_contents(self::directory()."/{$version}.json"), true);
        if (!is_array($data))
            throw new InvalidArgumentException("Recipe data of version '{$version}' is not valid JSON.");

        if (($data['version'] ?? null) !== $version)
            throw new InvalidArgumentException("Recipe data of version '{$version}' declares version '".($data['version'] ?? '')."'.");

        // items a farm design makes are raw resources too (coal, wheat, slime balls)
        $farmItems = FarmCatalog::exists($version) ? array_keys(FarmCatalog::load($version)->items()) : [];

        return self::$cache[$version] = self::fromArray($data, $farmItems);
    }

    /**
     * @param string[] $rawItems items that are raw resources although a recipe makes them (farm items)
     * @throws InvalidArgumentException when the data does not pass validation
     */
    public static function fromArray(array $data, array $rawItems = []): self
    {
        $errors = self::validate($data, $rawItems);
        if ($errors !== [])
            throw new InvalidArgumentException("Invalid recipe data:\n- ".implode("\n- ", $errors));

        return new self($data);
    }

    /**
     * Check a decoded recipe file: known fields, unknown item ids, missing
     * rates (cooking time, trade limits, fuel values) and recipe loops.
     * $rawItems are items that count as raw resources although a recipe makes
     * them (the items of the farm catalogue). Returns a list of problems,
     * empty when valid.
     *
     * @param string[] $rawItems
     * @return string[]
     */
    public static function validate(array $data, array $rawItems = []): array
    {
        $errors = [];

        self::checkKeys($data, ['version', 'name', 'updated', 'sources', 'defaults', 'fuels', 'items', 'recipes'], ['notes'], 'file', $errors);

        if (!is_string($data['version'] ?? null) || !preg_match(self::VERSION_PATTERN, $data['version']))
            $errors[] = 'version must look like 1.21 or 26.3';
        if (!self::isText($data['name'] ?? null))
            $errors[] = 'name must be a non-empty string';
        if (!self::isDate($data['updated'] ?? null))
            $errors[] = 'updated must be a date (YYYY-MM-DD)';
        if (array_key_exists('notes', $data) && !self::isText($data['notes']))
            $errors[] = 'notes must be a non-empty string when present';

        // sources of the file as a whole
        if (!self::isList($data['sources'] ?? null) || $data['sources'] === []) {
            $errors[] = 'sources must name at least one source';
        } else {
            foreach ($data['sources'] as $i => $source) {
                if (!is_array($source)) { $errors[] = "sources[{$i}] must be an object"; continue; }
                self::checkKeys($source, ['title', 'url', 'retrieved'], [], "sources[{$i}]", $errors);
                if (!self::isText($source['title'] ?? null))
                    $errors[] = "sources[{$i}].title must be a non-empty string";
                if (!is_string($source['url'] ?? null) || !preg_match('#^https://[^\s/]+\.[^\s]+$#', $source['url']))
                    $errors[] = "sources[{$i}].url must be an https URL";
                if (!self::isDate($source['retrieved'] ?? null))
                    $errors[] = "sources[{$i}].retrieved must be a date (YYYY-MM-DD)";
            }
        }

        // items
        $itemIds = [];
        if (!self::isList($data['items'] ?? null) || $data['items'] === []) {
            $errors[] = 'items must be a non-empty list';
        } else {
            foreach ($data['items'] as $i => $item) {
                if (!is_array($item)) { $errors[] = "items[{$i}] must be an object"; continue; }
                self::checkKeys($item, ['id', 'name'], [], "items[{$i}]", $errors);
                $id = $item['id'] ?? null;
                if (!is_string($id) || !preg_match(self::ID_PATTERN, $id))
                    $errors[] = "items[{$i}].id must be a lower-case id";
                elseif (isset($itemIds[$id]))
                    $errors[] = "duplicate item id '{$id}'";
                else
                    $itemIds[$id] = true;
                if (!self::isText($item['name'] ?? null))
                    $errors[] = "items[{$i}].name must be a non-empty string";
            }
        }
        $used = [];
        $known = function (mixed $item, string $path) use (&$errors, &$used, $itemIds): bool {
            if (!is_string($item) || !isset($itemIds[$item])) {
                $errors[] = "{$path} names unknown item '".(is_scalar($item) ? $item : '?')."'";
                return false;
            }
            $used[$item] = true;
            return true;
        };

        // fuels
        $fuelIds = [];
        if (!self::isList($data['fuels'] ?? null) || $data['fuels'] === []) {
            $errors[] = 'fuels must be a non-empty list';
        } else {
            foreach ($data['fuels'] as $i => $fuel) {
                if (!is_array($fuel)) { $errors[] = "fuels[{$i}] must be an object"; continue; }
                self::checkKeys($fuel, ['item', 'smelts'], [], "fuels[{$i}]", $errors);
                if ($known($fuel['item'] ?? null, "fuels[{$i}]")) {
                    if (isset($fuelIds[$fuel['item']]))
                        $errors[] = "fuel '{$fuel['item']}' is listed twice";
                    $fuelIds[$fuel['item']] = true;
                }
                if (!self::isPositiveNumber($fuel['smelts'] ?? null))
                    $errors[] = "fuels[{$i}].smelts is missing: give the items one fuel item smelts";
            }
        }

        // planner defaults
        $defaults = $data['defaults'] ?? null;
        if (!is_array($defaults)) {
            $errors[] = 'defaults must be an object';
        } else {
            self::checkKeys($defaults, ['fuel', 'restocks_per_day', 'hopper_items_per_hour'], [], 'defaults', $errors);
            if (!isset($fuelIds[$defaults['fuel'] ?? '']))
                $errors[] = 'defaults.fuel must be one of the fuels';
            if (!self::isPositiveNumber($defaults['restocks_per_day'] ?? null))
                $errors[] = 'defaults.restocks_per_day is missing';
            if (!self::isPositiveNumber($defaults['hopper_items_per_hour'] ?? null))
                $errors[] = 'defaults.hopper_items_per_hour is missing';
        }

        // recipes
        if (!self::isList($data['recipes'] ?? null) || $data['recipes'] === []) {
            $errors[] = 'recipes must be a non-empty list';
            return $errors;
        }

        $recipeIds = [];
        $valid = [];
        foreach ($data['recipes'] as $i => $recipe) {
            if (!is_array($recipe)) { $errors[] = "recipes[{$i}] must be an object"; continue; }
            $path = 'recipes['.(is_string($recipe['id'] ?? null) ? $recipe['id'] : $i).']';
            $type = $recipe['type'] ?? null;
            $optional = match ($type) {
                'smelting', 'blasting', 'smoking' => ['seconds'],
                'trading' => ['profession', 'level', 'max_uses'],
                default => [],
            };
            self::checkKeys($recipe, array_merge(['id', 'type', 'output', 'inputs'], $optional), [], $path, $errors);
            $before = count($errors);

            $id = $recipe['id'] ?? null;
            if (!is_string($id) || !preg_match(self::ID_PATTERN, $id))
                $errors[] = "{$path}.id must be a lower-case id";
            elseif (isset($recipeIds[$id]))
                $errors[] = "duplicate recipe id '{$id}'";
            else
                $recipeIds[$id] = true;

            if (!in_array($type, self::TYPES, true))
                $errors[] = "{$path}.type must be one of: ".implode(', ', self::TYPES);

            $output = $recipe['output'] ?? null;
            if (!is_array($output)) {
                $errors[] = "{$path}.output must be an object {item, count}";
            } else {
                self::checkKeys($output, ['item', 'count'], [], "{$path}.output", $errors);
                $known($output['item'] ?? null, "{$path}.output");
                if (!is_int($output['count'] ?? null) || $output['count'] < 1)
                    $errors[] = "{$path}.output.count must be a positive integer";
            }

            $inputs = $recipe['inputs'] ?? null;
            if (!self::isList($inputs) || $inputs === []) {
                $errors[] = "{$path}.inputs must list at least one item";
            } else {
                $seen = [];
                foreach ($inputs as $j => $input) {
                    if (!is_array($input)) { $errors[] = "{$path}.inputs[{$j}] must be an object"; continue; }
                    self::checkKeys($input, ['item', 'count'], [], "{$path}.inputs[{$j}]", $errors);
                    if ($known($input['item'] ?? null, "{$path}.inputs[{$j}]")) {
                        if (isset($seen[$input['item']]))
                            $errors[] = "{$path}.inputs lists item '{$input['item']}' twice";
                        $seen[$input['item']] = true;
                        if (is_array($output) && ($output['item'] ?? null) === $input['item'])
                            $errors[] = "{$path} uses its own output as input";
                    }
                    if (!is_int($input['count'] ?? null) || $input['count'] < 1)
                        $errors[] = "{$path}.inputs[{$j}].count must be a positive integer";
                }
                if (in_array($type, self::COOKING_TYPES, true) && count($inputs) !== 1)
                    $errors[] = "{$path} is a {$type} recipe and must have exactly one input";
            }

            if (in_array($type, self::COOKING_TYPES, true) && !self::isPositiveNumber($recipe['seconds'] ?? null))
                $errors[] = "{$path}.seconds is missing: give the cooking time per item";

            if ($type === 'trading') {
                if (!self::isText($recipe['profession'] ?? null))
                    $errors[] = "{$path}.profession must be a non-empty string";
                if (!in_array($recipe['level'] ?? null, self::TRADE_LEVELS, true))
                    $errors[] = "{$path}.level must be one of: ".implode(', ', self::TRADE_LEVELS);
                if (!is_int($recipe['max_uses'] ?? null) || $recipe['max_uses'] < 1)
                    $errors[] = "{$path}.max_uses is missing: give the trades per restock";
            }

            if (count($errors) === $before)
                $valid[] = $recipe;
        }

        foreach (array_keys($itemIds) as $itemId)
            if (!isset($used[$itemId]))
                $errors[] = "item '{$itemId}' is not used by any recipe or fuel";

        if ($errors === [])
            foreach (self::loopErrors($valid, $defaults['fuel'], $rawItems) as $error)
                $errors[] = $error;

        return $errors;
    }

    /**
     * Recipe loops that break the planner: an item that can only be made from
     * itself (no way down to raw items) or a loop that makes items out of
     * nothing. Normal loops such as ingot to block and back are fine.
     *
     * @return string[]
     */
    private static function loopErrors(array $recipes, string $fuel, array $rawItems): array
    {
        $produced = [];
        foreach ($recipes as $recipe)
            $produced[$recipe['output']['item']] = true;

        // an item nothing makes or a farm makes is a raw item
        $raw = array_flip($rawItems);
        $sources = fn (string $item) => isset($produced[$item]) && !isset($raw[$item]) ? INF : 1.0;
        $costs = self::rawCosts($recipes, $sources, $fuel);

        $errors = [];
        foreach ($costs as $item => $cost) {
            if (!isset($produced[$item]))
                continue;
            if (is_infinite($cost))
                $errors[] = "recipe loop: '{$item}' cannot be made without itself";
            elseif ($cost < self::MIN_COST)
                $errors[] = "recipe loop: '{$item}' is made out of nothing";
        }

        return $errors;
    }

    /**
     * Cheapest cost (raw items per item made) of every item and the option
     * that reaches it: a recipe id or 'source'. $sourceCost gives the cost of
     * taking an item as a raw resource (INF when that is not allowed); cooking
     * adds the fuel per item.
     *
     * Recipe loops cannot recurse forever here:
     *  1. without fuel, Bellman-Ford style relaxation from INF downwards; an
     *     option only replaces another when it is strictly cheaper, so the
     *     chosen options never form a loop (ingot to block and back);
     *  2. with fuel, the options of step 1 are evaluated by iteration (a fuel
     *     loop that pays back, such as kelp smelted with dried kelp blocks,
     *     converges geometrically; one that does not grows past a cap = INF);
     *  3. value iteration from that upper bound down to the optimum, again
     *     switching options only when strictly cheaper.
     *
     * @param array<int, array> $recipes
     * @param callable(string): float $sourceCost
     * @return array{costs: array<string, float>, choices: array<string, string>}
     */
    public static function cheapest(array $recipes, callable $sourceCost, string $fuel, ?array $fuels = null): array
    {
        $items = [$fuel => true];
        $byOutput = [];
        foreach ($recipes as $recipe) {
            $items[$recipe['output']['item']] = true;
            $byOutput[$recipe['output']['item']][] = $recipe;
            foreach ($recipe['inputs'] as $input)
                $items[$input['item']] = true;
        }

        $source = [];
        $costs = [];
        $choices = [];
        foreach (array_keys($items) as $item) {
            $source[$item] = $sourceCost($item);
            $costs[$item] = $source[$item];
            $choices[$item] = 'source';
        }

        $smelts = $fuels[$fuel] ?? null;
        $fuelShare = $smelts === null ? 0.0 : 1 / $smelts;
        $cost = function (array $recipe, array $values, float $fuelWeight) use ($fuel): float {
            $sum = 0.0;
            foreach ($recipe['inputs'] as $input)
                $sum += $input['count'] * $values[$input['item']];
            if ($fuelWeight > 0.0 && in_array($recipe['type'], self::COOKING_TYPES, true))
                $sum += $fuelWeight * $values[$fuel];

            return $sum / $recipe['output']['count'];
        };
        $better = fn (float $new, float $old) => $new < $old - 1e-9 * max(1.0, abs($new));
        $recipeById = [];
        foreach ($recipes as $recipe)
            $recipeById[$recipe['id']] = $recipe;

        // 1) without fuel
        $rounds = max(50, 4 * count($items));
        for ($round = 0; $round < $rounds; $round++) {
            $changed = false;
            foreach ($recipes as $recipe) {
                $value = $cost($recipe, $costs, 0.0);
                $item = $recipe['output']['item'];
                if ($better($value, $costs[$item])) {
                    $costs[$item] = $value;
                    $choices[$item] = $recipe['id'];
                    $changed = true;
                }
            }
            if (!$changed)
                break;
        }
        if ($fuelShare <= 0.0)
            return ['costs' => $costs, 'choices' => $choices];

        // 2) evaluate these options with fuel, upwards from the fuel-free costs
        $evaluate = function (array $values) use ($choices, $source, $recipeById, $cost, $fuelShare): array {
            $next = [];
            foreach ($choices as $item => $choice)
                $next[$item] = $choice === 'source' ? $source[$item] : $cost($recipeById[$choice], $values, $fuelShare);
            return $next;
        };
        for ($round = 0; $round < 1000; $round++) {
            $next = $evaluate($costs);
            $done = true;
            foreach ($next as $item => $value) {
                if (is_infinite($value) || $value > 1e12) {
                    $next[$item] = INF;
                    if (!is_infinite($costs[$item]))
                        $done = false;
                } elseif (abs($value - $costs[$item]) > 1e-12 * max(1.0, $value)) {
                    $done = false;
                }
            }
            $costs = $next;
            if ($done)
                break;
        }

        // 3) improve from that upper bound
        for ($round = 0; $round < 1000; $round++) {
            $changed = false;
            $next = $costs;
            foreach ($byOutput as $item => $options) {
                if ($better($source[$item], $next[$item])) {
                    $next[$item] = $source[$item];
                    $choices[$item] = 'source';
                    $changed = true;
                }
                foreach ($options as $recipe) {
                    $value = $cost($recipe, $costs, $fuelShare);
                    if ($better($value, $next[$item])) {
                        $next[$item] = $value;
                        $choices[$item] = $recipe['id'];
                        $changed = true;
                    }
                }
                // keep the value of the current option up to date
                $current = $choices[$item] === 'source' ? $source[$item] : $cost($recipeById[$choices[$item]], $costs, $fuelShare);
                if ($current < $next[$item])
                    $next[$item] = $current;
            }
            $costs = $next;
            if (!$changed)
                break;
        }

        return ['costs' => $costs, 'choices' => $choices];
    }

    /**
     * Cheapest cost of every item, see cheapest().
     *
     * @return array<string, float>
     */
    public static function rawCosts(array $recipes, callable $sourceCost, string $fuel, ?array $fuels = null): array
    {
        return self::cheapest($recipes, $sourceCost, $fuel, $fuels)['costs'];
    }

    public function version(): string
    {
        return $this->data['version'];
    }

    public function name(): string
    {
        return $this->data['name'];
    }

    /** @return array<string, array> */
    public function items(): array
    {
        return $this->items;
    }

    public function hasItem(string $id): bool
    {
        return isset($this->items[$id]);
    }

    public function itemName(string $id): string
    {
        return $this->items[$id]['name'] ?? $id;
    }

    /** @return array<string, array> */
    public function recipes(): array
    {
        return $this->recipes;
    }

    public function recipe(string $id): ?array
    {
        return $this->recipes[$id] ?? null;
    }

    /**
     * Recipes that make an item, in file order.
     *
     * @return array<int, array>
     */
    public function recipesFor(string $itemId): array
    {
        return array_map(fn (string $id) => $this->recipes[$id], $this->producers[$itemId] ?? []);
    }

    /** @return array<string, float> items smelted per fuel item */
    public function fuels(): array
    {
        return $this->fuels;
    }

    public function defaults(): array
    {
        return $this->data['defaults'];
    }

    public static function isCooking(array $recipe): bool
    {
        return in_array($recipe['type'], self::COOKING_TYPES, true);
    }

    /**
     * File header and item list for the client.
     */
    public function meta(): array
    {
        $items = [];
        foreach ($this->items as $id => $item)
            $items[] = ['id' => $id, 'name' => $item['name'], 'craftable' => isset($this->producers[$id])];

        usort($items, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return [
            'version' => $this->data['version'],
            'name' => $this->data['name'],
            'updated' => $this->data['updated'],
            'sources' => $this->data['sources'],
            'notes' => $this->data['notes'] ?? null,
            'defaults' => $this->data['defaults'],
            'fuels' => array_map(
                fn (string $item, float $smelts) => ['item' => $item, 'name' => $this->itemName($item), 'smelts' => $smelts],
                array_keys($this->fuels),
                $this->fuels
            ),
            'items' => $items,
        ];
    }

    private static function checkKeys(array $value, array $required, array $optional, string $path, array &$errors): void
    {
        foreach ($required as $key)
            if (!array_key_exists($key, $value))
                $errors[] = "{$path}: missing field '{$key}'";

        foreach (array_keys($value) as $key)
            if (!in_array($key, $required, true) && !in_array($key, $optional, true))
                $errors[] = "{$path}: unknown field '{$key}'";
    }

    private static function isList(mixed $value): bool
    {
        return is_array($value) && array_is_list($value);
    }

    private static function isText(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    private static function isDate(mixed $value): bool
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    private static function isPositiveNumber(mixed $value): bool
    {
        return (is_int($value) || is_float($value)) && $value > 0;
    }
}
