import { describe, expect, it } from 'vitest';

import { countFaces, createMeshContext, meshChunk, atlasRect } from '../../../resources/js/minecraft/viewer/mesher.js';
import { resolveBlock } from '../../../resources/js/minecraft/viewer/blocks.js';
import { rotateBox, shapeBoxes } from '../../../resources/js/minecraft/viewer/shapes.js';
import { modelOf, table } from './helpers.js';

const atlas = {
    columns: 4,
    rows: 4,
    textures: { __white: [0, 0], stone: [1, 0], glass: [2, 0] },
};

describe('face culling', () => {
    it('draws all six faces of a single block', () => {
        const ctx = createMeshContext(modelOf([1, 1, 1], { '0,0,0': 'minecraft:stone' }), table);
        expect(countFaces(ctx)).toEqual({ solid: 6, cutout: 0, translucent: 0 });
    });

    it('culls the faces between two opaque blocks', () => {
        const ctx = createMeshContext(modelOf([2, 1, 1], { '0,0,0': 'minecraft:stone', '1,0,0': 'minecraft:dirt' }), table);
        expect(countFaces(ctx).solid).toBe(10);
    });

    it('culls a 3x3x3 cube down to its surface', () => {
        const blocks = {};
        for (let x = 0; x < 3; x++)
            for (let y = 0; y < 3; y++)
                for (let z = 0; z < 3; z++)
                    blocks[`${x},${y},${z}`] = 'minecraft:stone';
        const ctx = createMeshContext(modelOf([3, 3, 3], blocks), table);
        expect(countFaces(ctx).solid).toBe(54);
    });

    it('culls between glass blocks but not between glass and stone', () => {
        const ctx = createMeshContext(modelOf([3, 1, 1], {
            '0,0,0': 'minecraft:glass', '1,0,0': 'minecraft:glass', '2,0,0': 'minecraft:stone',
        }), table, atlas);
        const faces = countFaces(ctx);
        // glass pair: 10 faces minus the one hidden by the stone; stone keeps its face to the glass
        expect(faces.cutout).toBe(9);
        expect(faces.solid).toBe(6);
    });

    it('culls water against water and puts it in the translucent layer', () => {
        const ctx = createMeshContext(modelOf([2, 1, 1], {
            '0,0,0': 'minecraft:water[level=0]', '1,0,0': 'minecraft:water[level=0]',
        }), table);
        expect(countFaces(ctx)).toEqual({ solid: 0, cutout: 0, translucent: 10 });
    });

    it('hides the bottom of a slab on a full block but keeps its top', () => {
        const ctx = createMeshContext(modelOf([1, 2, 1], {
            '0,0,0': 'minecraft:stone', '0,1,0': 'minecraft:stone_slab[type=bottom]',
        }), table);
        // stone keeps all 6 faces (a slab does not hide the top), the slab loses its bottom
        expect(countFaces(ctx).solid).toBe(11);
    });
});

describe('shapes and fallback', () => {
    it('renders blocks missing from the table as a full cube', () => {
        const ctx = createMeshContext(modelOf([1, 1, 1], { '0,0,0': 'minecraft:mystery_block' }), table);
        expect(ctx.resolved[1].known).toBe(false);
        expect(countFaces(ctx).solid).toBe(6);
    });

    it('renders unknown shapes as a cube with the block texture', () => {
        const custom = { blocks: { ...table.blocks, 'minecraft:odd': { color: '#123456', shape: 'spiral', tex: 'stone' } } };
        const ctx = createMeshContext(modelOf([1, 1, 1], { '0,0,0': 'minecraft:odd' }), custom, atlas);
        const mesh = meshChunk(ctx, 0, 0, 0);
        expect(mesh.solid.faces).toBe(6);
        const [u0, v0, u1, v1] = atlasRect(atlas, 'stone');
        mesh.solid.uvs.forEach((v, i) => {
            const [lo, hi] = i % 2 === 0 ? [u0, u1] : [v0, v1];
            expect(v).toBeGreaterThanOrEqual(lo - 1e-6);
            expect(v).toBeLessThanOrEqual(hi + 1e-6);
        });
    });

    it('draws plants as two double-sided crossed planes', () => {
        const ctx = createMeshContext(modelOf([1, 1, 1], { '0,0,0': 'minecraft:sugar_cane' }), table, atlas);
        expect(countFaces(ctx).cutout).toBe(4);
    });

    it('uses box models for non-cube blocks', () => {
        const hopper = resolveBlock({ name: 'minecraft:hopper', properties: { facing: 'down' } }, table);
        expect(shapeBoxes(hopper)).toHaveLength(3);
        const fence = resolveBlock({ name: 'minecraft:oak_fence', properties: { north: 'true', east: 'true' } }, table);
        expect(shapeBoxes(fence)).toHaveLength(5);
        const wire = resolveBlock({ name: 'minecraft:redstone_wire', properties: {} }, table);
        const ctx = createMeshContext(modelOf([1, 1, 1], { '0,0,0': 'minecraft:redstone_wire' }), table);
        expect(shapeBoxes(wire)[0].faces).toEqual(['up']);
        expect(countFaces(ctx).solid).toBe(1);
    });

    it('rotates stairs to their facing', () => {
        const stairs = resolveBlock({ name: 'minecraft:oak_stairs', properties: { facing: 'east', half: 'bottom' } }, table);
        const step = shapeBoxes(stairs)[1];
        expect(step.from).toEqual([8, 8, 0]);
        expect(step.to).toEqual([16, 16, 16]);
        expect(rotateBox({ from: [0, 0, 0], to: [16, 16, 4] }, 'up').to[1]).toBe(16);
    });

    it('orients textures by facing and axis', () => {
        const observer = resolveBlock({ name: 'minecraft:observer', properties: { facing: 'down' } }, table);
        expect(observer.faces.down).toBe('observer_front');
        expect(observer.faces.up).toBe('observer_back');
        const log = resolveBlock({ name: 'minecraft:oak_log', properties: { axis: 'x' } }, table);
        expect(log.faces.east).toBe('oak_log_top');
        expect(log.faces.up).toBe('oak_log');
    });
});

describe('textures and colours', () => {
    it('falls back to the white tile and flat colour when a texture is missing', () => {
        const ctx = createMeshContext(modelOf([1, 1, 1], { '0,0,0': 'minecraft:gold_block' }), table, atlas);
        const mesh = meshChunk(ctx, 0, 0, 0);
        const white = atlasRect(atlas, '__white');
        expect(mesh.solid.uvs[0]).toBeGreaterThanOrEqual(white[0]);
        expect(mesh.solid.uvs[0]).toBeLessThanOrEqual(white[2]);
        // gold colour (not white) on the top face (shade 1)
        const top = mesh.solid.colors.slice(0, 3);
        expect(top[2]).toBeLessThan(0.5);
    });

    it('draws see-through blocks translucent in flat colour mode', () => {
        const ctx = createMeshContext(modelOf([1, 1, 1], { '0,0,0': 'minecraft:glass' }), table, null);
        const faces = countFaces(ctx);
        expect(faces.translucent).toBe(6);
        expect(meshChunk(ctx, 0, 0, 0).translucent.colors[3]).toBeLessThan(1);
    });

    it('splits large models into 16x16x16 chunks', () => {
        const ctx = createMeshContext(modelOf([20, 1, 1], { '0,0,0': 'minecraft:stone', '19,0,0': 'minecraft:stone' }), table);
        expect(meshChunk(ctx, 0, 0, 0).solid.faces).toBe(6);
        expect(meshChunk(ctx, 1, 0, 0).solid.faces).toBe(6);
    });
});
