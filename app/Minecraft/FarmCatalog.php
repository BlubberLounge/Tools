<?php

namespace App\Minecraft;

use InvalidArgumentException;

/**
 * Farm module catalogue of one Minecraft Java version: public, community
 * documented farm designs with their rate per module.
 *
 * Every supported version has one hand-maintained JSON file in
 * resources/minecraft/farms/{version}.json. This class is the only loader of
 * those files; the farm catalogue page and the farm chain planner read module
 * rates through it. Item ids are vanilla ids without namespace, the same
 * convention as the enchantment data.
 */
final class FarmCatalog
{
    /** How a rate was obtained. 'unknown' means no published number (per_hour is null). */
    public const RATE_BASES = ['designer', 'measured', 'unknown'];

    public const DIFFICULTIES = ['easy', 'medium', 'hard'];

    public const SOURCE_KINDS = ['schematic', 'video', 'wiki'];

    public const SORTS = ['rate', 'footprint', 'name'];

    private const VERSION_PATTERN = '/^\d+(\.\d+){1,2}$/';

    private const DESIGN_ID_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';

    /** @var array<string, self> loaded files, cached per process */
    private static array $cache = [];

    /** @var array<string, array> */
    private array $designs = [];

    /** @var array<string, array> */
    private array $items = [];

    /** @var array<string, array> */
    private array $farmTypes = [];

    private function __construct(private readonly array $data)
    {
        foreach ($data['items'] as $item)
            $this->items[$item['id']] = $item;

        foreach ($data['farm_types'] as $type)
            $this->farmTypes[$type['id']] = $type;

        foreach ($data['designs'] as $design)
            $this->designs[$design['id']] = $design;
    }

