/**
 * Chunked mesher: turns a model into plain vertex arrays, one set per render
 * layer (solid, cutout, translucent) and chunk. No three.js in here, so the
 * logic runs in tests; renderer.js wraps the arrays in BufferGeometry.
 *
 * - Faces between a block and a full opaque neighbour are culled.
 * - Faces between two blocks of the same see-through kind (glass, water) are culled.
 * - Non-cube shapes come from shapes.js; unknown shapes are full cubes.
 * - Directional shading and biome tint are baked into vertex colours.
 * - Without a texture atlas every face gets the block's flat colour.
 *
 * Hot path: no allocations per face (typed growable buffers, face styles
 * cached per palette entry).
 */
import { FACES, FACE_NORMALS, resolveBlock } from './blocks.js';
import { shapeBoxes } from './shapes.js';

export const CHUNK = 16;
export const LAYERS = ['solid', 'cutout', 'translucent'];

const SHADE = { up: 1, down: 0.5, north: 0.8, south: 0.8, east: 0.6, west: 0.6 };
const CROSS_SHADE = 0.85;
const FULL = { from: [0, 0, 0], to: [16, 16, 16] };

// corner selection per face (0 = box `from`, 1 = box `to` on that axis), counter-clockwise from outside
const CORNERS = {
    up: [[0, 1, 1], [1, 1, 1], [1, 1, 0], [0, 1, 0]],
    down: [[0, 0, 0], [1, 0, 0], [1, 0, 1], [0, 0, 1]],
    north: [[1, 0, 0], [0, 0, 0], [0, 1, 0], [1, 1, 0]],
    south: [[0, 0, 1], [1, 0, 1], [1, 1, 1], [0, 1, 1]],
    east: [[1, 0, 1], [1, 0, 0], [1, 1, 0], [1, 1, 1]],
    west: [[0, 0, 0], [0, 0, 1], [0, 1, 1], [0, 1, 0]],
};

// boundary test per face: [axis, side] (side 1 = the face sits on the block's max side)
const BOUNDARY = {
    up: [1, 1], down: [1, 0], north: [2, 0], south: [2, 1], east: [0, 1], west: [0, 0],
};

/** Texture coordinates (0..1 inside the tile) of a corner, like the game's auto UV. */
function cornerU(face, x, y, z) {
    switch (face) {
        case 'up':
        case 'down':
        case 'south': return x / 16;
        case 'north': return 1 - x / 16;
        case 'east': return 1 - z / 16;
        default: return z / 16; // west
    }
}

function cornerV(face, x, y, z) {
    switch (face) {
        case 'up': return 1 - z / 16;
        case 'down': return z / 16;
        default: return y / 16;
    }
}

/** Atlas rectangle [u0, v0, u1, v1] of a texture, or null. */
export function atlasRect(atlas, name) {
    const cell = atlas?.textures?.[name];
    if (!cell)
        return null;
    const inset = 0.001;
    const u0 = (cell[0] + inset) / atlas.columns;
    const u1 = (cell[0] + 1 - inset) / atlas.columns;
    // v = 1 is the top of the image
    const v1 = 1 - (cell[1] + inset) / atlas.rows;
    const v0 = 1 - (cell[1] + 1 - inset) / atlas.rows;
    return [u0, v0, u1, v1];
}

/** Growable vertex buffer of one layer. */
class LayerBuffer {
    constructor() {
        this.capacity = 0;
        this.vertices = 0;
        this.faces = 0;
        this.grow(64);
    }

    grow(quads) {
        const vertexCapacity = Math.max(quads * 4, this.capacity * 2);
        const resize = (old, Type, size) => {
            const next = new Type(vertexCapacity * size);
            if (old)
                next.set(old.subarray(0, this.vertices * size));
            return next;
        };
        this.positions = resize(this.positions, Float32Array, 3);
        this.uvs = resize(this.uvs, Float32Array, 2);
        this.colors = resize(this.colors, Float32Array, 4);
        this.indexArray = (() => {
            const next = new Uint32Array(vertexCapacity / 4 * 6);
            if (this.indexArray)
                next.set(this.indexArray.subarray(0, this.faces * 6));
            return next;
        })();
        this.capacity = vertexCapacity;
    }

