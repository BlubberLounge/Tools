/**
 * Minecraft 3D farm viewer (resources/views/minecraft/farm-viewer.blade.php).
 *
 * Loads a farm model (public/minecraft/models/{id}.json), shows it in 3D
 * (three.js, loaded on demand from ./viewer/renderer.js) and as 2D slices
 * with block counts and a material list. Textures come from the optional
 * atlas in public/minecraft/atlas/ (built locally, see README); without it
 * every block uses its flat colour from the block table.
 */
import blockTable from '../../minecraft/viewer/blocks.json';
import { loadModel } from './viewer/model.js';
import { resolveBlock } from './viewer/blocks.js';
import { atlasRect } from './viewer/mesher.js';
import { layerCount, layerCounts, materialList, sliceLayer } from './viewer/slice.js';

const SLICE_FACE = { y: 'up', x: 'east', z: 'south' };
const BEHIND_DEPTH = 6;

// heavy objects live outside Alpine's reactive proxy
let currentModel = null;
let resolvedPalette = [];
let viewer = null;
let viewerPromise = null;
let atlas = null; // {meta, image}

/** "minecraft:redstone_wall_torch" -> "Redstone Wall Torch" */
export function blockLabel(name) {
    const id = name.includes(':') ? name.split(':')[1] : name;
    return id.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

async function loadAtlas(url) {
    try {
        const response = await fetch(url, { cache: 'no-cache' });
        if (!response.ok)
            return null;
        const meta = await response.json();
        const image = new Image();
        image.src = new URL(meta.image ?? 'atlas.png', new URL(url, window.location.href)).href;
        await image.decode();
        return { meta, image };
    } catch {
        return null;
    }
}

function rgbCss(rgb, alpha = 1) {
    return `rgba(${Math.round(rgb[0] * 255)}, ${Math.round(rgb[1] * 255)}, ${Math.round(rgb[2] * 255)}, ${alpha})`;
}

function farmViewer(config) {
    return {
        models: config.models,
        text: config.text,
        modelId: config.initial,
        info: null,
        loading: false,
        progress: 0,
        error: null,

        view: '3d',            // '3d' | 'slice'
        camMode: 'orbit',      // 'orbit' | 'fly'
        speed: 8,
        pointerLocked: false,
        canFly: true,
        helpOpen: false,
        atlasLoaded: false,
        atlasVersion: null,
        stats: null,

        axis: 'y',
        layer: 0,
        layerMax: 0,
        counts: [],
        materials: [],
        hover: null,

        async init() {
            this.canFly = !(window.matchMedia('(pointer: coarse)').matches || window.innerWidth < 640);

            atlas = await loadAtlas(config.atlasUrl);
            this.atlasLoaded = atlas !== null;
            this.atlasVersion = atlas?.meta?.version ?? null;

            window.addEventListener('keydown', e => this.onKey(e));
            window.addEventListener('resize', () => this.drawSlice());

            if (this.modelId)
                await this.selectModel(this.modelId);
        },

        async selectModel(id) {
            const entry = this.models.find(m => m.id === id);
            if (!entry)
                return;
            this.modelId = id;
            this.error = null;
            this.loading = true;
            this.progress = 0;

            const url = new URL(window.location.href);
            url.searchParams.set('design', entry.design ?? entry.id);
            window.history.replaceState(null, '', url);

            try {
                const response = await fetch(entry.url);
                if (!response.ok)
                    throw new Error(response.statusText);
                currentModel = loadModel(await response.json(), blockTable.blocks);
            } catch (e) {
                currentModel = null;
                this.info = null;
                this.loading = false;
                this.error = this.text.loadError;
                return;
            }

            this.stats = null;
            resolvedPalette = currentModel.palette.map(p => resolveBlock(p, blockTable));
            this.info = {
                name: currentModel.name,
                size: currentModel.size,
                version: currentModel.version,
                source: currentModel.source,
            };
            this.materials = materialList(currentModel, blockTable);
            this.setAxis(this.axis, true);

            if (this.view === '3d')
                await this.showIn3d();
            else
                this.loading = false;
            this.$nextTick(() => this.drawSlice());
        },

        async ensureViewer() {
            if (viewer)
                return viewer;
            if (!viewerPromise)
                viewerPromise = import('./viewer/renderer.js').then(({ createViewer }) => {
                    viewer = createViewer(this.$refs.stage, {
                        table: blockTable,
                        atlas,
                        background: config.background,
                        onSpeed: s => { this.speed = s; },
                        onModeChange: m => { this.camMode = m; },
                        onPointerLock: locked => { this.pointerLocked = locked; },
                    });
                    return viewer;
                });
            return viewerPromise;
        },

        async showIn3d() {
            if (!currentModel)
                return;
            this.loading = true;
            try {
                const v = await this.ensureViewer();
                this.stats = await v.setModel(currentModel, (done, total) => {
                    this.progress = Math.round(done / total * 100);
                });
            } catch (e) {
                this.error = this.text.webglError;
            }
            this.loading = false;
        },

        async setView(view) {
            if (view === this.view)
                return;
            if (viewer && this.camMode === 'fly')
                viewer.setMode('orbit');
            this.view = view;
            if (view === '3d') {
                if (!viewer || this.stats === null)
                    await this.showIn3d();
            } else {
                this.$nextTick(() => this.drawSlice());
            }
        },

        setCamMode(mode) {
            if (!viewer || this.view !== '3d')
                return;
            if (mode === 'fly' && !this.canFly)
                return;
            viewer.setMode(mode);
            this.camMode = mode;
        },

        topDown() {
            if (viewer && this.view === '3d')
                viewer.topDown();
        },

        resetView() {
            if (viewer && this.view === '3d')
                viewer.resetView();
        },

        onKey(e) {
            const t = e.target;
            if (t && (t.tagName === 'INPUT' || t.tagName === 'SELECT' || t.tagName === 'TEXTAREA'))
                return;
            if (e.ctrlKey || e.metaKey || e.altKey)
                return;

            if (e.key === 'Escape' && this.helpOpen) {
                this.helpOpen = false;
                return;
            }
            if (e.key === 'h' || e.key === 'H' || e.key === '?') {
                this.helpOpen = !this.helpOpen;
                return;
            }

            if (this.view === '3d') {
                if (e.code === 'KeyF' && this.canFly)
                    this.setCamMode(this.camMode === 'fly' ? 'orbit' : 'fly');
                else if (e.code === 'KeyO')
                    this.setCamMode('orbit');
                else if (e.code === 'KeyT')
                    this.topDown();
                else if (e.code === 'KeyR')
                    this.resetView();
                return;
            }

            // slice view
            if (e.key === 'ArrowUp' || e.key === 'PageUp' || e.key === 'ArrowRight') {
                this.setLayer(this.layer + 1);
                e.preventDefault();
            } else if (e.key === 'ArrowDown' || e.key === 'PageDown' || e.key === 'ArrowLeft') {
                this.setLayer(this.layer - 1);
                e.preventDefault();
            } else if (e.key === 'Home') {
                this.setLayer(0);
                e.preventDefault();
            } else if (e.key === 'End') {
                this.setLayer(this.layerMax);
                e.preventDefault();
            } else if (e.key === 'x' || e.key === 'X') {
                this.setAxis('x');
            } else if (e.key === 'y' || e.key === 'Y') {
                this.setAxis('y');
            } else if (e.key === 'z' || e.key === 'Z') {
                this.setAxis('z');
            }
        },

        setAxis(axis, keepLayer = false) {
            if (!currentModel)
                return;
            this.axis = axis;
            this.layerMax = layerCount(currentModel.size, axis) - 1;
            if (!keepLayer || this.layer > this.layerMax)
                this.layer = 0;
            this.setLayer(this.layer);
        },

        setLayer(layer) {
            if (!currentModel)
                return;
            this.layer = Math.max(0, Math.min(this.layerMax, Number(layer)));
            this.counts = layerCounts(currentModel, this.axis, this.layer);
            this.drawSlice();
        },

        layerBlocks() {
            return this.counts.reduce((sum, c) => sum + c.count, 0);
        },

        label(name) {
            return blockLabel(name);
        },

        drawSlice() {
            const canvas = this.$refs.slice;
            if (!canvas || !currentModel || this.view !== 'slice')
                return;

            const slice = sliceLayer(currentModel, this.axis, this.layer, BEHIND_DEPTH);
            const wrap = canvas.parentElement;
            const available = Math.max(160, wrap.clientWidth - 2);
            const cell = Math.max(4, Math.min(32, Math.floor(available / slice.width)));
            const dpr = Math.min(window.devicePixelRatio || 1, 2);

            canvas.width = slice.width * cell * dpr;
            canvas.height = slice.height * cell * dpr;
            canvas.style.width = `${slice.width * cell}px`;
            canvas.style.height = `${slice.height * cell}px`;
            canvas.dataset.cell = String(cell);

            const g = canvas.getContext('2d');
            g.setTransform(dpr, 0, 0, dpr, 0, 0);
            g.imageSmoothingEnabled = false;
            g.clearRect(0, 0, slice.width * cell, slice.height * cell);

            const face = SLICE_FACE[this.axis];
            const drawBlock = (idx, x, y, alpha) => {
                const block = resolvedPalette[idx];
                if (!block || block.invisible)
                    return;
                const rect = atlas && !block.tint ? atlasRect(atlas.meta, block.faces[face]) : null;
                g.globalAlpha = alpha;
                if (rect) {
                    const tile = atlas.meta.tile;
                    const sx = Math.round(rect[0] * atlas.meta.columns) * tile;
                    const sy = Math.round((1 - rect[3]) * atlas.meta.rows) * tile;
                    g.drawImage(atlas.image, sx, sy, tile, tile, x, y, cell, cell);
                } else {
                    g.fillStyle = rgbCss(block.color);
                    g.fillRect(x, y, cell, cell);
                }
                g.globalAlpha = 1;
            };

            for (let v = 0; v < slice.height; v++)
                for (let u = 0; u < slice.width; u++) {
                    const i = v * slice.width + u;
                    const x = u * cell, y = v * cell;
                    if (slice.cells[i] !== 0) {
                        drawBlock(slice.cells[i], x, y, 1);
                    } else if (slice.behind[i] !== 0) {
                        drawBlock(slice.behind[i], x, y, 0.4 * (1 - (slice.depth[i] - 1) / BEHIND_DEPTH));
                    }
                }

            if (cell >= 8) {
                g.strokeStyle = 'rgba(127, 127, 127, 0.25)';
                g.lineWidth = 1;
                g.beginPath();
                for (let u = 0; u <= slice.width; u++) {
                    g.moveTo(u * cell + 0.5, 0);
                    g.lineTo(u * cell + 0.5, slice.height * cell);
                }
                for (let v = 0; v <= slice.height; v++) {
                    g.moveTo(0, v * cell + 0.5);
                    g.lineTo(slice.width * cell, v * cell + 0.5);
                }
                g.stroke();
            }
        },

        sliceHover(event) {
            if (!currentModel)
                return;
            const canvas = this.$refs.slice;
            const cell = Number(canvas.dataset.cell || 0);
            if (!cell)
                return;
            const r = canvas.getBoundingClientRect();
            const u = Math.floor((event.clientX - r.left) / cell);
            const v = Math.floor((event.clientY - r.top) / cell);
            const slice = sliceLayer(currentModel, this.axis, this.layer, 0);
            if (u < 0 || v < 0 || u >= slice.width || v >= slice.height) {
                this.hover = null;
                return;
            }
            const idx = slice.cells[v * slice.width + u];
            if (idx === 0) {
                this.hover = null;
                return;
            }
            const entry = currentModel.palette[idx];
            const props = Object.entries(entry.properties).map(([k, val]) => `${k}=${val}`).join(', ');
            this.hover = blockLabel(entry.name) + (props ? ` (${props})` : '');
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('farm-viewer');
const configElement = document.getElementById('farm-viewer-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('farmViewer', () => farmViewer(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'farmViewer'));
    window.Alpine.initTree(root);
}
