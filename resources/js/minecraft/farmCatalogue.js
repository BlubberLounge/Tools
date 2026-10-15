/**
 * Minecraft farm module catalogue (resources/views/minecraft/farm-catalogue.blade.php).
 *
 * Filtering and sorting run on the server (App\Minecraft\FarmCatalog via
 * /api/v1/minecraft/farms/{version}); this component only holds the filter
 * state, keeps it in sessionStorage and renders the list and the detail view.
 */

const STORAGE_KEY = 'minecraft.farmCatalogue.filters';
const FILTER_KEYS = ['version', 'item', 'type', 'difficulty', 'maxFootprint', 'sort', 'direction'];
const DEBOUNCE_MS = 300;

function readStoredFilters() {
    try {
        const stored = JSON.parse(window.sessionStorage.getItem(STORAGE_KEY) || '{}');
        return stored && typeof stored === 'object' ? stored : {};
    } catch (e) {
        return {};
    }
}

function farmCatalogue(config) {
    const stored = readStoredFilters();
    const versionIds = config.versions.map(v => v.id);

    return {
        versions: config.versions,
        text: config.text,

        // filter state (kept per browser session)
        version: versionIds.includes(stored.version) ? stored.version : (versionIds[0] || null),
        item: typeof stored.item === 'string' ? stored.item : '',
        type: typeof stored.type === 'string' ? stored.type : '',
        difficulty: typeof stored.difficulty === 'string' ? stored.difficulty : '',
        maxFootprint: Number.isInteger(stored.maxFootprint) ? stored.maxFootprint : '',
        sort: ['rate', 'footprint', 'name'].includes(stored.sort) ? stored.sort : 'rate',
        direction: ['asc', 'desc'].includes(stored.direction) ? stored.direction : '',

        meta: null,
        designs: [],
        total: 0,
        loading: false,
        error: null,

        detail: null,
        comparison: [],
        detailLoading: false,

        requestId: 0,
        timer: null,

        init() {
            for (const key of FILTER_KEYS) {
                this.$watch(key, () => {
                    this.store();
                    if (key === 'version') {
                        // farm types differ per version
                        this.closeDetail();
                        this.load();
                    } else {
                        this.loadDebounced(key === 'item' || key === 'maxFootprint');
                    }
                });
            }
            if (this.version)
                this.load();
        },

        store() {
            const state = {};
            for (const key of FILTER_KEYS)
                state[key] = key === 'maxFootprint' ? (parseInt(this.maxFootprint, 10) || '') : this[key];
            try {
                window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            } catch (e) {
                // storage blocked: the filters just are not remembered
            }
        },

        loadDebounced(typing) {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.load(), typing ? DEBOUNCE_MS : 0);
        },

        params() {
            const params = { sort: this.sort };
            if (this.direction)
                params.direction = this.direction;
            if (this.item.trim())
                params.item = this.item.trim();
            if (this.type)
                params.type = this.type;
            if (this.difficulty)
                params.difficulty = this.difficulty;
            const max = parseInt(this.maxFootprint, 10);
            if (max > 0)
                params.max_footprint = max;
            return params;
        },

        async load() {
            const requestId = ++this.requestId;
            this.loading = true;
            this.error = null;
            try {
                const url = config.endpoints.farms.replace('__VERSION__', encodeURIComponent(this.version));
                const response = await window.axios.get(url, { params: this.params() });
                if (requestId !== this.requestId)
                    return;
                const data = response.data.data;
                this.meta = data;
                this.designs = data.designs;
                this.total = data.total;
                // a farm type of the old version may not exist here
                if (this.type && !data.farm_types.some(t => t.id === this.type))
                    this.type = '';
            } catch (e) {
                if (requestId !== this.requestId)
                    return;
                this.designs = [];
                this.error = (e.response && e.response.status === 422) ? this.text.invalidFilter : this.text.loadError;
            } finally {
                if (requestId === this.requestId)
                    this.loading = false;
            }
        },

        resetFilters() {
            this.item = '';
            this.type = '';
            this.difficulty = '';
            this.maxFootprint = '';
            this.sort = 'rate';
            this.direction = '';
        },

        toggleDirection() {
            const natural = this.sort === 'rate' ? 'desc' : 'asc';
            const current = this.direction || natural;
            this.direction = current === 'asc' ? 'desc' : 'asc';
        },

        get currentDirection() {
            return this.direction || (this.sort === 'rate' ? 'desc' : 'asc');
        },

        get hasFilters() {
            return !!(this.item.trim() || this.type || this.difficulty || parseInt(this.maxFootprint, 10) > 0);
        },

        async openDesign(id) {
            this.detailLoading = true;
            this.error = null;
            try {
                const url = config.endpoints.design
                    .replace('__VERSION__', encodeURIComponent(this.version))
                    .replace('__DESIGN__', encodeURIComponent(id));
                const response = await window.axios.get(url);
                this.detail = response.data.data.design;
                this.comparison = response.data.data.comparison;
                this.$nextTick(() => {
                    const box = this.$refs.detailBox;
                    if (box)
                        box.scrollTop = 0;
                });
            } catch (e) {
                this.error = this.text.loadError;
            } finally {
                this.detailLoading = false;
            }
        },

        closeDetail() {
            this.detail = null;
            this.comparison = [];
        },

        // ---- display helpers ----

        rateLabel(perHour) {
            return perHour === null || perHour === undefined
                ? this.text.unknown
                : `${Number(perHour).toLocaleString(undefined, { maximumFractionDigits: 1 })}${this.text.perHour}`;
        },

        basisLabel(basis) {
            return this.text.basis[basis] || basis;
        },

        basisClass(basis) {
            return { designer: 'badge-ghost', measured: 'badge-success', unknown: 'badge-warning' }[basis] || 'badge-ghost';
        },

        footprintLabel(footprint) {
            return footprint ? `${footprint.x} × ${footprint.y} × ${footprint.z}` : this.text.unknown;
        },

        difficultyLabel(difficulty) {
            return this.text.difficulty[difficulty] || difficulty;
        },

        difficultyClass(difficulty) {
            return { easy: 'badge-success', medium: 'badge-warning', hard: 'badge-error' }[difficulty] || 'badge-ghost';
        },

        versionRange(range) {
            return range.max ? `${range.min} – ${range.max}` : `${range.min}+`;
        },

        countLabel(count) {
            return count === null ? '?' : Number(count).toLocaleString();
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('farm-catalogue');
const configElement = document.getElementById('farm-catalogue-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('farmCatalogue', () => farmCatalogue(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'farmCatalogue'));
    window.Alpine.initTree(root);
}
