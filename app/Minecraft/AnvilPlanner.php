<?php

namespace App\Minecraft;

use InvalidArgumentException;

/**
 * Finds the cheapest order to apply enchanted books to an item in an anvil.
 *
 * Anvil rules (Java Edition, see AnvilMenu#createResult):
 * - each step costs the prior-work penalty of both inputs plus, for every
 *   enchantment on the right-hand input, its book multiplier times its level;
 * - the result's prior-work count is max(left, right) + 1, the penalty of a
 *   count n comes from the version data (2^n - 1);
 * - a step above the version's level cap is "Too Expensive!".
 *
 * Every wanted enchantment is bought as one book at the wanted level. The item
 * always stays in the left slot; books can be combined with each other first.
 * The search is a memoised dynamic program over subsets of books that keeps,
 * per subset and prior-work count, the cheapest valid way to build it
 * (3^n subset pairs; n is at most ten for real items).
 */
final class AnvilPlanner
{
    public const MAX_BOOKS = 12;

    public const MAX_PRIOR_WORK = 10;

    public function __construct(private readonly EnchantmentData $data)
    {
    }

    /**
     * @param string $itemId item from the version data
     * @param array<string, int> $wanted enchantment id => wanted level
     * @param array<string, int> $present enchantments the item already carries (id => level)
     * @param int $priorWork how many times the item already went through an anvil
     *
     * @throws InvalidArgumentException for unknown items/enchantments, wrong levels or clashing enchantments
     */
    public function plan(string $itemId, array $wanted, array $present = [], int $priorWork = 0): array
    {
        $item = $this->data->item($itemId);
        if ($item === null)
            throw new InvalidArgumentException("Unknown item '{$itemId}' in version {$this->data->version()}.");

        if ($priorWork < 0 || $priorWork > self::MAX_PRIOR_WORK)
            throw new InvalidArgumentException('Prior work must be between 0 and '.self::MAX_PRIOR_WORK.'.');

        $this->checkEnchantments($itemId, $wanted, 'wanted');
        $this->checkEnchantments($itemId, $present, 'present');

        $ids = array_keys($wanted + $present);
        foreach ($ids as $i => $a)
            foreach (array_slice($ids, $i + 1) as $b)
                if (!$this->data->areCompatible($a, $b))
                    throw new InvalidArgumentException(
                        "{$this->data->enchantment($a)['name']} and {$this->data->enchantment($b)['name']} cannot be on the same item."
                    );

        // one book per enchantment that is not already on the item at that level
        $books = [];
        ksort($wanted);
        foreach ($wanted as $id => $level) {
            if (($present[$id] ?? 0) >= $level)
                continue;

            $enchantment = $this->data->enchantment($id);
            $books[] = [
                'id' => $id,
                'level' => $level,
                'cost' => $enchantment['book_multiplier'] * $level,
            ];
        }

        if (count($books) > self::MAX_BOOKS)
            throw new InvalidArgumentException('At most '.self::MAX_BOOKS.' books can be planned at once.');

        $cap = $this->data->levelCap();
        $search = $this->search($books, $priorWork, $cap);
        $possible = $search !== null;
        if (!$possible)
            $search = $this->search($books, $priorWork, null);

        return $this->buildPlan($item, $books, $present, $priorWork, $search, $possible);
    }

    private function checkEnchantments(string $itemId, array $enchantments, string $label): void
    {
        foreach ($enchantments as $id => $level) {
            $enchantment = is_string($id) ? $this->data->enchantment($id) : null;
            if ($enchantment === null)
                throw new InvalidArgumentException("Unknown {$label} enchantment '{$id}'.");

            if (!$this->data->itemAccepts($itemId, $id))
                throw new InvalidArgumentException("{$enchantment['name']} cannot be applied to this item.");

            if (!is_int($level) || $level < 1 || $level > $enchantment['max_level'])
                throw new InvalidArgumentException("{$enchantment['name']} level must be between 1 and {$enchantment['max_level']}.");
        }
    }

