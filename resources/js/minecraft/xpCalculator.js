/**
 * Minecraft XP calculator (resources/views/minecraft/xp-calculator.blade.php).
 *
 * Levels to points and back, points between two levels, how many of each XP
 * source that takes, plus enchanting, Mending and death drops. The maths
 * lives in xpMath.js; mob XP comes from the enchantment data file.
 */
import {
    OTHER_SOURCES, SMELTING_SOURCES,
    pointsForLevel, pointsToNext, levelForPoints, pointsBetween, enchantsFrom, mendingPoints, deathDrop, sourceCount,
} from './xpMath';

/** Durability of common Mending items. */
const DURABILITY = [
    { id: 'netherite', value: 2031 },
    { id: 'diamond', value: 1561 },
    { id: 'elytra', value: 432 },
    { id: 'iron', value: 250 },
];

function xpCalculator(config) {
    return {
        text: config.text,
        durabilities: DURABILITY,
        groups: [
            { id: 'other', sources: OTHER_SOURCES.map(s => ({ ...s, name: config.text.sources[s.id] ?? s.id })) },
            { id: 'smelting', sources: SMELTING_SOURCES.map(s => ({ ...s, name: config.text.sources[s.id] ?? s.id })) },
            { id: 'mobs', sources: config.mobs.map(m => ({ id: m.id, name: m.name, xp: m.xp })) },
        ],
        group: 'other',

        from: 0,
        to: 30,
        points: 1395,
        durability: 1561,

        get needed() {
            return pointsBetween(this.from, this.to);
        },

        get pointsLevel() {
            return levelForPoints(this.points);
        },

        get sources() {
            return this.groups.find(g => g.id === this.group).sources;
        },

        totalPoints(level) {
            return pointsForLevel(level);
        },

        nextPoints(level) {
            return pointsToNext(level);
        },

        enchants(level) {
            return enchantsFrom(level);
        },

        mending() {
            return mendingPoints(this.durability);
        },

        death(level) {
            return deathDrop(level);
        },

        count(xp) {
            return sourceCount(this.needed, xp);
        },

        range(source) {
            return source.min === undefined ? this.format(source.xp) : `${source.min}–${source.max}`;
        },

        format(value, digits = 2) {
            return Number.isFinite(value) ? value.toLocaleString(undefined, { maximumFractionDigits: digits }) : '-';
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('xp-calculator');
const configElement = document.getElementById('xp-calculator-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('xpCalculator', () => xpCalculator(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'xpCalculator'));
    window.Alpine.initTree(root);
}
