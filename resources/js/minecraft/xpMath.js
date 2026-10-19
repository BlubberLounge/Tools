/**
 * Experience maths (Java Edition), used by xpCalculator.js. Same level
 * formulas as App\Minecraft\Experience.
 *
 * Average XP per source, read from the 26.3 server jar (recipe files,
 * Blocks, ThrownExperienceBottle, Animal, FishingHook, Villager, SpawnerBlock):
 * random ranges are uniform, so the average is the middle of the range.
 * Smelting XP is a fraction per item; the game pays out the whole part and
 * the rest with that chance, so the average holds.
 */

export const OTHER_SOURCES = [
    { id: 'experience_bottle', xp: 7, min: 3, max: 11 },
    { id: 'breeding', xp: 4, min: 1, max: 7 },
    { id: 'fishing', xp: 3.5, min: 1, max: 6 },
    { id: 'trading', xp: 4.5, min: 3, max: 6 },
    { id: 'spawner', xp: 29, min: 15, max: 43 },
    { id: 'coal_ore', xp: 1, min: 0, max: 2 },
    { id: 'nether_gold_ore', xp: 0.5, min: 0, max: 1 },
    { id: 'redstone_ore', xp: 3, min: 1, max: 5 },
    { id: 'lapis_ore', xp: 3.5, min: 2, max: 5 },
    { id: 'nether_quartz_ore', xp: 3.5, min: 2, max: 5 },
    { id: 'diamond_ore', xp: 5, min: 3, max: 7 },
    { id: 'emerald_ore', xp: 5, min: 3, max: 7 },
];

export const SMELTING_SOURCES = [
    { id: 'ancient_debris', xp: 2 },
    { id: 'raw_gold', xp: 1 },
    { id: 'cactus', xp: 1 },
    { id: 'raw_iron', xp: 0.7 },
    { id: 'raw_copper', xp: 0.7 },
    { id: 'raw_food', xp: 0.35 },
    { id: 'clay', xp: 0.35 },
    { id: 'clay_ball', xp: 0.3 },
    { id: 'log', xp: 0.15 },
    { id: 'kelp', xp: 0.1 },
    { id: 'cobblestone', xp: 0.1 },
    { id: 'sand', xp: 0.1 },
];

/** Levels an enchanting table takes for its top option (which needs level 30). */
export const ENCHANT_COST = 3;
export const ENCHANT_MIN_LEVEL = 30;

/** Mending repairs 2 durability per XP point. */
export const MENDING_PER_POINT = 2;

const whole = value => {
    const n = Math.floor(Number(value));
    return Number.isFinite(n) && n > 0 ? n : 0;
};

/** Total points from level 0 to `level`. */
export function pointsForLevel(level) {
    const l = whole(level);
    if (l <= 16)
        return l * l + 6 * l;
    if (l <= 31)
        return Math.round(2.5 * l * l - 40.5 * l + 360);
    return Math.round(4.5 * l * l - 162.5 * l + 2220);
}

/** Points from `level` to `level + 1`. */
export function pointsToNext(level) {
    const l = whole(level);
    if (l <= 15)
        return 2 * l + 7;
    if (l <= 30)
        return 5 * l - 38;
    return 9 * l - 158;
}

/** Level and bar progress (0..1) for a total of points. */
export function levelForPoints(points) {
    let rest = whole(points);
    let level = 0;
    while (rest >= pointsToNext(level)) {
        rest -= pointsToNext(level);
        level++;
    }
    return { level, points: rest, progress: rest / pointsToNext(level) };
}

/** Points from level `from` to level `to` (0 when `to` is not above `from`). */
export function pointsBetween(from, to) {
    return Math.max(0, pointsForLevel(to) - pointsForLevel(from));
}

/** How many top-option enchants (level 30 needed, 3 levels taken) a level allows. */
export function enchantsFrom(level) {
    const l = whole(level);
    return l < ENCHANT_MIN_LEVEL ? 0 : Math.floor((l - ENCHANT_MIN_LEVEL) / ENCHANT_COST) + 1;
}

/** Points to repair `durability` with Mending. */
export function mendingPoints(durability) {
    return Math.ceil(whole(durability) / MENDING_PER_POINT);
}

/** Points a player drops on death: 7 per level, at most 100. */
export function deathDrop(level) {
    return Math.min(7 * whole(level), 100);
}

/** How many of a source give `points`. */
export function sourceCount(points, xp) {
    return xp > 0 ? Math.ceil(whole(points) / xp) : Infinity;
}
