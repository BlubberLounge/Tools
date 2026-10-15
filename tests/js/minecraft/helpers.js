import { readFileSync } from 'node:fs';
import { buildModelJson, loadModel, parseBlockState } from '../../../resources/js/minecraft/viewer/model.js';

export const table = JSON.parse(readFileSync(new URL('../../../resources/minecraft/viewer/blocks.json', import.meta.url), 'utf8'));

/**
 * Build an in-memory model from a block map: {"x,y,z": "minecraft:stone"}.
 */
export function modelOf(size, blocks) {
    const palette = [{ name: 'minecraft:air', properties: {} }];
    const keys = new Map();
    const grid = new Uint32Array(size[0] * size[1] * size[2]);
    for (const [pos, state] of Object.entries(blocks)) {
        const [x, y, z] = pos.split(',').map(Number);
        if (!keys.has(state)) {
            keys.set(state, palette.length);
            palette.push(parseBlockState(state));
        }
        grid[(y * size[2] + z) * size[0] + x] = keys.get(state);
    }
    const json = buildModelJson({
        id: 'test-model',
        name: 'Test',
        size,
        palette,
        grid,
        source: { url: 'https://example.org', license: 'test' },
    });
    return loadModel(json);
}
