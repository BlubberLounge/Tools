@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/materialCalculator.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/materialCalculator.js --}}
    <script type="application/json" id="material-calculator-config">
        {!! json_encode([
            'text' => [
                'shapes' => [
                    'solid' => __('Solid'),
                    'hollow' => __('Hollow (walls, floor and ceiling)'),
                    'walls' => __('Walls only'),
                    'wallsFloor' => __('Walls and floor'),
                    'frame' => __('Frame (edges only)'),
                ],
                'units' => [
                    'item' => __('Items'),
                    'stack' => __('Stacks'),
                    'chest' => __('Chest / barrel'),
                    'shulker' => __('Shulker box'),
                    'doubleChest' => __('Double chest'),
                    'shulkerDoubleChest' => __('Double chest of shulker boxes'),
                ],
                'breakdowns' => [
                    'shulker' => __('In shulker boxes'),
                    'chest' => __('In chests'),
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="material-calculator" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-cube text-primary"></i>
                {{ __('Material calculator') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Blocks for boxes, rooms, walls and frames, split into stacks, chests and shulker boxes. Add several parts to plan a whole build.') }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- Parts --}}
            <div class="flex flex-col gap-4 xl:col-span-2" data-testid="material-parts">
                <template x-for="(part, index) in parts" :key="index">
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body p-4 gap-3">
                            <div class="flex items-center gap-2">
                                <h2 class="card-title text-sm flex-1"><i class="fa-solid fa-cube text-primary"></i>{{ __('Part') }} <span x-text="index + 1"></span></h2>
                                <button type="button" class="btn btn-ghost btn-xs" @click="removePart(index)" title="{{ __('Remove') }}"><i class="fa-solid fa-xmark"></i></button>
                            </div>

                            <div class="flex flex-wrap gap-1">
                                <template x-for="shape in shapes" :key="shape">
                                    <button type="button" class="btn btn-xs" :class="part.shape === shape && 'btn-primary'" @click="part.shape = shape" x-text="text.shapes[shape]"></button>
                                </template>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Length') }}</span><input type="number" min="0" class="input input-bordered input-sm w-full" x-model="part.length"></label>
                                <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Width') }}</span><input type="number" min="0" class="input input-bordered input-sm w-full" x-model="part.width"></label>
                                <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Height') }}</span><input type="number" min="0" class="input input-bordered input-sm w-full" x-model="part.height"></label>
                                <label class="form-control" x-show="part.shape !== 'solid'"><span class="label-text text-xs mb-1">{{ __('Wall thickness') }}</span><input type="number" min="1" class="input input-bordered input-sm w-full" x-model="part.thickness"></label>
                            </div>

                            <div class="flex flex-wrap gap-4 text-sm">
                                <span>{{ __('Blocks') }}: <span class="font-mono font-semibold" x-text="format(result(part).blocks, 0)"></span></span>
                                <span class="text-base-content/60" x-show="result(part).inside > 0">{{ __('Inside') }}: <span class="font-mono" x-text="format(result(part).inside, 0)"></span></span>
                            </div>
                        </div>
                    </div>
                </template>

                <div>
                    <button type="button" class="btn btn-ghost btn-sm" @click="addPart()"><i class="fa-solid fa-plus"></i>{{ __('Add part') }}</button>
                </div>
            </div>

            {{-- Result --}}
            <div class="card bg-base-100 shadow-sm self-start" data-testid="material-result">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-calculator text-primary"></i>{{ __('Result') }}</h2>

                    <div>
                        <span class="label-text text-xs mb-1 block">{{ __('Stack size') }}</span>
                        <div class="join w-full">
                            <template x-for="size in stackSizes" :key="size">
                                <button type="button" class="btn btn-sm join-item flex-1" :class="stackSize === size && 'btn-primary'" @click="stackSize = size" x-text="size"></button>
                            </template>
                        </div>
                    </div>

                    <div class="stats stats-vertical bg-base-200 w-full">
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Blocks') }}</div><div class="stat-value text-2xl" x-text="format(total, 0)"></div></div>
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Slots needed') }}</div><div class="stat-value text-lg" x-text="format(slots, 0)"></div></div>
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Shulker box') }}</div><div class="stat-value text-lg" x-text="format(shulkers)"></div></div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <template x-for="b in breakdowns" :key="b.id">
                            <div class="rounded-box bg-base-200 px-3 py-2 text-sm">
                                <span class="text-xs text-base-content/60 block" x-text="text.breakdowns[b.id]"></span>
                                <span class="font-semibold" x-text="breakdownText(b.parts)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
