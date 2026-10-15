/**
 * Farm model format (public/minecraft/models/{id}.json), shared by the viewer
 * and the converter scripts in scripts/minecraft/.
 *
 * {
 *   "format": 1,
 *   "id": "example-sugar-cane-module",     // file name, kebab-case
 *   "design": "catalogue-design-id" | null, // farm catalogue design this model shows
 *   "name": "...", "version": "1.21",
 *   "size": [x, y, z],                      // Y up, origin at the model's min corner
 *   "palette": [{"name": "minecraft:air"}, {"name": "minecraft:hopper", "properties": {"facing": "down"}}],
 *   "blocks": [run, paletteIndex, run, paletteIndex, ...],  // run-length encoded grid
 *   "source": {"url": "...", "author": "...", "license": "...", "note": "..."}
 * }
 *
 * Grid order: x fastest, then z, then y (index = (y * sizeZ + z) * sizeX + x).
 * Palette entry 0 is always air.
 */

export const MODEL_FORMAT = 1;
export const MAX_SIZE = 128;
export const AIR = 'minecraft:air';

/** Block state properties the viewer uses; the converter drops all others. */
export const KEPT_PROPERTIES = [
    'facing', 'half', 'shape', 'type', 'axis', 'open', 'face', 'part', 'hinge', 'attachment',
    'north', 'east', 'south', 'west', 'up', 'waterlogged', 'level', 'layers',
];

const ID_PATTERN = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;

export function gridIndex(size, x, y, z) {
    return (y * size[2] + z) * size[0] + x;
}

/**
 * Run-length encode a grid of palette indices.
 * @param {ArrayLike<number>} grid
 * @returns {number[]}
 */
export function encodeBlocks(grid) {
    const out = [];
    let i = 0;
    while (i < grid.length) {
        const value = grid[i];
        let run = 1;
        while (i + run < grid.length && grid[i + run] === value)
            run++;
        out.push(run, value);
        i += run;
    }
    return out;
}

/**
 * Decode run-length encoded blocks into a Uint16Array of `total` cells.
 */
export function decodeBlocks(rle, total) {
    if (!Array.isArray(rle) || rle.length % 2 !== 0)
        throw new Error('blocks must be a flat list of [run, index] pairs');

    const grid = new Uint16Array(total);
    let pos = 0;
    for (let i = 0; i < rle.length; i += 2) {
        const run = rle[i];
        const value = rle[i + 1];
        if (!Number.isInteger(run) || run < 1 || !Number.isInteger(value) || value < 0)
            throw new Error(`invalid run at ${i}`);
        if (pos + run > total)
            throw new Error('blocks are longer than the model size');
        if (value !== 0)
            grid.fill(value, pos, pos + run);
        pos += run;
    }
    if (pos !== total)
        throw new Error(`blocks cover ${pos} cells, the size needs ${total}`);

    return grid;
}

/** "minecraft:hopper[facing=down,enabled=true]" -> {name, properties} */
export function parseBlockState(text) {
    const match = /^([^[\]]+)(?:\[(.*)\])?$/.exec(text.trim());
    if (!match)
        throw new Error(`invalid block state "${text}"`);

    let name = match[1];
    if (!name.includes(':'))
        name = 'minecraft:' + name;

    const properties = {};
    if (match[2])
        for (const pair of match[2].split(',')) {
            const [key, value] = pair.split('=');
            if (key && value !== undefined)
                properties[key.trim()] = value.trim();
        }

    return { name, properties };
}

/** Stable key of a palette entry, e.g. "minecraft:hopper[facing=down]". */
export function blockKey(entry) {
    const props = entry.properties || {};
    const keys = Object.keys(props).sort();
    if (keys.length === 0)
        return entry.name;
    return entry.name + '[' + keys.map(k => `${k}=${props[k]}`).join(',') + ']';
}

/** Keep only the properties the viewer reads. */
export function filterProperties(properties) {
    const out = {};
    for (const key of KEPT_PROPERTIES)
        if (properties && properties[key] !== undefined)
            out[key] = String(properties[key]);
    return out;
}

/**
 * Check a raw model JSON object. Returns a list of problems (empty when valid).
 * `knownBlocks` (optional) is a Set or object of known block ids.
 */
