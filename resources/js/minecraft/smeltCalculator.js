/**
 * Minecraft smelting and fuel calculator (resources/views/minecraft/smelt-calculator.blade.php).
 *
 * Time, fuel and XP to cook a number of items in a furnace array, and how
 * much of every fuel that takes. The maths lives in smeltMath.js; the fuels
 * come from the recipe data file, the XP per item from xpMath.js.
 */
import { MACHINES, smelt, hoppersNeeded } from './smeltMath';
import { SMELTING_SOURCES } from './xpMath';
import { unitSize } from './stackMath';

const INPUT_UNITS = ['item', 'stack', 'shulker', 'doubleChest'];

function smeltCalculator(config) {
    return {
        text: config.text,
        fuels: config.fuels,
        machines: MACHINES.map(m => m.id),
        inputUnits: INPUT_UNITS,
        xpItems: SMELTING_SOURCES,

        amount: 1,
        unit: 'shulker',
        machine: 'furnace',
        furnaces: 9,
        fuel: config.fuels[0]?.item ?? null,
        xpItem: 'raw_iron',

        get items() {
            const amount = Number(this.amount);
            return Number.isFinite(amount) && amount > 0 ? Math.ceil(amount * unitSize(this.unit, 64) - 1e-9) : 0;
        },

        smelts(fuelId) {
            return this.fuels.find(f => f.item === fuelId)?.smelts ?? 1;
        },

        result(fuelId) {
            return smelt(this.items, this.furnaces, this.machine, this.smelts(fuelId));
        },

        get current() {
            return this.result(this.fuel);
        },

        get hoppers() {
            return hoppersNeeded(this.current.perHour);
        },

        get xp() {
            const source = this.xpItems.find(s => s.id === this.xpItem);
            return source ? this.items * source.xp : 0;
        },

        stacks(count) {
            const full = Math.floor(count / 64);
            const rest = count % 64;
            if (full === 0)
                return '';
            return rest === 0 ? `${full} × 64` : `${full} × 64 + ${rest}`;
        },

        duration(seconds) {
            const total = Math.round(seconds);
            const h = Math.floor(total / 3600);
            const m = Math.floor((total % 3600) / 60);
            const s = total % 60;
            const pad = n => String(n).padStart(2, '0');
            return h > 0 ? `${h}:${pad(m)}:${pad(s)}` : `${m}:${pad(s)}`;
        },

        format(value, digits = 2) {
            return Number.isFinite(value) ? value.toLocaleString(undefined, { maximumFractionDigits: digits }) : '-';
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('smelt-calculator');
const configElement = document.getElementById('smelt-calculator-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('smeltCalculator', () => smeltCalculator(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'smeltCalculator'));
    window.Alpine.initTree(root);
}
