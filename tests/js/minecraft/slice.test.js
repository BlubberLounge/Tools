import { describe, expect, it } from 'vitest';

import { layerCount, layerCounts, materialList, sliceLayer, stacksOf } from '../../../resources/js/minecraft/viewer/slice.js';
import { modelOf, table } from './helpers.js';

// 3 x 3 x 2: a stone floor with a chest on top and two hoppers
const model = () => modelOf([3, 3, 2], {
    '0,0,0': 'minecraft:stone', '1,0,0': 'minecraft:stone', '2,0,0': 'minecraft:stone',
    '0,0,1': 'minecraft:stone', '1,0,1': 'minecraft:stone', '2,0,1': 'minecraft:stone',
    '1,1,0': 'minecraft:chest[facing=north]',
    '0,2,1': 'minecraft:hopper[facing=down]', '1,2,1': 'minecraft:hopper[facing=east]',
});

describe('slice layers', () => {
    it('knows the number of layers per axis', () => {
        expect(layerCount([3, 3, 2], 'y')).toBe(3);
        expect(layerCount([3, 3, 2], 'z')).toBe(2);
    });

    it('counts the blocks of one layer, block states together', () => {
        expect(layerCounts(model(), 'y', 0)).toEqual([{ name: 'minecraft:stone', count: 6 }]);
        expect(layerCounts(model(), 'y', 2)).toEqual([{ name: 'minecraft:hopper', count: 2 }]);
        expect(layerCounts(model(), 'x', 1)).toEqual([
            { name: 'minecraft:stone', count: 2 },
            { name: 'minecraft:chest', count: 1 },
            { name: 'minecraft:hopper', count: 1 },
        ]);
    });

    it('shows the nearest lower block behind empty cells', () => {
        const m = model();
        const slice = sliceLayer(m, 'y', 2);
        expect(slice.width).toBe(3);
        expect(slice.height).toBe(2);
        // (1, z=0): empty on layer 2, chest one layer down
        const i = 0 * slice.width + 1;
        expect(slice.cells[i]).toBe(0);
        expect(m.palette[slice.behind[i]].name).toBe('minecraft:chest');
        expect(slice.depth[i]).toBe(1);
        // (2, z=0): stone two layers down
        expect(slice.depth[2]).toBe(2);
    });

    it('draws side slices with up at the top', () => {
        const m = model();
        const slice = sliceLayer(m, 'z', 0);
        expect(slice.width).toBe(3);
        expect(slice.height).toBe(3);
        // bottom row (v = 2) is y = 0: stone
        expect(m.palette[slice.cells[2 * 3]].name).toBe('minecraft:stone');
        // middle row is y = 1: chest at x = 1
        expect(m.palette[slice.cells[1 * 3 + 1]].name).toBe('minecraft:chest');
    });
});

describe('material list', () => {
    it('splits counts into stacks and shulker boxes', () => {
        expect(stacksOf(130)).toEqual({ stacks: 2, rest: 2, shulkers: 130 / 1728 });
        expect(stacksOf(16, 16)).toEqual({ stacks: 1, rest: 0, shulkers: 16 / 432 });
    });

    it('lists items of the whole model, largest first', () => {
        expect(materialList(model(), table).map(m => [m.item, m.count])).toEqual([
            ['minecraft:stone', 6],
            ['minecraft:hopper', 2],
            ['minecraft:chest', 1],
        ]);
    });

    it('counts double slabs twice and skips things that are not placed by hand', () => {
        const m = modelOf([6, 1, 2], {
            '0,0,0': 'minecraft:stone_slab[type=double]',
            '1,0,0': 'minecraft:oak_door[half=lower,facing=north]',
            '1,0,1': 'minecraft:oak_door[half=upper,facing=north]',
            '2,0,0': 'minecraft:water[level=0]',
            '2,0,1': 'minecraft:water[level=3]',
            '3,0,0': 'minecraft:kelp_plant',
            '3,0,1': 'minecraft:kelp',
            '4,0,0': 'minecraft:piston_head[facing=up]',
            '5,0,0': 'minecraft:redstone_wire',
            '5,0,1': 'minecraft:red_bed[part=head,facing=north]',
        });
        const list = Object.fromEntries(materialList(m, table).map(e => [e.item, e]));
        expect(list['minecraft:stone_slab'].count).toBe(2);
        expect(list['minecraft:oak_door'].count).toBe(1);
        expect(list['minecraft:water_bucket'].count).toBe(1);
        expect(list['minecraft:water_bucket'].stack).toBe(1);
        expect(list['minecraft:kelp'].count).toBe(1);
        expect(list['minecraft:redstone'].count).toBe(1);
        expect(list['minecraft:piston_head']).toBeUndefined();
        expect(list['minecraft:red_bed']).toBeUndefined();
    });
});