    /** Start a quad; returns the index of its first vertex. */
    quad() {
        if (this.vertices + 4 > this.capacity)
            this.grow(0);
        const base = this.vertices;
        const i = this.faces * 6;
        this.indexArray[i] = base;
        this.indexArray[i + 1] = base + 1;
        this.indexArray[i + 2] = base + 2;
        this.indexArray[i + 3] = base;
        this.indexArray[i + 4] = base + 2;
        this.indexArray[i + 5] = base + 3;
        this.faces++;
        this.vertices += 4;
        return base;
    }

    vertex(v, x, y, z, u, uvV, color) {
        this.positions[v * 3] = x;
        this.positions[v * 3 + 1] = y;
        this.positions[v * 3 + 2] = z;
        this.uvs[v * 2] = u;
        this.uvs[v * 2 + 1] = uvV;
        this.colors[v * 4] = color[0];
        this.colors[v * 4 + 1] = color[1];
        this.colors[v * 4 + 2] = color[2];
        this.colors[v * 4 + 3] = color[3];
    }

    /** Trimmed arrays for the renderer and tests. */
    result() {
        return {
            positions: this.positions.subarray(0, this.vertices * 3),
            uvs: this.uvs.subarray(0, this.vertices * 2),
            colors: this.colors.subarray(0, this.vertices * 4),
            indices: this.indexArray.subarray(0, this.faces * 6),
            faces: this.faces,
        };
    }
}

/** Layer a block is drawn in. Without textures cutout blocks are plain solid colour. */
function layerOf(textured, block) {
    if (textured)
        return block.layer;
    return block.alpha < 1 ? 'translucent' : 'solid';
}

/** Vertex colour and atlas rectangle of a face. */
function faceStyle(atlas, block, face, texName, shade) {
    let rect = null;
    let rgb = block.color;
    if (atlas) {
        rect = atlasRect(atlas, texName);
        if (rect) {
            const tinted = block.tint && (!block.tintFaces || block.tintFaces.includes(face));
            rgb = tinted ? block.tint : [1, 1, 1];
        } else {
            // texture missing from the atlas: flat colour on the white tile
            rect = atlasRect(atlas, '__white');
        }
    }
    const alpha = atlas ? 1 : block.alpha;
    return { rect, color: [rgb[0] * shade, rgb[1] * shade, rgb[2] * shade, alpha] };
}

/**
 * Prepare everything the mesher needs once per model.
 * @param {object} model   loadModel() result
 * @param {object} table   block table {blocks}
 * @param {object|null} atlas  {columns, rows, textures: {name: [col, row]}} or null for flat colours
 */
export function createMeshContext(model, table, atlas = null) {
    const resolved = model.palette.map(entry => resolveBlock(entry, table));
    const occludes = Uint8Array.from(resolved, b => (b.occludes ? 1 : 0));
    const boxes = resolved.map(b => shapeBoxes(b));
    const textured = !!atlas;
    const layers = resolved.map(b => layerOf(textured, b));
    // face style per palette entry and face (box texture overrides are styled on demand)
    const styles = resolved.map(b => {
        const s = {};
        for (const f of FACES)
            s[f] = faceStyle(atlas, b, f, b.faces[f], SHADE[f]);
        s.cross = faceStyle(atlas, b, 'north', b.faces.north, CROSS_SHADE);
        return s;
    });
    return { model, resolved, occludes, boxes, layers, styles, atlas, textured };
}

function emitFace(buf, style, face, from, to, ox, oy, oz) {
    const rect = style.rect;
    const color = style.color;
    const corners = CORNERS[face];
    const base = buf.quad();
    for (let c = 0; c < 4; c++) {
        const sel = corners[c];
        const x = sel[0] ? to[0] : from[0];
        const y = sel[1] ? to[1] : from[1];
        const z = sel[2] ? to[2] : from[2];
        let u = cornerU(face, x, y, z);
        let v = cornerV(face, x, y, z);
        if (rect) {
            u = rect[0] + u * (rect[2] - rect[0]);
            v = rect[1] + v * (rect[3] - rect[1]);
        }
        buf.vertex(base + c, ox + x / 16, oy + y / 16, oz + z / 16, u, v, color);
    }
}

