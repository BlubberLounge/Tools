/**
 * Minecraft stack calculator (resources/views/minecraft/stack-calculator.blade.php).
 *
 * Mixed input rows (items, stacks, chests, shulker boxes, ...) are added up to
 * a total and shown in every unit, plus a greedy breakdown into shulker boxes
 * and into chests. The maths lives in stackMath.js.
 */
import {
    STACK_SIZES, UNITS, BREAKDOWNS,
    unitSize, totalItems, convertAll, breakdown, slotsNeeded,
} from './stackMath';

const emptyRow = (unit = 'item') => ({ amount: '', unit });

function stackCalculator(config) {
    return {
        text: config.text,
        stackSizes: STACK_SIZES,
        units: UNITS.map(u => u.id),
        stackSize: 64,
        rows: [{ amount: '', unit: 'shulker' }, { amount: '', unit: 'stack' }, emptyRow()],

        get total() {
            return totalItems(this.rows, this.stackSize);
        },

        get slots() {
            return slotsNeeded(this.total, this.stackSize);
        },

        get conversions() {
            return convertAll(this.total, this.stackSize);
        },

        get breakdowns() {
            return Object.entries(BREAKDOWNS).map(([id, unitIds]) => ({
                id,
                parts: breakdown(this.total, this.stackSize, unitIds),
            }));
        },

        unitName(unitId) {
            return this.text.units[unitId] ?? unitId;
        },

        itemsPer(unitId) {
            return unitSize(unitId, this.stackSize);
        },

        addRow() {
            this.rows.push(emptyRow());
        },

        removeRow(index) {
            this.rows.splice(index, 1);
            if (this.rows.length === 0)
                this.addRow();
        },

        clear() {
            this.rows = [emptyRow()];
        },

        /** Replace the input with the total expressed in one unit. */
        useUnit(unitId) {
            const value = this.conversions.find(c => c.unit === unitId).value;
            this.rows = [{ amount: String(Number(value.toFixed(4))), unit: unitId }];
        },

        format(value, digits = 2) {
            return Number.isFinite(value) ? value.toLocaleString(undefined, { maximumFractionDigits: digits }) : '-';
        },

        breakdownText(parts) {
            return parts.length === 0
                ? '0'
                : parts.map(p => `${this.format(p.count, 0)} × ${this.unitName(p.unit)}`).join(' + ');
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('stack-calculator');
const configElement = document.getElementById('stack-calculator-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('stackCalculator', () => stackCalculator(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'stackCalculator'));
    window.Alpine.initTree(root);
}
