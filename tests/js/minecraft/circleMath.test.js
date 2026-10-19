import { describe, expect, it } from 'vitest';

import { boxOf, generate, runs } from '../../../resources/js/minecraft/circleMath.js';

const picture = (result, layer = 0) => {
    const [sx, , sz] = result.size;
    const rows = [];
    for (let z = 0; z < sz; z++) {
        let row = '';
        for (let x = 0; x < sx; x++)
            row += result.layers[layer][z * sx + x] ? '#' : '.';
        rows.push(row);
    }
    return rows;
};

describe('circles and ellipses', () => {
    it('draws a filled circle', () => {
        expect(picture(generate('circle', { diameter: 5 }))).toEqual([
            '.###.',
            '#####',
            '#####',
            '#####',
            '.###.',
        ]);
    });

    it('draws a closed outline', () => {
        expect(picture(generate('circle', { diameter: 7 }, true))).toEqual([
            '..###..',
            '.#...#.',
            '#.....#',
            '#.....#',
            '#.....#',
            '.#...#.',
            '..###..',
        ]);
    });

    it('is symmetric for even diameters', () => {
        const rows = picture(generate('circle', { diameter: 10 }, true));
        for (const row of rows)
            expect(row).toBe([...row].reverse().join(''));
        expect(rows).toEqual([...rows].reverse());
    });

    it('stretches into an ellipse', () => {
        const result = generate('ellipse', { width: 9, depth: 3 });
        expect(result.size).toEqual([9, 1, 3]);
        expect(picture(result)).toEqual([
            '.#######.',
            '#########',
            '.#######.',
        ]);
    });
});

describe('spheres and domes', () => {
    it('builds a sphere layer by layer', () => {
        const result = generate('sphere', { diameter: 5 });
        expect(result.size).toEqual([5, 5, 5]);
        expect(result.counts).toEqual([9, 21, 21, 21, 9]);
        expect(result.total).toBe(81);
    });

    it('keeps only the shell of a hollow sphere', () => {
        const solid = generate('sphere', { diameter: 9 });
        const hollow = generate('sphere', { diameter: 9 }, true);
        expect(hollow.total).toBeLessThan(solid.total);
        // the middle block of the middle layer is air
        expect(hollow.layers[4][4 * 9 + 4]).toBe(0);
    });

    it('cuts a dome at the middle', () => {
        const sphere = generate('sphere', { diameter: 5 });
        const dome = generate('dome', { diameter: 5 });
        expect(dome.size).toEqual([5, 3, 5]);
        expect(dome.counts).toEqual(sphere.counts.slice(2));

        expect(generate('dome', { diameter: 6 }).size).toEqual([6, 3, 6]);
    });
});

describe('input', () => {
    it('clamps sizes', () => {
        expect(boxOf('circle', { diameter: 0 })).toEqual([1, 1, 1]);
        expect(boxOf('sphere', { diameter: 9999 })).toEqual([160, 160, 160]);
        expect(boxOf('ellipse', { width: 'x', depth: 4 })).toEqual([1, 1, 4]);
        expect(() => boxOf('cube', {})).toThrow('Unknown shape');
    });

    it('lists the block runs of a row', () => {
        const result = generate('circle', { diameter: 7 }, true);
        expect(runs(result.layers[0], 7, 0)).toEqual([3]);
        expect(runs(result.layers[0], 7, 3)).toEqual([1, 1]);
    });
});
