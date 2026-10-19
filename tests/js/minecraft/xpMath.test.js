import { describe, expect, it } from 'vitest';

import {
    deathDrop, enchantsFrom, levelForPoints, mendingPoints, pointsBetween, pointsForLevel, pointsToNext, sourceCount,
} from '../../../resources/js/minecraft/xpMath.js';

describe('levels and points', () => {
    it('matches the wiki totals', () => {
        expect(pointsForLevel(0)).toBe(0);
        expect(pointsForLevel(16)).toBe(352);
        expect(pointsForLevel(30)).toBe(1395);
        expect(pointsForLevel(31)).toBe(1507);
        expect(pointsForLevel(50)).toBe(5345);
    });

    it('adds up the per-level steps to the totals', () => {
        let sum = 0;
        for (let level = 0; level < 60; level++) {
            expect(pointsForLevel(level)).toBe(sum);
            sum += pointsToNext(level);
        }
    });

    it('turns points back into a level and progress', () => {
        expect(levelForPoints(0)).toEqual({ level: 0, points: 0, progress: 0 });
        expect(levelForPoints(1395)).toEqual({ level: 30, points: 0, progress: 0 });
        const result = levelForPoints(1395 + 56);
        expect(result.level).toBe(30);
        expect(result.points).toBe(56);
        expect(result.progress).toBeCloseTo(0.5);
    });

    it('measures the points between two levels', () => {
        expect(pointsBetween(0, 30)).toBe(1395);
        expect(pointsBetween(27, 30)).toBe(1395 - 1089);
        expect(pointsBetween(30, 10)).toBe(0);
    });

    it('treats empty or invalid input as 0', () => {
        expect(pointsForLevel('')).toBe(0);
        expect(pointsForLevel(-5)).toBe(0);
        expect(levelForPoints('abc').level).toBe(0);
    });
});

describe('extras', () => {
    it('counts top-option enchants from a level', () => {
        expect(enchantsFrom(29)).toBe(0);
        expect(enchantsFrom(30)).toBe(1);
        expect(enchantsFrom(32)).toBe(1);
        expect(enchantsFrom(33)).toBe(2);
        expect(enchantsFrom(60)).toBe(11);
    });

    it('repairs 2 durability per point with Mending', () => {
        expect(mendingPoints(1561)).toBe(781);
        expect(mendingPoints(0)).toBe(0);
    });

    it('drops 7 points per level, at most 100', () => {
        expect(deathDrop(5)).toBe(35);
        expect(deathDrop(30)).toBe(100);
    });

    it('counts how many of a source are needed', () => {
        expect(sourceCount(1395, 7)).toBe(200);
        expect(sourceCount(10, 0.7)).toBe(15);
        expect(sourceCount(10, 0)).toBe(Infinity);
    });
});
