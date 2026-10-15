#!/usr/bin/env node
/**
 * Build the block texture atlas of the 3D farm viewer from a Minecraft Java
 * client jar that you provide locally:
 *
 *   node scripts/minecraft/build-atlas.mjs <path/to/client.jar> [--out public/minecraft/atlas]
 *
 * The jar is usually at .minecraft/versions/<version>/<version>.jar. The
 * script never downloads anything. It copies the first animation frame of
 * every block texture the block table (resources/minecraft/viewer/blocks.json)
 * and the committed models use into one PNG (16 px tiles) and writes
 * atlas.json with the tile positions. Textures missing in the jar are listed;
 * the viewer draws those faces in the block's flat colour.
 *
 * The textures are Mojang assets: the output folder is git-ignored and is
 * not committed. Without an atlas the viewer uses flat colours.
 */
import { readFileSync, writeFileSync, mkdirSync, readdirSync, existsSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { PNG } from 'pngjs';

import { readZipIndex, readZipEntry } from './lib/zip.js';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const TILE = 16;
const COLUMNS = 32;
const WHITE = '__white';

/** Texture names used by the block table and the models. */
export function textureNames(table, modelPalettes = []) {
    const names = new Set(['lever']);
    for (const [id, def] of Object.entries(table.blocks)) {
        if (def.shape === 'none')
            continue;
        const tex = def.tex ?? id.split(':')[1];
        if (typeof tex === 'string')
            names.add(tex);
        else
            Object.values(tex).forEach(t => names.add(t));
    }
    // blocks without a table entry fall back to a texture named like the block
    for (const name of modelPalettes)
        if (!(name in table.blocks))
            names.add(name.split(':')[1]);
    names.delete('air');
    return [...names].sort();
}

/** Nearest-neighbour copy of the top square frame of a texture into a TILE x TILE tile. */
function copyTile(src, dst, col, row) {
    const frame = src.width; // animated textures are vertical strips of square frames
    for (let y = 0; y < TILE; y++)
        for (let x = 0; x < TILE; x++) {
            const sx = Math.floor(x * frame / TILE);
            const sy = Math.floor(y * frame / TILE);
            const si = (sy * src.width + sx) * 4;
            const di = ((row * TILE + y) * dst.width + col * TILE + x) * 4;
            dst.data[di] = src.data[si];
            dst.data[di + 1] = src.data[si + 1];
            dst.data[di + 2] = src.data[si + 2];
            dst.data[di + 3] = src.data[si + 3];
        }
}

function modelPalettes() {
    const dir = join(root, 'public/minecraft/models');
    if (!existsSync(dir))
        return [];
    const names = new Set();
    for (const f of readdirSync(dir).filter(f => f.endsWith('.json')))
        for (const p of JSON.parse(readFileSync(join(dir, f), 'utf8')).palette ?? [])
            names.add(p.name);
    return [...names];
}

function main() {
    const argv = process.argv.slice(2);
    const jarPath = argv.find(a => !a.startsWith('--'));
    const outIndex = argv.indexOf('--out');
    const outDir = resolve(outIndex >= 0 ? argv[outIndex + 1] : join(root, 'public/minecraft/atlas'));
    if (!jarPath || !existsSync(jarPath)) {
        console.error('usage: node scripts/minecraft/build-atlas.mjs <path/to/client.jar> [--out dir]');
        process.exit(2);
    }

    const jar = readFileSync(jarPath);
    const index = readZipIndex(jar);

    let version = null;
    if (index.has('version.json')) {
        const info = JSON.parse(readZipEntry(jar, index.get('version.json')).toString('utf8'));
        version = info.name ?? info.id ?? null;
    }

    const table = JSON.parse(readFileSync(join(root, 'resources/minecraft/viewer/blocks.json'), 'utf8'));
    const wanted = textureNames(table, modelPalettes());

    const found = [];
    const missing = [];
    for (const name of wanted) {
        const entry = index.get(`assets/minecraft/textures/block/${name}.png`);
        if (!entry) {
            missing.push(name);
            continue;
        }
        found.push({ name, png: PNG.sync.read(readZipEntry(jar, entry)) });
    }

    const tiles = found.length + 1;
    const rows = Math.ceil(tiles / COLUMNS);
    const atlas = new PNG({ width: COLUMNS * TILE, height: rows * TILE });
    atlas.data.fill(0);

    const textures = {};
    // tile 0: white, used for faces whose texture is missing
    for (let y = 0; y < TILE; y++)
        for (let x = 0; x < TILE; x++)
            atlas.data.fill(255, (y * atlas.width + x) * 4, (y * atlas.width + x) * 4 + 4);
    textures[WHITE] = [0, 0];

    found.forEach(({ name, png }, i) => {
        const col = (i + 1) % COLUMNS;
        const row = Math.floor((i + 1) / COLUMNS);
        copyTile(png, atlas, col, row);
        textures[name] = [col, row];
    });

    mkdirSync(outDir, { recursive: true });
    writeFileSync(join(outDir, 'atlas.png'), PNG.sync.write(atlas));
    writeFileSync(join(outDir, 'atlas.json'), JSON.stringify({
        format: 1,
        version,
        created: new Date().toISOString(),
        image: 'atlas.png',
        tile: TILE,
        columns: COLUMNS,
        rows,
        textures,
        missing,
    }, null, 1) + '\n');

    console.log(`atlas: ${found.length} textures from ${version ?? 'unknown version'} -> ${outDir}`);
    if (missing.length)
        console.warn(`${missing.length} texture(s) not in this jar (flat colour instead): ${missing.join(', ')}`);
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url))
    main();
