import { readFileSync } from 'node:fs';
import { describe, expect, it } from 'vitest';

import { hoursToLevel, perKill, rates, stackSizeOf } from '../../../resources/js/minecraft/mobMath.js';

const data = JSON.parse(readFileSync(new URL('../../../resources/minecraft/mobs/26.3.json', import.meta.url), 'utf8'));
const mob = id => data.mobs.find(m => m.id === id);
const drop = (id, item) => mob(id).drops.find(d => d.item === item);

describe('mob data', () => {
    it('has the known loot table averages', () => {
        // uniform 0..2 plus round(level * U(0, 1)) per Looting level
        expect(drop('zombie', 'rotten_flesh')).toEqual({ item: 'rotten_flesh', auto: 1, player: [1, 1.5, 2, 2.5] });
        expect(drop('enderman', 'ender_pearl').player).toEqual([0.5, 1, 1.5, 2]);
        // witch: redstone 4..8 in its own pool
        expect(drop('witch', 'redstone').auto).toBe(6);
        // iron golem: 3..5 ingots, no Looting bonus
        expect(drop('iron_golem', 'iron_ingot').player).toEqual([4, 4, 4, 4]);
        // blaze rods only drop for player kills
        expect(drop('blaze', 'blaze_rod').auto).toBe(0);
    });

    it('lists every mob with a name, XP and drops', () => {
        for (const m of data.mobs) {
            expect(m.name).toBeTruthy();
            expect(m.xp).toBeGreaterThanOrEqual(0);
            expect(m.drops.length).toBeGreaterThan(0);
            for (const d of m.drops)
                expect(d.player).toHaveLength(4);
        }
    });
});

describe('rates', () => {
    it('scales drops per kill to an hour', () => {
        const result = rates(mob('zombie'), 1000, 'player', 3);
        const flesh = result.rows.find(r => r.item === 'rotten_flesh');
        expect(flesh.perHour).toBe(2500);
        expect(flesh.stacks).toBeCloseTo(2500 / 64);
        expect(result.xpPerHour).toBe(5000);
    });

    it('drops player-only items and XP for farm kills', () => {
        const result = rates(mob('blaze'), 600, 'auto');
        expect(result.rows).toEqual([]);
        expect(result.xpPerHour).toBe(0);
    });

    it('uses the right stack size', () => {
        expect(stackSizeOf('ender_pearl')).toBe(16);
        expect(stackSizeOf('totem_of_undying')).toBe(1);
        expect(stackSizeOf('string')).toBe(64);
        const result = rates(mob('enderman'), 160, 'auto');
        expect(result.rows[0].stacks).toBe(5);
        expect(result.shulkers).toBeCloseTo(5 / 27);
    });

    it('picks the Looting level and treats bad input as 0', () => {
        expect(perKill(drop('creeper', 'gunpowder'), 'player', 2)).toBe(2);
        expect(perKill(drop('creeper', 'gunpowder'), 'player', 9)).toBe(2.5);
        expect(rates(mob('creeper'), 'abc', 'auto').rows[0].perHour).toBe(0);
    });

    it('turns XP per hour into time to a level', () => {
        expect(hoursToLevel(30, 1395)).toBe(1);
        expect(hoursToLevel(30, 0)).toBe(Infinity);
    });
});
