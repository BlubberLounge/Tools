/**
 * Inventory maths for the stack calculator (stackCalculator.js).
 *
 * Every unit is a number of slots; one slot holds one stack. A stack is 64,
 * 16 or 1 items depending on the item. Containers with 27 slots: chest,
 * barrel, ender chest, shulker box, minecart with chest. A double chest has
 * 54 slots, a hopper 5, a dispenser or dropper 9, the player inventory 36
 * (without armour and offhand). A full shulker box goes into one slot, so a
 * chest of full shulker boxes holds 27 x 27 stacks.
 */

export const STACK_SIZES = [64, 16, 1];

export const SHULKER_SLOTS = 27;

/** Units in slots (stacks), smallest first. `item` is the only unit not counted in slots. */
export const UNITS = [
    { id: 'item', slots: null },
    { id: 'stack', slots: 1 },
    { id: 'hopper', slots: 5 },
    { id: 'dispenser', slots: 9 },
    { id: 'chest', slots: 27 },
    { id: 'shulker', slots: SHULKER_SLOTS },
    { id: 'inventory', slots: 36 },
    { id: 'doubleChest', slots: 54 },
    { id: 'shulkerChest', slots: 27 * SHULKER_SLOTS },
    { id: 'shulkerDoubleChest', slots: 54 * SHULKER_SLOTS },
];

/** Greedy breakdowns, largest unit first. */
export const BREAKDOWNS = {
    shulker: ['shulkerDoubleChest', 'shulker', 'stack', 'item'],
    chest: ['doubleChest', 'chest', 'stack', 'item'],
};

/** Items in one unit for a stack size. */
export function unitSize(unitId, stackSize) {
    const unit = UNITS.find(u => u.id === unitId);
    if (!unit)
        throw new Error(`Unknown unit: ${unitId}`);
    return unit.slots === null ? 1 : unit.slots * stackSize;
}

/**
 * Total items of mixed input rows [{amount, unit}]. Amounts may be fractions
 * ("1.5 shulker"); the total is rounded up to whole items. Empty, negative or
 * invalid amounts count as 0.
 */
export function totalItems(rows, stackSize) {
    const sum = rows.reduce((total, row) => {
        const amount = Number(row.amount);
        if (!Number.isFinite(amount) || amount <= 0)
            return total;
        return total + amount * unitSize(row.unit, stackSize);
    }, 0);

    // guard against float noise just above a whole number
    return sum === 0 ? 0 : Math.ceil(sum - 1e-9);
}

/** The total in every unit as a fraction: [{unit, value}]. */
export function convertAll(total, stackSize) {
    return UNITS.map(u => ({ unit: u.id, value: total / unitSize(u.id, stackSize) }));
}

/** Split a total greedily into the given units: [{unit, count}], zero counts left out. */
export function breakdown(total, stackSize, unitIds) {
    let rest = total;
    const parts = [];
    for (const unitId of unitIds) {
        const size = unitSize(unitId, stackSize);
        const count = Math.floor(rest / size);
        rest -= count * size;
        if (count > 0)
            parts.push({ unit: unitId, count });
    }
    return parts;
}

/** Slots the total fills (partial stacks take a whole slot). */
export function slotsNeeded(total, stackSize) {
    return Math.ceil(total / stackSize);
}
