<?php

namespace App\Minecraft;

use InvalidArgumentException;

/**
 * Item, enchantment and anvil data of one Minecraft Java version.
 *
 * Every supported version has one hand-maintained JSON file in
 * resources/minecraft/enchantments/{version}.json (source: Minecraft Wiki).
 * Adding a version means adding a file; no code change is needed.
 */
final class EnchantmentData
{
    /** Prior-work formulas the planner knows how to evaluate. */
    public const PRIOR_WORK_FORMULAS = ['2^n-1'];

    private const VERSION_PATTERN = '/^\d+(\.\d+){1,2}$/';

    /** @var array<string, self> loaded files, cached per process */
    private static array $cache = [];

    /** @var array<string, array> */
    private array $enchantments = [];

    /** @var array<string, array> */
    private array $items = [];

    private function __construct(private readonly array $data)
    {
        foreach ($data['enchantments'] as $enchantment)
            $this->enchantments[$enchantment['id']] = $enchantment;

        foreach ($data['items'] as $item)
            $this->items[$item['id']] = $item;
    }

    public static function directory(): string
    {
        return resource_path('minecraft/enchantments');
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
     * Load and validate the data file of a version.
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
            throw new InvalidArgumentException("Data file of version '{$version}' is not valid JSON.");

        if (($data['version'] ?? null) !== $version)
            throw new InvalidArgumentException("Data file of version '{$version}' declares version '".($data['version'] ?? '')."'.");

        return self::$cache[$version] = self::fromArray($data);
    }

    /**
     * @throws InvalidArgumentException when the data does not pass validation
     */
    public static function fromArray(array $data): self
    {
        $errors = self::validate($data);
        if ($errors !== [])
            throw new InvalidArgumentException("Invalid enchantment data:\n- ".implode("\n- ", $errors));

        return new self($data);
    }

    /**
     * Check a decoded version file. Returns a list of problems, empty when valid.
     *
     * @return string[]
     */
    public static function validate(array $data): array
    {
        $errors = [];

        self::checkKeys($data, ['version', 'name', 'released', 'sources', 'anvil', 'xp_sources', 'exclusive_sets', 'enchantments', 'items'], ['notes'], 'file', $errors);

        if (!is_string($data['version'] ?? null) || !preg_match(self::VERSION_PATTERN, $data['version']))
            $errors[] = 'version must look like 1.21 or 26.3';
        if (!is_string($data['name'] ?? null) || $data['name'] === '')
            $errors[] = 'name must be a non-empty string';
        if (!is_string($data['released'] ?? null) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['released']))
            $errors[] = 'released must be a date (YYYY-MM-DD)';

