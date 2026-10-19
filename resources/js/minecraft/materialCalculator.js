/**
 * Minecraft material calculator (resources/views/minecraft/material-calculator.blade.php).
 *
 * A list of boxes (solid, hollow, walls, walls with floor, frame) is added up
 * to a block count and split into stacks, chests and shulker boxes. The maths
 * lives in materialMath.js and stackMath.js.
 */
import { SHAPES, shapeBlocks } from './materialMath';
import { STACK_SIZES, BREAKDOWNS, breakdown, slotsNeeded, unitSize } from './stackMath';

const emptyPart = () => ({ shape: 'hollow', length: 9, width: 9, height: 5, thickness: 1 });

function materialCalculator(config) {
    return {
        text: config.text,
        shapes: SHAPES,
        stackSizes: STACK_SIZES,
        stackSize: 64,
        parts: [emptyPart()],

        result(part) {
            return shapeBlocks(part.shape, part, part.thickness);
        },

        get total() {
            return this.parts.reduce((sum, part) => sum + this.result(part).blocks, 0);
        },

        get slots() {
            return slotsNeeded(this.total, this.stackSize);
        },

        get shulkers() {
            return this.total / unitSize('shulker', this.stackSize);
        },

        get breakdowns() {
            return Object.entries(BREAKDOWNS).map(([id, unitIds]) => ({
                id,
                parts: breakdown(this.total, this.stackSize, unitIds),
            }));
        },

        addPart() {
            this.parts.push(emptyPart());
        },

        removePart(index) {
            this.parts.splice(index, 1);
            if (this.parts.length === 0)
                this.addPart();
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
const root = document.getElementById('material-calculator');
const configElement = document.getElementById('material-calculator-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('materialCalculator', () => materialCalculator(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'materialCalculator'));
    window.Alpine.initTree(root);
}