    /**
     * Dynamic program over book subsets.
     *
     * @return array|null tables and the chosen root, null when no plan keeps every step at or below $cap
     */
    private function search(array $books, int $priorWork, ?int $cap): ?array
    {
        $n = count($books);
        $full = (1 << $n) - 1;

        $penalty = [];
        for ($w = 0; $w <= $n + $priorWork + 1; $w++)
            $penalty[$w] = $this->data->priorWorkPenalty($w);

        // enchantment cost of putting a book subset into the right slot
        $bitIndex = [];
        for ($i = 0; $i < $n; $i++)
            $bitIndex[1 << $i] = $i;

        $value = [0];
        for ($mask = 1; $mask <= $full; $mask++) {
            $low = $mask & -$mask;
            $value[$mask] = $value[$mask ^ $low] + $books[$bitIndex[$low]]['cost'];
        }

        // $bookBest[mask][work] = [total, leftMask, leftWork, rightMask, rightWork]
        $bookBest = [];
        for ($mask = 1; $mask <= $full; $mask++) {
            if (($mask & ($mask - 1)) === 0) {
                $bookBest[$mask] = [0 => [0, 0, 0, 0, 0]];
                continue;
            }

            $best = [];
            for ($left = ($mask - 1) & $mask; $left > 0; $left = ($left - 1) & $mask) {
                $right = $mask ^ $left;
                $this->combine($best, $bookBest[$left], $bookBest[$right], $left, $right, $value[$right], $penalty, $cap);
            }
            $bookBest[$mask] = $this->prune($best);
        }

        // $itemBest[mask][work]: the item with the books of mask applied
        $itemBest = [0 => [$priorWork => [0, 0, 0, 0, 0]]];
        for ($mask = 1; $mask <= $full; $mask++) {
            $best = [];
            for ($right = $mask; $right > 0; $right = ($right - 1) & $mask) {
                $left = $mask ^ $right;
                if (empty($itemBest[$left]) || empty($bookBest[$right]))
                    continue;
                $this->combine($best, $itemBest[$left], $bookBest[$right], $left, $right, $value[$right], $penalty, $cap);
            }
            $itemBest[$mask] = $this->prune($best);
        }

        if (empty($itemBest[$full]))
            return null;

        // cheapest total; on a tie the lower prior work is better for later anvil use
        $rootWork = null;
        foreach ($itemBest[$full] as $work => $entry)
            if ($rootWork === null || $entry[0] < $itemBest[$full][$rootWork][0])
                $rootWork = $work;

        return [
            'bookBest' => $bookBest,
            'itemBest' => $itemBest,
            'value' => $value,
            'penalty' => $penalty,
            'full' => $full,
            'rootWork' => $rootWork,
        ];
    }

    private function combine(array &$best, array $leftOptions, array $rightOptions, int $left, int $right, int $value, array &$penalty, ?int $cap): void
    {
        foreach ($leftOptions as $leftWork => $leftEntry) {
            foreach ($rightOptions as $rightWork => $rightEntry) {
                $step = $penalty[$leftWork] + $penalty[$rightWork] + $value;
                if ($cap !== null && $step > $cap)
                    continue;

                $work = max($leftWork, $rightWork) + 1;
                if (!isset($penalty[$work]))
                    $penalty[$work] = $this->data->priorWorkPenalty($work);

                $total = $leftEntry[0] + $rightEntry[0] + $step;
                if (!isset($best[$work]) || $total < $best[$work][0])
                    $best[$work] = [$total, $left, $leftWork, $right, $rightWork];
            }
        }
    }

    /**
     * Drop options that cost more and leave a higher prior-work count than another option.
     */
    private function prune(array $options): array
    {
        ksort($options);
        $kept = [];
        $cheapest = PHP_INT_MAX;
        foreach ($options as $work => $entry) {
            if ($entry[0] < $cheapest) {
                $kept[$work] = $entry;
                $cheapest = $entry[0];
            }
        }

        return $kept;
    }

