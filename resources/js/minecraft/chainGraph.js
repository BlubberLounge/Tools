/**
 * Flow graph of the farm chain planner: a small layered DAG layout rendered
 * as SVG, no graph library.
 *
 * Layers: the target sits in the right-most column, every input one column
 * further left than its left-most consumer (longest path), so raw resources
 * end up on the left. Edges that point back (fuel loops) are drawn dashed.
 * The order inside a column is improved with a few barycenter sweeps.
 */

const COLUMN_WIDTH = 230;
const NODE_WIDTH = 160;
const ROW_GAP = 22;
const PADDING = 24;

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatRate(value) {
    return Number(value).toLocaleString(undefined, { maximumFractionDigits: value < 10 ? 2 : 0 });
}

/**
 * Column per item: longest distance from the target along consumer -> input.
 * Relaxation stops after n rounds, so a loop cannot push items forever.
 */
export function assignLayers(items, edges, target) {
    const layer = { [target]: 0 };
    const limit = items.length;
    for (let round = 0; round < limit; round++) {
        let changed = false;
        for (const edge of edges) {
            if (layer[edge.to] === undefined)
                continue;
            const candidate = layer[edge.to] + 1;
            if (candidate > limit)
                continue;
            if (layer[edge.from] === undefined || candidate > layer[edge.from]) {
                // an input never goes further left than the number of items
                layer[edge.from] = candidate;
                changed = true;
            }
        }
        if (!changed)
            break;
    }
    for (const item of items)
        if (layer[item] === undefined)
            layer[item] = 0;
    return layer;
}

/**
 * Positions of nodes and paths of edges.
 */
export function layoutGraph(plan) {
    const items = plan.nodes.map(n => n.item);
    const edges = plan.edges;
    const layer = assignLayers(items, edges, plan.target.item);
    const maxLayer = Math.max(0, ...Object.values(layer));

    // columns, raw resources (highest layer) on the left
    const columns = [];
    for (let i = 0; i <= maxLayer; i++)
        columns.push([]);
    for (const item of items)
        columns[maxLayer - layer[item]].push(item);

    const neighbours = {};
    for (const item of items)
        neighbours[item] = [];
    for (const edge of edges) {
        neighbours[edge.from].push(edge.to);
        neighbours[edge.to].push(edge.from);
    }

    // barycenter sweeps: order each column by the mean position of its neighbours
    const position = {};
    const refresh = () => columns.forEach(col => col.forEach((item, index) => { position[item] = index; }));
    refresh();
    for (let sweep = 0; sweep < 4; sweep++) {
        const range = sweep % 2 === 0 ? [...columns.keys()] : [...columns.keys()].reverse();
        for (const c of range) {
            const weight = item => {
                const others = neighbours[item].filter(o => position[o] !== undefined);
                return others.length ? others.reduce((sum, o) => sum + position[o], 0) / others.length : position[item];
            };
            columns[c].sort((a, b) => weight(a) - weight(b));
            refresh();
        }
    }

    // node size grows with the rate (log scale)
    const byItem = Object.fromEntries(plan.nodes.map(n => [n.item, n]));
    const maxRate = Math.max(1, ...plan.nodes.map(n => n.items_per_hour));
    const heightOf = item => 40 + Math.round(24 * Math.log10(1 + byItem[item].items_per_hour) / Math.log10(1 + maxRate));

    const boxes = {};
    let height = 0;
    columns.forEach((col, c) => {
        let y = PADDING;
        for (const item of col) {
            const h = heightOf(item);
            boxes[item] = { x: PADDING + c * COLUMN_WIDTH, y, w: NODE_WIDTH, h };
            y += h + ROW_GAP;
        }
        height = Math.max(height, y);
    });
    // centre short columns vertically
    columns.forEach(col => {
        if (!col.length)
            return;
        const last = boxes[col[col.length - 1]];
        const offset = (height - (last.y + last.h + ROW_GAP)) / 2;
        col.forEach(item => { boxes[item].y += offset; });
    });

    const maxFlow = Math.max(1, ...edges.map(e => e.items_per_hour));
    const paths = edges.map(edge => {
        const from = boxes[edge.from];
        const to = boxes[edge.to];
        const back = from.x >= to.x;
        const x1 = back ? from.x : from.x + from.w;
        const y1 = from.y + from.h / 2;
        const x2 = back ? to.x + to.w : to.x;
        const y2 = to.y + to.h / 2;
        const bend = back ? -80 : Math.max(40, (x2 - x1) / 2);
        return {
            edge,
            back,
            d: `M${x1},${y1} C${x1 + bend},${y1} ${x2 - bend},${y2} ${x2},${y2}`,
            width: 1.5 + 6 * Math.log10(1 + edge.items_per_hour) / Math.log10(1 + maxFlow),
            labelX: (x1 + x2) / 2,
            labelY: (y1 + y2) / 2 - 4,
        };
    });

    return {
        width: PADDING * 2 + (maxLayer) * COLUMN_WIDTH + NODE_WIDTH,
        height: Math.max(height, 120),
        boxes,
        paths,
        byItem,
    };
}

