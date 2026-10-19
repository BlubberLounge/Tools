@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/xpCalculator.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/xpCalculator.js --}}
    <script type="application/json" id="xp-calculator-config">
        {!! json_encode([
            'mobs' => $mobs,
            'text' => [
                'groups' => [
                    'other' => __('Bottles, breeding, ores'),
                    'smelting' => __('Smelting'),
                    'mobs' => __('Mobs'),
                ],
                'sources' => [
                    'experience_bottle' => __('Bottle o\' enchanting'),
                    'breeding' => __('Breeding'),
                    'fishing' => __('Fishing'),
                    'trading' => __('Villager trade'),
                    'spawner' => __('Breaking a spawner'),
                    'coal_ore' => __('Coal ore'),
                    'nether_gold_ore' => __('Nether gold ore'),
                    'redstone_ore' => __('Redstone ore'),
                    'lapis_ore' => __('Lapis lazuli ore'),
                    'nether_quartz_ore' => __('Nether quartz ore'),
                    'diamond_ore' => __('Diamond ore'),
                    'emerald_ore' => __('Emerald ore'),
                    'ancient_debris' => __('Ancient debris'),
                    'raw_gold' => __('Raw gold'),
                    'cactus' => __('Cactus'),
                    'raw_iron' => __('Raw iron'),
                    'raw_copper' => __('Raw copper'),
                    'raw_food' => __('Raw meat, fish, potato'),
                    'clay' => __('Clay block'),
                    'clay_ball' => __('Clay ball'),
                    'log' => __('Log (charcoal)'),
                    'kelp' => __('Kelp'),
                    'cobblestone' => __('Cobblestone'),
                    'sand' => __('Sand'),
                ],
                'durability' => [
                    'netherite' => __('Netherite'),
                    'diamond' => __('Diamond'),
                    'elytra' => __('Elytra'),
                    'iron' => __('Iron'),
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="xp-calculator" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-star text-primary"></i>
                {{ __('XP calculator') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Levels and experience points, how much XP a level takes and how many mobs, bottles or smelted items that is. Java Edition values.') }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- Levels --}}
            <div class="card bg-base-100 shadow-sm" data-testid="xp-levels">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-arrow-up-right-dots text-primary"></i>{{ __('From level to level') }}</h2>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="form-control"><span class="label-text text-xs mb-1">{{ __('From level') }}</span><input type="number" min="0" class="input input-bordered input-sm w-full" x-model="from"></label>
                        <label class="form-control"><span class="label-text text-xs mb-1">{{ __('To level') }}</span><input type="number" min="0" class="input input-bordered input-sm w-full" x-model="to"></label>
                    </div>

                    <div class="stats stats-vertical bg-base-200 w-full">
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Points needed') }}</div><div class="stat-value text-2xl" x-text="format(needed, 0)"></div></div>
                        <div class="stat py-2">
                            <div class="stat-title text-xs">{{ __('Level') }} <span x-text="to"></span></div>
                            <div class="stat-desc">{{ __('Total from 0') }}: <span class="font-mono" x-text="format(totalPoints(to), 0)"></span> · {{ __('to the next level') }}: <span class="font-mono" x-text="format(nextPoints(to), 0)"></span></div>
                        </div>
                    </div>

                    <div class="divider my-0 text-xs">{{ __('Points to level') }}</div>
                    <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Points') }}</span><input type="number" min="0" class="input input-bordered input-sm w-full" x-model="points"></label>
                    <p class="text-sm">
                        {{ __('Level') }} <span class="font-semibold" x-text="pointsLevel.level"></span>
                        + <span class="font-mono" x-text="pointsLevel.points"></span> {{ __('points') }}
                        (<span x-text="format(pointsLevel.progress * 100, 0)"></span> %)
                    </p>
                </div>
            </div>

            {{-- Sources --}}
            <div class="card bg-base-100 shadow-sm" data-testid="xp-sources">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-list-ol text-primary"></i>{{ __('How many for') }} <span x-text="format(needed, 0)"></span> {{ __('points') }}</h2>

                    <div class="join w-full">
                        <template x-for="g in groups" :key="g.id">
                            <button type="button" class="btn btn-xs join-item flex-1" :class="group === g.id && 'btn-primary'" @click="group = g.id" x-text="text.groups[g.id]"></button>
                        </template>
                    </div>

                    <div class="overflow-x-auto max-h-[28rem]">
                        <table class="table table-sm table-pin-rows">
                            <thead>
                                <tr><th>{{ __('Source') }}</th><th class="text-right">XP</th><th class="text-right">{{ __('Count') }}</th></tr>
                            </thead>
                            <tbody>
                                <template x-for="s in sources" :key="s.id">
                                    <tr>
                                        <td x-text="s.name"></td>
                                        <td class="text-right font-mono" x-text="range(s)"></td>
                                        <td class="text-right font-mono font-semibold" x-text="format(count(s.xp), 0)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-base-content/50" x-show="group === 'mobs'">{{ __('Mobs only drop XP when a player (or a tamed wolf) hit them in the last 5 seconds.') }}</p>
                    <p class="text-xs text-base-content/50" x-show="group !== 'mobs'">{{ __('Random ranges are averaged. Ores give XP only without Silk Touch.') }}</p>
                </div>
            </div>

            {{-- More --}}
            <div class="card bg-base-100 shadow-sm" data-testid="xp-extras">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-wand-magic-sparkles text-primary"></i>{{ __('Enchanting, Mending, death') }}</h2>

                    <div class="rounded-box bg-base-200 p-3 text-sm">
                        <span class="text-xs text-base-content/60 block">{{ __('Level-30 enchants from level') }} <span x-text="to"></span></span>
                        <span class="font-semibold text-lg" x-text="enchants(to)"></span>
                        <span class="text-xs text-base-content/60 block">{{ __('The top option needs level 30 and takes 3 levels.') }}</span>
                    </div>

                    <div class="rounded-box bg-base-200 p-3 text-sm flex flex-col gap-2">
                        <span class="text-xs text-base-content/60">{{ __('Mending: points to repair') }}</span>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="d in durabilities" :key="d.id">
                                <button type="button" class="btn btn-xs" :class="Number(durability) === d.value && 'btn-primary'" @click="durability = d.value" x-text="text.durability[d.id]"></button>
                            </template>
                        </div>
                        <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Durability') }}</span><input type="number" min="0" class="input input-bordered input-sm w-full" x-model="durability"></label>
                        <span><span class="font-semibold text-lg" x-text="format(mending(), 0)"></span> {{ __('points') }}</span>
                    </div>

                    <div class="rounded-box bg-base-200 p-3 text-sm">
                        <span class="text-xs text-base-content/60 block">{{ __('Dropped on death at level') }} <span x-text="to"></span></span>
                        <span class="font-semibold text-lg" x-text="death(to)"></span> {{ __('points') }}
                        <span class="text-xs text-base-content/60 block">{{ __('7 points per level, at most 100.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
