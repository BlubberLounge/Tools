/**
 * Minimal NBT reader/writer (Java Edition, big-endian, optionally gzipped).
 *
 * Reading returns plain values: compounds are objects, lists arrays, longs
 * BigInt, byte/int/long arrays typed arrays. Writing takes typed tags
 * ({type: 'compound', value: {...}}) and is used by tests and fixtures.
 */
import { gunzipSync, gzipSync } from 'node:zlib';

const TAG = {
    end: 0, byte: 1, short: 2, int: 3, long: 4, float: 5, double: 6,
    byteArray: 7, string: 8, list: 9, compound: 10, intArray: 11, longArray: 12,
};
const TAG_NAMES = Object.fromEntries(Object.entries(TAG).map(([k, v]) => [v, k]));

class Reader {
    constructor(buffer) {
        this.view = new DataView(buffer.buffer, buffer.byteOffset, buffer.byteLength);
        this.bytes = buffer;
        this.pos = 0;
    }

    byte() { return this.view.getInt8(this.pos++); }
    ubyte() { return this.view.getUint8(this.pos++); }
    short() { const v = this.view.getInt16(this.pos); this.pos += 2; return v; }
    ushort() { const v = this.view.getUint16(this.pos); this.pos += 2; return v; }
    int() { const v = this.view.getInt32(this.pos); this.pos += 4; return v; }
    long() { const v = this.view.getBigInt64(this.pos); this.pos += 8; return v; }
    float() { const v = this.view.getFloat32(this.pos); this.pos += 4; return v; }
    double() { const v = this.view.getFloat64(this.pos); this.pos += 8; return v; }

    string() {
        const length = this.ushort();
        const text = new TextDecoder().decode(this.bytes.subarray(this.pos, this.pos + length));
        this.pos += length;
        return text;
    }

    payload(type) {
        switch (type) {
            case TAG.byte: return this.byte();
            case TAG.short: return this.short();
            case TAG.int: return this.int();
            case TAG.long: return this.long();
            case TAG.float: return this.float();
            case TAG.double: return this.double();
            case TAG.byteArray: {
                const n = this.int();
                const out = new Int8Array(this.bytes.buffer.slice(this.bytes.byteOffset + this.pos, this.bytes.byteOffset + this.pos + n));
                this.pos += n;
                return out;
            }
            case TAG.string: return this.string();
            case TAG.list: {
                const itemType = this.ubyte();
                const n = this.int();
                const out = [];
                for (let i = 0; i < n; i++)
                    out.push(this.payload(itemType));
                return out;
            }
            case TAG.compound: {
                const out = {};
                for (;;) {
                    const t = this.ubyte();
                    if (t === TAG.end)
                        break;
                    const name = this.string();
                    out[name] = this.payload(t);
                }
                return out;
            }
            case TAG.intArray: {
                const n = this.int();
                const out = new Int32Array(n);
                for (let i = 0; i < n; i++)
                    out[i] = this.int();
                return out;
            }
            case TAG.longArray: {
                const n = this.int();
                const out = new BigInt64Array(n);
                for (let i = 0; i < n; i++)
                    out[i] = this.long();
                return out;
            }
            default:
                throw new Error(`unknown NBT tag type ${type} at ${this.pos}`);
        }
    }
}

/**
 * Read an NBT file (gzipped or not). Returns {name, value} of the root compound.
 * @param {Uint8Array} data
 */
export function readNbt(data) {
    let bytes = data instanceof Uint8Array ? data : new Uint8Array(data);
    if (bytes[0] === 0x1f && bytes[1] === 0x8b)
        bytes = new Uint8Array(gunzipSync(bytes));

    const reader = new Reader(bytes);
    const type = reader.ubyte();
    if (type !== TAG.compound)
        throw new Error('NBT root is not a compound');
    const name = reader.string();
    return { name, value: reader.payload(TAG.compound) };
}

class Writer {
    constructor() {
        this.chunks = [];
    }

    raw(size, fn) {
        const b = new DataView(new ArrayBuffer(size));
        fn(b);
        this.chunks.push(new Uint8Array(b.buffer));
    }

    ubyte(v) { this.raw(1, b => b.setUint8(0, v)); }
    string(s) {
        const enc = new TextEncoder().encode(s);
        this.raw(2, b => b.setUint16(0, enc.length));
        this.chunks.push(enc);
    }

    payload(tag) {
        const { type, value } = tag;
        switch (type) {
            case 'byte': return this.raw(1, b => b.setInt8(0, value));
            case 'short': return this.raw(2, b => b.setInt16(0, value));
            case 'int': return this.raw(4, b => b.setInt32(0, value));
            case 'long': return this.raw(8, b => b.setBigInt64(0, BigInt(value)));
            case 'float': return this.raw(4, b => b.setFloat32(0, value));
            case 'double': return this.raw(8, b => b.setFloat64(0, value));
            case 'string': return this.string(value);
            case 'byteArray':
                this.raw(4, b => b.setInt32(0, value.length));
                return this.chunks.push(Uint8Array.from(value, v => v & 255));
            case 'intArray':
                this.raw(4, b => b.setInt32(0, value.length));
                return value.forEach(v => this.raw(4, b => b.setInt32(0, v)));
            case 'longArray':
                this.raw(4, b => b.setInt32(0, value.length));
                return value.forEach(v => this.raw(8, b => b.setBigInt64(0, BigInt.asIntN(64, BigInt(v)))));
            case 'list': {
                const itemType = tag.itemType ?? (value[0]?.type ?? 'end');
                this.ubyte(TAG[itemType]);
                this.raw(4, b => b.setInt32(0, value.length));
                return value.forEach(v => this.payload(v));
            }
            case 'compound':
                for (const [name, child] of Object.entries(value)) {
                    this.ubyte(TAG[child.type]);
                    this.string(name);
                    this.payload(child);
                }
                return this.ubyte(TAG.end);
            default:
                throw new Error(`cannot write NBT type ${type}`);
        }
    }
}

/**
 * Write a typed root compound to bytes (gzipped by default).
 * @param {string} name
 * @param {{type: 'compound', value: object}} root
 */
export function writeNbt(name, root, gzip = true) {
    const w = new Writer();
    w.ubyte(TAG.compound);
    w.string(name);
    w.payload(root);
    const total = w.chunks.reduce((n, c) => n + c.length, 0);
    const out = new Uint8Array(total);
    let pos = 0;
    for (const c of w.chunks) {
        out.set(c, pos);
        pos += c.length;
    }
    return gzip ? new Uint8Array(gzipSync(out)) : out;
}

export { TAG, TAG_NAMES };