/**
 * SVG markup of the graph. Colours come from daisyUI theme variables.
 */
export function renderGraph(plan, text) {
    const layout = layoutGraph(plan);
    const parts = [];
    parts.push(`<svg xmlns="http://www.w3.org/2000/svg" width="${layout.width}" height="${layout.height}" viewBox="0 0 ${layout.width} ${layout.height}" role="img" aria-label="${escapeHtml(text.graph)}" font-family="inherit">`);

    for (const p of layout.paths) {
        const colour = p.edge.fuel ? 'var(--color-warning)' : 'var(--color-primary)';
        parts.push(`<path d="${p.d}" fill="none" stroke="${colour}" stroke-opacity="0.55" stroke-width="${p.width.toFixed(1)}"${p.back ? ' stroke-dasharray="6 4"' : ''}><title>${escapeHtml(`${layout.byItem[p.edge.from].name} → ${layout.byItem[p.edge.to].name}: ${formatRate(p.edge.items_per_hour)}${text.perHour}, ${p.edge.hopper_lines} ${text.hopperLines}`)}</title></path>`);
        parts.push(`<text x="${p.labelX}" y="${p.labelY}" text-anchor="middle" font-size="10" fill="currentColor" fill-opacity="0.7">${escapeHtml(formatRate(p.edge.items_per_hour))}${p.edge.hopper_lines > 1 ? ` ×${p.edge.hopper_lines}` : ''}</text>`);
    }

    for (const [item, box] of Object.entries(layout.boxes)) {
        const node = layout.byItem[item];
        const source = node.kind === 'source';
        const target = item === plan.target.item;
        const fill = target ? 'var(--color-primary)' : (source ? 'var(--color-success)' : 'var(--color-base-200)');
        const textColour = target ? 'var(--color-primary-content)' : (source ? 'var(--color-success-content)' : 'currentColor');
        let detail = `${formatRate(node.items_per_hour)}${text.perHour}`;
        if (node.machine)
            detail += ` · ${node.machine.count} ${text.machines[node.machine.type] || node.machine.type}`;
        parts.push(`<g data-item="${escapeHtml(item)}">`);
        parts.push(`<rect x="${box.x}" y="${box.y}" width="${box.w}" height="${box.h}" rx="8" fill="${fill}" stroke="currentColor" stroke-opacity="0.15"/>`);
        parts.push(`<text x="${box.x + 10}" y="${box.y + 18}" font-size="12" font-weight="600" fill="${textColour}">${escapeHtml(node.name.length > 22 ? node.name.slice(0, 21) + '…' : node.name)}</text>`);
        parts.push(`<text x="${box.x + 10}" y="${box.y + 33}" font-size="10.5" fill="${textColour}" fill-opacity="0.8">${escapeHtml(detail)}</text>`);
        parts.push('</g>');
    }

    parts.push('</svg>');
    return parts.join('');
}
