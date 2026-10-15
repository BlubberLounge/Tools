#!/usr/bin/env node
/**
 * Convert a public .litematic, .schem or structure .nbt file into a farm
 * model for the 3D farm viewer (public/minecraft/models/{id}.json).
 *
 *   node scripts/minecraft/convert-schematic.mjs <file> --id <model-id> --name "<name>" \
 *        --source-url <url> --license "<licence>" [--author "<name>"] [--design <catalogue id>] \
 *        [--version 1.21] [--note "<text>"] [--out public/minecraft/models] [--no-trim]
 *
 * Empty borders are trimmed, block properties are reduced to the ones the
 * viewer uses, and the result must fit into 128 x 128 x 128. Blocks missing
 * from resources/minecraft/viewer/blocks.json are reported: add them to the
 * table before committing the model (the model test rejects unknown ids).
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

import { readSchematic, trimEmpty } from './lib/schematic.js';
import { buildModelJson, stringifyModel, validateModel, MAX_SIZE } from '../../resources/js/minecraft/viewer/model.js';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');

function parseArgs(argv) {
    const args = { _: [] };
    for (let i = 0; i < argv.length; i++) {
        const a = argv[i];
        if (a === '--no-trim')
            args.trim = false;
        else if (a.startsWith('--'))
            args[a.slice(2)] = argv[++i];
        else
            args._.push(a);
    }
    return args;
}

/** Convert schematic bytes into a model JSON object (used by the CLI and tests). */
export function convert(bytes, meta, { trim = true } = {}) {
    let schematic = readSchematic(bytes);
    const format = schematic.format;
    if (trim)
        schematic = trimEmpty(schematic);

    const { size } = schematic;
    if (size.some(n => n > MAX_SIZE))
        throw new Error(`model is ${size.join(' x ')}, the viewer allows at most ${MAX_SIZE} per axis`);

    return {
        format,
        model: buildModelJson({
            id: meta.id,
            design: meta.design ?? null,
            name: meta.name,
            version: meta.version ?? null,
            size,
            palette: schematic.palette,
            grid: schematic.grid,
            source: Object.fromEntries(Object.entries({
                url: meta.sourceUrl,
                author: meta.author,
                license: meta.license,
                note: meta.note,
            }).filter(([, v]) => v !== undefined && v !== null && v !== '')),
        }),
    };
}

function main() {
    const args = parseArgs(process.argv.slice(2));
    const file = args._[0];
    if (!file || !args.id || !args.name || !args['source-url'] || !args.license) {
        console.error('usage: node scripts/minecraft/convert-schematic.mjs <file> --id <model-id> --name "<name>" '
            + '--source-url <url> --license "<licence>" [--author ..] [--design ..] [--version ..] [--note ..] [--out dir] [--no-trim]');
        process.exit(2);
    }

    const { format, model } = convert(readFileSync(file), {
        id: args.id,
        design: args.design,
        name: args.name,
        version: args.version,
        sourceUrl: args['source-url'],
        author: args.author,
        license: args.license,
        note: args.note,
    }, { trim: args.trim !== false });

    const table = JSON.parse(readFileSync(join(root, 'resources/minecraft/viewer/blocks.json'), 'utf8'));
    const unknown = [...new Set(model.palette.map(p => p.name).filter(n => !(n in table.blocks)))];

    const errors = validateModel(model);
    if (errors.length) {
        console.error('invalid model:\n  ' + errors.join('\n  '));
        process.exit(1);
    }

    const outDir = resolve(args.out ?? join(root, 'public/minecraft/models'));
    mkdirSync(outDir, { recursive: true });
    const outFile = join(outDir, `${model.id}.json`);
    writeFileSync(outFile, stringifyModel(model));

    console.log(`${format}: ${model.size.join(' x ')} blocks, ${model.palette.length} palette entries -> ${outFile}`);
    if (unknown.length) {
        console.warn(`\n${unknown.length} block id(s) missing in resources/minecraft/viewer/blocks.json `
            + '(rendered as grey cubes; the model test fails until they are added):');
        unknown.forEach(n => console.warn('  ' + n));
    }
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url))
    main();
