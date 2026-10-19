import { describe, expect, it } from 'vitest';

import { compareWays, plan, producersOf } from '../../../resources/js/minecraft/craftMath.js';

const recipe = (id, output, count, inputs, type = 'crafting') => ({
    id, type, output: { item: output, count }, inputs: inputs.map(([item, n]) => ({ item, count: n })),
});

const RECIPES = [
    recipe('oak_planks', 'oak_planks', 4, [['oak_log', 1]]),
    recipe('stick', 'stick', 4, [['oak_planks', 2]]),
    recipe('stick_from_bamboo', 'stick', 1, [['bamboo', 2]]),
    recipe('chest', 'chest', 1, [['oak_planks', 8]]),
    recipe('hopper', 'hopper', 1, [['iron_ingot', 5], ['chest', 1]]),
    recipe('ladder', 'ladder', 3, [['stick', 7]]),
    recipe('torch', 'torch', 4, [['coal', 1], ['stick', 1]]),
    recipe('iron_block', 'iron_block', 1, [['iron_ingot', 9]]),
    recipe('iron_ingot_from_iron_block', 'iron_ingot', 9, [['iron_block', 1]]),
    recipe('iron_ingot_from_smelting_raw_iron', 'iron_ingot', 1, [['raw_iron', 1]], 'smelting'),
];

const DEFAULTS = { oak_planks: 'oak_planks', stick: 'stick', chest: 'chest', hopper: 'hopper', ladder: 'ladder', torch: 'torch' };

const rawOf = result => Object.fromEntries(result.raw.map(r => [r.item, r.count]));

describe('planning', () => {
    it('expands a hopper to logs and ingots', () => {
        const result = plan(RECIPES, DEFAULTS, 'hopper', 1);
        expect(rawOf(result)).toEqual({ iron_ingot: 5, oak_log: 2 });
        const planks = result.steps.find(s => s.item === 'oak_planks');
        expect(planks).toMatchObject({ needed: 8, crafts: 2, made: 8, leftover: 0 });
        expect(result.steps.at(-1).item).toBe('hopper');
    });

    it('rounds up to whole crafts and reports leftovers', () => {
        // 64 torches = 16 crafts = 16 sticks; 64 ladders = 22 crafts = 154 sticks
        const torches = plan(RECIPES, DEFAULTS, 'torch', 64);
        expect(torches.steps.find(s => s.item === 'stick')).toMatchObject({ needed: 16, crafts: 4, leftover: 0 });

        const ladders = plan(RECIPES, DEFAULTS, 'ladder', 64);
        const stick = ladders.steps.find(s => s.item === 'stick');
        expect(stick).toMatchObject({ needed: 22 * 7, crafts: 39, made: 156, leftover: 2 });
        expect(ladders.steps.find(s => s.item === 'ladder')).toMatchObject({ crafts: 22, made: 66, leftover: 2 });
    });

    it('follows a chosen way', () => {
        const result = plan(RECIPES, { ...DEFAULTS, stick: 'stick_from_bamboo' }, 'stick', 32);
        expect(rawOf(result)).toEqual({ bamboo: 64 });
    });

    it('counts furnace operations for cooking recipes', () => {
        const result = plan(RECIPES, { ...DEFAULTS, iron_ingot: 'iron_ingot_from_smelting_raw_iron' }, 'hopper', 2);
        expect(result.cookOps).toBe(10);
        expect(rawOf(result).raw_iron).toBe(10);
    });

    it('breaks loops by gathering the looping item', () => {
        const choices = { iron_block: 'iron_block', iron_ingot: 'iron_ingot_from_iron_block' };
        const result = plan(RECIPES, choices, 'iron_block', 1);
        expect(result.forced).toEqual(['iron_block']);
        expect(result.steps).toEqual([]);
        expect(rawOf(result)).toEqual({ iron_block: 1 });
    });

    it('treats a missing choice as a raw item', () => {
        expect(rawOf(plan(RECIPES, {}, 'chest', 3))).toEqual({ chest: 3 });
        expect(plan(RECIPES, DEFAULTS, 'chest', '').raw).toEqual([]);
    });
});

describe('ways', () => {
    it('lists the producers of each item', () => {
        expect(producersOf(RECIPES).stick).toEqual(['stick', 'stick_from_bamboo']);
    });

    it('compares the ways to make an item, fewest raw items first', () => {
        const ways = compareWays(RECIPES, DEFAULTS, 'stick', 32);
        expect(ways.map(w => w.way)).toEqual(['stick', 'stick_from_bamboo']);
        expect(ways[0].total).toBe(4);
        expect(ways[1].total).toBe(64);
    });
});
