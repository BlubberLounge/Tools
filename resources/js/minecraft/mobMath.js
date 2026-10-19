/**
 * Mob farm rates, used by mobFarm.js. The drops per kill come from
 * resources/minecraft/mobs/{version}.json (expected values computed from the
 * loot tables by scripts/minecraft/mob-drops.py).
 */
import { pointsForLevel } from './xpMath';

export const LOOTING_LEVELS = [0, 1, 2, 3];

/** Items that stack to 16 or 1 among mob drops; everything else stacks to 64. */
const STACK_SIZE = {
    ender_pearl: 16,
    egg: 16,
    snowball: 16,
    totem_of_undying: 1,
    saddle: 1,
};

export function stackSizeOf(item) {
    return STACK_SIZE[item] ?? 64;
}

/** Drops per kill for a kill method: 'auto' (no player) or 'player' with a Looting level. */
export function perKill(drop, method, looting = 0) {
    return method === 'auto' ? drop.auto : drop.player[Math.min(Math.max(Number(looting) || 0, 0), 3)];
}

/**
 * Rates of a mob at `killsPerHour`: rows [{item, perKill, perHour, stacks}],
 * plus the slots (stacks) and shulker boxes filled per hour and XP per hour.
 */
export function rates(mob, killsPerHour, method, looting = 0) {
    const kills = Math.max(0, Number(killsPerHour) || 0);
    const rows = mob.drops
        .map(drop => {
            const each = perKill(drop, method, looting);
            const perHour = each * kills;
            return { item: drop.item, perKill: each, perHour, stacks: perHour / stackSizeOf(drop.item) };
        })
        .filter(row => row.perKill > 0);

    const slots = rows.reduce((sum, row) => sum + row.stacks, 0);

    return {
        rows,
        slots,
        shulkers: slots / 27,
        xpPerHour: method === 'auto' ? 0 : mob.xp * kills,
    };
}

/** Hours to go from level 0 to `level` at `xpPerHour`. */
export function hoursToLevel(level, xpPerHour) {
    return xpPerHour > 0 ? pointsForLevel(level) / xpPerHour : Infinity;
}
