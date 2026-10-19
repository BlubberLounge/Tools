/**
 * Slime chunks (Java Edition), used by slimeFinder.js.
 *
 * Same as WorldgenRandom.seedSlimeChunk in the 26.3 server jar: a
 * java.util.Random-style generator seeded with
 *   seed + x*x*4987142 + x*5947611 + z*z*4392871L + z*389711 ^ 987234911L
 * (the int products overflow like Java ints) decides with nextInt(10) == 0.
 * Slimes spawn in these chunks below Y 40 in any biome. Bedrock Edition uses
 * a different rule.
 */

const MASK_48 = (1n << 48n) - 1n;
const MULTIPLIER = 0x5DEECE66Dn;
const SALT = 987234911n;
const INT_MAX = 2147483647;

export const CHUNK = 16;

/** Spawn sphere: mobs spawn between 24 and 128 blocks from the player. */
export const SPAWN_MIN = 24;
export const SPAWN_MAX = 128;

/** Java String.hashCode over UTF-16 code units. */
export function javaHash(text) {
    let hash = 0;
    for (let i = 0; i < text.length; i++)
        hash = (Math.imul(hash, 31) + text.charCodeAt(i)) | 0;
    return hash;
}

/**
 * World seed as BigInt, the way the game reads the seed field: a whole number
 * in the long range is used as is, any other text is hashed. Empty = null.
 */
export function parseSeed(input) {
    const text = String(input ?? '').trim();
    if (text === '')
        return null;
    if (/^[+-]?\d+$/.test(text)) {
        const value = BigInt(text);
        if (value >= -(1n << 63n) && value < (1n << 63n))
            return value;
    }
    return BigInt(javaHash(text));
}

/** True when chunk (x, z) is a slime chunk for the seed (BigInt). */
export function isSlimeChunk(seed, x, z) {
    const mixed = BigInt.asIntN(64,
        seed
        + BigInt(Math.imul(Math.imul(x, x), 4987142))
        + BigInt(Math.imul(x, 5947611))
        + BigInt(Math.imul(z, z)) * 4392871n
        + BigInt(Math.imul(z, 389711))
    ) ^ SALT;

    let state = (BigInt.asIntN(64, mixed) ^ MULTIPLIER) & MASK_48;
    for (;;) {
        state = (state * MULTIPLIER + 0xBn) & MASK_48;
        const bits = Number(state >> 17n);
        const value = bits % 10;
        if (bits - value + 9 <= INT_MAX)
            return value === 0;
    }
}

/** Chunk of a block coordinate. */
export function chunkOf(block) {
    return Math.floor(Number(block) / CHUNK);
}

/** Slime chunks in a square of chunks around (cx, cz): [{x, z}]. */
export function slimeChunksAround(seed, cx, cz, radius) {
    const result = [];
    for (let z = cz - radius; z <= cz + radius; z++)
        for (let x = cx - radius; x <= cx + radius; x++)
            if (isSlimeChunk(seed, x, z))
                result.push({ x, z });
    return result;
}

/** Horizontal distance from a block position to the centre of a chunk. */
export function distanceToChunk(bx, bz, chunk) {
    const dx = chunk.x * CHUNK + 8 - bx;
    const dz = chunk.z * CHUNK + 8 - bz;
    return Math.hypot(dx, dz);
}

/** Slime chunks whose centre lies in the spawn ring around a block position. */
export function chunksInSpawnRange(chunks, bx, bz) {
    return chunks.filter(c => {
        const d = distanceToChunk(bx, bz, c);
        return d >= SPAWN_MIN && d <= SPAWN_MAX;
    });
}

/**
 * Best AFK chunk centre in a square around (cx, cz): the one with the most
 * slime chunks in its spawn ring. `chunks` must cover the square plus 8 chunks.
 */
export function bestSpot(chunks, cx, cz, radius) {
    let best = null;
    for (let z = cz - radius; z <= cz + radius; z++)
        for (let x = cx - radius; x <= cx + radius; x++) {
            const bx = x * CHUNK + 8;
            const bz = z * CHUNK + 8;
            const count = chunksInSpawnRange(chunks, bx, bz).length;
            if (best === null || count > best.count)
                best = { x, z, bx, bz, count };
        }
    return best;
}
