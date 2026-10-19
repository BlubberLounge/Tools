/**
 * Minecraft crafting calculator (resources/views/minecraft/craft-calculator.blade.php).
 *
 * Expands an item into crafts and raw items, lets every step switch to
 * another recipe (or gather the item instead) and compares the ways to make
 * the target. Recipes and default ways come from the recipe data file (the
 * same as the farm chain planner); the maths lives in craftMath.js.
 */
import { SOURCE, COOKING_TYPES, plan, compareWays, producersOf } from './craftMath';

function craftCalculator(config) {
    const names = Object.fromEntries(config.items.map(i => [i.id, i.name]));
    const byId = Object.fromEntries(config.recipes.map(r => [r.id, r]));

    return {
        text: config.text,
        SOURCE,
        items: config.items.filter(i => i.craftable),
        fuels: config.fuels,
        producers: producersOf(config.recipes),

        target: config.items.some(i => i.id === 'hopper') ? 'hopper' : config.items.find(i => i.craftable)?.id,
        amount: 1,
        fuel: config.fuels[0]?.item ?? null,
        choices: { ...config.choices },

        get result() {
            return plan(config.recipes, this.choices, this.target, this.amount);
        },

        get ways() {
            return compareWays(config.recipes, this.choices, this.target, this.amount);
        },

        get fuelNeeded() {
            const fuel = this.fuels.find(f => f.item === this.fuel);
            return fuel ? Math.ceil(this.result.cookOps / fuel.smelts - 1e-9) : 0;
        },

        name(item) {
            return names[item] ?? item.replaceAll('_', ' ');
        },

        options(item) {
            return [...(this.producers[item] ?? []), SOURCE];
        },

        hasOptions(item) {
            return (this.producers[item] ?? []).length > 0;
        },

        choice(item) {
            return this.choices[item] ?? SOURCE;
        },

        choose(item, way) {
            this.choices = { ...this.choices, [item]: way };
        },

        reset() {
            this.choices = { ...config.choices };
        },

        recipeLabel(way) {
            if (way === SOURCE)
                return this.text.gather;
            const recipe = byId[way];
            const inputs = recipe.inputs.map(i => `${i.count} ${this.name(i.item)}`).join(' + ');
            const machine = COOKING_TYPES.includes(recipe.type) ? ` (${this.text.types[recipe.type] ?? recipe.type})` : '';
            return `${inputs} → ${recipe.output.count}${machine}`;
        },

        rawText(raw) {
            return raw.map(r => `${this.format(r.count, 0)} ${this.name(r.item)}`).join(', ');
        },

        format(value, digits = 2) {
            return Number.isFinite(value) ? value.toLocaleString(undefined, { maximumFractionDigits: digits }) : '-';
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('craft-calculator');
const configElement = document.getElementById('craft-calculator-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('craftCalculator', () => craftCalculator(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'craftCalculator'));
    window.Alpine.initTree(root);
}