    private function buildPlan(array $item, array $books, array $present, int $priorWork, array $search, bool $possible): array
    {
        $cap = $this->data->levelCap();
        $steps = [];

        $emit = function (bool $isItem, int $mask, int $work) use (&$emit, &$steps, $search, $books, $present, $item, $priorWork, $cap): array {
            if ($isItem && $mask === 0)
                return $this->slot('item', null, $item, $present, $books, 0, $priorWork, $search['penalty']);

            if (!$isItem && ($mask & ($mask - 1)) === 0)
                return $this->slot('book', null, null, [], $books, $mask, 0, $search['penalty']);

            [, $left, $leftWork, $right, $rightWork] = $isItem
                ? $search['itemBest'][$mask][$work]
                : $search['bookBest'][$mask][$work];

            $leftSlot = $emit($isItem, $left, $leftWork);
            $rightSlot = $emit(false, $right, $rightWork);

            $penaltyCost = $search['penalty'][$leftWork] + $search['penalty'][$rightWork];
            $cost = $penaltyCost + $search['value'][$right];
            $number = count($steps) + 1;

            $result = $this->slot('result', $number, $isItem ? $item : null, $isItem ? $present : [], $books, $mask, $work, $search['penalty']);
            $steps[] = [
                'number' => $number,
                'left' => $leftSlot,
                'right' => $rightSlot,
                'result' => $result,
                'cost' => $cost,
                'penalty_cost' => $penaltyCost,
                'enchantment_cost' => $search['value'][$right],
                'xp_points' => Experience::pointsForLevel($cost),
                'too_expensive' => $cost > $cap,
            ];

            return $result;
        };

        $final = $emit(true, $search['full'], $search['rootWork']);

        $totalLevels = array_sum(array_column($steps, 'cost'));
        $totalPoints = array_sum(array_column($steps, 'xp_points'));
        $failing = null;
        foreach ($steps as $step) {
            if ($step['too_expensive']) {
                $failing = $step['number'];
                break;
            }
        }

        return [
            'version' => $this->data->version(),
            'item' => ['id' => $item['id'], 'name' => $item['name']],
            'possible' => $possible,
            'level_cap' => $cap,
            'failing_step' => $failing,
            'books' => count($books),
            'book_list' => array_map(fn ($book) => $this->describe($book['id'], $book['level']), $books),
            'total_levels' => $totalLevels,
            'max_step_cost' => $steps === [] ? 0 : max(array_column($steps, 'cost')),
            'total_xp_points' => $totalPoints,
            'xp_sources' => Experience::sourceCounts($totalPoints, $this->data->xpSources()),
            'steps' => $steps,
            'result' => $final,
        ];
    }

    private function slot(string $type, ?int $fromStep, ?array $item, array $present, array $books, int $mask, int $work, array $penalty): array
    {
        $levels = $present;
        foreach ($books as $i => $book)
            if ($mask & (1 << $i))
                $levels[$book['id']] = max($levels[$book['id']] ?? 0, $book['level']);
        ksort($levels);

        $enchantments = [];
        foreach ($levels as $id => $level)
            $enchantments[] = $this->describe($id, $level);

        return [
            'type' => $type,
            'from_step' => $fromStep,
            'is_item' => $item !== null,
            'name' => $item['name'] ?? 'Enchanted Book',
            'item_id' => $item['id'] ?? 'enchanted_book',
            'enchantments' => $enchantments,
            'prior_work' => $work,
            'penalty' => $penalty[$work] ?? $this->data->priorWorkPenalty($work),
        ];
    }

    private function describe(string $id, int $level): array
    {
        $enchantment = $this->data->enchantment($id);

        return [
            'id' => $id,
            'name' => $enchantment['name'],
            'level' => $level,
            'max_level' => $enchantment['max_level'],
            'curse' => $enchantment['curse'],
        ];
    }
}
