import { describe, expect, it } from 'vitest';

import { readNbt, writeNbt } from '../../../scripts/minecraft/lib/nbt.js';
import { readSchematic, trimEmpty, unpackLitematicStates } from '../../../scripts/minecraft/lib/schematic.js';
import { convert } from '../../../scripts/minecraft/convert-schematic.mjs';
import { loadModel, validateModel } from '../../../resources/js/minecraft/viewer/model.js';
import { table } from './helpers.js';

const int = value => ({ type: 'int', value });
const short = value => ({ type: 'short', value });
const str = value => ({ type: 'string', value });
const compound = value => ({ type: 'compound', value });
const list = (itemType, value) => ({ type: 'list', itemType, value });

const META = { id: 'fixture', name: 'Fixture', sourceUrl: 'https://example.org/fixture', license: 'CC0-1.0' };

/** Pack values like Litematica: `bits` per entry, entries may span two longs. */
function packLitematic(values, bits) {
    const longs = new Array(Math.ceil(values.length * bits / 64)).fill(0n);
    values.forEach((v, i) => {
        const start = i * bits;
        const idx = Math.floor(start / 64);
        const off = start % 64;
        longs[idx] |= (BigInt(v) << BigInt(off)) & ((1n << 64n) - 1n);
        if (off + bits > 64)
            longs[idx + 1] |= BigInt(v) >> BigInt(64 - off);
    });
    return longs.map(l => BigInt.asIntN(64, l));
}

function varints(values) {
    const out = [];
    for (let v of values) {
        do {
            let b = v & 0x7f;
            v >>>= 7;
            if (v) b |= 0x80;
            out.push(b);
        } while (v);
    }
    return out;
}

describe('NBT', () => {
    it('round-trips typed tags', () => {
        const bytes = writeNbt('root', compound({
            a: int(5), s: str('hopper'), l: list('int', [int(1), int(2)]),
            longs: { type: 'longArray', value: [1n, -1n] },
        }));
        const { name, value } = readNbt(bytes);
        expect(name).toBe('root');
        expect(value.a).toBe(5);
        expect(value.s).toBe('hopper');
        expect(value.l).toEqual([1, 2]);
        expect([...value.longs]).toEqual([1n, -1n]);
    });
});

