@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/smeltCalculator.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/smeltCalculator.js --}}
    <script type="application/json" id="smelt-calculator-config">
        {!! json_encode([
            'fuels' => $fuels,
            'text' => [
                'machines' => [
                    'furnace' => __('Furnace (10 s)'),
                    'fast' => __('Blast furnace / smoker (5 s)'),
                ],
                'units' => [
                    'item' => __('Items'),
                    'stack' => __('Stacks'),
                    'shulker' => __('Shulker box'),
                    'doubleChest' => __('Double chest'),
                ],
                'xpItems' => [
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
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="smelt-calculator" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-fire text-primary"></i>
                {{ __('Smelting calculator') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Time, fuel and XP to smelt a batch in a furnace array, and how much of every fuel that takes.') }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- Input --}}
            <div class="card bg-base-100 shadow-sm" data-testid="smelt-input">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-pen text-primary"></i>{{ __('Input') }}</h2>

                    <div class="join w-full">
                        <input type="number" min="0" step="any" class="input input-bordered input-sm join-item w-24" x-model="amount">
                        <select class="select select-bordered select-sm join-item flex-1" x-model="unit">
                            <template x-for="u in inputUnits" :key="u">
                                <option :value="u" x-text="text.units[u]" :selected="unit === u"></option>
                            </template>
                        </select>
                    </div>
                    <p class="text-xs text-base-content/60"><span x-text="format(items, 0)"></span> {{ __('items') }}</p>

                    <div class="flex flex-col gap-1">
                        <template x-for="m in machines" :key="m">
                            <button type="button" class="btn btn-sm" :class="machine === m && 'btn-primary'" @click="machine = m" x-text="text.machines[m]"></button>
                        </template>
                    </div>

                    <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Number of furnaces') }}</span><input type="number" min="1" class="input input-bordered input-sm w-full" x-model="furnaces"></label>

                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Fuel') }}</span>
                        <select class="select select-bordered select-sm w-full" x-model="fuel">
                            <template x-for="f in fuels" :key="f.item">
                                <option :value="f.item" x-text="f.name + ' (' + format(f.smelts) + ')'" :selected="fuel === f.item"></option>
                            </template>
                        </select>
                    </label>

                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Smelted item (for XP)') }}</span>
                        <select class="select select-bordered select-sm w-full" x-model="xpItem">
                            <template x-for="s in xpItems" :key="s.id">
                                <option :value="s.id" x-text="text.xpItems[s.id] + ' (' + format(s.xp) + ' XP)'" :selected="xpItem === s.id"></option>
                            </template>
                        </select>
                    </label>
                </div>
            </div>

            {{-- Result --}}
            <div class="card bg-base-100 shadow-sm" data-testid="smelt-result">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-calculator text-primary"></i>{{ __('Result') }}</h2>

                    <div class="stats stats-vertical bg-base-200 w-full">
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Time') }}</div><div class="stat-value text-2xl" x-text="duration(current.seconds)"></div></div>
                        <div class="stat py-2">
                            <div class="stat-title text-xs">{{ __('Fuel') }}</div>
                            <div class="stat-value text-2xl" x-text="format(current.fuel, 0)"></div>
                            <div class="stat-desc" x-text="stacks(current.fuel)"></div>
                            <div class="stat-desc" x-show="current.wasted > 0">{{ __('Left over') }}: <span x-text="format(current.wasted, 1)"></span> {{ __('smelts') }}</div>
                        </div>
                        <div class="stat py-2">
                            <div class="stat-title text-xs">{{ __('Throughput') }}</div>
                            <div class="stat-value text-lg"><span x-text="format(current.perHour, 0)"></span> / h</div>
                            <div class="stat-desc">{{ __('Hoppers needed to feed it') }}: <span x-text="hoppers"></span></div>
                        </div>
                        <div class="stat py-2"><div class="stat-title text-xs">XP</div><div class="stat-value text-lg" x-text="format(xp, 1)"></div></div>
                    </div>
                </div>
            </div>

            {{-- Fuel comparison --}}
            <div class="card bg-base-100 shadow-sm" data-testid="smelt-fuels">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-scale-balanced text-primary"></i>{{ __('Every fuel') }}</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>{{ __('Fuel') }}</th><th class="text-right">{{ __('Items each') }}</th><th class="text-right">{{ __('Needed') }}</th></tr>
                            </thead>
                            <tbody>
                                <template x-for="f in fuels" :key="f.item">
                                    <tr class="cursor-pointer hover" :class="fuel === f.item && 'bg-base-200'" @click="fuel = f.item">
                                        <td x-text="f.name"></td>
                                        <td class="text-right font-mono" x-text="format(f.smelts)"></td>
                                        <td class="text-right font-mono font-semibold" x-text="format(result(f.item).fuel, 0)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-base-content/50">{{ __('Blast furnaces and smokers cook twice as fast and burn fuel twice as fast, so the fuel per item is the same. Each furnace needs whole fuel items for its own share.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
