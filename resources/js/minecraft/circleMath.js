/**
 * Block grids for circles, ellipses, spheres and domes, used by
 * circleGenerator.js. Pure functions, no DOM.
 *
 * A block belongs to the shape when its centre lies inside the ellipse or
 * ellipsoid that fills the bounding box. A hollow shape keeps only the blocks
 * with a side neighbour outside (4 neighbours in 2D, 6 in 3D), so the outline
 * is closed without gaps. A dome is the upper half of a sphere, open at the
 * cut.
 */

export const SHAPES = ['circle', 'ellipse', 'sphere', 'dome'];

export const MAX_SIZE = { flat: 512, solid: 160 };

const clamp = (value, max) => {
    const n = Math.floor(Number(value));
    return Number.isFinite(n) ? Math.min(Math.max(n, 1), max) : 1;
};

/** Bounding box [x, y, z] of a shape for the given input sizes. */
export function boxOf(shape, size) {
    switch (shape) {
        case 'circle': {
            const d = clamp(size.diameter, MAX_SIZE.flat);
            return [d, 1, d];
        }
        case 'ellipse':
            return [clamp(size.width, MAX_SIZE.flat), 1, clamp(size.depth, MAX_SIZE.flat)];
        case 'sphere':
        case 'dome': {
            const d = clamp(size.diameter, MAX_SIZE.solid);
            return [d, d, d];
        }
        default:
            throw new Error(`Unknown shape: ${shape}`);
    }
}

/**
 * Generate a shape. Returns {size: [x, y, z], layers, counts, total}: one
 * Uint8Array per Y layer (bottom first), index z * x + x, 1 = block.
 */
export function generate(shape, size, hollow = false) {
    const [sx, sy, sz] = boxOf(shape, size);
    const flat = sy === 1;
    const rx = sx / 2, ry = sy / 2, rz = sz / 2;

    const inside = (x, y, z) => {
        if (x < 0 || z < 0 || x >= sx || z >= sz || y < 0 || y >= sy)
            return false;
        const dx = (x + 0.5 - rx) / rx;
        const dz = (z + 0.5 - rz) / rz;
        const dy = flat ? 0 : (y + 0.5 - ry) / ry;
        return dx * dx + dy * dy + dz * dz <= 1;
    };

    const keep = (x, y, z) => {
        if (!inside(x, y, z))
            return false;
        if (!hollow)
            return true;
        return !inside(x - 1, y, z) || !inside(x + 1, y, z)
            || !inside(x, y, z - 1) || !inside(x, y, z + 1)
            || (!flat && (!inside(x, y - 1, z) || !inside(x, y + 1, z)));
    };

    // a dome keeps the upper half; with an odd diameter the middle layer too
    const firstLayer = shape === 'dome' ? Math.floor(sy / 2) : 0;

    const layers = [];
    const counts = [];
    for (let y = firstLayer; y < sy; y++) {
        const layer = new Uint8Array(sx * sz);
        let count = 0;
        for (let z = 0; z < sz; z++)
            for (let x = 0; x < sx; x++)
                if (keep(x, y, z)) {
                    layer[z * sx + x] = 1;
                    count++;
                }
        layers.push(layer);
        counts.push(count);
    }

    return {
        size: [sx, sy - firstLayer, sz],
        layers,
        counts,
        total: counts.reduce((a, b) => a + b, 0),
    };
}

/** Lengths of the block runs in one row of a layer, for building row by row. */
export function runs(layer, width, row) {
    const result = [];
    let length = 0;
    for (let x = 0; x < width; x++) {
        if (layer[row * width + x]) {
            length++;
        } else if (length > 0) {
            result.push(length);
            length = 0;
        }
    }
    if (length > 0)
        result.push(length);
    return result;
}
