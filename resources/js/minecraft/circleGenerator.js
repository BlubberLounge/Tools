/**
 * Minecraft circle and sphere generator (resources/views/minecraft/circle-generator.blade.php).
 *
 * Draws one layer of a circle, ellipse, sphere or dome on a canvas, with the
 * layer below faded in, block counts per layer and in total, and the block
 * runs of the row under the mouse. The maths lives in circleMath.js.
 */
import { SHAPES, MAX_SIZE, generate, runs } from './circleMath';
import { BREAKDOWNS, breakdown } from './stackMath';

const MAX_CANVAS_HEIGHT = 720;

const cssColor = (name, fallback) => {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return value || fallback;
};

function circleGenerator(config) {
    // the grids stay outside Alpine's reactive proxy: they can hold millions of cells
    let current = null;
    let cell = 1;

    return {
        text: config.text,
        shapes: SHAPES,
        maxSize: MAX_SIZE,

        shape: 'circle',
        diameter: 21,
        width: 31,
        depth: 15,
        hollow: true,
        layer: 0,

        // copies of the result for the template
        size: [1, 1, 1],
        counts: [0],
        total: 0,
        hover: null,

        init() {
            this.$watch('shape', () => { this.layer = 0; this.update(); });
            for (const key of ['diameter', 'width', 'depth', 'hollow'])
                this.$watch(key, () => this.update());
            this.$watch('layer', () => this.draw());
            window.addEventListener('resize', () => this.draw());
            this.update();
        },

        get flat() {
            return this.shape === 'circle' || this.shape === 'ellipse';
        },

        get layerCount() {
            return this.counts[this.layer] ?? 0;
        },

        get breakdowns() {
            return Object.entries(BREAKDOWNS).map(([id, unitIds]) => ({ id, parts: breakdown(this.total, 64, unitIds) }));
        },

        update() {
            current = generate(this.shape, { diameter: this.diameter, width: this.width, depth: this.depth }, this.hollow);
            this.size = current.size;
            this.counts = current.counts;
            this.total = current.total;
            this.layer = Math.min(Number(this.layer), current.layers.length - 1);
            this.$nextTick(() => this.draw());
        },

        draw() {
            const canvas = this.$refs.canvas;
            if (!canvas || !current)
                return;
            const [sx, , sz] = current.size;
            const available = canvas.parentElement.clientWidth || 600;
            cell = Math.max(2, Math.min(32, Math.floor(available / sx), Math.floor(MAX_CANVAS_HEIGHT / sz)));

            const ratio = window.devicePixelRatio || 1;
            canvas.width = sx * cell * ratio;
            canvas.height = sz * cell * ratio;
            canvas.style.width = `${sx * cell}px`;
            canvas.style.height = `${sz * cell}px`;

            const ctx = canvas.getContext('2d');
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.fillStyle = cssColor('--color-base-200', '#e5e7eb');
            ctx.fillRect(0, 0, sx * cell, sz * cell);

            const paint = (layer, color) => {
                ctx.fillStyle = color;
                for (let z = 0; z < sz; z++)
                    for (let x = 0; x < sx; x++)
                        if (layer[z * sx + x])
                            ctx.fillRect(x * cell, z * cell, cell, cell);
            };

            const index = Number(this.layer);
            if (index > 0) {
                ctx.globalAlpha = 0.25;
                paint(current.layers[index - 1], cssColor('--color-base-content', '#374151'));
                ctx.globalAlpha = 1;
            }
            paint(current.layers[index], cssColor('--color-primary', '#16a34a'));

            if (cell >= 6) {
                ctx.strokeStyle = cssColor('--color-base-300', '#d1d5db');
                ctx.lineWidth = 1;
                ctx.beginPath();
                for (let x = 0; x <= sx; x++) {
                    ctx.moveTo(x * cell + 0.5, 0);
                    ctx.lineTo(x * cell + 0.5, sz * cell);
                }
                for (let z = 0; z <= sz; z++) {
                    ctx.moveTo(0, z * cell + 0.5);
                    ctx.lineTo(sx * cell, z * cell + 0.5);
                }
                ctx.stroke();
            }

            // centre lines
            ctx.strokeStyle = cssColor('--color-error', '#dc2626');
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(sx * cell / 2, 0);
            ctx.lineTo(sx * cell / 2, sz * cell);
            ctx.moveTo(0, sz * cell / 2);
            ctx.lineTo(sx * cell, sz * cell / 2);
            ctx.stroke();
        },

        onMove(event) {
            if (!current)
                return;
            const rect = event.target.getBoundingClientRect();
            const x = Math.floor((event.clientX - rect.left) / cell);
            const z = Math.floor((event.clientY - rect.top) / cell);
            const [sx, , sz] = current.size;
            if (x < 0 || z < 0 || x >= sx || z >= sz) {
                this.hover = null;
                return;
            }
            this.hover = {
                x: x + 1,
                z: z + 1,
                runs: runs(current.layers[Number(this.layer)], sx, z),
            };
        },

        format(value, digits = 2) {
            return Number.isFinite(value) ? value.toLocaleString(undefined, { maximumFractionDigits: digits }) : '-';
        },

        breakdownText(parts) {
            return parts.length === 0
                ? '0'
                : parts.map(p => `${this.format(p.count, 0)} × ${this.text.units[p.unit] ?? p.unit}`).join(' + ');
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('circle-generator');
const configElement = document.getElementById('circle-generator-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('circleGenerator', () => circleGenerator(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'circleGenerator'));
    window.Alpine.initTree(root);
}
