import { describe, expect, it } from 'vitest';

import {
    NETHER, OVERWORLD,
    analyseLinks, convert, findLink, frameMaterials, inSearchRange, measure, partnerFor,
} from '../../../resources/js/minecraft/portalMath.js';

const portal = (id, dimension, x, y, z) => ({ id, name: `p${id}`, dimension, x, y, z });

describe('coordinate conversion', () => {
    it('divides X and Z by 8 into the Nether and keeps Y', () => {
        expect(convert({ x: 800, y: 70, z: -1600 }, OVERWORLD)).toEqual({ x: 100, y: 70, z: -200 });
    });

    it('floors to the block below, also for negative coordinates', () => {
        expect(convert({ x: 7, y: 64, z: -1 }, OVERWORLD)).toEqual({ x: 0, y: 64, z: -1 });
        expect(convert({ x: -9, y: 64, z: 15 }, OVERWORLD)).toEqual({ x: -2, y: 64, z: 1 });
    });

    it('multiplies by 8 back into the Overworld', () => {
        expect(convert({ x: -12, y: 40, z: 5 }, NETHER)).toEqual({ x: -96, y: 40, z: 40 });
    });

    it('reports how far the round trip drifts', () => {
        expect(partnerFor({ x: 800, y: 64, z: 0 }, OVERWORLD).drift).toBe(0);
        const result = partnerFor({ x: 807, y: 64, z: 0 }, OVERWORLD);
        expect(result.partner).toEqual({ x: 100, y: 64, z: 0 });
        expect(result.back).toEqual({ x: 800, y: 64, z: 0 });
        expect(result.drift).toBe(7);
    });
});

describe('portal search', () => {
    it('uses a square of 16 blocks in the Nether and 128 in the Overworld', () => {
        expect(inSearchRange({ x: 0, z: 0 }, { x: 16, z: -16 }, NETHER)).toBe(true);
        expect(inSearchRange({ x: 0, z: 0 }, { x: 17, z: 0 }, NETHER)).toBe(false);
        expect(inSearchRange({ x: 0, z: 0 }, { x: 128, z: 128 }, OVERWORLD)).toBe(true);
        expect(inSearchRange({ x: 0, z: 0 }, { x: 0, z: 129 }, OVERWORLD)).toBe(false);
    });

    it('picks the closest portal in 3D, Y counts', () => {
        const home = portal(1, OVERWORLD, 800, 64, 0);
        const near = portal(2, NETHER, 104, 64, 0);   // 4 away horizontally
        const above = portal(3, NETHER, 100, 120, 0); // straight above, 56 away
        const link = findLink(home, [home, near, above]);

        expect(link.target).toEqual({ x: 100, y: 64, z: 0 });
        expect(link.portal.id).toBe(2);
        expect(link.distance).toBe(4);
    });

    it('takes the lower portal on equal distance', () => {
        const home = portal(1, OVERWORLD, 0, 64, 0);
        const upper = portal(2, NETHER, 0, 70, 0);
        const lower = portal(3, NETHER, 0, 58, 0);

        expect(findLink(home, [upper, lower]).portal.id).toBe(3);
    });

    it('returns no portal when nothing is in range', () => {
        const link = findLink(portal(1, OVERWORLD, 0, 64, 0), [portal(2, NETHER, 40, 64, 0)]);

        expect(link.portal).toBeNull();
        expect(link.distance).toBeNull();
    });

    it('ignores portals of the same dimension', () => {
        const link = findLink(portal(1, NETHER, 0, 64, 0), [portal(2, NETHER, 0, 64, 0)]);

        expect(link.portal).toBeNull();
    });
});

describe('link analysis', () => {
    it('marks pairs that lead to each other as linked', () => {
        const result = analyseLinks([
            portal(1, OVERWORLD, 800, 64, 0),
            portal(2, NETHER, 100, 64, 0),
        ]);

        expect(result.map(r => r.status)).toEqual(['linked', 'linked']);
        expect(result[0].destination.id).toBe(2);
        expect(result[1].destination.id).toBe(1);
    });

    it('flags a portal whose destination leads somewhere else', () => {
        // both Overworld portals land at the same Nether portal; it leads back to the closer one
        const result = analyseLinks([
            portal(1, OVERWORLD, 800, 64, 0),
            portal(2, OVERWORLD, 880, 64, 0),
            portal(3, NETHER, 100, 64, 0),
        ]);
        const byId = Object.fromEntries(result.map(r => [r.portal.id, r]));

        expect(byId[1].status).toBe('linked');
        expect(byId[2].status).toBe('oneway');
        expect(byId[2].destination.id).toBe(3);
        expect(byId[2].returnsTo.id).toBe(1);
    });

    it('flags a portal that would create a new one', () => {
        const result = analyseLinks([portal(1, NETHER, 0, 64, 0)]);

        expect(result[0].status).toBe('new');
        expect(result[0].destination).toBeNull();
        expect(result[0].target).toEqual({ x: 0, y: 64, z: 0 });
    });
});

describe('frame materials', () => {
    it('counts obsidian for the smallest frame', () => {
        expect(frameMaterials(2, 3)).toEqual({
            withCorners: 14,
            withoutCorners: 10,
            portalBlocks: 6,
            outer: { width: 4, height: 5 },
        });
    });

    it('counts obsidian for the largest frame', () => {
        expect(frameMaterials(21, 21).withCorners).toBe(88);
        expect(frameMaterials(21, 21).portalBlocks).toBe(441);
    });

    it('rejects sizes the game does not accept', () => {
        expect(frameMaterials(1, 3)).toBeNull();
        expect(frameMaterials(2, 2)).toBeNull();
        expect(frameMaterials(22, 5)).toBeNull();
        expect(frameMaterials(2.5, 3)).toBeNull();
    });
});

describe('distance', () => {
    it('measures 3D, horizontal and block distance', () => {
        const result = measure({ x: 0, y: 64, z: 0 }, { x: 3, y: 76, z: 4 });

        expect(result.distance).toBe(13);
        expect(result.horizontal).toBe(5);
        expect(result.manhattan).toBe(19);
        expect([result.dx, result.dy, result.dz]).toEqual([3, 12, 4]);
    });

    it('shows the same trip through the other dimension', () => {
        const overworld = measure({ x: 0, y: 64, z: 0 }, { x: 800, y: 64, z: 0 }, OVERWORLD);
        expect(overworld.other).toMatchObject({ dimension: NETHER, horizontal: 100 });

        const nether = measure({ x: 0, y: 64, z: 0 }, { x: 100, y: 64, z: 0 }, NETHER);
        expect(nether.other).toMatchObject({ dimension: OVERWORLD, horizontal: 800 });
    });

    it('converts the distance into walking and sprinting time', () => {
        const result = measure({ x: 0, y: 64, z: 0 }, { x: 5612, y: 64, z: 0 });

        expect(result.time.sprint).toBeCloseTo(1000, 6);
        expect(result.time.walk).toBeCloseTo(5612 / 4.317, 6);
    });
});
