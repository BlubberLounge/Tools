/**
 * Crafting maths, used by craftCalculator.js.
 *
 * Expands an item into its recipe tree down to raw items. Every item uses one
 * chosen way: a recipe id or SOURCE (gathered, not made). Demand is collected
 * per item first and rounded up to whole crafts once, so shared inputs (sticks
 * for several parts) are not rounded twice. Cooking recipes count as one
 * furnace operation per craft.
 */

export const SOURCE = 'source';

export const COOKING_TYPES = ['smelting', 'blasting', 'smoking'];

/** Recipe ids per output item. */
export function producersOf(recipes) {
    const producers = {};
    for (const recipe of recipes)
        (producers[recipe.output.item] ??= []).push(recipe.id);
    return producers;
}

/**
 * Items reachable from `target` in topological order (makers before their
 * inputs). Items whose chosen recipe would loop back into itself are forced
 * to SOURCE; `forced` lists them.
 */
function order(target, recipeFor) {
    const forced = new Set();

    for (;;) {
        const done = new Set();
        const onStack = new Set();
        const post = [];
        let loopItem = null;

        const visit = item => {
            if (loopItem !== null || done.has(item))
                return;
            onStack.add(item);
            const recipe = forced.has(item) ? null : recipeFor(item);
            for (const input of recipe?.inputs ?? []) {
                if (onStack.has(input.item)) {
                    loopItem = input.item;
                    return;
                }
                visit(input.item);
                if (loopItem !== null)
                    return;
            }
            onStack.delete(item);
            done.add(item);
            post.push(item);
        };

        visit(target);
        if (loopItem === null)
            return { items: post.reverse(), forced: [...forced] };
        forced.add(loopItem);
    }
}

/**
 * Plan `amount` of `target`. `choices` maps item => recipe id or SOURCE;
 * missing items take SOURCE. Returns
 *   steps: [{item, recipe, needed, crafts, made, leftover}] in crafting order (raw side first)
 *   raw: [{item, count}] largest first
 *   cookOps: furnace operations, forced: items forced to SOURCE because of a loop
 */
export function plan(recipes, choices, target, amount) {
    const byId = Object.fromEntries(recipes.map(r => [r.id, r]));
    const recipeFor = item => {
        const choice = choices[item] ?? SOURCE;
        return choice === SOURCE ? null : byId[choice] ?? null;
    };

    const n = Math.max(0, Math.ceil(Number(amount) || 0));
    const { items, forced } = order(target, recipeFor);
    const forcedSet = new Set(forced);

    const demand = { [target]: n };
    const steps = [];
    const raw = [];
    let cookOps = 0;

    for (const item of items) {
        const needed = demand[item] ?? 0;
        const recipe = forcedSet.has(item) ? null : recipeFor(item);
        if (recipe === null) {
            if (needed > 0)
                raw.push({ item, count: needed });
            continue;
        }

        const crafts = Math.ceil(needed / recipe.output.count);
        for (const input of recipe.inputs)
            demand[input.item] = (demand[input.item] ?? 0) + crafts * input.count;
        if (COOKING_TYPES.includes(recipe.type))
            cookOps += crafts;

        const made = crafts * recipe.output.count;
        steps.push({ item, recipe: recipe.id, type: recipe.type, needed, crafts, made, leftover: made - needed });
    }

    raw.sort((a, b) => b.count - a.count || a.item.localeCompare(b.item));

    return { steps: steps.reverse(), raw, cookOps, forced };
}

/** Raw items for `amount` of `target` with each of its ways, cheapest (fewest raw items) first. */
export function compareWays(recipes, choices, target, amount) {
    return (producersOf(recipes)[target] ?? [])
        .map(way => {
            const result = plan(recipes, { ...choices, [target]: way }, target, amount);
            return { way, raw: result.raw, total: result.raw.reduce((sum, r) => sum + r.count, 0) };
        })
        .sort((a, b) => a.total - b.total);
}
