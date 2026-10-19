/**
 * Furnace maths (Java Edition), used by smeltCalculator.js.
 *
 * A furnace cooks one item in 10 s (200 ticks). A blast furnace or smoker
 * takes 5 s but burns its fuel twice as fast, so the items per fuel item are
 * the same in every machine. Fuel cannot be shared: every furnace needs whole
 * fuel items for its own share of the items.
 */

export const MACHINES = [
    { id: 'furnace', seconds: 10 },
    { id: 'fast', seconds: 5 },
];

/** Items a hopper moves per hour (8 ticks per item). */
export const HOPPER_PER_HOUR = 9000;

const whole = value => {
    const n = Math.floor(Number(value));
    return Number.isFinite(n) && n > 0 ? n : 0;
};

function machineSeconds(machineId) {
    const machine = MACHINES.find(m => m.id === machineId);
    if (!machine)
        throw new Error(`Unknown machine: ${machineId}`);
    return machine.seconds;
}

/** Items per furnace when `items` are split as evenly as possible: [{items, furnaces}]. */
export function split(items, furnaces) {
    const n = whole(items);
    const f = Math.max(1, whole(furnaces));
    const base = Math.floor(n / f);
    const extra = n % f;
    return [
        { items: base + 1, furnaces: extra },
        { items: base, furnaces: f - extra },
    ].filter(part => part.furnaces > 0 && part.items > 0);
}

/**
 * Time and fuel to cook `items` in `furnaces` machines with a fuel that cooks
 * `smelts` items. Returns {seconds, fuel, wasted, perHour}: `wasted` is the
 * share of the last fuel items that burns without anything to cook.
 */
export function smelt(items, furnaces, machineId, smelts) {
    const n = whole(items);
    const f = Math.max(1, whole(furnaces));
    const seconds = machineSeconds(machineId);
    const parts = split(n, f);

    const fuel = parts.reduce((sum, part) => sum + part.furnaces * Math.ceil(part.items / smelts - 1e-9), 0);
    const wasted = n === 0 ? 0 : fuel * smelts - n;

    return {
        seconds: (parts[0]?.items ?? 0) * seconds,
        fuel,
        wasted: Math.max(0, wasted),
        perHour: f * 3600 / seconds,
    };
}

/** Hoppers needed in parallel to feed (or empty) the furnaces. */
export function hoppersNeeded(perHour) {
    return Math.ceil(perHour / HOPPER_PER_HOUR);
}