    public static function directory(): string
    {
        return resource_path('minecraft/farms');
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

    public static function latestVersion(): ?string
    {
        return self::versions()[0] ?? null;
    }

    public static function exists(string $version): bool
    {
        return preg_match(self::VERSION_PATTERN, $version) === 1
            && is_file(self::directory()."/{$version}.json");
    }

    /**
     * Load and validate the catalogue of a version.
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
            throw new InvalidArgumentException("Farm catalogue of version '{$version}' is not valid JSON.");

        if (($data['version'] ?? null) !== $version)
            throw new InvalidArgumentException("Farm catalogue of version '{$version}' declares version '".($data['version'] ?? '')."'.");

        return self::$cache[$version] = self::fromArray($data);
    }

    /**
     * @throws InvalidArgumentException when the data does not pass validation
     */
    public static function fromArray(array $data): self
    {
        $errors = self::validate($data);
        if ($errors !== [])
            throw new InvalidArgumentException("Invalid farm catalogue:\n- ".implode("\n- ", $errors));

        return new self($data);
    }

    /**
     * Check a decoded catalogue file. Returns a list of problems, empty when valid.
     *
     * @return string[]
     */
    public static function validate(array $data): array
    {
        $errors = [];

        self::checkKeys($data, ['version', 'name', 'updated', 'sources', 'farm_types', 'items', 'designs'], ['notes'], 'file', $errors);

        $version = $data['version'] ?? null;
        if (!self::isVersion($version))
            $errors[] = 'version must look like 1.21 or 26.3';
        if (!self::isText($data['name'] ?? null))
            $errors[] = 'name must be a non-empty string';
        if (!self::isDate($data['updated'] ?? null))
            $errors[] = 'updated must be a date (YYYY-MM-DD)';

        // sources of the file as a whole
        if (!self::isList($data['sources'] ?? null) || $data['sources'] === []) {
            $errors[] = 'sources must name at least one source';
        } else {
            foreach ($data['sources'] as $i => $source) {
                if (!is_array($source)) { $errors[] = "sources[{$i}] must be an object"; continue; }
                self::checkKeys($source, ['title', 'url', 'retrieved'], [], "sources[{$i}]", $errors);
                if (!self::isText($source['title'] ?? null))
                    $errors[] = "sources[{$i}].title must be a non-empty string";
                if (!self::isUrl($source['url'] ?? null))
                    $errors[] = "sources[{$i}].url must be an https URL";
                if (!self::isDate($source['retrieved'] ?? null))
                    $errors[] = "sources[{$i}].retrieved must be a date (YYYY-MM-DD)";
            }
        }

        $typeIds = self::checkIdList($data['farm_types'] ?? null, 'farm_types', 'farm type', $errors);
        $itemIds = self::checkIdList($data['items'] ?? null, 'items', 'item', $errors);

        // designs
        if (!self::isList($data['designs'] ?? null) || $data['designs'] === []) {
            $errors[] = 'designs must be a non-empty list';
            return $errors;
        }

        $designIds = [];
        $usedItems = [];
        foreach ($data['designs'] as $i => $design) {
            if (!is_array($design)) { $errors[] = "designs[{$i}] must be an object"; continue; }
            $path = 'designs['.(is_string($design['id'] ?? null) ? $design['id'] : $i).']';
            self::checkKeys($design, ['id', 'name', 'type', 'produces', 'versions', 'footprint', 'materials', 'difficulty', 'condition', 'author', 'community', 'source'], ['notes', 'model'], $path, $errors);

            $id = $design['id'] ?? null;
            if (!is_string($id) || !preg_match(self::DESIGN_ID_PATTERN, $id))
                $errors[] = "{$path}.id must be a kebab-case id";
            elseif (isset($designIds[$id]))
                $errors[] = "duplicate design id '{$id}'";
            else
                $designIds[$id] = true;

            foreach (['name', 'condition', 'author'] as $field)
                if (!self::isText($design[$field] ?? null))
                    $errors[] = "{$path}.{$field} must be a non-empty string";
            if (($design['community'] ?? null) !== null && !self::isText($design['community']))
                $errors[] = "{$path}.community must be a non-empty string or null";
            if (array_key_exists('notes', $design) && !self::isText($design['notes']))
                $errors[] = "{$path}.notes must be a non-empty string when present";

            if (!in_array($design['type'] ?? null, $typeIds, true))
                $errors[] = "{$path}.type '".self::show($design['type'] ?? null)."' is not a known farm type";
            if (!in_array($design['difficulty'] ?? null, self::DIFFICULTIES, true))
                $errors[] = "{$path}.difficulty must be one of: ".implode(', ', self::DIFFICULTIES);

            // produced items with their rate per module
            if (!self::isList($design['produces'] ?? null) || $design['produces'] === []) {
                $errors[] = "{$path}.produces must list at least one item";
            } else {
                $seen = [];
                foreach ($design['produces'] as $j => $product) {
                    $productPath = "{$path}.produces[{$j}]";
                    if (!is_array($product)) { $errors[] = "{$productPath} must be an object"; continue; }
                    self::checkKeys($product, ['item', 'per_hour', 'basis'], ['note'], $productPath, $errors);
                    $item = $product['item'] ?? null;
                    if (!in_array($item, $itemIds, true)) {
                        $errors[] = "{$productPath} names unknown item '".self::show($item)."'";
                    } elseif (isset($seen[$item])) {
                        $errors[] = "{$path}.produces lists item '{$item}' twice";
                    } else {
                        $seen[$item] = true;
                        $usedItems[$item] = true;
                    }

                    $basis = $product['basis'] ?? null;
                    $rate = $product['per_hour'] ?? null;
                    if (!in_array($basis, self::RATE_BASES, true)) {
                        $errors[] = "{$productPath}.basis must be one of: ".implode(', ', self::RATE_BASES);
                    } elseif ($basis === 'unknown') {
                        if ($rate !== null)
                            $errors[] = "{$productPath}.per_hour must be null when the basis is 'unknown'";
                    } elseif (!self::isPositiveNumber($rate)) {
                        $errors[] = "{$productPath}.per_hour is missing: give a positive number or set basis 'unknown'";
                    }
                    if ($basis === 'measured' && !self::isText($product['note'] ?? null))
                        $errors[] = "{$productPath} is measured: the note must say who measured it, when and how";
                    if (array_key_exists('note', $product) && !self::isText($product['note']))
                        $errors[] = "{$productPath}.note must be a non-empty string when present";
                }
            }

            // version range; the file's version must lie inside it
            $range = $design['versions'] ?? null;
            if (!is_array($range)) {
                $errors[] = "{$path}.versions must be an object with min and max";
            } else {
                self::checkKeys($range, ['min', 'max'], [], "{$path}.versions", $errors);
                $min = $range['min'] ?? null;
                $max = $range['max'] ?? null;
                if (!self::isVersion($min))
                    $errors[] = "{$path}.versions.min must be a version";
                if ($max !== null && !self::isVersion($max))
                    $errors[] = "{$path}.versions.max must be a version or null";
                if (self::isVersion($min) && self::isVersion($max) && version_compare($max, $min, '<'))
                    $errors[] = "{$path}.versions.max is below min";
                if (self::isVersion($version) && self::isVersion($min)
                    && (version_compare($version, $min, '<') || (self::isVersion($max) && version_compare($version, $max, '>'))))
                    $errors[] = "{$path} does not work in version {$version} (range {$min} to ".($max ?? 'latest').')';
            }

            // footprint: null when the source does not publish it
            $footprint = $design['footprint'] ?? null;
            if ($footprint !== null) {
                if (!is_array($footprint)) {
                    $errors[] = "{$path}.footprint must be an object (x, y, z) or null";
                } else {
                    self::checkKeys($footprint, ['x', 'y', 'z'], [], "{$path}.footprint", $errors);
                    foreach (['x', 'y', 'z'] as $axis)
                        if (!is_int($footprint[$axis] ?? null) || $footprint[$axis] < 1)
                            $errors[] = "{$path}.footprint.{$axis} must be a positive integer";
                }
            }

            // build materials: count null when the source gives no amount
            if (!self::isList($design['materials'] ?? null) || $design['materials'] === []) {
                $errors[] = "{$path}.materials must list at least one item";
            } else {
                $seen = [];
                foreach ($design['materials'] as $j => $material) {
                    $materialPath = "{$path}.materials[{$j}]";
                    if (!is_array($material)) { $errors[] = "{$materialPath} must be an object"; continue; }
                    self::checkKeys($material, ['item', 'count'], [], $materialPath, $errors);
                    $item = $material['item'] ?? null;
                    if (!in_array($item, $itemIds, true)) {
                        $errors[] = "{$materialPath} names unknown item '".self::show($item)."'";
                    } elseif (isset($seen[$item])) {
                        $errors[] = "{$path}.materials lists item '{$item}' twice";
                    } else {
                        $seen[$item] = true;
                        $usedItems[$item] = true;
                    }
                    $count = $material['count'] ?? null;
                    if ($count !== null && (!is_int($count) || $count < 1))
                        $errors[] = "{$materialPath}.count must be a positive integer or null";
                }
            }

            // source of the design
            $source = $design['source'] ?? null;
            if (!is_array($source)) {
                $errors[] = "{$path}.source is missing";
            } else {
                self::checkKeys($source, ['url', 'title', 'kind', 'retrieved'], [], "{$path}.source", $errors);
                if (!self::isUrl($source['url'] ?? null))
                    $errors[] = "{$path}.source.url must be an https URL";
                if (!self::isText($source['title'] ?? null))
                    $errors[] = "{$path}.source.title must be a non-empty string";
                if (!in_array($source['kind'] ?? null, self::SOURCE_KINDS, true))
                    $errors[] = "{$path}.source.kind must be one of: ".implode(', ', self::SOURCE_KINDS);
                if (!self::isDate($source['retrieved'] ?? null))
                    $errors[] = "{$path}.source.retrieved must be a date (YYYY-MM-DD)";
            }

            // reserved for the 3D farm viewer: path of the model file below resources/minecraft/models
            $model = $design['model'] ?? null;
            if ($model !== null && (!is_string($model) || !preg_match('#^[a-z0-9_\-]+(/[a-z0-9_\-.]+)*\.json$#', $model)))
                $errors[] = "{$path}.model must be null or a relative .json path";
        }

        foreach ($itemIds as $itemId)
            if (!isset($usedItems[$itemId]))
                $errors[] = "item '{$itemId}' is not used by any design";

        return $errors;
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
    public function designs(): array
    {
        return $this->designs;
    }

    public function design(string $id): ?array
    {
        return $this->designs[$id] ?? null;
    }

    /** @return array<string, array> */
    public function items(): array
    {
        return $this->items;
    }

    public function item(string $id): ?array
    {
        return $this->items[$id] ?? null;
    }

    /** @return array<string, array> */
    public function farmTypes(): array
    {
        return $this->farmTypes;
    }

    /**
     * Items per hour one module of a design makes of an item. Null when the
     * design does not make the item or its rate is not published.
     */
    public function rate(string $designId, string $itemId): ?float
    {
        foreach ($this->designs[$designId]['produces'] ?? [] as $product)
            if ($product['item'] === $itemId)
                return $product['per_hour'] === null ? null : (float) $product['per_hour'];

        return null;
    }

    /**
     * Designs that make an item, highest rate first (unknown rates last).
     *
     * @return array<int, array>
     */
    public function designsProducing(string $itemId): array
    {
        $designs = array_values(array_filter(
            $this->designs,
            fn (array $design) => in_array($itemId, array_column($design['produces'], 'item'), true)
        ));

        usort($designs, fn (array $a, array $b) => self::compareNullsLast($this->rate($a['id'], $itemId), $this->rate($b['id'], $itemId), true)
            ?: strcasecmp($a['name'], $b['name']));

        return $designs;
    }

    /**
     * Filter designs. Keys (all optional):
     *  - item: search text matched against the id and name of the produced items
     *  - type: farm type id
     *  - difficulty: one of DIFFICULTIES
     *  - max_footprint: longest horizontal side (x or z) in blocks; designs
     *    without a published footprint do not match
     *
     * @return array<int, array>
     */
    public function filter(array $criteria): array
    {
        $query = (string) ($criteria['item'] ?? '');
        $type = $criteria['type'] ?? null;
        $difficulty = $criteria['difficulty'] ?? null;
        $maxFootprint = $criteria['max_footprint'] ?? null;

        return array_values(array_filter($this->designs, function (array $design) use ($query, $type, $difficulty, $maxFootprint) {
            if ($type !== null && $type !== '' && $design['type'] !== $type)
                return false;
            if ($difficulty !== null && $difficulty !== '' && $design['difficulty'] !== $difficulty)
                return false;
            if ($maxFootprint !== null && $maxFootprint !== '') {
                $side = self::longestSide($design);
                if ($side === null || $side > (int) $maxFootprint)
                    return false;
            }
            if ($query !== '' && $this->matchingProduct($design, $query) === null)
                return false;
            return true;
        }));
    }

    /**
     * Sort designs by 'rate' (highest first), 'footprint' (area x * z,
     * smallest first) or 'name' (A-Z). $direction 'asc' or 'desc' overrides
     * the default order; designs without a value always come last. The rate is
     * the one of the product matching $itemQuery, else of the main product.
     *
     * @param array<int, array> $designs
     * @return array<int, array>
     */
    public function sort(array $designs, string $by = 'rate', string $direction = 'default', string $itemQuery = ''): array
    {
        if (!in_array($by, self::SORTS, true))
            throw new InvalidArgumentException("Unknown sort '{$by}'.");

        $descending = $direction === 'default' ? $by === 'rate' : $direction === 'desc';

        usort($designs, function (array $a, array $b) use ($by, $descending, $itemQuery) {
            $result = match ($by) {
                'name' => $descending ? strcasecmp($b['name'], $a['name']) : strcasecmp($a['name'], $b['name']),
                'footprint' => self::compareNullsLast(self::area($a), self::area($b), $descending),
                'rate' => self::compareNullsLast($this->listedProduct($a, $itemQuery)['per_hour'], $this->listedProduct($b, $itemQuery)['per_hour'], $descending),
            };

            return $result ?: strcasecmp($a['name'], $b['name']);
        });

        return $designs;
    }

    /**
     * The product shown in the list: the first one matching the item search,
     * else the main product (the first listed, the one the design is built for).
     */
    public function listedProduct(array $design, string $itemQuery = ''): array
    {
        return $this->matchingProduct($design, $itemQuery) ?? $design['produces'][0];
    }

    /**
     * First product whose item id or name contains the search text.
     */
    public function matchingProduct(array $design, string $itemQuery): ?array
    {
        $query = mb_strtolower(trim($itemQuery));
        if ($query === '')
            return null;

        foreach ($design['produces'] as $product) {
            $name = mb_strtolower($this->items[$product['item']]['name'] ?? '');
            if (str_contains($product['item'], str_replace(' ', '_', $query)) || str_contains($name, $query))
                return $product;
        }

        return null;
    }

    /**
     * Rate of the first listed product, the one the design is built for.
     */
    public static function mainRate(array $design): ?float
    {
        $rate = $design['produces'][0]['per_hour'] ?? null;

        return $rate === null ? null : (float) $rate;
    }

    public static function area(array $design): ?int
    {
        $footprint = $design['footprint'] ?? null;

        return $footprint === null ? null : $footprint['x'] * $footprint['z'];
    }

    public static function longestSide(array $design): ?int
    {
        $footprint = $design['footprint'] ?? null;

        return $footprint === null ? null : max($footprint['x'], $footprint['z']);
    }

    /**
     * A design for the client: item names resolved, area, type name and the
     * product to show in the list (see listedProduct) added.
     */
    public function presentDesign(array $design, string $itemQuery = ''): array
    {
        $named = fn (array $entry) => $entry + ['name' => $this->items[$entry['item']]['name']];

        return array_merge($design, [
            'type_name' => $this->farmTypes[$design['type']]['name'],
            'produces' => array_map($named, $design['produces']),
            'materials' => array_map($named, $design['materials']),
            'listed_product' => $named($this->listedProduct($design, $itemQuery)),
            'area' => self::area($design),
            'model' => $design['model'] ?? null,
        ]);
    }

    /**
     * Same-item comparison for the detail view: other designs that make the
     * design's main product, highest rate first.
     *
     * @return array<int, array{id: string, name: string, type: string, per_hour: float|null, basis: string, footprint: ?array, difficulty: string}>
     */
    public function comparison(string $designId): array
    {
        $design = $this->designs[$designId] ?? null;
        if ($design === null)
            return [];

        $itemId = $design['produces'][0]['item'];
        $rows = [];
        foreach ($this->designsProducing($itemId) as $other) {
            if ($other['id'] === $designId)
                continue;
            $product = current(array_filter($other['produces'], fn ($p) => $p['item'] === $itemId));
            $rows[] = [
                'id' => $other['id'],
                'name' => $other['name'],
                'type' => $other['type'],
                'per_hour' => $product['per_hour'],
                'basis' => $product['basis'],
                'footprint' => $other['footprint'],
                'difficulty' => $other['difficulty'],
            ];
        }

        return $rows;
    }

    /**
     * File header for the client (everything except the designs).
     */
    public function meta(): array
    {
        return [
            'version' => $this->data['version'],
            'name' => $this->data['name'],
            'updated' => $this->data['updated'],
            'sources' => $this->data['sources'],
            'notes' => $this->data['notes'] ?? null,
            'farm_types' => array_values($this->farmTypes),
            'difficulties' => self::DIFFICULTIES,
            'sorts' => self::SORTS,
        ];
    }

    /**
     * Compare two values, missing values (null) always last.
     */
    private static function compareNullsLast(int|float|null $a, int|float|null $b, bool $descending): int
    {
        if ($a === null || $b === null)
            return ($a === null) <=> ($b === null);

        return $descending ? $b <=> $a : $a <=> $b;
    }

    /**
     * Validate a list of {id, name} entries and return its ids.
     *
     * @return string[]
     */
    private static function checkIdList(mixed $list, string $path, string $label, array &$errors): array
    {
        if (!self::isList($list) || $list === []) {
            $errors[] = "{$path} must be a non-empty list";
            return [];
        }

        $ids = [];
        foreach ($list as $i => $entry) {
            if (!is_array($entry)) { $errors[] = "{$path}[{$i}] must be an object"; continue; }
            self::checkKeys($entry, ['id', 'name'], [], "{$path}[{$i}]", $errors);
            $id = $entry['id'] ?? null;
            if (!is_string($id) || !preg_match('/^[a-z0-9_]+$/', $id))
                $errors[] = "{$path}[{$i}].id must be a lower-case id";
            elseif (in_array($id, $ids, true))
                $errors[] = "duplicate {$label} id '{$id}'";
            else
                $ids[] = $id;
            if (!self::isText($entry['name'] ?? null))
                $errors[] = "{$path}[{$i}].name must be a non-empty string";
        }

        return $ids;
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

    private static function isUrl(mixed $value): bool
    {
        return is_string($value) && preg_match('#^https://[^\s/]+\.[^\s]+$#', $value) === 1;
    }

    private static function isVersion(mixed $value): bool
    {
        return is_string($value) && preg_match(self::VERSION_PATTERN, $value) === 1;
    }

    private static function isPositiveNumber(mixed $value): bool
    {
        return (is_int($value) || is_float($value)) && $value > 0;
    }

    private static function show(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '?';
    }
}
