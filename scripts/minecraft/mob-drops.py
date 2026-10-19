"""Expected drops per kill from the entity loot tables of the 26.3 server jar.

python -I scripts/minecraft/mob-drops.py <inner.jar of the server jar> resources/minecraft/mobs/26.3.json

Scenarios: 'auto' = not killed by a player (no looting, no player-only drops),
'player' = killed by a player with Looting 0..3. Counts are exact expected
values: count distributions are tracked per entry (set_count, looting bonus,
limit_count) and clipped at 0 like ItemStack.isEmpty.
"""
import json, sys, zipfile
from fractions import Fraction

jar = zipfile.ZipFile(sys.argv[1])


def table(ref):
    name = ref.removeprefix('minecraft:')
    return json.loads(jar.read(f'data/minecraft/loot_table/{name}.json'))


def as_list(value):
    if value is None:
        return []
    return value if isinstance(value, list) else [value]


# mob id, display name, xp per player kill, entity properties assumed
MOBS = [
    ('zombie', 'Zombie', 5, {}), ('husk', 'Husk', 5, {}), ('drowned', 'Drowned', 5, {}),
    ('zombie_villager', 'Zombie Villager', 5, {}), ('zombified_piglin', 'Zombified Piglin', 5, {}),
    ('skeleton', 'Skeleton', 5, {}), ('stray', 'Stray', 5, {}), ('bogged', 'Bogged', 5, {}),
    ('wither_skeleton', 'Wither Skeleton', 5, {}), ('creeper', 'Creeper', 5, {}),
    ('spider', 'Spider', 5, {}), ('cave_spider', 'Cave Spider', 5, {}), ('enderman', 'Enderman', 5, {}),
    ('witch', 'Witch', 5, {}), ('blaze', 'Blaze', 10, {}), ('breeze', 'Breeze', 10, {}),
    ('slime', 'Slime (small)', 1, {'size': 1}), ('magma_cube', 'Magma Cube (medium)', 2, {'size': 2}),
    ('guardian', 'Guardian', 10, {}), ('phantom', 'Phantom', 5, {}), ('shulker', 'Shulker', 5, {}),
    ('ghast', 'Ghast', 5, {}), ('hoglin', 'Hoglin', 5, {}),
    ('vindicator', 'Vindicator', 5, {}), ('evoker', 'Evoker', 10, {}),
    ('ravager', 'Ravager', 20, {}), ('iron_golem', 'Iron Golem', 0, {}),
    ('cow', 'Cow', 2, {}), ('chicken', 'Chicken', 2, {}), ('pig', 'Pig', 2, {}), ('rabbit', 'Rabbit', 2, {}),
]

unknown = set()


def cond_prob(c, ctx):
    t = c['type'].removeprefix('minecraft:')
    if t == 'killed_by_player':
        return 1.0 if ctx['player'] else 0.0
    if t == 'random_chance':
        return float(c['chance'])
    if t == 'random_chance_with_enchanted_bonus':
        level = ctx['looting'] if ctx['player'] else 0
        if level == 0:
            return float(c['unenchanted_chance'])
        e = c['enchanted_chance']
        if isinstance(e, (int, float)):
            return float(e)
        assert e['type'].removeprefix('minecraft:') == 'linear', e
        return min(1.0, e['base'] + e['per_level_above_first'] * (level - 1))
    if t == 'all_of':
        p = 1.0
        for term in c['terms']:
            p *= cond_prob(term, ctx)
        return p
    if t == 'any_of':
        q = 1.0
        for term in c['terms']:
            q *= 1 - cond_prob(term, ctx)
        return 1 - q
    if t == 'inverted':
        return 1 - cond_prob(c['term'], ctx)
    if t == 'damage_source_properties':
        return 0.0  # not killed by a frog, a skeleton arrow etc.
    if t == 'entity_properties':
        pred = c.get('predicate', {})
        if c.get('entity') == 'this' and 'minecraft:type_specific/cube_mob' in pred:
            size = pred['minecraft:type_specific/cube_mob']['size']
            have = ctx['props'].get('size', 1)
            if isinstance(size, int):
                return 1.0 if have == size else 0.0
            return 1.0 if size.get('min', -10**9) <= have <= size.get('max', 10**9) else 0.0
        return 0.0  # on fire, riding a horse, baby, equipment of the attacker
    unknown.add(t)
    return 0.0


def int_provider(p):
    """Distribution {value: prob} of an int number provider."""
    if isinstance(p, (int, float)):
        return {int(p): Fraction(1)}
    t = p['type'].removeprefix('minecraft:')
    if t == 'constant':
        return {int(p['value']): Fraction(1)}
    if t == 'uniform':
        lo, hi = int(p['min']), int(p['max'])
        if lo >= hi:
            return {lo: Fraction(1)}
        n = hi - lo + 1
        return {v: Fraction(1, n) for v in range(lo, hi + 1)}
    if t == 'binomial':
        n, prob = int(p['n']), Fraction(p['p']).limit_denominator(10**6)
        from math import comb
        return {k: comb(n, k) * prob ** k * (1 - prob) ** (n - k) for k in range(n + 1)}
    unknown.add('number:' + t)
    return {0: Fraction(1)}


