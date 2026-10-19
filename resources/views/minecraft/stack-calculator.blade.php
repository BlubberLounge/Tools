@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/stackCalculator.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/stackCalculator.js --}}
    <script type="application/json" id="stack-calculator-config">
        {!! json_encode([
            'text' => [
                'units' => [
                    'item' => __('Items'),
                    'stack' => __('Stacks'),
                    'hopper' => __('Hopper'),
                    'dispenser' => __('Dispenser / dropper'),
                    'chest' => __('Chest / barrel'),
                    'shulker' => __('Shulker box'),
                    'inventory' => __('Player inventory'),
                    'doubleChest' => __('Double chest'),
                    'shulkerChest' => __('Chest of shulker boxes'),
                    'shulkerDoubleChest' => __('Double chest of shulker boxes'),
                ],
                'breakdowns' => [
                    'shulker' => __('In shulker boxes'),
                    'chest' => __('In chests'),
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="stack-calculator" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-primary"></i>
                {{ __('Stack calculator') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Add up items, stacks, chests and shulker boxes in any mix and see the total in every unit.') }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- Input --}}
            <div class="card bg-base-100 shadow-sm" data-testid="stack-input">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-pen text-primary"></i>{{ __('Input') }}</h2>

                    <div>
                        <span class="label-text text-xs mb-1 block">{{ __('Stack size') }}</span>
                        <div class="join w-full">
                            <template x-for="size in stackSizes" :key="size">
                                <button type="button" class="btn btn-sm join-item flex-1" :class="stackSize === size && 'btn-primary'" @click="stackSize = size" x-text="size"></button>
                            </template>
                        </div>
                        <p class="text-xs text-base-content/60 mt-1">{{ __('16: ender pearls, eggs, snowballs, signs, buckets, banners. 1: tools, armour, potions, filled buckets.') }}</p>
                    </div>

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="join w-full">
                            <input type="number" min="0" step="any" class="input input-bordered input-sm join-item w-24" x-model="row.amount" placeholder="0" data-testid="stack-amount">
                            <select class="select select-bordered select-sm join-item flex-1" x-model="row.unit">
                                <template x-for="unit in units" :key="unit">
                                    <option :value="unit" x-text="unitName(unit)" :selected="row.unit === unit"></option>
                                </template>
                            </select>
                            <button type="button" class="btn btn-sm join-item" @click="removeRow(index)" title="{{ __('Remove') }}"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn btn-ghost btn-xs" @click="addRow()"><i class="fa-solid fa-plus"></i>{{ __('Add row') }}</button>
                        <button type="button" class="btn btn-ghost btn-xs" @click="clear()"><i class="fa-solid fa-eraser"></i>{{ __('Clear') }}</button>
                    </div>
                </div>
            </div>

            {{-- Result --}}
            <div class="card bg-base-100 shadow-sm xl:col-span-2" data-testid="stack-result">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-calculator text-primary"></i>{{ __('Result') }}</h2>

                    <div class="stats stats-vertical sm:stats-horizontal bg-base-200 w-full">
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Items') }}</div><div class="stat-value text-2xl" x-text="format(total, 0)"></div></div>
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Slots needed') }}</div><div class="stat-value text-2xl" x-text="format(slots, 0)"></div></div>
                    </div>

                    <div class="flex flex-col gap-1" data-testid="stack-breakdown">
                        <template x-for="b in breakdowns" :key="b.id">
                            <div class="rounded-box bg-base-200 px-3 py-2 text-sm">
                                <span class="text-xs text-base-content/60 block" x-text="text.breakdowns[b.id]"></span>
                                <span class="font-semibold" x-text="breakdownText(b.parts)"></span>
                            </div>
                        </template>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-sm" data-testid="stack-conversions">
                            <thead>
                                <tr>
                                    <th>{{ __('Unit') }}</th>
                                    <th class="text-right">{{ __('Items per unit') }}</th>
                                    <th class="text-right">{{ __('Amount') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="c in conversions" :key="c.unit">
                                    <tr>
                                        <td x-text="unitName(c.unit)"></td>
                                        <td class="text-right font-mono" x-text="format(itemsPer(c.unit), 0)"></td>
                                        <td class="text-right font-mono font-semibold" x-text="format(c.value, c.value < 0.01 && c.value > 0 ? 4 : 2)"></td>
                                        <td class="text-right"><button type="button" class="btn btn-ghost btn-xs" @click="useUnit(c.unit)" title="{{ __('Use as input') }}"><i class="fa-solid fa-arrow-turn-up"></i></button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-base-content/50">{{ __('A full shulker box takes one slot. Player inventory = 36 slots without armour and offhand.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
