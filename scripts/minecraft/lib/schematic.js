/**
 * Reads the three public schematic formats into one dense block grid:
 *   .litematic       Litematica (all regions merged into one bounding box)
 *   .schem           Sponge schematic v1, v2 and v3 (WorldEdit, Amulet, ...)
 *   .nbt             vanilla structure block files
 *
 * Result: {size: [x, y, z], palette: [{name, properties}], grid: Uint32Array}
 * with the same grid order as the model format (x fastest, then z, then y).
 */
import { readNbt } from './nbt.js';
import { parseBlockState } from '../../../resources/js/minecraft/viewer/model.js';

const AIR = { name: 'minecraft:air', properties: {} };

function propsToStrings(props) {
    const out = {};
    for (const [k, v] of Object.entries(props || {}))
        out[k] = String(v);
    return out;
}

/** Detect the format from the NBT content. */
export function detectFormat(root) {
    const v = root.value;
    if (v.Regions)
        return 'litematic';
    if (v.Schematic || (v.Palette && v.BlockData) || (v.Blocks && v.Width !== undefined && v.Version !== undefined))
        return 'schem';
    if (Array.isArray(v.size) && Array.isArray(v.blocks))
        return 'structure';
    throw new Error('unknown schematic format (expected .litematic, .schem or structure .nbt)');
}

/** Read a schematic file's bytes. */
export function readSchematic(bytes) {
    const root = readNbt(bytes);
    const format = detectFormat(root);
    const result = format === 'litematic' ? fromLitematic(root.value)
        : format === 'schem' ? fromSponge(root.value)
            : fromStructure(root.value);
    return { format, ...result };
}

/* ---------------------------------------------------------------- litematic */

/** Unpack Litematica's tightly packed long array (entries may span two longs). */
export function unpackLitematicStates(longs, bits, count) {
    const out = new Uint32Array(count);
    const mask = (1n << BigInt(bits)) - 1n;
    for (let i = 0; i < count; i++) {
        const startBit = i * bits;
        const index = Math.floor(startBit / 64);
        const offset = startBit % 64;
        let value = BigInt.asUintN(64, longs[index]) >> BigInt(offset);
        if (offset + bits > 64)
            value |= BigInt.asUintN(64, longs[index + 1]) << BigInt(64 - offset);
        out[i] = Number(value & mask);
    }
    return out;
}

export function fromLitematic(v) {
    const regions = Object.values(v.Regions || {});
    if (regions.length === 0)
        throw new Error('litematic has no regions');

    // bounding box over all regions
    const boxes = regions.map(r => {
        const pos = [r.Position.x, r.Position.y, r.Position.z];
        const size = [r.Size.x, r.Size.y, r.Size.z];
        const min = pos.map((p, i) => (size[i] < 0 ? p + size[i] + 1 : p));
        return { r, min, abs: size.map(Math.abs) };
    });
    const min = [0, 1, 2].map(i => Math.min(...boxes.map(b => b.min[i])));
    const max = [0, 1, 2].map(i => Math.max(...boxes.map(b => b.min[i] + b.abs[i])));
    const size = max.map((m, i) => m - min[i]);

    const palette = [AIR];
    const keyIndex = new Map();
    const grid = new Uint32Array(size[0] * size[1] * size[2]);

    for (const { r, min: rmin, abs } of boxes) {
        const regionPalette = r.BlockStatePalette.map(p => ({ name: p.Name, properties: propsToStrings(p.Properties) }));
        const map = regionPalette.map(entry => {
            if (entry.name === 'minecraft:air')
                return 0;
            const key = JSON.stringify([entry.name, entry.properties]);
            if (!keyIndex.has(key)) {
                keyIndex.set(key, palette.length);
                palette.push(entry);
            }
            return keyIndex.get(key);
        });

        const volume = abs[0] * abs[1] * abs[2];
        const bits = Math.max(2, Math.ceil(Math.log2(regionPalette.length)));
        const states = unpackLitematicStates(r.BlockStates, bits, volume);

        for (let y = 0; y < abs[1]; y++)
            for (let z = 0; z < abs[2]; z++)
                for (let x = 0; x < abs[0]; x++) {
                    const value = map[states[(y * abs[2] + z) * abs[0] + x]] ?? 0;
                    if (value === 0)
                        continue;
                    const gx = rmin[0] - min[0] + x, gy = rmin[1] - min[1] + y, gz = rmin[2] - min[2] + z;
                    grid[(gy * size[2] + gz) * size[0] + gx] = value;
                }
    }

    return { size, palette, grid };
}

