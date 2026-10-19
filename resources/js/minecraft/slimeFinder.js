/**
 * Minecraft slime chunk finder (resources/views/minecraft/slime-finder.blade.php).
 *
 * Map of the slime chunks around a position for a world seed, the nearest
 * slime chunks with their block ranges, and the AFK spot with the most slime
 * chunks in its spawn ring. The maths lives in slimeMath.js.
 */
import {
    CHUNK, SPAWN_MIN, SPAWN_MAX,
    parseSeed, chunkOf, slimeChunksAround, chunksInSpawnRange, distanceToChunk, bestSpot,
} from './slimeMath';

const RADII = [8, 16, 32];
const MAX_CANVAS = 720;

const cssColor = (name, fallback) => {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return value || fallback;
};

function slimeFinder(config) {
    let cell = 1;

    return {
        text: config.text,
        radii: RADII,
        CHUNK,

        seed: '',
        x: 0,
        z: 0,
        radius: 16,

        // results, refreshed by update()
        valid: false,
        chunks: [],
        nearest: [],
        inRange: 0,
        best: null,
        hover: null,

        init() {
            for (const key of ['seed', 'x', 'z', 'radius'])
                this.$watch(key, () => this.update());
            window.addEventListener('resize', () => this.draw());
            this.update();
        },

        get center() {
            return { x: chunkOf(this.x), z: chunkOf(this.z) };
        },

        update() {
            const seed = parseSeed(this.seed);
            this.valid = seed !== null;
            if (!this.valid) {
                this.chunks = [];
                this.nearest = [];
                this.inRange = 0;
                this.best = null;
                this.$nextTick(() => this.draw());
                return;
            }

            const { x: cx, z: cz } = this.center;
            const radius = Number(this.radius);
            // 8 chunks more than the map so the spawn ring of every spot on it is covered
            const around = slimeChunksAround(seed, cx, cz, radius + 8);
            this.chunks = around.filter(c => Math.abs(c.x - cx) <= radius && Math.abs(c.z - cz) <= radius);

            const bx = Number(this.x) || 0;
            const bz = Number(this.z) || 0;
            this.nearest = this.chunks
                .map(c => ({ ...c, distance: distanceToChunk(bx, bz, c) }))
                .sort((a, b) => a.distance - b.distance)
                .slice(0, 8);
            this.inRange = chunksInSpawnRange(around, bx, bz).length;
            this.best = bestSpot(around, cx, cz, Math.min(radius, 16));

            this.$nextTick(() => this.draw());
        },

        draw() {
            const canvas = this.$refs.canvas;
            if (!canvas)
                return;
            const radius = Number(this.radius);
            const size = 2 * radius + 1;
            const available = canvas.parentElement.clientWidth || 600;
            cell = Math.max(4, Math.min(28, Math.floor(Math.min(available, MAX_CANVAS) / size)));

            const ratio = window.devicePixelRatio || 1;
            canvas.width = size * cell * ratio;
            canvas.height = size * cell * ratio;
            canvas.style.width = `${size * cell}px`;
            canvas.style.height = `${size * cell}px`;

            const ctx = canvas.getContext('2d');
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.fillStyle = cssColor('--color-base-200', '#e5e7eb');
            ctx.fillRect(0, 0, size * cell, size * cell);

            const { x: cx, z: cz } = this.center;
            const left = cx - radius;
            const top = cz - radius;

            ctx.fillStyle = cssColor('--color-success', '#16a34a');
            for (const c of this.chunks)
                ctx.fillRect((c.x - left) * cell, (c.z - top) * cell, cell, cell);

            if (cell >= 8) {
                ctx.strokeStyle = cssColor('--color-base-300', '#d1d5db');
                ctx.lineWidth = 1;
                ctx.beginPath();
                for (let i = 0; i <= size; i++) {
                    ctx.moveTo(i * cell + 0.5, 0);
                    ctx.lineTo(i * cell + 0.5, size * cell);
                    ctx.moveTo(0, i * cell + 0.5);
                    ctx.lineTo(size * cell, i * cell + 0.5);
                }
                ctx.stroke();
            }

            // position and its spawn ring (block coordinates scaled to chunks)
            const px = ((Number(this.x) || 0) / CHUNK - left) * cell;
            const pz = ((Number(this.z) || 0) / CHUNK - top) * cell;
            ctx.strokeStyle = cssColor('--color-error', '#dc2626');
            ctx.lineWidth = 2;
            for (const r of [SPAWN_MIN, SPAWN_MAX]) {
                ctx.beginPath();
                ctx.arc(px, pz, r / CHUNK * cell, 0, 2 * Math.PI);
                ctx.stroke();
            }
            ctx.fillStyle = cssColor('--color-error', '#dc2626');
            ctx.beginPath();
            ctx.arc(px, pz, Math.max(3, cell / 4), 0, 2 * Math.PI);
            ctx.fill();

            if (this.best) {
                ctx.strokeStyle = cssColor('--color-warning', '#f59e0b');
                ctx.lineWidth = 3;
                ctx.strokeRect((this.best.x - left) * cell + 1.5, (this.best.z - top) * cell + 1.5, cell - 3, cell - 3);
            }
        },

        onMove(event) {
            const rect = event.target.getBoundingClientRect();
            const radius = Number(this.radius);
            const i = Math.floor((event.clientX - rect.left) / cell);
            const j = Math.floor((event.clientY - rect.top) / cell);
            if (i < 0 || j < 0 || i > 2 * radius || j > 2 * radius) {
                this.hover = null;
                return;
            }
            const x = this.center.x - radius + i;
            const z = this.center.z - radius + j;
            this.hover = { x, z, slime: this.chunks.some(c => c.x === x && c.z === z) };
        },

        goTo(chunk) {
            this.x = chunk.x * CHUNK + 8;
            this.z = chunk.z * CHUNK + 8;
        },

        blocks(chunk) {
            return `X ${chunk.x * CHUNK}…${chunk.x * CHUNK + 15}, Z ${chunk.z * CHUNK}…${chunk.z * CHUNK + 15}`;
        },

        format(value, digits = 0) {
            return Number.isFinite(value) ? value.toLocaleString(undefined, { maximumFractionDigits: digits }) : '-';
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('slime-finder');
const configElement = document.getElementById('slime-finder-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('slimeFinder', () => slimeFinder(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'slimeFinder'));
    window.Alpine.initTree(root);
}
