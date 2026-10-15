/**
 * Box models for the non-cube block shapes, in block units 0..16 like the
 * game's own models. Boxes are written for a block facing north and rotated
 * to the block's `facing`. Shapes that are not listed here render as a full
 * cube with the block's texture (see shapeBoxes()).
 *
 * A box is {from: [x, y, z], to: [x, y, z], faces?: string[], tex?: string}:
 * `faces` limits the faces that are drawn, `tex` replaces the face textures.
 */

const DIRS = ['north', 'east', 'south', 'west'];

function box(x0, y0, z0, x1, y1, z1, extra = {}) {
    return { from: [x0, y0, z0], to: [x1, y1, z1], ...extra };
}

/** Rotate a point around the block centre so that north maps to `facing`. */
function rotatePoint([x, y, z], facing) {
    const dx = x - 8, dy = y - 8, dz = z - 8;
    let r;
    switch (facing) {
        case 'east': r = [-dz, dy, dx]; break;
        case 'south': r = [-dx, dy, -dz]; break;
        case 'west': r = [dz, dy, -dx]; break;
        case 'up': r = [dx, -dz, dy]; break;
        case 'down': r = [dx, dz, -dy]; break;
        default: return [x, y, z];
    }
    return [r[0] + 8, r[1] + 8, r[2] + 8];
}

export function rotateBox(b, facing) {
    if (!facing || facing === 'north')
        return b;
    const a = rotatePoint(b.from, facing);
    const c = rotatePoint(b.to, facing);
    return {
        ...b,
        from: [Math.min(a[0], c[0]), Math.min(a[1], c[1]), Math.min(a[2], c[2])],
        to: [Math.max(a[0], c[0]), Math.max(a[1], c[1]), Math.max(a[2], c[2])],
    };
}

/** Rotate a y-axis box onto the x or z axis. */
function alongAxis(b, axis) {
    if (axis === 'x')
        return { ...b, from: [b.from[1], b.from[0], b.from[2]], to: [b.to[1], b.to[0], b.to[2]] };
    if (axis === 'z')
        return { ...b, from: [b.from[0], b.from[2], b.from[1]], to: [b.to[0], b.to[2], b.to[1]] };
    return b;
}

const isOn = v => v !== undefined && v !== 'false' && v !== 'none';

/**
 * Boxes of a resolved block, or null for a full cube, or the string 'cross'
 * for plants drawn as two crossed planes.
 */
