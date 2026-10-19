import { describe, expect, it } from 'vitest';

import {
    bestSpot, chunkOf, chunksInSpawnRange, isSlimeChunk, javaHash, parseSeed, slimeChunksAround,
} from '../../../resources/js/minecraft/slimeMath.js';

// Reference lists from java.util.Random with the formula of WorldgenRandom.seedSlimeChunk (26.3),
// chunks -8..8 in X and Z.
const REFERENCE = {
    '0': '-8,0 -8,1 -8,4 -8,8 -7,1 -5,5 -4,-8 -4,-7 -4,8 -3,-7 -2,0 -1,6 1,-3 2,-3 2,2 2,4 3,8 4,2 5,-3 6,-1 7,-5 7,-4 7,1 8,-7 8,-2',
    '12345': '-8,-7 -8,-2 -7,1 -7,3 -5,2 -4,0 -3,7 -2,-4 -2,1 -2,4 -1,2 0,-6 0,-2 0,7 3,0 3,5 4,-6 4,-5 4,-3 4,1 5,-3 6,-2 6,7 8,-4 8,0',
    '-4172144997902289642': '-8,-8 -8,1 -7,-2 -6,-7 -6,-5 -5,2 -5,3 -4,-4 -3,-4 -2,-5 -2,3 -1,-5 0,-8 0,-7 1,0 1,1 2,-5 2,8 3,0 4,-4 4,-2 4,3 5,-8 5,-6 5,-5 6,-6 6,-5',
    '99162322': '-8,-5 -8,0 -8,8 -7,5 -6,-4 -6,-1 -5,-8 -5,-3 -5,0 -4,-5 -4,3 -1,-2 -1,-1 -1,7 0,-2 0,3 1,5 2,-3 2,3 3,-4 3,-2 3,6 4,-3 4,2 5,-1 5,2 6,0 7,-7 7,-5 7,3 8,2',
    '9223372036854775807': '-8,2 -8,7 -8,8 -7,-1 -7,0 -6,-1 -5,-6 -5,0 -4,-5 -4,-3 -3,-1 -2,-8 -2,-5 -1,-5 -1,3 0,-3 1,-5 1,2 2,-3 3,-5 4,-6 4,-2 4,5 5,0 6,-3 8,7',
};

const list = (seed, x0, x1, z0, z1) => {
    const found = [];
    for (let x = x0; x <= x1; x++)
        for (let z = z0; z <= z1; z++)
            if (isSlimeChunk(seed, x, z))
                found.push(`${x},${z}`);
    return found.join(' ');
};

describe('slime chunks', () => {
    for (const [seed, expected] of Object.entries(REFERENCE))
        it(`matches Java for seed ${seed}`, () => {
            expect(list(BigInt(seed), -8, 8, -8, 8)).toBe(expected);
        });

    it('overflows like Java ints far from spawn', () => {
        expect(list(12345n, 1874990, 1875000, -1875000, -1874990)).toBe(
            '1874992,-1874999 1874992,-1874995 1874993,-1875000 1874993,-1874997 1874995,-1874994 '
            + '1874995,-1874990 1874996,-1874997 1874998,-1874992 1875000,-1874998'
        );
    });
});

describe('seeds', () => {
    it('reads numbers as longs and hashes text', () => {
        expect(parseSeed(' 12345 ')).toBe(12345n);
        expect(parseSeed('-4172144997902289642')).toBe(-4172144997902289642n);
        expect(parseSeed('hello')).toBe(99162322n);
        expect(javaHash('hello')).toBe(99162322);
        expect(parseSeed('')).toBeNull();
    });

    it('hashes numbers outside the long range', () => {
        expect(parseSeed('9223372036854775808')).toBe(BigInt(javaHash('9223372036854775808')));
    });
});

describe('search', () => {
    it('turns block coordinates into chunks', () => {
        expect(chunkOf(15)).toBe(0);
        expect(chunkOf(16)).toBe(1);
        expect(chunkOf(-1)).toBe(-1);
        expect(chunkOf(-16)).toBe(-1);
        expect(chunkOf(-17)).toBe(-2);
    });

    it('lists the chunks around a centre', () => {
        const chunks = slimeChunksAround(0n, 0, 0, 8);
        expect(chunks.map(c => `${c.x},${c.z}`).sort()).toEqual(REFERENCE['0'].split(' ').sort());
    });

    it('counts chunks in the spawn ring and finds the best spot', () => {
        const chunks = [{ x: 0, z: 0 }, { x: 2, z: 0 }, { x: 20, z: 0 }];
        // from the centre of chunk 0: chunk 0 is too close, chunk 2 is 32 blocks away, chunk 20 too far
        expect(chunksInSpawnRange(chunks, 8, 8)).toEqual([{ x: 2, z: 0 }]);

        const best = bestSpot(chunks, 1, 0, 1);
        expect(best.count).toBeGreaterThanOrEqual(1);
    });
});