function emitCross(buf, style, ox, oy, oz) {
    const { rect, color } = style;
    const u0 = rect ? rect[0] : 0, v0 = rect ? rect[1] : 0;
    const u1 = rect ? rect[2] : 1, v1 = rect ? rect[3] : 1;
    const a = 0.15, b = 0.85;
    const planes = [
        [ox + a, oz + a, ox + b, oz + b],
        [ox + a, oz + b, ox + b, oz + a],
    ];
    for (const [x0, z0, x1, z1] of planes) {
        let base = buf.quad();
        buf.vertex(base, x0, oy, z0, u0, v0, color);
        buf.vertex(base + 1, x1, oy, z1, u1, v0, color);
        buf.vertex(base + 2, x1, oy + 1, z1, u1, v1, color);
        buf.vertex(base + 3, x0, oy + 1, z0, u0, v1, color);
        // back side
        base = buf.quad();
        buf.vertex(base, x1, oy, z1, u0, v0, color);
        buf.vertex(base + 1, x0, oy, z0, u1, v0, color);
        buf.vertex(base + 2, x0, oy + 1, z0, u1, v1, color);
        buf.vertex(base + 3, x1, oy + 1, z1, u0, v1, color);
    }
}

/**
 * Mesh one chunk. Returns {solid, cutout, translucent}, each with typed
 * arrays positions (xyz), uvs, colors (rgba), indices and a face count.
 */
export function meshChunk(ctx, cx, cy, cz) {
    const { model, resolved, occludes, boxes, layers, styles, atlas } = ctx;
    const [sx, sy, sz] = model.size;
    const grid = model.grid;
    const bufs = { solid: new LayerBuffer(), cutout: new LayerBuffer(), translucent: new LayerBuffer() };

    const x1 = Math.min(sx, (cx + 1) * CHUNK);
    const y1 = Math.min(sy, (cy + 1) * CHUNK);
    const z1 = Math.min(sz, (cz + 1) * CHUNK);

    for (let y = cy * CHUNK; y < y1; y++)
        for (let z = cz * CHUNK; z < z1; z++)
            for (let x = cx * CHUNK; x < x1; x++) {
                const idx = grid[(y * sz + z) * sx + x];
                if (idx === 0)
                    continue;
                const block = resolved[idx];
                if (block.invisible)
                    continue;

                const buf = bufs[layers[idx]];
                const shape = boxes[idx];
                const style = styles[idx];

                if (shape === 'cross') {
                    emitCross(buf, style.cross, x, y, z);
                    continue;
                }

                const list = shape === null ? [FULL] : shape;
                for (let bi = 0; bi < list.length; bi++) {
                    const b = list[bi];
                    for (let fi = 0; fi < 6; fi++) {
                        const face = FACES[fi];
                        if (b.faces && !b.faces.includes(face))
                            continue;

                        // only faces on the block boundary can touch the neighbour
                        const [axis, side] = BOUNDARY[face];
                        if (side ? b.to[axis] === 16 : b.from[axis] === 0) {
                            const n = FACE_NORMALS[face];
                            const nx = x + n[0], ny = y + n[1], nz = z + n[2];
                            if (nx >= 0 && ny >= 0 && nz >= 0 && nx < sx && ny < sy && nz < sz) {
                                const other = grid[(ny * sz + nz) * sx + nx];
                                if (other !== 0) {
                                    if (occludes[other])
                                        continue;
                                    if (shape === null && block.cullSame && resolved[other].name === block.name)
                                        continue;
                                }
                            }
                        }

                        const faceStyleOf = b.tex
                            ? faceStyle(atlas, block, face, b.tex, SHADE[face])
                            : style[face];
                        emitFace(buf, faceStyleOf, face, b.from, b.to, x, y, z);
                    }
                }
            }

    return { solid: bufs.solid.result(), cutout: bufs.cutout.result(), translucent: bufs.translucent.result() };
}

/** Chunk coordinates of a model, bottom layer first. */
export function chunkList(size) {
    const list = [];
    for (let cy = 0; cy < Math.ceil(size[1] / CHUNK); cy++)
        for (let cz = 0; cz < Math.ceil(size[2] / CHUNK); cz++)
            for (let cx = 0; cx < Math.ceil(size[0] / CHUNK); cx++)
                list.push([cx, cy, cz]);
    return list;
}

/** Face count per layer for the whole model (tests and stats). */
export function countFaces(ctx) {
    const counts = { solid: 0, cutout: 0, translucent: 0 };
    for (const [cx, cy, cz] of chunkList(ctx.model.size)) {
        const m = meshChunk(ctx, cx, cy, cz);
        for (const l of LAYERS)
            counts[l] += m[l].faces;
    }
    return counts;
}
