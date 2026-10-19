/**
 * Block counts for boxes and rooms, used by materialCalculator.js.
 *
 * A box is length x width x height blocks. Along every axis a block is either
 * on the shell (within `thickness` of a face) or inside. Counting the blocks
 * per axis and multiplying keeps every shape a formula, so large builds cost
 * nothing to compute.
 */

export const SHAPES = ['solid', 'hollow', 'walls', 'wallsFloor', 'frame'];

export const MAX_SIZE = 100000;

const clampSize = value => {
    const n = Math.floor(Number(value));
    return Number.isFinite(n) ? Math.min(Math.max(n, 0), MAX_SIZE) : 0;
};

/** Shell and inner block count along one axis. */
function axis(size, thickness) {
    const shell = Math.min(size, 2 * thickness);
    return { shell, inner: size - shell };
}

/**
 * Blocks for a shape: {blocks, inside}. `inside` is the air (or water) volume
 * the shape encloses or, for the frame, the room inside the edges.
 */
export function shapeBlocks(shape, size, thickness = 1) {
    const l = clampSize(size.length);
    const w = clampSize(size.width);
    const h = clampSize(size.height);
    const t = Math.max(1, clampSize(thickness));

    const total = l * w * h;
    const L = axis(l, t);
    const W = axis(w, t);
    const H = axis(h, t);
    const hollowInside = L.inner * W.inner * H.inner;

    switch (shape) {
        case 'solid':
            return { blocks: total, inside: 0 };
        case 'hollow':
            return { blocks: total - hollowInside, inside: hollowInside };
        case 'walls': {
            const inside = L.inner * W.inner * h;
            return { blocks: total - inside, inside };
        }
        case 'wallsFloor': {
            const inside = L.inner * W.inner * Math.max(0, h - t);
            return { blocks: total - inside, inside };
        }
        case 'frame': {
            // blocks on the shell along at least two axes = the edges
            const blocks = L.shell * W.shell * H.shell
                + L.shell * W.shell * H.inner
                + L.shell * W.inner * H.shell
                + L.inner * W.shell * H.shell;
            return { blocks, inside: hollowInside };
        }
        default:
            throw new Error(`Unknown shape: ${shape}`);
    }
}

