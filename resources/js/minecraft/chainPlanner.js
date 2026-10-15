/**
 * Minecraft farm chain planner (resources/views/minecraft/chain-planner.blade.php).
 *
 * The calculation runs on the server (App\Minecraft\ChainPlanner via
 * /api/v1/minecraft/chains/plan); this component holds the inputs and the
 * overrides, keeps them in sessionStorage and renders the raw-resource list,
 * the indented tree and the flow graph (desktop only).
 */

import { renderGraph } from './chainGraph';

const STORAGE_KEY = 'minecraft.chainPlanner.state';
const DEBOUNCE_MS = 250;
const VIEWS = ['list', 'tree', 'graph'];
const DESKTOP_QUERY = '(min-width: 768px)';

function readStored() {
    try {
        const stored = JSON.parse(window.sessionStorage.getItem(STORAGE_KEY) || '{}');
        return stored && typeof stored === 'object' ? stored : {};
    } catch (e) {
        return {};
    }
}

const isMap = value => value && typeof value === 'object' && !Array.isArray(value);

function chainPlanner(config) {
    const stored = readStored();
    const versionIds = config.versions.map(v => v.id);

    return {
        versions: config.versions,
        text: config.text,

        // inputs (kept per browser session)
        version: versionIds.includes(stored.version) ? stored.version : (versionIds[0] || null),
        item: typeof stored.item === 'string' ? stored.item : 'book',
        rate: Number(stored.rate) > 0 ? Number(stored.rate) : 1000,
        mode: stored.mode === 'auto' ? 'auto' : 'manual',
        fuel: typeof stored.fuel === 'string' ? stored.fuel : '',
        view: VIEWS.includes(stored.view) ? stored.view : 'list',

        // overrides (marked in the UI, one-click reset)
        choices: isMap(stored.choices) ? stored.choices : {},
        designs: isMap(stored.designs) ? stored.designs : {},
        moduleRates: isMap(stored.moduleRates) ? stored.moduleRates : {},
        furnaceRates: isMap(stored.furnaceRates) ? stored.furnaceRates : {},
        restocksPerDay: Number(stored.restocksPerDay) > 0 ? Number(stored.restocksPerDay) : null,

        meta: null,
        plan: null,
        loading: false,
        error: null,
        loop: [],

        itemQuery: '',
        itemPickerOpen: false,
        desktop: window.matchMedia ? window.matchMedia(DESKTOP_QUERY).matches : true,
        graphSvg: '',

        requestId: 0,
        timer: null,

        init() {
            if (window.matchMedia) {
                const query = window.matchMedia(DESKTOP_QUERY);
                const update = () => {
                    this.desktop = query.matches;
                    this.renderGraph();
                };
                if (query.addEventListener)
                    query.addEventListener('change', update);
            }

            for (const key of ['item', 'rate', 'mode', 'fuel', 'choices', 'designs', 'moduleRates', 'furnaceRates', 'restocksPerDay'])
                this.$watch(key, () => this.changed(key === 'rate'));
            this.$watch('view', () => {
                this.store();
                this.renderGraph();
            });
            this.$watch('version', () => {
                // recipe ids, designs and items differ per version
                this.meta = null;
                this.plan = null;
                this.graphSvg = '';
                this.resetOverrides(false);
                this.loadVersion();
            });

            if (this.version)
                this.loadVersion();
        },

        store() {
            const state = {
                version: this.version,
                item: this.item,
                rate: this.rate,
                mode: this.mode,
                fuel: this.fuel,
                view: this.view,
                choices: this.choices,
                designs: this.designs,
                moduleRates: this.moduleRates,
                furnaceRates: this.furnaceRates,
                restocksPerDay: this.restocksPerDay,
            };
            try {
                window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            } catch (e) {
                // storage blocked: the inputs just are not remembered
            }
        },

        changed(typing) {
            this.store();
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.calculate(), typing ? DEBOUNCE_MS : 0);
        },

        async loadVersion() {
            this.error = null;
            try {
                const url = config.endpoints.version.replace('__VERSION__', encodeURIComponent(this.version));
                const response = await window.axios.get(url);
                this.meta = response.data.data;
                if (!this.meta.items.some(i => i.id === this.item))
                    this.item = this.meta.items.some(i => i.id === 'book') ? 'book' : this.meta.items[0].id;
                if (!this.meta.fuels.some(f => f.item === this.fuel))
                    this.fuel = this.meta.defaults.fuel;
                this.store();
                this.calculate();
            } catch (e) {
                this.meta = null;
                this.error = this.text.loadError;
            }
        },

        payload() {
            const payload = {
                version: this.version,
                item: this.item,
                rate: Number(this.rate),
                mode: this.mode,
                fuel: this.fuel || undefined,
            };
            if (this.mode === 'manual' && Object.keys(this.choices).length)
                payload.choices = this.choices;
            if (Object.keys(this.designs).length)
                payload.designs = this.designs;
            const rates = this.positiveEntries(this.moduleRates);
            if (Object.keys(rates).length)
                payload.module_rates = rates;
            const furnaces = this.positiveEntries(this.furnaceRates);
            if (Object.keys(furnaces).length)
                payload.furnace_rates = furnaces;
            if (Number(this.restocksPerDay) > 0)
                payload.restocks_per_day = Number(this.restocksPerDay);
            return payload;
        },

        positiveEntries(map) {
            const result = {};
            for (const [key, value] of Object.entries(map))
                if (Number(value) > 0)
                    result[key] = Number(value);
            return result;
        },

        async calculate() {
            if (!this.meta || !this.item || !(Number(this.rate) > 0))
                return;
            const requestId = ++this.requestId;
            this.loading = true;
            this.error = null;
            this.loop = [];
            try {
                const response = await window.axios.post(config.endpoints.plan, this.payload());
                if (requestId !== this.requestId)
                    return;
                this.plan = response.data.data;
                this.renderGraph();
            } catch (e) {
                if (requestId !== this.requestId)
                    return;
                const data = e.response && e.response.data;
                this.plan = null;
                this.graphSvg = '';
                this.error = (data && data.message) ? data.message : this.text.planError;
                this.loop = (data && data.data && Array.isArray(data.data.loop)) ? data.data.loop : [];
            } finally {
                if (requestId === this.requestId)
                    this.loading = false;
            }
        },

        renderGraph() {
            this.graphSvg = this.plan && this.desktop && this.view === 'graph' ? renderGraph(this.plan, this.text) : '';
        },

        // ---- item picker ----

        get itemName() {
            const found = this.meta && this.meta.items.find(i => i.id === this.item);
            return found ? found.name : this.item;
        },

        get filteredItems() {
            if (!this.meta)
                return [];
            const query = this.itemQuery.trim().toLowerCase();
            return this.meta.items
                .filter(i => !query || i.name.toLowerCase().includes(query) || i.id.includes(query.replace(/ /g, '_')))
                .sort((a, b) => (b.craftable - a.craftable) || a.name.localeCompare(b.name))
                .slice(0, 60);
        },

        pickItem(id) {
            this.item = id;
            this.itemQuery = '';
            this.itemPickerOpen = false;
        },

        // ---- overrides ----

        setChoice(item, choice) {
            const node = this.plan && this.plan.nodes.find(n => n.item === item);
            const next = { ...this.choices };
            if (node && choice === node.default_choice)
                delete next[item];
            else
                next[item] = choice;
            this.choices = next;
        },

        setDesign(item, designId) {
            const next = { ...this.designs };
            next[item] = designId;
            this.designs = next;
            // a module rate override belongs to the design it was entered for
            if (this.moduleRates[item] !== undefined) {
                const rates = { ...this.moduleRates };
                delete rates[item];
                this.moduleRates = rates;
            }
        },

        setModuleRate(item, value) {
            const rates = { ...this.moduleRates };
            if (value === '' || !(Number(value) > 0))
                delete rates[item];
            else
                rates[item] = Number(value);
            this.moduleRates = rates;
        },

        setFurnaceRate(type, value) {
            const rates = { ...this.furnaceRates };
            if (value === '' || !(Number(value) > 0))
                delete rates[type];
            else
                rates[type] = Number(value);
            this.furnaceRates = rates;
        },

        get overrideCount() {
            return Object.keys(this.choices).length
                + Object.keys(this.designs).length
                + Object.keys(this.moduleRates).length
                + Object.keys(this.furnaceRates).length
                + (this.restocksPerDay ? 1 : 0)
                + (this.meta && this.fuel && this.fuel !== this.meta.defaults.fuel ? 1 : 0);
        },

        resetOverrides(includeFuel = true) {
            this.choices = {};
            this.designs = {};
            this.moduleRates = {};
            this.furnaceRates = {};
            this.restocksPerDay = null;
            if (includeFuel && this.meta)
                this.fuel = this.meta.defaults.fuel;
        },

        // ---- views ----

        get craftedNodes() {
            return this.plan ? this.plan.nodes.filter(n => n.kind === 'recipe') : [];
        },

        get treeRows() {
            const rows = [];
            const walk = (node, depth, key) => {
                rows.push({ ...node, depth, key });
                node.children.forEach((child, index) => walk(child, depth + 1, `${key}.${index}`));
            };
            if (this.plan)
                walk(this.plan.tree, 0, '0');
            return rows;
        },

        nodeFor(item) {
            return this.plan ? this.plan.nodes.find(n => n.item === item) : null;
        },

        edgesInto(item) {
            return this.plan ? this.plan.edges.filter(e => e.to === item) : [];
        },

        hopperLinesInto(item) {
            return this.edgesInto(item).reduce((sum, e) => sum + e.hopper_lines, 0);
        },

        isChoiceOverridden(item) {
            return this.mode === 'manual' && this.choices[item] !== undefined;
        },

        // ---- display helpers ----

        rateLabel(value) {
            return value === null || value === undefined
                ? this.text.unknown
                : `${this.number(value)}${this.text.perHour}`;
        },

        number(value) {
            return Number(value).toLocaleString(undefined, { maximumFractionDigits: value < 10 ? 2 : 0 });
        },

        machineLabel(machine) {
            if (!machine)
                return '';
            return `${machine.count} × ${this.text.machines[machine.type] || machine.type}`;
        },

        optionLabel(option) {
            const label = option.id === 'source'
                ? (option.label === 'farm' ? this.text.fromFarm : this.text.rawResource)
                : option.label;
            return option.raw_items === null ? label : `${label} · ${this.number(option.raw_items)} ${this.text.rawItems}`;
        },

        kindIcon(row) {
            if (row.loop)
                return 'fa-rotate';
            if (row.kind === 'source')
                return 'fa-seedling';
            return { smelting: 'fa-fire', blasting: 'fa-fire-flame-curved', smoking: 'fa-fire-burner', trading: 'fa-user' }[row.recipe_type] || 'fa-table-cells';
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('chain-planner');
const configElement = document.getElementById('chain-planner-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('chainPlanner', () => chainPlanner(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'chainPlanner'));
    window.Alpine.initTree(root);
}
