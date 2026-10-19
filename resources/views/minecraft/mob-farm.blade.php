@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/mobFarm.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/mobFarm.js --}}
    <script type="application/json" id="mob-farm-config">
        {!! json_encode([
            'mobs' => $mobs,
            'text' => [],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="mob-farm" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-skull text-primary"></i>
                {{ __('Mob farm rates') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Drops, storage and XP per hour of a mob farm from its kill rate. Average drops per kill come from the loot tables.') }} ({{ $versionName }})</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- Input --}}
            <div class="card bg-base-100 shadow-sm self-start" data-testid="mob-input">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-pen text-primary"></i>{{ __('Farm') }}</h2>

                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Mob') }}</span>
                        <select class="select select-bordered select-sm w-full" x-model="mob">
                            <template x-for="m in mobs" :key="m.id">
                                <option :value="m.id" x-text="m.name" :selected="mob === m.id"></option>
                            </template>
                        </select>
                    </label>

                    <div>
                        <span class="label-text text-xs mb-1 block">{{ __('Kills') }}</span>
                        <div class="join w-full">
                            <input type="number" min="0" class="input input-bordered input-sm join-item flex-1" x-model="kills">
                            <button type="button" class="btn btn-sm join-item" :class="!perMinute && 'btn-primary'" @click="perMinute = false">/ h</button>
                            <button type="button" class="btn btn-sm join-item" :class="perMinute && 'btn-primary'" @click="perMinute = true">/ min</button>
                        </div>
                    </div>

                    <div>
                        <span class="label-text text-xs mb-1 block">{{ __('Killed by') }}</span>
                        <div class="join w-full">
                            <button type="button" class="btn btn-sm join-item flex-1" :class="method === 'auto' && 'btn-primary'" @click="method = 'auto'">{{ __('the farm') }}</button>
                            <button type="button" class="btn btn-sm join-item flex-1" :class="method === 'player' && 'btn-primary'" @click="method = 'player'">{{ __('a player') }}</button>
                        </div>
                    </div>

                    <div x-show="method === 'player'">
                        <span class="label-text text-xs mb-1 block">{{ __('Looting') }}</span>
                        <div class="join w-full">
                            <template x-for="l in lootingLevels" :key="l">
                                <button type="button" class="btn btn-sm join-item flex-1" :class="Number(looting) === l && 'btn-primary'" @click="looting = l" x-text="l === 0 ? '-' : 'I'.repeat(l)"></button>
                            </template>
                        </div>
                    </div>

                    <p class="text-xs text-base-content/60">{{ __('Farm kills (fall, lava, suffocation, entity cramming) get no Looting, no player-only drops and no XP. A player kill counts when the player hit the mob in the last 5 seconds.') }}</p>
                </div>
            </div>

            {{-- Drops --}}
            <div class="card bg-base-100 shadow-sm xl:col-span-2" data-testid="mob-result">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-calculator text-primary"></i>{{ __('Per hour') }}</h2>

                    <div class="stats stats-vertical sm:stats-horizontal bg-base-200 w-full">
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Kills') }}</div><div class="stat-value text-2xl" x-text="format(killsPerHour, 0)"></div></div>
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Shulker box') }}</div><div class="stat-value text-2xl" x-text="format(result.shulkers)"></div><div class="stat-desc"><span x-text="format(result.slots, 1)"></span> {{ __('slots') }}</div></div>
                        <div class="stat py-2"><div class="stat-title text-xs">XP</div><div class="stat-value text-2xl" x-text="format(result.xpPerHour, 0)"></div><div class="stat-desc" x-show="result.xpPerHour > 0">{{ __('Level 0 to 30') }}: <span x-text="duration(hoursTo30)"></span></div></div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Item') }}</th>
                                    <th class="text-right">{{ __('Per kill') }}</th>
                                    <th class="text-right">{{ __('Per hour') }}</th>
                                    <th class="text-right">{{ __('Stacks') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="r in result.rows" :key="r.item">
                                    <tr>
                                        <td x-text="itemName(r.item)"></td>
                                        <td class="text-right font-mono" x-text="format(r.perKill, 3)"></td>
                                        <td class="text-right font-mono font-semibold" x-text="format(r.perHour, 0)"></td>
                                        <td class="text-right font-mono" x-text="format(r.stacks, 1)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-sm text-base-content/60" x-show="result.rows.length === 0">{{ __('This mob only drops items when a player kills it.') }}</p>
                    <p class="text-xs text-base-content/50">{{ __('Averages per kill. Armour, bows and other equipment a mob carries are not included, nor the extra XP for it.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
