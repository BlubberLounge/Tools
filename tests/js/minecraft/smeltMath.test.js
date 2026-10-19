import { describe, expect, it } from 'vitest';

import { hoppersNeeded, smelt, split } from '../../../resources/js/minecraft/smeltMath.js';

describe('splitting items over furnaces', () => {
    it('spreads the rest over the first furnaces', () => {
        expect(split(10, 3)).toEqual([{ items: 4, furnaces: 1 }, { items: 3, furnaces: 2 }]);
        expect(split(9, 3)).toEqual([{ items: 3, furnaces: 3 }]);
        expect(split(2, 4)).toEqual([{ items: 1, furnaces: 2 }]);
        expect(split(0, 4)).toEqual([]);
    });
});

describe('smelting', () => {
    it('takes 10 s per item in a furnace and 5 s in a blast furnace', () => {
        expect(smelt(64, 1, 'furnace', 8).seconds).toBe(640);
        expect(smelt(64, 1, 'fast', 8).seconds).toBe(320);
        expect(smelt(64, 8, 'furnace', 8).seconds).toBe(80);
    });

    it('needs whole fuel items per furnace', () => {
        // 64 items with coal: 8 coal in one furnace
        expect(smelt(64, 1, 'furnace', 8)).toMatchObject({ fuel: 8, wasted: 0 });
        // 9 furnaces with 7 or 8 items each still burn one coal each
        expect(smelt(64, 9, 'furnace', 8)).toMatchObject({ fuel: 9, wasted: 8 });
        // a lava bucket lasts for 100 items
        expect(smelt(64, 1, 'furnace', 100)).toMatchObject({ fuel: 1, wasted: 36 });
    });

    it('handles fractional fuel values', () => {
        // bamboo smelts a quarter item
        expect(smelt(10, 1, 'furnace', 0.25).fuel).toBe(40);
        // dried kelp block: 20.005 items
        expect(smelt(20, 1, 'furnace', 20.005).fuel).toBe(1);
        expect(smelt(21, 1, 'furnace', 20.005).fuel).toBe(2);
    });

    it('reports the throughput of the array', () => {
        expect(smelt(0, 9, 'furnace', 8).perHour).toBe(3240);
        expect(smelt(0, 9, 'fast', 8).perHour).toBe(6480);
    });

    it('rejects unknown machines', () => {
        expect(() => smelt(1, 1, 'campfire', 8)).toThrow('Unknown machine');
    });
});

describe('hoppers', () => {
    it('moves 9000 items per hour', () => {
        expect(hoppersNeeded(3240)).toBe(1);
        expect(hoppersNeeded(9001)).toBe(2);
    });
});
