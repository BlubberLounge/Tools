import { describe, expect, it } from 'vitest';
import { readdirSync, readFileSync } from 'node:fs';

import {
    blockKey, buildModelJson, decodeBlocks, encodeBlocks, loadModel, parseBlockState, validateModel,
} from '../../../resources/js/minecraft/viewer/model.js';
import { table } from './helpers.js';

const validJson = () => ({
    format: 1,
    id: 'tiny',
    design: null,
    name: 'Tiny',
    size: [2, 1, 2],
    palette: [{ name: 'minecraft:air' }, { name: 'minecraft:stone' }],
    blocks: [3, 1, 1, 0],
    source: { url: 'https://example.org/tiny.litematic', license: 'CC0-1.0' },
});

describe('run-length encoding', () => {
    it('round-trips a grid', () => {
        const grid = Uint16Array.from([0, 0, 1, 1, 1, 2, 0, 0]);
        const rle = encodeBlocks(grid);
        expect(rle).toEqual([2, 0, 3, 1, 1, 2, 2, 0]);
        expect([...decodeBlocks(rle, grid.length)]).toEqual([...grid]);
    });

    it('rejects blocks that do not match the size', () => {
        expect(() => decodeBlocks([3, 1], 4)).toThrow(/cover 3 cells/);
        expect(() => decodeBlocks([5, 1], 4)).toThrow(/longer/);
        expect(() => decodeBlocks([2, 1, 1], 3)).toThrow(/pairs/);
    });
});

describe('model loader', () => {
    it('loads a valid model', () => {
        const model = loadModel(validJson(), table.blocks);
        expect(model.size).toEqual([2, 1, 2]);
        expect(model.grid).toBeInstanceOf(Uint16Array);
        expect([...model.grid]).toEqual([1, 1, 1, 0]);
        expect(model.palette[1].properties).toEqual({});
    });

    it('reports unknown block ids, oversize models and missing sources', () => {
        const json = validJson();
        json.palette[1] = { name: 'minecraft:not_a_block' };
        json.size = [2, 1, 200];
        delete json.source.license;
        const errors = validateModel(json, table.blocks);
        expect(errors).toContain('unknown block id minecraft:not_a_block');
        expect(errors.some(e => e.startsWith('size must be'))).toBe(true);
        expect(errors).toContain('source.license is missing');
        expect(() => loadModel(json, table.blocks)).toThrow(/Invalid model/);
    });

    it('rejects palette indices out of range', () => {
        const json = validJson();
        json.blocks = [3, 5, 1, 0];
        expect(validateModel(json)).toContain('palette index 5 out of range');
    });

    it('parses block states and builds stable keys', () => {
        const s = parseBlockState('hopper[facing=down,enabled=true]');
        expect(s).toEqual({ name: 'minecraft:hopper', properties: { facing: 'down', enabled: 'true' } });
        expect(blockKey({ name: 'minecraft:hopper', properties: { facing: 'down', enabled: 'true' } }))
            .toBe('minecraft:hopper[enabled=true,facing=down]');
    });

    it('builds a model: air first, unused entries dropped, properties filtered', () => {
        const json = buildModelJson({
            id: 'built',
            name: 'Built',
            size: [3, 1, 1],
            palette: [
                { name: 'minecraft:hopper', properties: { facing: 'down', enabled: 'true' } },
                { name: 'minecraft:cave_air' },
                { name: 'minecraft:gold_block' }, // unused
                { name: 'minecraft:hopper', properties: { facing: 'down', enabled: 'false' } },
            ],
            grid: Uint32Array.from([1, 0, 3]),
            source: { url: 'https://example.org', license: 'MIT' },
        });
        expect(json.palette).toEqual([
            { name: 'minecraft:air' },
            { name: 'minecraft:hopper', properties: { facing: 'down' } },
        ]);
        // cave air becomes air, both hopper states merge once `enabled` is dropped
        expect(json.blocks).toEqual([1, 0, 2, 1]);
        expect(validateModel(json, table.blocks)).toEqual([]);
    });
});

describe('committed models', () => {
    const dir = new URL('../../../public/minecraft/models/', import.meta.url);
    const files = readdirSync(dir).filter(f => f.endsWith('.json'));

    it('exist', () => {
        expect(files.length).toBeGreaterThanOrEqual(3);
    });

    it.each(files)('%s is valid and uses known blocks', file => {
        const json = JSON.parse(readFileSync(new URL(file, dir), 'utf8'));
        expect(validateModel(json, table.blocks)).toEqual([]);
        expect(file).toBe(`${json.id}.json`);
    });
});
