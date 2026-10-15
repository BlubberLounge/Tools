/**
 * Minimal read-only ZIP reader (stored and deflated entries, ZIP64 aware),
 * enough to pull textures out of a Minecraft client jar without a dependency.
 */
import { inflateRawSync } from 'node:zlib';

const EOCD = 0x06054b50;
const ZIP64_LOCATOR = 0x07064b50;
const CENTRAL = 0x02014b50;
const LOCAL = 0x04034b50;

/**
 * List the entries of a zip file.
 * @param {Buffer} buf whole file
 * @returns {Map<string, {method, compressedSize, size, offset}>}
 */
export function readZipIndex(buf) {
    let eocd = -1;
    for (let i = buf.length - 22; i >= Math.max(0, buf.length - 22 - 65535); i--)
        if (buf.readUInt32LE(i) === EOCD) {
            eocd = i;
            break;
        }
    if (eocd < 0)
        throw new Error('not a zip file (no end of central directory)');

    let count = buf.readUInt16LE(eocd + 10);
    let cdOffset = buf.readUInt32LE(eocd + 16);

    if ((count === 0xffff || cdOffset === 0xffffffff) && eocd >= 20 && buf.readUInt32LE(eocd - 20) === ZIP64_LOCATOR) {
        const z64 = Number(buf.readBigUInt64LE(eocd - 20 + 8));
        count = Number(buf.readBigUInt64LE(z64 + 32));
        cdOffset = Number(buf.readBigUInt64LE(z64 + 48));
    }

    const entries = new Map();
    let p = cdOffset;
    for (let i = 0; i < count; i++) {
        if (buf.readUInt32LE(p) !== CENTRAL)
            throw new Error('broken central directory');
        const method = buf.readUInt16LE(p + 10);
        let compressedSize = buf.readUInt32LE(p + 20);
        let size = buf.readUInt32LE(p + 24);
        const nameLength = buf.readUInt16LE(p + 28);
        const extraLength = buf.readUInt16LE(p + 30);
        const commentLength = buf.readUInt16LE(p + 32);
        let offset = buf.readUInt32LE(p + 42);
        const name = buf.toString('utf8', p + 46, p + 46 + nameLength);

        // ZIP64 extra field
        let e = p + 46 + nameLength;
        const end = e + extraLength;
        while (e + 4 <= end) {
            const id = buf.readUInt16LE(e);
            const len = buf.readUInt16LE(e + 2);
            if (id === 0x0001) {
                let q = e + 4;
                if (size === 0xffffffff) { size = Number(buf.readBigUInt64LE(q)); q += 8; }
                if (compressedSize === 0xffffffff) { compressedSize = Number(buf.readBigUInt64LE(q)); q += 8; }
                if (offset === 0xffffffff) { offset = Number(buf.readBigUInt64LE(q)); }
            }
            e += 4 + len;
        }

        entries.set(name, { method, compressedSize, size, offset });
        p += 46 + nameLength + extraLength + commentLength;
    }
    return entries;
}

/** Read one entry's bytes. */
export function readZipEntry(buf, entry) {
    if (buf.readUInt32LE(entry.offset) !== LOCAL)
        throw new Error('broken local header');
    const nameLength = buf.readUInt16LE(entry.offset + 26);
    const extraLength = buf.readUInt16LE(entry.offset + 28);
    const start = entry.offset + 30 + nameLength + extraLength;
    const data = buf.subarray(start, start + entry.compressedSize);
    if (entry.method === 0)
        return Buffer.from(data);
    if (entry.method === 8)
        return inflateRawSync(data);
    throw new Error(`unsupported zip compression method ${entry.method}`);
}
