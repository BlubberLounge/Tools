import { describe, expect, it } from 'vitest';

import { SHAPES, shapeBlocks } from '../../../resources/js/minecraft/materialMath.js';

const size = (length, width, height) => ({ length, width, height });

describe('shape blocks', () => {
    it('fills a solid box', () => {
        expect(shapeBlocks('solid', size(10, 10, 5))).toEqual({ blocks: 500, inside: 0 });
    });

    it('builds a hollow box with floor and ceiling', () => {
        expect(shapeBlocks('hollow', size(5, 5, 5))).toEqual({ blocks: 98, inside: 27 });
        // two blocks high: nothing is inside
        expect(shapeBlocks('hollow', size(5, 5, 2))).toEqual({ blocks: 50, inside: 0 });
    });

    it('builds walls only and walls with a floor', () => {
        expect(shapeBlocks('walls', size(5, 5, 3))).toEqual({ blocks: 48, inside: 27 });
        expect(shapeBlocks('wallsFloor', size(5, 5, 3))).toEqual({ blocks: 57, inside: 18 });
    });

    it('counts only the edges for a frame', () => {
        expect(shapeBlocks('frame', size(3, 3, 3)).blocks).toBe(20);
        expect(shapeBlocks('frame', size(5, 4, 3)).blocks).toBe(4 * (5 + 4 + 3) - 16);
        expect(shapeBlocks('frame', size(1, 1, 7)).blocks).toBe(7);
    });

    it('takes the wall thickness into account', () => {
        expect(shapeBlocks('hollow', size(7, 7, 7), 2)).toEqual({ blocks: 343 - 27, inside: 27 });
        expect(shapeBlocks('walls', size(6, 6, 4), 2)).toEqual({ blocks: 144 - 16, inside: 16 });
    });

    it('treats empty or invalid sizes as 0', () => {
        for (const shape of SHAPES)
            expect(shapeBlocks(shape, size('', 'x', 3)).blocks).toBe(0);
        expect(shapeBlocks('solid', size(-4, 2, 2)).blocks).toBe(0);
    });

    it('rejects unknown shapes', () => {
        expect(() => shapeBlocks('sphere', size(1, 1, 1))).toThrow('Unknown shape');
    });
});