export function shapeBoxes(block) {
    const p = block.props;
    const facing = p.facing ?? 'north';
    const rot = list => list.map(b => rotateBox(b, facing));

    switch (block.shape) {
        case 'cube':
            return null;
        case 'none':
            return [];
        case 'cross':
            return 'cross';
        case 'slab':
            return [p.type === 'top' ? box(0, 8, 0, 16, 16, 16) : box(0, 0, 0, 16, 8, 16)];
        case 'stairs':
            return rot(p.half === 'top'
                ? [box(0, 8, 0, 16, 16, 16), box(0, 0, 0, 16, 8, 8)]
                : [box(0, 0, 0, 16, 8, 16), box(0, 8, 0, 16, 16, 8)]);
        case 'trapdoor':
            if (p.open === 'true')
                return rot([box(0, 0, 13, 16, 16, 16)]);
            return [p.half === 'top' ? box(0, 13, 0, 16, 16, 16) : box(0, 0, 0, 16, 3, 16)];
        case 'hopper': {
            const spout = facing === 'down'
                ? box(6, 0, 6, 10, 4, 10)
                : rotateBox(box(6, 4, 0, 10, 8, 4), facing);
            return [box(0, 10, 0, 16, 16, 16), box(4, 4, 4, 12, 10, 12), spout];
        }
        case 'path':
            return [box(0, 0, 0, 16, 15, 16)];
        case 'carpet':
            return [box(0, 0, 0, 16, 1, 16)];
        case 'snow': {
            const layers = Math.max(1, Math.min(8, parseInt(p.layers ?? '1', 10) || 1));
            return layers >= 8 ? null : [box(0, 0, 0, 16, layers * 2, 16)];
        }
        case 'pressure_plate':
            return [box(1, 0, 1, 15, 1, 15)];
        case 'button':
            if (p.face === 'floor')
                return rot([box(5, 0, 6, 11, 2, 10)]);
            if (p.face === 'ceiling')
                return rot([box(5, 14, 6, 11, 16, 10)]);
            return rot([box(5, 6, 14, 11, 10, 16)]);
        case 'lever':
            if (p.face === 'floor')
                return rot([box(5, 0, 4, 11, 3, 12), box(7, 3, 7, 9, 10, 9, { tex: 'lever' })]);
            if (p.face === 'ceiling')
                return rot([box(5, 13, 4, 11, 16, 12), box(7, 6, 7, 9, 13, 9, { tex: 'lever' })]);
            return rot([box(5, 4, 13, 11, 12, 16), box(7, 7, 6, 9, 9, 13, { tex: 'lever' })]);
        case 'torch':
            return [box(7, 0, 7, 9, 10, 9)];
        case 'wall_torch':
            return rot([box(7, 3, 12, 9, 13, 16)]);
        case 'diode':
            return [box(0, 0, 0, 16, 2, 16)];
        case 'daylight_detector':
            return [box(0, 0, 0, 16, 6, 16)];
        case 'rail':
            return [box(0, 0, 0, 16, 1, 16, { faces: ['up', 'down'] })];
        case 'wire':
            return [box(0, 0, 0, 16, 0.25, 16, { faces: ['up'] })];
        case 'flat':
            return [box(0, 0, 0, 16, 0.25, 16, { faces: ['up', 'down'] })];
        case 'fence': {
            const boxes = [box(6, 0, 6, 10, 16, 10)];
            for (const d of DIRS)
                if (isOn(p[d]))
                    boxes.push(rotateBox(box(7, 12, 0, 9, 15, 6), d), rotateBox(box(7, 6, 0, 9, 9, 6), d));
            return boxes;
        }
        case 'fence_gate': {
            const boxes = [box(0, 5, 7, 2, 16, 9), box(14, 5, 7, 16, 16, 9)];
            if (p.open !== 'true')
                boxes.push(box(2, 6, 7, 14, 9, 9), box(2, 12, 7, 14, 15, 9));
            return rot(boxes);
        }
        case 'wall': {
            const boxes = [];
            if (p.up === undefined || isOn(p.up))
                boxes.push(box(4, 0, 4, 12, 16, 12));
            for (const d of DIRS)
                if (isOn(p[d]))
                    boxes.push(rotateBox(box(5, 0, 0, 11, p[d] === 'tall' ? 16 : 14, 8), d));
            return boxes.length ? boxes : [box(4, 0, 4, 12, 16, 12)];
        }
        case 'pane': {
            const boxes = [box(7, 0, 7, 9, 16, 9)];
            for (const d of DIRS)
                if (isOn(p[d]))
                    boxes.push(rotateBox(box(7, 0, 0, 9, 16, 7), d));
            return boxes;
        }
        case 'chest':
            return [box(1, 0, 1, 15, 14, 15)];
        case 'bed':
            return [box(0, 0, 0, 16, 9, 16)];
        case 'door':
            return rot([box(0, 0, 13, 16, 16, 16)]);
        case 'ladder':
            return rot([box(0, 0, 15, 16, 16, 16)]);
        case 'vine': {
            const boxes = [];
            for (const d of DIRS)
                if (isOn(p[d]))
                    boxes.push(rotateBox(box(0, 0, 0, 16, 16, 1), d));
            if (isOn(p.up))
                boxes.push(box(0, 15, 0, 16, 16, 16));
            return boxes.length ? boxes : [box(0, 0, 0, 16, 16, 1)];
        }
        case 'sign':
            return [box(7, 0, 7, 9, 9, 9), box(0, 8, 7, 16, 16, 9)];
        case 'wall_sign':
            return rot([box(0, 4, 14, 16, 12, 16)]);
        case 'cactus':
            return [box(1, 0, 1, 15, 16, 15)];
        case 'bamboo':
            return [box(6, 0, 6, 10, 16, 10)];
        case 'lantern':
            return [box(5, 0, 5, 11, 7, 11), box(6, 7, 6, 10, 9, 10)];
        case 'chain':
            return [alongAxis(box(6.5, 0, 6.5, 9.5, 16, 9.5), p.axis ?? 'y')];
        case 'rod': {
            const axis = facing === 'east' || facing === 'west' ? 'x' : facing === 'north' || facing === 'south' ? 'z' : 'y';
            return [alongAxis(box(7, 0, 7, 9, 16, 9), p.facing ? axis : 'y')];
        }
        case 'campfire':
            return [box(0, 0, 0, 16, 7, 16)];
        case 'piston_head': {
            // the rod uses the side texture on all faces
            const sideTex = block.faces[facing === 'up' || facing === 'down' ? 'north' : 'up'];
            return rot([box(0, 0, 0, 16, 16, 4), box(6, 6, 4, 10, 10, 16, { tex: sideTex })]);
        }
        default:
            // unknown shape: full cube with the block's texture
            return null;
    }
}
