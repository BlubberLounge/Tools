/**
 * Nether portal maths (Java Edition), used by portalCalculator.js.
 *
 * - The Nether has a coordinate scale of 8: X and Z are divided by 8 on the
 *   way in and multiplied by 8 on the way out, Y stays the same.
 * - On arrival the game looks for the closest portal block in a square around
 *   the target position: 16 blocks in each direction in the Nether, 128 in the
 *   Overworld, over the full height. Closest means 3D distance, ties go to the
 *   lower Y. Without a portal in range it builds a new one near the target.
 * - A frame has an inner size of 2 x 3 up to 21 x 21 blocks.
 */

export const OVERWORLD = 'overworld';
export const NETHER = 'nether';

export const SCALE = 8;
export const SEARCH_RADIUS = { [OVERWORLD]: 128, [NETHER]: 16 };

export const FRAME_MIN = { width: 2, height: 3 };
export const FRAME_MAX = { width: 21, height: 21 };

/** Blocks per second on flat ground. */
export const SPEED = { walk: 4.317, sprint: 5.612 };

export function otherDimension(dimension) {
    return dimension === NETHER ? OVERWORLD : NETHER;
}

/**
 * Target block position in the other dimension for a position in `from`.
 * X and Z are converted exactly and then floored to a block, like the game does.
 */
export function convert(position, from) {
    const factor = from === OVERWORLD ? 1 / SCALE : SCALE;

    return {
        x: Math.floor(position.x * factor),
        y: position.y,
        z: Math.floor(position.z * factor),
    };
}

export function distance3d(a, b) {
    return Math.hypot(a.x - b.x, a.y - b.y, a.z - b.z);
}

export function distanceHorizontal(a, b) {
    return Math.hypot(a.x - b.x, a.z - b.z);
}

/** Whether `candidate` lies in the search square around `target` of `dimension`. */
export function inSearchRange(target, candidate, dimension) {
    const radius = SEARCH_RADIUS[dimension];

    return Math.abs(candidate.x - target.x) <= radius && Math.abs(candidate.z - target.z) <= radius;
}

/**
 * Portal the game picks when an entity goes through `portal`.
 *
 * `portals` is the list of all known portals ({ id, dimension, x, y, z }).
 * Returns { target, dimension, portal, distance }: `portal` is null when no
 * known portal is in range and the game would build a new one.
 */
export function findLink(portal, portals) {
    const dimension = otherDimension(portal.dimension);
    const target = convert(portal, portal.dimension);

    let best = null;
    let bestDistance = Infinity;
    for (const candidate of portals) {
        if (candidate.dimension !== dimension || !inSearchRange(target, candidate, dimension))
            continue;

        const d = distance3d(target, candidate);
        if (d < bestDistance || (d === bestDistance && candidate.y < best.y)) {
            best = candidate;
            bestDistance = d;
        }
    }

    return {
        target,
        dimension,
        portal: best,
        distance: best ? bestDistance : null,
    };
}

/**
 * Link of every portal: { portal, target, dimension, destination, distance,
 * status, returnsTo }. `status` is
 * - 'linked'  both portals lead to each other
 * - 'oneway'  the destination leads back to a different portal (`returnsTo`)
 * - 'new'     no portal in range, the game builds a new one
 */
export function analyseLinks(portals) {
    const links = new Map(portals.map(p => [p.id, findLink(p, portals)]));

    return portals.map(portal => {
        const link = links.get(portal.id);
        let status = 'new';
        let returnsTo = null;
        if (link.portal) {
            returnsTo = links.get(link.portal.id).portal;
            status = returnsTo && returnsTo.id === portal.id ? 'linked' : 'oneway';
        }

        return {
            portal,
            target: link.target,
            dimension: link.dimension,
            destination: link.portal,
            distance: link.distance,
            status,
            returnsTo,
        };
    });
}

/**
 * Where to build the partner of a portal so both lead to each other:
 * the converted position, and the converted position of that spot back.
 */
export function partnerFor(position, from) {
    const partner = convert(position, from);
    const back = convert(partner, otherDimension(from));

    return { partner, back, drift: distanceHorizontal(position, back) };
}

/**
 * Obsidian and portal blocks for an inner frame size. Returns null for a size
 * the game does not accept.
 */
export function frameMaterials(width, height) {
    if (!Number.isInteger(width) || !Number.isInteger(height))
        return null;
    if (width < FRAME_MIN.width || height < FRAME_MIN.height || width > FRAME_MAX.width || height > FRAME_MAX.height)
        return null;

    const withoutCorners = 2 * (width + height);

    return {
        withCorners: withoutCorners + 4,
        withoutCorners,
        portalBlocks: width * height,
        outer: { width: width + 2, height: height + 2 },
    };
}

/**
 * Distances between two points of the same dimension, with travel times.
 * `dimension` is the dimension both points are in.
 */
export function measure(a, b, dimension = OVERWORLD) {
    const horizontal = distanceHorizontal(a, b);
    const seconds = d => ({ walk: d / SPEED.walk, sprint: d / SPEED.sprint });

    // the same trip through the other dimension (horizontal only)
    const otherHorizontal = dimension === OVERWORLD ? horizontal / SCALE : horizontal * SCALE;

    return {
        dx: b.x - a.x,
        dy: b.y - a.y,
        dz: b.z - a.z,
        distance: distance3d(a, b),
        horizontal,
        manhattan: Math.abs(b.x - a.x) + Math.abs(b.y - a.y) + Math.abs(b.z - a.z),
        time: seconds(horizontal),
        other: {
            dimension: otherDimension(dimension),
            horizontal: otherHorizontal,
            time: seconds(otherHorizontal),
        },
    };
}
