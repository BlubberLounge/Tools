/**
 * Minecraft mob farm rates (resources/views/minecraft/mob-farm.blade.php).
 *
 * Drops, storage and XP per hour of a mob farm from its kill rate, for farm
 * kills or player kills with Looting. The maths lives in mobMath.js.
 */
import { LOOTING_LEVELS, rates, hoursToLevel } from './mobMath';

function mobFarm(config) {
    return {
        text: config.text,
        mobs: config.mobs,
        lootingLevels: LOOTING_LEVELS,

        mob: config.mobs.some(m => m.id === 'zombie') ? 'zombie' : config.mobs[0]?.id,
        kills: 1000,
        perMinute: false,
        method: 'auto',
        looting: 3,

        get selected() {
            return this.mobs.find(m => m.id === this.mob) ?? this.mobs[0];
        },

        get killsPerHour() {
            const value = Number(this.kills) || 0;
            return this.perMinute ? value * 60 : value;
        },

        get result() {
            return rates(this.selected, this.killsPerHour, this.method, this.looting);
        },

        get hoursTo30() {
            return hoursToLevel(30, this.result.xpPerHour);
        },

        itemName(item) {
            return item.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        },

        duration(hours) {
            if (!Number.isFinite(hours))
                return '-';
            const minutes = Math.round(hours * 60);
            return minutes >= 60 ? `${Math.floor(minutes / 60)} h ${minutes % 60} min` : `${minutes} min`;
        },

        format(value, digits = 2) {
            return Number.isFinite(value) ? value.toLocaleString(undefined, { maximumFractionDigits: digits }) : '-';
        },
    };
}

// app.js starts Alpine before page modules run, so the component is mounted here by hand.
const root = document.getElementById('mob-farm');
const configElement = document.getElementById('mob-farm-config');
if (root && configElement && window.Alpine) {
    const config = JSON.parse(configElement.textContent);
    window.Alpine.data('mobFarm', () => mobFarm(config));
    window.Alpine.mutateDom(() => root.setAttribute('x-data', 'mobFarm'));
    window.Alpine.initTree(root);
}