export function validateModel(json, knownBlocks = null) {
    const errors = [];
    if (!json || typeof json !== 'object')
        return ['model is not an object'];

    if (json.format !== MODEL_FORMAT)
        errors.push(`format must be ${MODEL_FORMAT}`);
    if (typeof json.id !== 'string' || !ID_PATTERN.test(json.id))
        errors.push('id must be kebab-case');
    if (json.design !== null && json.design !== undefined && (typeof json.design !== 'string' || !ID_PATTERN.test(json.design)))
        errors.push('design must be a kebab-case catalogue id or null');
    if (typeof json.name !== 'string' || json.name === '')
        errors.push('name is missing');

    const size = json.size;
    if (!Array.isArray(size) || size.length !== 3 || !size.every(n => Number.isInteger(n) && n >= 1 && n <= MAX_SIZE))
        errors.push(`size must be [x, y, z] with 1..${MAX_SIZE} each`);

    if (!json.source || typeof json.source.url !== 'string' || !/^https?:\/\//.test(json.source.url))
        errors.push('source.url is missing');
    if (!json.source || typeof json.source.license !== 'string' || json.source.license === '')
        errors.push('source.license is missing');

    const palette = json.palette;
    if (!Array.isArray(palette) || palette.length === 0 || palette[0]?.name !== AIR) {
        errors.push('palette must start with minecraft:air');
    } else {
        const seen = new Set();
        palette.forEach((entry, i) => {
            if (!entry || typeof entry.name !== 'string') {
                errors.push(`palette[${i}] has no name`);
                return;
            }
            const key = blockKey(entry);
            if (seen.has(key))
                errors.push(`palette[${i}] duplicates ${key}`);
            seen.add(key);
            if (knownBlocks && !(knownBlocks instanceof Set ? knownBlocks.has(entry.name) : entry.name in knownBlocks))
                errors.push(`unknown block id ${entry.name}`);
        });
    }

    if (errors.length === 0) {
        try {
            const grid = decodeBlocks(json.blocks, size[0] * size[1] * size[2]);
            for (let i = 0; i < grid.length; i++)
                if (grid[i] >= palette.length) {
                    errors.push(`palette index ${grid[i]} out of range`);
                    break;
                }
        } catch (e) {
            errors.push(e.message);
        }
    }

    return errors;
}

/**
 * Parse and validate a model JSON object into the in-memory model.
 * @returns {{id, design, name, version, size: number[], palette: object[], grid: Uint16Array, source: object}}
 */
export function loadModel(json, knownBlocks = null) {
    const errors = validateModel(json, knownBlocks);
    if (errors.length)
        throw new Error('Invalid model: ' + errors.join('; '));

    const size = json.size.slice();
    return {
        id: json.id,
        design: json.design ?? null,
        name: json.name,
        version: json.version ?? null,
        size,
        palette: json.palette.map(p => ({ name: p.name, properties: { ...(p.properties || {}) } })),
        grid: decodeBlocks(json.blocks, size[0] * size[1] * size[2]),
        source: { ...json.source },
    };
}

/**
 * Build a model JSON object from a dense grid (used by the converter).
 * Properties are reduced to KEPT_PROPERTIES, equal entries are merged,
 * unused entries dropped and air is moved to index 0.
 */
export function buildModelJson({ id, design = null, name, version = null, size, palette, grid, source }) {
    const AIRS = ['minecraft:air', 'minecraft:cave_air', 'minecraft:void_air', 'minecraft:structure_void'];
    const cleaned = palette.map(entry => {
        if (AIRS.includes(entry.name))
            return null;
        const clean = { name: entry.name };
        const props = filterProperties(entry.properties);
        if (Object.keys(props).length)
            clean.properties = props;
        return clean;
    });

    const newPalette = [{ name: AIR }];
    const keyToIndex = new Map();
    const remap = new Int32Array(palette.length).fill(-1);
    const out = new Uint16Array(grid.length);

    for (let i = 0; i < grid.length; i++) {
        const src = grid[i];
        let idx = remap[src];
        if (idx === -1) {
            const entry = cleaned[src];
            if (entry === null || entry === undefined) {
                idx = 0;
            } else {
                const key = blockKey(entry);
                idx = keyToIndex.get(key);
                if (idx === undefined) {
                    idx = newPalette.length;
                    newPalette.push(entry);
                    keyToIndex.set(key, idx);
                }
            }
            remap[src] = idx;
        }
        out[i] = idx;
    }

    return {
        format: MODEL_FORMAT,
        id,
        design,
        name,
        version,
        size,
        palette: newPalette,
        blocks: encodeBlocks(out),
        source,
    };
}

/** Model JSON text: one line per field and palette entry, blocks on one line. */
export function stringifyModel(model) {
    const lines = ['{'];
    const keys = Object.keys(model);
    keys.forEach((key, i) => {
        const comma = i < keys.length - 1 ? ',' : '';
        if (key === 'palette') {
            lines.push('    "palette": [');
            model.palette.forEach((p, j) => lines.push('        ' + JSON.stringify(p) + (j < model.palette.length - 1 ? ',' : '')));
            lines.push('    ]' + comma);
        } else {
            lines.push(`    ${JSON.stringify(key)}: ${JSON.stringify(model[key])}${comma}`);
        }
    });
    lines.push('}', '');
    return lines.join('\n');
}
