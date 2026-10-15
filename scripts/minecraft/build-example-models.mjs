#!/usr/bin/env node
/**
 * Writes the hand-built example models of the 3D farm viewer to
 * public/minecraft/models/. They are small illustrative layouts of common farm
 * mechanisms written for this repository (MIT, like the rest of it), not
 * copies of published designs and not tested in game. Real catalogue designs
 * are converted from their public schematics with convert-schematic.mjs.
 *
 *   node scripts/minecraft/build-example-models.mjs
 */
import { writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

import { buildModelJson, parseBlockState, stringifyModel } from '../../resources/js/minecraft/viewer/model.js';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const SOURCE = {
    url: 'https://github.com/BlubberLounge/Tools',
    author: 'BlubberLounge Tools',
    license: 'MIT (own example)',
    note: 'Hand-built example layout to show the viewer, not a published design and not tested in game.',
};

/** Tiny builder: set blocks by block state string, then export the model. */
class Build {
    constructor(sx, sy, sz) {
        this.size = [sx, sy, sz];
        this.palette = [{ name: 'minecraft:air', properties: {} }];
        this.keys = new Map([['minecraft:air', 0]]);
        this.grid = new Uint32Array(sx * sy * sz);
    }

    set(x, y, z, state) {
        const [sx, sy, sz] = this.size;
        if (x < 0 || y < 0 || z < 0 || x >= sx || y >= sy || z >= sz)
            throw new Error(`(${x}, ${y}, ${z}) is outside the model`);
        const key = state.includes(':') ? state : 'minecraft:' + state;
        if (!this.keys.has(key)) {
            this.keys.set(key, this.palette.length);
            this.palette.push(parseBlockState(key));
        }
        this.grid[(y * sz + z) * sx + x] = this.keys.get(key);
    }

    fill(x0, y0, z0, x1, y1, z1, state) {
        for (let y = y0; y <= y1; y++)
            for (let z = z0; z <= z1; z++)
                for (let x = x0; x <= x1; x++)
                    this.set(x, y, z, state);
    }

    model(meta) {
        return buildModelJson({ ...meta, size: this.size, palette: this.palette, grid: this.grid, source: SOURCE });
    }
}

/** Sugar cane: water channel over a hopper line, dirt row, pistons and observers. */
function sugarCane() {
    const b = new Build(9, 6, 6);
    const len = 7;
    // floor and back wall
    b.fill(0, 0, 0, len, 0, 5, 'stone_bricks');
    b.fill(0, 1, 0, len - 1, 3, 0, 'glass');
    // hopper line under the water, into a chest at the end
    for (let x = 0; x < len; x++)
        b.set(x, 0, 1, 'hopper[facing=east]');
    b.set(len, 0, 1, 'chest[facing=north]');
    b.set(len, 1, 1, 'stone_brick_slab[type=bottom]');
    b.fill(0, 1, 1, len - 1, 1, 1, 'water[level=0]');
    // dirt row with sugar cane
    b.fill(0, 1, 2, len - 1, 1, 2, 'dirt');
    for (let x = 0; x < len; x++) {
        b.set(x, 2, 2, 'sugar_cane');
        b.set(x, 3, 2, 'sugar_cane');
    }
    // pistons push the third block, observers on top watch it
    b.fill(0, 1, 3, len - 1, 2, 3, 'smooth_stone');
    for (let x = 0; x < len; x++) {
        b.set(x, 3, 3, 'piston[facing=north]');
        b.set(x, 4, 2, 'observer[facing=down]');
        b.set(x, 4, 3, 'redstone_wire');
        b.set(x, 5, 2, 'stone_brick_slab[type=bottom]');
    }
    // end caps
    b.fill(len, 1, 2, len, 4, 3, 'stone_bricks');
    b.set(len, 5, 2, 'torch');
    b.set(len, 2, 1, 'wall_torch[facing=west]');
    b.set(8, 1, 4, 'oak_stairs[facing=west,half=bottom]');
    b.set(8, 0, 1, 'cobblestone_wall[up=true,north=none,south=none,east=none,west=low]');
    return b.model({
        id: 'example-sugar-cane-module',
        name: 'Sugar cane module (example)',
        version: '1.21',
    });
}

/** Kelp: glass column with water, kelp on sand, piston and observer, hopper below. */
function kelp() {
    const b = new Build(5, 13, 5);
    b.fill(0, 0, 0, 4, 0, 4, 'stone_bricks');
    b.set(2, 0, 2, 'chest[facing=south]');
    b.set(2, 1, 2, 'hopper[facing=down]');
    b.set(2, 2, 2, 'sand');
    // glass shell
    b.fill(1, 1, 1, 3, 12, 3, 'glass');
    b.set(2, 1, 2, 'hopper[facing=down]');
    b.set(2, 2, 2, 'sand');
    b.fill(2, 3, 2, 2, 11, 2, 'water[level=0]');
    b.set(2, 3, 2, 'kelp_plant');
    for (let y = 4; y <= 10; y++)
        b.set(2, y, 2, 'kelp_plant');
    b.set(2, 11, 2, 'kelp');
    b.set(2, 12, 2, 'air');
    // piston harvests above the bottom kelp, observer watches the next block
    b.set(2, 4, 1, 'sticky_piston[facing=south]');
    b.set(2, 5, 1, 'observer[facing=south]');
    b.set(2, 4, 0, 'redstone_block');
    b.set(2, 5, 0, 'redstone_wire');
    b.set(2, 6, 0, 'repeater[facing=north]');
    b.set(3, 0, 2, 'comparator[facing=west]');
    b.set(4, 0, 2, 'redstone_lamp');
    b.set(0, 1, 0, 'lantern');
    b.set(4, 1, 4, 'lantern');
    b.set(0, 1, 4, 'ladder[facing=east]');
    b.fill(0, 1, 3, 0, 4, 3, 'oak_planks');
    for (let y = 1; y <= 4; y++)
        b.set(0, y, 4, 'ladder[facing=north]');
    b.fill(1, 12, 1, 3, 12, 3, 'glass');
    b.set(2, 12, 2, 'water[level=0]');
    return b.model({
        id: 'example-kelp-column',
        name: 'Kelp column (example)',
        version: '1.21',
    });
}

/** Iron farm pod: hopper floor into a chest, glass kill chamber, villager pod with beds and composters. */
function ironFarm() {
    const b = new Build(9, 11, 9);
    b.fill(0, 0, 0, 8, 0, 8, 'smooth_stone');
    // hopper floor collecting into the centre, chest below
    b.set(4, 0, 4, 'chest[facing=south]');
    const hoppers = {
        '3,3': 'east', '4,3': 'south', '5,3': 'west',
        '3,4': 'east', '4,4': 'down', '5,4': 'west',
        '3,5': 'east', '4,5': 'north', '5,5': 'west',
    };
    for (const [k, f] of Object.entries(hoppers)) {
        const [x, z] = k.split(',').map(Number);
        b.set(x, 1, z, `hopper[facing=${f}]`);
    }
    // kill chamber walls
    for (let y = 1; y <= 4; y++)
        for (let i = 2; i <= 6; i++) {
            b.set(i, y, 2, 'glass');
            b.set(i, y, 6, 'glass');
            b.set(2, y, i, 'glass');
            b.set(6, y, i, 'glass');
        }
    b.fill(3, 4, 3, 5, 4, 5, 'lava[level=0]');
    b.set(3, 4, 4, 'oak_wall_sign[facing=east]');
    b.set(5, 4, 4, 'oak_wall_sign[facing=west]');
    // spawning platform
    b.fill(1, 5, 1, 7, 5, 7, 'smooth_stone_slab[type=bottom]');
    b.fill(3, 5, 3, 5, 5, 5, 'air');
    b.fill(2, 6, 2, 6, 6, 2, 'stone_brick_stairs[facing=north,half=bottom]');
    // villager pod with beds and composters
    b.fill(2, 8, 2, 6, 8, 6, 'stone_bricks');
    for (const x of [2, 4, 6]) {
        b.set(x, 9, 3, 'red_bed[facing=north,part=head]');
        b.set(x, 9, 4, 'red_bed[facing=north,part=foot]');
        b.set(x, 9, 5, 'composter');
    }
    for (let x = 1; x <= 7; x++) {
        b.set(x, 9, 1, 'glass_pane[east=true,west=true]');
        b.set(x, 9, 7, 'glass_pane[east=true,west=true]');
    }
    b.set(1, 10, 4, 'oak_trapdoor[facing=east,half=bottom,open=true]');
    b.set(7, 10, 4, 'oak_trapdoor[facing=west,half=bottom,open=true]');
    b.fill(2, 10, 2, 6, 10, 2, 'oak_trapdoor[facing=south,half=top,open=false]');
    // corners: fences, walls, torches
    for (const [x, z] of [[0, 0], [8, 0], [0, 8], [8, 8]]) {
        b.set(x, 1, z, 'cobblestone_wall[up=true]');
        b.set(x, 2, z, 'oak_fence');
        b.set(x, 3, z, 'torch');
    }
    b.set(1, 1, 0, 'oak_fence[east=true,west=true]');
    b.set(7, 1, 0, 'oak_fence[east=true,west=true]');
    // a rail line to the chest for a minecart, with a lever and a button
    for (let x = 0; x <= 8; x++)
        if (x !== 4)
            b.set(x, 1, 8, x === 2 || x === 6 ? 'powered_rail[shape=east_west]' : 'rail[shape=east_west]');
    b.set(4, 1, 8, 'detector_rail[shape=east_west]');
    b.set(4, 1, 7, 'lever[face=floor,facing=north]');
    b.set(8, 2, 4, 'stone_button[face=wall,facing=east]');
    b.set(8, 1, 4, 'iron_block');
    b.set(8, 1, 3, 'redstone_wall_torch[facing=east]');
    return b.model({
        id: 'example-iron-farm-pod',
        name: 'Iron farm pod (example)',
        version: '1.21',
    });
}

function main() {
    const out = join(root, 'public/minecraft/models');
    mkdirSync(out, { recursive: true });
    for (const model of [sugarCane(), kelp(), ironFarm()]) {
        writeFileSync(join(out, `${model.id}.json`), stringifyModel(model));
        console.log(`${model.id}: ${model.size.join(' x ')}, ${model.palette.length} palette entries`);
    }
}

main();