/* ------------------------------------------------------------------- sponge */

export function readVarints(bytes, count) {
    const out = new Uint32Array(count);
    let pos = 0;
    for (let i = 0; i < count; i++) {
        let value = 0, shift = 0, b;
        do {
            if (pos >= bytes.length)
                throw new Error('BlockData ends early');
            b = bytes[pos++] & 0xff;
            value |= (b & 0x7f) << shift;
            shift += 7;
        } while (b & 0x80);
        out[i] = value >>> 0;
    }
    return out;
}

export function fromSponge(v) {
    const s = v.Schematic ?? v;
    const size = [s.Width & 0xffff, s.Height & 0xffff, s.Length & 0xffff];
    const paletteTag = s.Blocks ? s.Blocks.Palette : s.Palette;
    const data = s.Blocks ? s.Blocks.Data : s.BlockData;
    if (!paletteTag || !data)
        throw new Error('schem has no block palette or block data');

    const maxId = Math.max(...Object.values(paletteTag));
    const palette = new Array(maxId + 1).fill(AIR);
    for (const [state, id] of Object.entries(paletteTag))
        palette[id] = parseBlockState(state);

    const volume = size[0] * size[1] * size[2];
    // Sponge order is (y * Length + z) * Width + x, the same as ours
    const grid = readVarints(new Uint8Array(data.buffer, data.byteOffset, data.byteLength), volume);
    return { size, palette, grid };
}

/* ---------------------------------------------------------------- structure */

export function fromStructure(v) {
    const size = v.size.map(Number);
    const states = v.palette ?? (v.palettes ? v.palettes[0] : null);
    if (!states)
        throw new Error('structure has no palette');

    // index 0 is air: cells without a block (structure void) stay empty
    const palette = [AIR, ...states.map(p => ({ name: p.Name, properties: propsToStrings(p.Properties) }))];
    const grid = new Uint32Array(size[0] * size[1] * size[2]);
    for (const b of v.blocks) {
        const [x, y, z] = b.pos.map(Number);
        grid[(y * size[2] + z) * size[0] + x] = b.state + 1;
    }
    return { size, palette, grid };
}

/* --------------------------------------------------------------------- trim */

const AIR_NAMES = new Set(['minecraft:air', 'minecraft:cave_air', 'minecraft:void_air', 'minecraft:structure_void']);

/** Cut empty borders so the model's origin is the min corner of its blocks. */
export function trimEmpty({ size, palette, grid }) {
    const air = palette.map(p => AIR_NAMES.has(p.name));
    const [sx, sy, sz] = size;
    const min = [sx, sy, sz], max = [-1, -1, -1];
    for (let y = 0; y < sy; y++)
        for (let z = 0; z < sz; z++)
            for (let x = 0; x < sx; x++)
                if (!air[grid[(y * sz + z) * sx + x]]) {
                    if (x < min[0]) min[0] = x;
                    if (y < min[1]) min[1] = y;
                    if (z < min[2]) min[2] = z;
                    if (x > max[0]) max[0] = x;
                    if (y > max[1]) max[1] = y;
                    if (z > max[2]) max[2] = z;
                }
    if (max[0] < 0)
        throw new Error('the schematic contains no blocks');

    const ns = [max[0] - min[0] + 1, max[1] - min[1] + 1, max[2] - min[2] + 1];
    const out = new Uint32Array(ns[0] * ns[1] * ns[2]);
    for (let y = 0; y < ns[1]; y++)
        for (let z = 0; z < ns[2]; z++)
            for (let x = 0; x < ns[0]; x++)
                out[(y * ns[2] + z) * ns[0] + x] = grid[((y + min[1]) * sz + (z + min[2])) * sx + (x + min[0])];
    return { size: ns, palette, grid: out };
}
