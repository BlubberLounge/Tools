<?php

namespace App\Minecraft;

/**
 * Vanilla level <-> experience point conversion (Java Edition).
 */
final class Experience
{
    /**
     * Total experience points needed to go from level 0 to $level.
     */
    public static function pointsForLevel(int $level): int
    {
        if ($level <= 0)
            return 0;

        if ($level <= 16)
            return $level * $level + 6 * $level;

        if ($level <= 31)
            return (int) round(2.5 * $level * $level - 40.5 * $level + 360);

        return (int) round(4.5 * $level * $level - 162.5 * $level + 2220);
    }

    /**
     * How many of each XP source are needed to collect $points.
     *
     * @param array{mobs: array, smelting: array} $sources xp sources of a version file
     */
    public static function sourceCounts(int $points, array $sources): array
    {
        $count = fn (array $source) => $source + ['count' => (int) ceil($points / $source['xp'])];

        return [
            'mobs' => array_map($count, $sources['mobs']),
            'smelting' => array_map($count, $sources['smelting']),
        ];
    }
}