def looting_bonus(level, p):
    """Distribution of round(level * U(min, max)) for a uniform float provider."""
    if isinstance(p, (int, float)) or p['type'].removeprefix('minecraft:') == 'constant':
        value = p if isinstance(p, (int, float)) else p['value']
        return {round(level * value): Fraction(1)}
    assert p['type'].removeprefix('minecraft:') == 'uniform', p
    lo, hi = Fraction(p['min']).limit_denominator(1000), Fraction(p['max']).limit_denominator(1000)
    a, b = level * lo, level * hi  # bonus ~ U(a, b), Java Math.round = floor(x + 0.5)
    dist = {}
    k = int((a + Fraction(1, 2)) // 1)
    while k - Fraction(1, 2) < b:
        left, right = max(a, k - Fraction(1, 2)), min(b, k + Fraction(1, 2))
        if right > left:
            dist[k] = (right - left) / (b - a)
        k += 1
    return dist


def convolve(x, y):
    out = {}
    for a, pa in x.items():
        for b, pb in y.items():
            out[a + b] = out.get(a + b, 0) + pa * pb
    return out


def entry_count(entry, ctx):
    dist = {1: Fraction(1)}
    for f in as_list(entry.get('modifier') or entry.get('functions')):
        t = f['type'].removeprefix('minecraft:')
        if f.get('condition') or f.get('conditions'):
            if t != 'furnace_smelt':
                unknown.add('conditional function ' + t)
            continue
        if t == 'set_count':
            new = int_provider(f['count'])
            dist = convolve(dist, new) if f.get('add') else new
        elif t in ('enchanted_count_increase', 'looting_enchant'):
            level = ctx['looting'] if ctx['player'] else 0
            if level > 0:
                dist = convolve(dist, looting_bonus(level, f['count']))
                if f.get('limit', 0) > 0:
                    dist = clamp(dist, None, f['limit'])
        elif t == 'limit_count':
            lim = f['limit']
            dist = clamp(dist, lim.get('min'), lim.get('max'))
        elif t in ('furnace_smelt', 'set_components', 'set_potion', 'enchant_randomly', 'set_name',
                   'set_instrument', 'set_custom_data', 'set_damage', 'set_stew_effect', 'set_ominous_bottle_amplifier'):
            pass
        else:
            unknown.add('function ' + t)
    return sum(max(0, v) * p for v, p in dist.items())


def clamp(dist, lo, hi):
    out = {}
    for v, p in dist.items():
        if lo is not None:
            v = max(v, lo)
        if hi is not None:
            v = min(v, hi)
        out[v] = out.get(v, 0) + p
    return out


def expand(tbl, ctx, scale, result):
    for pool in tbl.get('pools', []):
        p_pool = 1.0
        for c in as_list(pool.get('condition') or pool.get('conditions')):
            p_pool *= cond_prob(c, ctx)
        if p_pool == 0:
            continue
        rolls = int_provider(pool['rolls'])
        mean_rolls = float(sum(v * p for v, p in rolls.items()))
        candidates = []
        for entry in pool['entries']:
            p_entry = 1.0
            for c in as_list(entry.get('condition') or entry.get('conditions')):
                p_entry *= cond_prob(c, ctx)
            if 0 < p_entry < 1:
                unknown.add('random entry condition')
            if p_entry > 0:
                candidates.append(entry)
        if not candidates:
            continue
        weights = [1 if len(candidates) == 1 else e.get('weight', 1) for e in candidates]
        total = sum(weights)
        for entry, w in zip(candidates, weights):
            share = scale * p_pool * mean_rolls * w / total
            t = entry['type'].removeprefix('minecraft:')
            if t == 'item':
                item = entry['name'].removeprefix('minecraft:')
                result[item] = result.get(item, 0) + share * float(entry_count(entry, ctx))
            elif t == 'loot_table':
                value = entry.get('value') or entry.get('name')
                sub = table(value) if isinstance(value, str) else value
                expand(sub, ctx, share, result)
            elif t == 'empty':
                pass
            else:
                unknown.add('entry ' + t)


mobs = []
for mob_id, name, xp, props in MOBS:
    tbl = table(f'entities/{mob_id}')
    scenarios = [{'player': False, 'looting': 0, 'props': props}] + \
                [{'player': True, 'looting': l, 'props': props} for l in range(4)]
    per = [dict() for _ in scenarios]
    for i, ctx in enumerate(scenarios):
        expand(tbl, ctx, 1.0, per[i])
    items = sorted({k for d in per for k in d}, key=lambda k: -per[-1].get(k, 0))
    drops = []
    for item in items:
        values = [round(d.get(item, 0), 6) for d in per]
        if max(values) == 0:
            continue
        drops.append({'item': item, 'auto': values[0], 'player': values[1:]})
    mobs.append({'id': mob_id, 'name': name, 'xp': xp, 'drops': drops})

out = {
    'version': '26.3',
    'name': 'Java Edition 26.3',
    'updated': '2026-10-19',
    'sources': [{
        'title': 'Java Edition 26.3 server jar data (loot_table/entities)',
        'url': 'https://www.minecraft.net/en-us/download/server',
        'retrieved': '2026-10-19',
    }],
    'notes': ('Expected drops per kill, computed from the entity loot tables of the official 26.3 server jar. '
              '"auto" is a kill without a player (no Looting, no player-only drops); "player" lists Looting 0 to 3. '
              'Not on fire, not killed by a frog, no zombie horse rider. Equipment drops (armour, bows, tridents) '
              'are not part of the loot tables and are left out. XP per player kill; animals give 1 to 3 (average 2), '
              'slimes and magma cubes XP equal to their size.'),
    'mobs': mobs,
}
with open(sys.argv[2], 'w', encoding='utf-8', newline='\n') as fh:
    json.dump(out, fh, indent=4)
    fh.write('\n')
print('unknown:', sorted(unknown))
for m in mobs:
    print(m['id'], [(d['item'], d['auto'], d['player']) for d in m['drops']][:4])
