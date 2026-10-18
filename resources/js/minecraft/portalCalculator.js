/**
 * Minecraft nether portal calculator (resources/views/minecraft/portal-calculator.blade.php).
 *
 * Coordinate converter, link check against the saved portals, frame materials
 * and a distance calculator. The maths lives in portalMath.js; the saved
 * portals come from /api/v1/minecraft/portals (per user).
 */
import {
    OVERWORLD, NETHER, SEARCH_RADIUS, FRAME_MIN, FRAME_MAX,
    convert, findLink, analyseLinks, partnerFor, frameMaterials, measure, otherDimension,
} from './portalMath';

const toInt = value => {
    const n = Number.parseInt(value, 10);
    return Number.isFinite(n) ? n : 0;
};

const point = p => ({ x: toInt(p.x), y: toInt(p.y), z: toInt(p.z) });

const emptyForm = () => ({ id: null, name: '', world: '', dimension: OVERWORLD, x: '', y: '', z: '' });

function portalCalculator(config) {
    return {
        text: config.text,
        OVERWORLD,
        NETHER,
        radius: SEARCH_RADIUS,
        frameMin: FRAME_MIN,
        frameMax: FRAME_MAX,

        // converter
        from: OVERWORLD,
        position: { x: '', y: '64', z: '' },

        // saved portals
        portals: [],
        world: '',
        form: emptyForm(),
        loading: false,
        saving: false,
        error: null,
        formErrors: {},

        // frame
        frame: { width: 2, height: 3 },

        // distance
        distanceDimension: OVERWORLD,
        a: { x: '', y: '64', z: '' },
        b: { x: '', y: '64', z: '' },

        init() {
            this.loadPortals();
        },

        dimensionName(dimension) {
            return dimension === NETHER ? this.text.nether : this.text.overworld;
        },

        // --- converter -------------------------------------------------------

        get converted() {
            return convert(point(this.position), this.from);
        },

        get partner() {
            return partnerFor(point(this.position), this.from);
        },

        /** What the game would pick for a portal at the entered position. */
        get previewLink() {
            const portal = { id: null, dimension: this.from, ...point(this.position) };
            return findLink(portal, this.worldPortals);
        },

        swap() {
            const c = this.converted;
            this.from = otherDimension(this.from);
            this.position = { x: String(c.x), y: String(c.y), z: String(c.z) };
        },

        // --- saved portals ---------------------------------------------------

        get worlds() {
            return [...new Set(this.portals.map(p => p.world || ''))].sort();
        },

        get worldPortals() {
            return this.portals.filter(p => (p.world || '') === this.world);
        },

        get links() {
            return analyseLinks(this.worldPortals);
        },

        linksOf(dimension) {
            return this.links.filter(l => l.portal.dimension === dimension);
        },

        get problemCount() {
            return this.links.filter(l => l.status !== 'linked').length;
        },

        async loadPortals() {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.get(config.endpoints.portals);
                this.portals = response.data.data;
                if (!this.worlds.includes(this.world))
                    this.world = this.worlds[0] ?? '';
            } catch (e) {
                this.error = this.text.loadError;
            } finally {
                this.loading = false;
            }
        },

        editPortal(portal) {
            this.formErrors = {};
            this.form = {
                id: portal.id,
                name: portal.name,
                world: portal.world || '',
                dimension: portal.dimension,
                x: String(portal.x),
                y: String(portal.y),
                z: String(portal.z),
            };
        },

        resetForm() {
            this.formErrors = {};
            this.form = { ...emptyForm(), world: this.world };
        },

        /** Prefill the form with the partner position from the converter. */
        usePartner() {
            const p = this.partner.partner;
            this.formErrors = {};
            this.form = {
                ...emptyForm(),
                world: this.world,
                dimension: otherDimension(this.from),
                x: String(p.x), y: String(p.y), z: String(p.z),
            };
        },

        useConverterPosition() {
            const p = point(this.position);
            this.formErrors = {};
            this.form = {
                ...emptyForm(),
                world: this.world,
                dimension: this.from,
                x: String(p.x), y: String(p.y), z: String(p.z),
            };
        },

        async savePortal() {
            this.saving = true;
            this.error = null;
            this.formErrors = {};
            const payload = {
                name: this.form.name,
                world: this.form.world || null,
                dimension: this.form.dimension,
                x: this.form.x, y: this.form.y, z: this.form.z,
            };
            try {
                if (this.form.id)
                    await window.axios.put(`${config.endpoints.portals}/${this.form.id}`, payload);
                else
                    await window.axios.post(config.endpoints.portals, payload);
                this.world = this.form.world || '';
                this.resetForm();
                await this.loadPortals();
            } catch (e) {
                if (e.response && e.response.status === 422)
                    this.formErrors = e.response.data.errors || {};
                else
                    this.error = this.text.saveError;
            } finally {
                this.saving = false;
            }
        },

        async deletePortal(portal) {
            if (!window.confirm(this.text.confirmDelete.replace(':name', portal.name)))
                return;
            this.error = null;
            try {
                await window.axios.delete(`${config.endpoints.portals}/${portal.id}`);
                if (this.form.id === portal.id)
                    this.resetForm();
                await this.loadPortals();
            } catch (e) {
                this.error = this.text.deleteError;
            }
        },

        statusLabel(status) {
            return this.text.status[status];
        },

        statusClass(status) {
            return { linked: 'badge-success', oneway: 'badge-warning', new: 'badge-error' }[status];
        },

        // --- frame -----------------------------------------------------------

        get materials() {
            return frameMaterials(toInt(this.frame.width), toInt(this.frame.height));
        },

        // --- distance --------------------------------------------------------

        get distance() {
            return measure(point(this.a), point(this.b), this.distanceDimension);
        },

        swapPoints() {
            [this.a, this.b] = [this.b, this.a];
        },

        // --- formatting ------------------------------------------------------

        coords(p) {
            return `${p.x} / ${p.y} / ${p.z}`;
        },

        round(value, digits = 1) {
            return Number.isFinite(value) ? value.toLocaleString(undefined, { maximumFractionDigits: digits }) : '-';
        },

        duration(seconds) {
            if (!Number.isFinite(seconds))
                return '-';
            const total = Math.round(seconds);
            const h = Math.floor(total / 3600);
            const m = Math.floor((total % 3600) / 60);
            const s = total % 60;
            const pad = n => String(n).padStart(2, '0');
            return h > 0 ? `${h}:${pad(m)}:${pad(s)}` : `${m}:${pad(s)}`;
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('portal-calculator');
const configElement = document.getElementById('portal-calculator-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('portalCalculator', () => portalCalculator(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'portalCalculator'));
    window.Alpine.initTree(root);
}
