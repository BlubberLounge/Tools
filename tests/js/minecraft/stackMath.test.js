import { describe, expect, it } from 'vitest';

import {
    BREAKDOWNS, UNITS,
    breakdown, convertAll, slotsNeeded, totalItems, unitSize,
} from '../../../resources/js/minecraft/stackMath.js';

describe('unit sizes', () => {
    it('counts containers in stacks of the chosen size', () => {
        expect(unitSize('item', 64)).toBe(1);
        expect(unitSize('stack', 64)).toBe(64);
        expect(unitSize('hopper', 64)).toBe(320);
        expect(unitSize('dispenser', 64)).toBe(576);
        expect(unitSize('chest', 64)).toBe(1728);
        expect(unitSize('shulker', 16)).toBe(432);
        expect(unitSize('inventory', 64)).toBe(2304);
        expect(unitSize('doubleChest', 1)).toBe(54);
    });

    it('fills chests with full shulker boxes', () => {
        expect(unitSize('shulkerChest', 64)).toBe(46656);
        expect(unitSize('shulkerDoubleChest', 64)).toBe(93312);
    });

    it('rejects unknown units', () => {
        expect(() => unitSize('barrel', 64)).toThrow('Unknown unit');
    });
});

describe('total items', () => {
    it('adds mixed rows', () => {
        const rows = [
            { amount: 2, unit: 'shulker' },
            { amount: 5, unit: 'stack' },
            { amount: 12, unit: 'item' },
        ];
        expect(totalItems(rows, 64)).toBe(2 * 1728 + 320 + 12);
    });

    it('accepts fractions and strings and rounds up to whole items', () => {
        expect(totalItems([{ amount: '1.5', unit: 'stack' }], 64)).toBe(96);
        expect(totalItems([{ amount: 0.3, unit: 'stack' }], 64)).toBe(20);
        expect(totalItems([{ amount: 0.1, unit: 'item' }, { amount: 0.2, unit: 'item' }], 64)).toBe(1);
    });

    it('ignores empty, negative and invalid amounts', () => {
        const rows = [
            { amount: '', unit: 'chest' },
            { amount: -3, unit: 'stack' },
            { amount: 'abc', unit: 'stack' },
            { amount: 7, unit: 'item' },
        ];
        expect(totalItems(rows, 64)).toBe(7);
        expect(totalItems([], 64)).toBe(0);
    });
});

describe('conversion', () => {
    it('gives the total in every unit', () => {
        const result = convertAll(3456, 64);
        expect(result.map(r => r.unit)).toEqual(UNITS.map(u => u.id));
        expect(result.find(r => r.unit === 'stack').value).toBe(54);
        expect(result.find(r => r.unit === 'shulker').value).toBe(2);
        expect(result.find(r => r.unit === 'doubleChest').value).toBe(1);
    });

    it('splits greedily into shulker boxes and chests', () => {
        const total = 93312 + 3 * 1728 + 12 * 64 + 5;
        expect(breakdown(total, 64, BREAKDOWNS.shulker)).toEqual([
            { unit: 'shulkerDoubleChest', count: 1 },
            { unit: 'shulker', count: 3 },
            { unit: 'stack', count: 12 },
            { unit: 'item', count: 5 },
        ]);
        expect(breakdown(total, 64, BREAKDOWNS.chest)).toEqual([
            { unit: 'doubleChest', count: 28 },
            { unit: 'chest', count: 1 },
            { unit: 'stack', count: 12 },
            { unit: 'item', count: 5 },
        ]);
    });

    it('returns nothing for zero', () => {
        expect(breakdown(0, 16, BREAKDOWNS.chest)).toEqual([]);
    });

    it('counts a partial stack as a full slot', () => {
        expect(slotsNeeded(65, 64)).toBe(2);
        expect(slotsNeeded(48, 16)).toBe(3);
        expect(slotsNeeded(0, 64)).toBe(0);
    });
});
