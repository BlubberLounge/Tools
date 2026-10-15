/**
 * Slice view logic: one layer of a model along Y (top-down), X or Z, the
 * blocks behind it (shown faded), block counts per layer and the material
 * list for the whole model. Pure functions, no DOM.
 */
import { materialFor } from './blocks.js';

export const AXES = ['y', 'x', 'z'];
export const SHULKER_SLOTS = 27;

/** Number of layers along an axis. */
export function layerCount(size, axis) {
    return size[{ x: 0, y: 1, z: 2 }[axis]];
}

/**
 * The 2D plane of a slice: width/height and a mapping from a cell (u, v) to
 * the model position. v = 0 is the top row of the picture.
 *   y: top-down, u = x (east to the right), v = z (north at the top)
 *   x: one X layer, u = z, v = y (up at the top), blocks behind = lower X
 *   z: one Z layer, u = x, v = y (up at the top), blocks behind = lower Z
 */
export function slicePlane(size, axis) {
    const [sx, sy, sz] = size;
    if (axis === 'y')
        return { width: sx, height: sz, pos: (u, v, i) => [u, i, v], depthStep: [0, -1, 0] };
    if (axis === 'x')
        return { width: sz, height: sy, pos: (u, v, i) => [i, sy - 1 - v, u], depthStep: [-1, 0, 0] };
    return { width: sx, height: sy, pos: (u, v, i) => [u, sy - 1 - v, i], depthStep: [0, 0, -1] };
}

function cellAt(model, x, y, z) {
    const [sx, , sz] = model.size;
    return model.grid[(y * sz + z) * sx + x];
}

/**
 * Cells of one layer plus, for empty cells, the nearest block behind the
 * layer (lower Y for the top-down view) and its distance.
 * @returns {{width, height, cells: Uint16Array, behind: Uint16Array, depth: Uint8Array}}
 */
export function sliceLayer(model, axis, index, maxDepth = 8) {
    const { width, height, pos, depthStep } = slicePlane(model.size, axis);
    const cells = new Uint16Array(width * height);
    const behind = new Uint16Array(width * height);
    const depth = new Uint8Array(width * height);

    for (let v = 0; v < height; v++)
        for (let u = 0; u < width; u++) {
            const [x, y, z] = pos(u, v, index);
            const i = v * width + u;
            cells[i] = cellAt(model, x, y, z);
            if (cells[i] !== 0)
                continue;
            for (let d = 1; d <= maxDepth; d++) {
                const bx = x + depthStep[0] * d, by = y + depthStep[1] * d, bz = z + depthStep[2] * d;
                if (bx < 0 || by < 0 || bz < 0)
                    break;
                const b = cellAt(model, bx, by, bz);
                if (b !== 0) {
                    behind[i] = b;
                    depth[i] = d;
                    break;
                }
            }
        }

    return { width, height, cells, behind, depth };
}

/**
 * Block counts of one layer, most common first: [{name, count}].
 * Block states of the same block id are counted together.
 */
export function layerCounts(model, axis, index) {
    const { cells } = sliceLayer(model, axis, index, 0);
    const counts = new Map();
    for (const idx of cells) {
        if (idx === 0)
            continue;
        const name = model.palette[idx].name;
        counts.set(name, (counts.get(name) ?? 0) + 1);
    }
    return [...counts.entries()]
        .map(([name, count]) => ({ name, count }))
        .sort((a, b) => b.count - a.count || a.name.localeCompare(b.name));
}

/** Split a count into stacks, rest and shulker boxes. */
export function stacksOf(count, stack = 64) {
    return {
        stacks: Math.floor(count / stack),
        rest: count % stack,
        shulkers: count / (stack * SHULKER_SLOTS),
    };
}

/**
 * Material list of the whole model: [{item, count, stack, stacks, rest, shulkers}],
 * largest first. Blocks without an item (air, piston heads, flowing water,
 * upper door halves) are left out; double slabs count twice.
 */
export function materialList(model, table) {
    const perEntry = model.palette.map(entry => (entry.name === 'minecraft:air' ? null : materialFor(entry, table)));
    const paletteCounts = new Uint32Array(model.palette.length);
    for (const idx of model.grid)
        paletteCounts[idx]++;

    const items = new Map();
    perEntry.forEach((m, idx) => {
        if (!m || paletteCounts[idx] === 0)
            return;
        const current = items.get(m.item) ?? { item: m.item, count: 0, stack: m.stack };
        current.count += paletteCounts[idx] * m.count;
        items.set(m.item, current);
    });

    return [...items.values()]
        .map(i => ({ ...i, ...stacksOf(i.count, i.stack) }))
        .sort((a, b) => b.count - a.count || a.item.localeCompare(b.item));
}

