/**
 * Resolves palette entries against the block table
 * (resources/minecraft/viewer/blocks.json) into render definitions:
 * shape, render layer, colours and one texture name per face.
 */

export const FACES = ['up', 'down', 'north', 'south', 'east', 'west'];

export const FACE_NORMALS = {
    up: [0, 1, 0],
    down: [0, -1, 0],
    north: [0, 0, -1],
    south: [0, 0, 1],
    east: [1, 0, 0],
    west: [-1, 0, 0],
};

export const OPPOSITE = { up: 'down', down: 'up', north: 'south', south: 'north', east: 'west', west: 'east' };

const AXIS_FACES = { x: ['east', 'west'], y: ['up', 'down'], z: ['north', 'south'] };

export function hexToRgb(hex) {
    const n = parseInt(String(hex).replace('#', ''), 16);
    return [((n >> 16) & 255) / 255, ((n >> 8) & 255) / 255, (n & 255) / 255];
}

/** Stable grey-ish colour for blocks missing from the table. */
export function fallbackColor(name) {
    let h = 0;
    for (let i = 0; i < name.length; i++)
        h = (h * 31 + name.charCodeAt(i)) >>> 0;
    const v = 0.45 + (h % 30) / 100;
    return [v, v * 0.95, v * 0.9];
}

/** Texture name per face for a texture spec, orientation mode and block properties. */
export function faceTextures(tex, orient, props, fallbackName) {
    const faces = {};
    if (tex === undefined || tex === null)
        tex = fallbackName;
    if (typeof tex === 'string') {
        for (const f of FACES)
            faces[f] = tex;
        return faces;
    }

    const any = tex.side ?? tex.all ?? Object.values(tex)[0];
    const side = tex.side ?? any;
    const top = tex.top ?? tex.end ?? side;
    const bottom = tex.bottom ?? tex.end ?? top;

    for (const f of FACES)
        faces[f] = tex[f] ?? side;
    faces.up = top;
    faces.down = bottom;

    if (orient === 'axis') {
        const axis = props.axis ?? 'y';
        const end = tex.end ?? top;
        for (const f of FACES)
            faces[f] = AXIS_FACES[axis]?.includes(f) ? end : side;
    } else if (orient === 'facing' || orient === 'horizontal') {
        const facing = props.facing ?? 'north';
        if (FACE_NORMALS[facing] && tex.front) {
            faces[facing] = tex.front;
            const back = OPPOSITE[facing];
            if (orient === 'facing')
                faces[back] = tex.back ?? side;
            else if (back === 'up' || back === 'down')
                faces[back] = side;
        }
    }

    return faces;
}

/**
 * Render definition for one palette entry.
 * @param {{name: string, properties?: object}} entry
 * @param {{blocks: object}} table
 */
export function resolveBlock(entry, table) {
    const def = table.blocks[entry.name];
    const props = entry.properties || {};
    const id = entry.name.includes(':') ? entry.name.split(':')[1] : entry.name;

    let shape = def?.shape ?? 'cube';
    if (shape === 'slab' && props.type === 'double')
        shape = 'cube';

    let faces = faceTextures(def?.tex, def?.orient, props, id);
    if (shape === 'door' && def?.tex && typeof def.tex === 'object') {
        const t = props.half === 'upper' ? def.tex.top : def.tex.bottom;
        faces = faceTextures(t, null, props, id);
    }

    const layer = def?.layer ?? 'solid';
    const fullCube = shape === 'cube';

    return {
        name: entry.name,
        known: def !== undefined,
        shape,
        layer,
        props,
        faces,
        color: def ? hexToRgb(def.color) : fallbackColor(entry.name),
        tint: def?.tint ? hexToRgb(def.tint) : null,
        tintFaces: def?.tintFaces ?? null,
        alpha: def?.alpha ?? 1,
        cullSame: !!def?.cullSame,
        fullCube,
        // a face next to this block is hidden
        occludes: fullCube && layer === 'solid' && (def?.alpha ?? 1) >= 1,
        invisible: shape === 'none',
    };
}

/**
 * Item for the material list: {item, stack, count} or null when the block is
 * not collected (air, piston heads, fluids that flow, upper door halves ...).
 */
export function materialFor(entry, table) {
    const def = table.blocks[entry.name];
    const props = entry.properties || {};
    if (def && def.item === false)
        return null;
    if (def?.shape === 'none' && def.item === undefined)
        return null;

    // only source blocks of fluids need a bucket
    if (props.level !== undefined && props.level !== '0')
        return null;
    // two-block things count once
    if (props.half === 'upper' && def?.shape === 'door')
        return null;
    if (props.part === 'head')
        return null;

    let count = 1;
    if (def?.shape === 'slab' && props.type === 'double')
        count = 2;
    if (entry.name.endsWith(':snow') && props.layers)
        count = parseInt(props.layers, 10) || 1;

    return {
        item: def?.item ?? entry.name,
        stack: def?.stack ?? 64,
        count,
    };
}