        // sources
        if (!self::isList($data['sources'] ?? null) || $data['sources'] === []) {
            $errors[] = 'sources must name at least one wiki source';
        } else {
            foreach ($data['sources'] as $i => $source) {
                if (!is_array($source)) { $errors[] = "sources[{$i}] must be an object"; continue; }
                self::checkKeys($source, ['title', 'url', 'retrieved'], [], "sources[{$i}]", $errors);
                if (!is_string($source['url'] ?? null) || !str_starts_with($source['url'], 'https://minecraft.wiki/'))
                    $errors[] = "sources[{$i}].url must point to https://minecraft.wiki/";
                if (!is_string($source['retrieved'] ?? null) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $source['retrieved']))
                    $errors[] = "sources[{$i}].retrieved must be a date (YYYY-MM-DD)";
            }
        }

        // anvil constants
        $anvil = $data['anvil'] ?? null;
        if (!is_array($anvil)) {
            $errors[] = 'anvil must be an object';
        } else {
            self::checkKeys($anvil, ['level_cap', 'prior_work_penalty', 'incompatible_penalty'], [], 'anvil', $errors);
            if (!is_int($anvil['level_cap'] ?? null) || $anvil['level_cap'] < 1)
                $errors[] = 'anvil.level_cap must be a positive integer';
            if (!in_array($anvil['prior_work_penalty'] ?? null, self::PRIOR_WORK_FORMULAS, true))
                $errors[] = 'anvil.prior_work_penalty must be one of: '.implode(', ', self::PRIOR_WORK_FORMULAS);
            if (!is_int($anvil['incompatible_penalty'] ?? null) || $anvil['incompatible_penalty'] < 0)
                $errors[] = 'anvil.incompatible_penalty must be a non-negative integer';
        }

        // xp sources
        $xp = $data['xp_sources'] ?? null;
        if (!is_array($xp)) {
            $errors[] = 'xp_sources must be an object';
        } else {
            self::checkKeys($xp, ['mobs', 'smelting'], [], 'xp_sources', $errors);
            foreach (['mobs', 'smelting'] as $kind) {
                if (!self::isList($xp[$kind] ?? null) || $xp[$kind] === []) {
                    $errors[] = "xp_sources.{$kind} must be a non-empty list";
                    continue;
                }
                $seen = [];
                foreach ($xp[$kind] as $i => $source) {
                    if (!is_array($source)) { $errors[] = "xp_sources.{$kind}[{$i}] must be an object"; continue; }
                    self::checkKeys($source, ['id', 'name', 'xp'], [], "xp_sources.{$kind}[{$i}]", $errors);
                    if (!self::isId($source['id'] ?? null))
                        $errors[] = "xp_sources.{$kind}[{$i}].id must be a lower-case id";
                    elseif (isset($seen[$source['id']]))
                        $errors[] = "xp_sources.{$kind} has a duplicate id '{$source['id']}'";
                    else
                        $seen[$source['id']] = true;
                    if (!is_int($source['xp'] ?? null) && !is_float($source['xp'] ?? null) || ($source['xp'] ?? 0) <= 0)
                        $errors[] = "xp_sources.{$kind}[{$i}].xp must be a positive number";
                }
            }
        }

        // enchantments
        $enchantmentIds = [];
        $exclusiveSetNames = is_array($data['exclusive_sets'] ?? null) ? array_keys($data['exclusive_sets']) : [];
        if (!self::isList($data['enchantments'] ?? null) || $data['enchantments'] === []) {
            $errors[] = 'enchantments must be a non-empty list';
        } else {
            foreach ($data['enchantments'] as $i => $enchantment) {
                if (!is_array($enchantment)) { $errors[] = "enchantments[{$i}] must be an object"; continue; }
                $path = 'enchantments['.($enchantment['id'] ?? $i).']';
                self::checkKeys($enchantment, ['id', 'name', 'max_level', 'item_multiplier', 'book_multiplier', 'exclusive_set', 'curse'], [], $path, $errors);
                if (!self::isId($enchantment['id'] ?? null)) {
                    $errors[] = "{$path}.id must be a lower-case id";
                } elseif (isset($enchantmentIds[$enchantment['id']])) {
                    $errors[] = "duplicate enchantment id '{$enchantment['id']}'";
                } else {
                    $enchantmentIds[$enchantment['id']] = true;
                }
                if (!is_string($enchantment['name'] ?? null) || $enchantment['name'] === '')
                    $errors[] = "{$path}.name must be a non-empty string";
                foreach (['max_level', 'item_multiplier', 'book_multiplier'] as $field)
                    if (!is_int($enchantment[$field] ?? null) || $enchantment[$field] < 1)
                        $errors[] = "{$path}.{$field} must be a positive integer";
                $set = $enchantment['exclusive_set'] ?? null;
                if ($set !== null && !in_array($set, $exclusiveSetNames, true))
                    $errors[] = "{$path}.exclusive_set '".(is_scalar($set) ? $set : '?')."' is not a known exclusive set";
                if (!is_bool($enchantment['curse'] ?? null))
                    $errors[] = "{$path}.curse must be true or false";
            }
        }

        // exclusive sets
        if (!is_array($data['exclusive_sets'] ?? null) || self::isList($data['exclusive_sets']) && $data['exclusive_sets'] !== []) {
            $errors[] = 'exclusive_sets must be an object of set name => member ids';
        } else {
            foreach ($data['exclusive_sets'] as $name => $members) {
                if (!self::isList($members) || count($members) < 2) {
                    $errors[] = "exclusive set '{$name}' needs at least two members";
                    continue;
                }
                if (count(array_unique($members)) !== count($members))
                    $errors[] = "exclusive set '{$name}' lists a member twice";
                foreach ($members as $member)
                    if (!isset($enchantmentIds[$member]))
                        $errors[] = "exclusive set '{$name}' names unknown enchantment '".(is_scalar($member) ? $member : '?')."'";
            }
        }

        // items
        if (!self::isList($data['items'] ?? null) || $data['items'] === []) {
            $errors[] = 'items must be a non-empty list';
        } else {
            $itemIds = [];
            $usedEnchantments = [];
            foreach ($data['items'] as $i => $item) {
                if (!is_array($item)) { $errors[] = "items[{$i}] must be an object"; continue; }
                $path = 'items['.($item['id'] ?? $i).']';
                self::checkKeys($item, ['id', 'name', 'category', 'enchantments'], [], $path, $errors);
                if (!self::isId($item['id'] ?? null)) {
                    $errors[] = "{$path}.id must be a lower-case id";
                } elseif (isset($itemIds[$item['id']])) {
                    $errors[] = "duplicate item id '{$item['id']}'";
                } else {
                    $itemIds[$item['id']] = true;
                }
                foreach (['name', 'category'] as $field)
                    if (!is_string($item[$field] ?? null) || $item[$field] === '')
                        $errors[] = "{$path}.{$field} must be a non-empty string";
                if (!self::isList($item['enchantments'] ?? null) || $item['enchantments'] === []) {
                    $errors[] = "{$path}.enchantments must be a non-empty list";
                    continue;
                }
                if (count(array_unique($item['enchantments'])) !== count($item['enchantments']))
                    $errors[] = "{$path}.enchantments lists an enchantment twice";
                foreach ($item['enchantments'] as $enchantmentId) {
                    if (!isset($enchantmentIds[$enchantmentId]))
                        $errors[] = "{$path} names unknown enchantment '".(is_scalar($enchantmentId) ? $enchantmentId : '?')."'";
                    else
                        $usedEnchantments[$enchantmentId] = true;
                }
            }
            foreach (array_keys($enchantmentIds) as $enchantmentId)
                if (!isset($usedEnchantments[$enchantmentId]))
                    $errors[] = "enchantment '{$enchantmentId}' is not allowed on any item";
        }

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

    public function levelCap(): int
    {
        return $this->data['anvil']['level_cap'];
    }

    /**
     * Prior-work penalty in levels of an item that went through the anvil $uses times.
     */
    public function priorWorkPenalty(int $uses): int
    {
        // only '2^n-1' passes validation
        return (1 << $uses) - 1;
    }

    /** @return array<string, array> */
    public function enchantments(): array
    {
        return $this->enchantments;
    }

    public function enchantment(string $id): ?array
    {
        return $this->enchantments[$id] ?? null;
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

    public function itemAccepts(string $itemId, string $enchantmentId): bool
    {
        return in_array($enchantmentId, $this->items[$itemId]['enchantments'] ?? [], true);
    }

    /**
     * Vanilla rule: two different enchantments clash when one of them lists the
     * other in its exclusive set. Sets are not symmetric groups (Riptide excludes
     * Loyalty and Channeling, which still go together).
     */
    public function areCompatible(string $a, string $b): bool
    {
        if ($a === $b)
            return true;

        foreach ([[$a, $b], [$b, $a]] as [$owner, $other]) {
            $set = $this->enchantments[$owner]['exclusive_set'] ?? null;
            if ($set !== null && in_array($other, $this->data['exclusive_sets'][$set], true))
                return false;
        }

        return true;
    }

    /**
     * Enchantments that clash with the given one.
     *
     * @return string[]
     */
    public function incompatibleWith(string $id): array
    {
        return array_values(array_filter(
            array_keys($this->enchantments),
            fn ($other) => !$this->areCompatible($id, $other)
        ));
    }

    /** @return array{mobs: array, smelting: array} */
    public function xpSources(): array
    {
        return $this->data['xp_sources'];
    }

    /**
     * Data for the planner UI: items with their categories and enchantments
     * including the ids each one excludes.
     */
    public function toClientArray(): array
    {
        $enchantments = [];
        foreach ($this->enchantments as $id => $enchantment)
            $enchantments[] = $enchantment + ['incompatible' => $this->incompatibleWith($id)];

        return [
            'version' => $this->data['version'],
            'name' => $this->data['name'],
            'released' => $this->data['released'],
            'sources' => $this->data['sources'],
            'notes' => $this->data['notes'] ?? null,
            'anvil' => $this->data['anvil'],
            'xp_sources' => $this->data['xp_sources'],
            'enchantments' => $enchantments,
            'items' => array_values($this->items),
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

    private static function isId(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-z0-9_]+$/', $value) === 1;
    }
}
