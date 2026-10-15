/**
 * Minecraft anvil enchantment planner (resources/views/minecraft/anvil-planner.blade.php).
 *
 * Loads the item/enchantment data of the chosen Java version from
 * /api/v1/minecraft/versions/{version}, lets the user pick an item and
 * enchantments and asks /api/v1/minecraft/anvil/plan for the cheapest order.
 */
import { pixelIcon, iconForItem, ANVIL_GLYPH, BOOK_ICON } from './pixelIcons';

const ROMAN = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

function anvilPlanner(config) {
    return {
        versions: config.versions,
        text: config.text,
        version: config.versions.length ? config.versions[0].id : null,
        data: null,
        error: null,
        loading: false,

        item: null,
        itemQuery: '',
        itemPickerOpen: true,
        modes: {},      // enchantment id => '' | 'want' | 'present'
        levels: {},     // enchantment id => chosen level
        priorWork: 0,

        result: null,
        stepIndex: 0,
        xpSource: 'zombie',

        anvilGlyph: pixelIcon(ANVIL_GLYPH),

        init() {
            if (this.version)
                this.loadVersion();
        },

        async loadVersion() {
            this.error = null;
            this.result = null;
            try {
                const url = config.endpoints.version.replace('__VERSION__', encodeURIComponent(this.version));
                const response = await window.axios.get(url);
                this.data = response.data.data;
            } catch (e) {
                this.data = null;
                this.error = this.text.loadError;
                return;
            }

            // keep the item and the selection where the new version still allows them
            if (this.item) {
                const item = this.data.items.find(i => i.id === this.item.id);
                if (item) {
                    this.selectItem(item, true);
                } else {
                    this.item = null;
                    this.itemPickerOpen = true;
                }
            }
        },

        get enchantmentMap() {
            const map = {};
            for (const enchantment of (this.data ? this.data.enchantments : []))
                map[enchantment.id] = enchantment;
            return map;
        },

        get itemGroups() {
            if (!this.data)
                return [];

            const query = this.itemQuery.trim().toLowerCase();
            const groups = [];
            const byCategory = {};
            for (const item of this.data.items) {
                if (query && !item.name.toLowerCase().includes(query) && !item.category.toLowerCase().includes(query))
                    continue;
                if (!byCategory[item.category]) {
                    byCategory[item.category] = { category: item.category, items: [] };
                    groups.push(byCategory[item.category]);
                }
                byCategory[item.category].items.push(item);
            }
            return groups;
        },

        get itemEnchantments() {
            if (!this.item)
                return [];
            const map = this.enchantmentMap;
            return this.item.enchantments
                .map(id => map[id])
                .filter(Boolean)
                .sort((a, b) => (a.curse - b.curse) || a.name.localeCompare(b.name));
        },

        get canPlan() {
            return !!this.item && Object.values(this.modes).some(mode => mode === 'want');
        },

        get currentStep() {
            return this.result && this.result.steps.length ? this.result.steps[this.stepIndex] : null;
        },

        get selectedMob() {
            if (!this.result)
                return null;
            return this.result.xp_sources.mobs.find(mob => mob.id === this.xpSource) || this.result.xp_sources.mobs[0];
        },

        selectItem(item, keepSelection = false) {
            const oldModes = keepSelection ? this.modes : {};
            const oldLevels = keepSelection ? this.levels : {};

            this.item = item;
            this.modes = {};
            this.levels = {};
            for (const id of item.enchantments) {
                const enchantment = this.enchantmentMap[id];
                if (!enchantment)
                    continue;
                this.modes[id] = oldModes[id] || '';
                this.levels[id] = Math.min(oldLevels[id] || enchantment.max_level, enchantment.max_level);
            }

            this.itemPickerOpen = false;
            this.result = null;
        },

        clearSelection() {
            for (const id of Object.keys(this.modes))
                this.modes[id] = '';
            this.result = null;
        },

        setMode(enchantment, mode) {
            this.modes[enchantment.id] = this.modes[enchantment.id] === mode ? '' : mode;
        },

        /**
         * Name of a chosen enchantment that excludes this one, or null.
         */
        blockedBy(enchantment) {
            if (this.modes[enchantment.id])
                return null;
            for (const other of enchantment.incompatible) {
                if (this.modes[other])
                    return this.enchantmentMap[other].name;
            }
            return null;
        },

        async plan() {
            const enchantments = {};
            const present = {};
            for (const [id, mode] of Object.entries(this.modes)) {
                if (mode === 'want')
                    enchantments[id] = this.levels[id];
                else if (mode === 'present')
                    present[id] = this.levels[id];
            }

            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.post(config.endpoints.plan, {
                    version: this.version,
                    item: this.item.id,
                    enchantments,
                    present,
                    prior_work: Number(this.priorWork) || 0,
                });
                this.result = response.data.data;
                this.stepIndex = this.result.failing_step ? this.result.failing_step - 1 : 0;
                if (!this.result.xp_sources.mobs.some(mob => mob.id === this.xpSource))
                    this.xpSource = this.result.xp_sources.mobs[0].id;
            } catch (e) {
                this.result = null;
                this.error = (e.response && e.response.data && e.response.data.message) || this.text.planError;
            } finally {
                this.loading = false;
            }
        },

        // ---- display helpers ----

        roman(level) {
            return ROMAN[level] || String(level);
        },

        enchantmentLabel(enchantment) {
            return enchantment.max_level > 1 ? `${enchantment.name} ${this.roman(enchantment.level)}` : enchantment.name;
        },

        formatNumber(value) {
            return Number(value).toLocaleString();
        },

        iconFor(item) {
            return pixelIcon(iconForItem(item));
        },

        slotIcon(slot) {
            if (!slot.is_item)
                return pixelIcon(BOOK_ICON);
            return this.item ? this.iconFor(this.item) : '';
        },

        slotName(slot) {
            if (slot.type === 'result' && slot.from_step)
                return `${slot.name} (${this.text.resultOfStep} ${slot.from_step})`;
            return slot.is_item ? slot.name : this.text.book;
        },

        slotShort(slot) {
            if (slot.type === 'result')
                return `#${slot.from_step}`;
            if (slot.is_item)
                return slot.name;
            return slot.enchantments.map(e => this.enchantmentLabel(e)).join(', ');
        },

        slotTitle(slot) {
            const lines = [this.slotName(slot), ...slot.enchantments.map(e => this.enchantmentLabel(e))];
            return lines.join('\n');
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('anvil-planner');
const configElement = document.getElementById('anvil-planner-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('anvilPlanner', () => anvilPlanner(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'anvilPlanner'));
    window.Alpine.initTree(root);
}