describe('schematic formats', () => {
    it('reads a Sponge v2 .schem', () => {
        // 2 x 1 x 2, order (y * length + z) * width + x
        const bytes = writeNbt('Schematic', compound({
            Version: int(2), DataVersion: int(3953),
            Width: short(2), Height: short(1), Length: short(2),
            Palette: compound({ 'minecraft:air': int(0), 'minecraft:hopper[enabled=true,facing=down]': int(1), 'minecraft:stone': int(2) }),
            BlockData: { type: 'byteArray', value: varints([1, 0, 0, 2]) },
        }));
        const s = readSchematic(bytes);
        expect(s.format).toBe('schem');
        expect(s.size).toEqual([2, 1, 2]);
        expect(s.palette[s.grid[0]]).toEqual({ name: 'minecraft:hopper', properties: { enabled: 'true', facing: 'down' } });
        expect(s.palette[s.grid[3]].name).toBe('minecraft:stone');
    });

    it('reads a Sponge v3 .schem with varints above 127', () => {
        const palette = { 'minecraft:air': int(0) };
        for (let i = 1; i <= 200; i++)
            palette[`minecraft:stone[n=${i}]`] = int(i);
        const bytes = writeNbt('', compound({
            Schematic: compound({
                Version: int(3), Width: short(2), Height: short(1), Length: short(1),
                Blocks: compound({ Palette: compound(palette), Data: { type: 'byteArray', value: varints([200, 1]) } }),
            }),
        }));
        const s = readSchematic(bytes);
        expect(s.palette[s.grid[0]].properties.n).toBe('200');
        expect(s.palette[s.grid[1]].properties.n).toBe('1');
    });

    it('reads a structure .nbt and leaves structure voids empty', () => {
        const bytes = writeNbt('', compound({
            size: list('int', [int(2), int(2), int(1)]),
            palette: list('compound', [
                compound({ Name: str('minecraft:observer'), Properties: compound({ facing: str('up'), powered: str('false') }) }),
                compound({ Name: str('minecraft:dirt') }),
            ]),
            blocks: list('compound', [
                compound({ pos: list('int', [int(0), int(0), int(0)]), state: int(1) }),
                compound({ pos: list('int', [int(1), int(1), int(0)]), state: int(0) }),
            ]),
        }));
        const s = readSchematic(bytes);
        expect(s.format).toBe('structure');
        expect(s.palette[s.grid[0]].name).toBe('minecraft:dirt');
        expect(s.grid[1]).toBe(0);
        expect(s.palette[s.grid[3]].name).toBe('minecraft:observer');
    });

    it('unpacks Litematica states that span two longs', () => {
        const values = Array.from({ length: 50 }, (_, i) => i % 5);
        expect([...unpackLitematicStates(packLitematic(values, 3), 3, 50)]).toEqual(values);
    });

    it('reads a .litematic with a negative-size region', () => {
        const palette = ['minecraft:air', 'minecraft:stone', 'minecraft:hopper', 'minecraft:glass', 'minecraft:dirt'];
        // 3 x 1 x 2 region: x fastest, then z
        const states = [1, 2, 3, 4, 0, 1];
        const bytes = writeNbt('', compound({
            MinecraftDataVersion: int(3953), Version: int(6),
            Regions: compound({
                main: compound({
                    Position: compound({ x: int(10), y: int(5), z: int(3) }),
                    Size: compound({ x: int(-3), y: int(1), z: int(2) }),
                    BlockStatePalette: list('compound', palette.map(n => compound(
                        n === 'minecraft:hopper'
                            ? { Name: str(n), Properties: compound({ facing: str('down') }) }
                            : { Name: str(n) }))),
                    BlockStates: { type: 'longArray', value: packLitematic(states, 3) },
                }),
            }),
        }));
        const s = readSchematic(bytes);
        expect(s.format).toBe('litematic');
        expect(s.size).toEqual([3, 1, 2]);
        expect(s.grid.length).toBe(6);
        expect(s.palette[s.grid[1]]).toEqual({ name: 'minecraft:hopper', properties: { facing: 'down' } });
        expect(s.grid[4]).toBe(0);
    });

    it('trims empty borders', () => {
        const grid = new Uint32Array(27);
        grid[(1 * 3 + 1) * 3 + 2] = 1;
        const t = trimEmpty({ size: [3, 3, 3], palette: [{ name: 'minecraft:air' }, { name: 'minecraft:stone' }], grid });
        expect(t.size).toEqual([1, 1, 1]);
        expect([...t.grid]).toEqual([1]);
    });
});

describe('converter', () => {
    it('turns a schematic into a valid model', () => {
        const bytes = writeNbt('', compound({
            size: list('int', [int(4), int(3), int(4)]),
            palette: list('compound', [
                compound({ Name: str('minecraft:hopper'), Properties: compound({ facing: str('east'), enabled: str('true') }) }),
                compound({ Name: str('minecraft:chest'), Properties: compound({ facing: str('north'), type: str('single'), waterlogged: str('false') }) }),
            ]),
            blocks: list('compound', [
                compound({ pos: list('int', [int(1), int(1), int(1)]), state: int(0) }),
                compound({ pos: list('int', [int(2), int(1), int(1)]), state: int(1) }),
            ]),
        }));
        const { format, model } = convert(bytes, { ...META, design: 'some-catalogue-design' });
        expect(format).toBe('structure');
        expect(model.size).toEqual([2, 1, 1]);
        expect(model.design).toBe('some-catalogue-design');
        expect(model.source).toEqual({ url: META.sourceUrl, license: META.license });
        expect(model.palette[1]).toEqual({ name: 'minecraft:hopper', properties: { facing: 'east' } });
        expect(validateModel(model, table.blocks)).toEqual([]);
        expect(loadModel(model).grid.length).toBe(2);
    });

    it('refuses models above 128 blocks per axis', () => {
        const bytes = writeNbt('', compound({
            size: list('int', [int(130), int(1), int(1)]),
            palette: list('compound', [compound({ Name: str('minecraft:stone') })]),
            blocks: list('compound', [
                compound({ pos: list('int', [int(0), int(0), int(0)]), state: int(0) }),
                compound({ pos: list('int', [int(129), int(0), int(0)]), state: int(0) }),
            ]),
        }));
        expect(() => convert(bytes, META)).toThrow(/at most 128/);
    });
});
