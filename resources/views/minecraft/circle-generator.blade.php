@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/circleGenerator.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/circleGenerator.js --}}
    <script type="application/json" id="circle-generator-config">
        {!! json_encode([
            'text' => [
                'shapes' => [
                    'circle' => __('Circle'),
                    'ellipse' => __('Ellipse'),
                    'sphere' => __('Sphere'),
                    'dome' => __('Dome'),
                ],
                'units' => [
                    'item' => __('Items'),
                    'stack' => __('Stacks'),
                    'chest' => __('Chest / barrel'),
                    'shulker' => __('Shulker box'),
                    'doubleChest' => __('Double chest'),
                    'shulkerDoubleChest' => __('Double chest of shulker boxes'),
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="circle-generator" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-regular fa-circle text-primary"></i>
                {{ __('Circle and sphere generator') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Block patterns for circles, ellipses, spheres and domes, layer by layer, with block counts. The red lines mark the centre.') }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">

            {{-- Settings --}}
            <div class="card bg-base-100 shadow-sm self-start" data-testid="circle-settings">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-sliders text-primary"></i>{{ __('Shape') }}</h2>

                    <div class="grid grid-cols-2 gap-1">
                        <template x-for="s in shapes" :key="s">
                            <button type="button" class="btn btn-sm" :class="shape === s && 'btn-primary'" @click="shape = s" x-text="text.shapes[s]"></button>
                        </template>
                    </div>

                    <label class="form-control" x-show="shape !== 'ellipse'">
                        <span class="label-text text-xs mb-1">{{ __('Diameter') }} (<span x-text="flat ? maxSize.flat : maxSize.solid"></span> max)</span>
                        <input type="number" min="1" class="input input-bordered input-sm w-full" x-model.number="diameter">
                    </label>
                    <div class="grid grid-cols-2 gap-2" x-show="shape === 'ellipse'">
                        <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Width') }} (X)</span><input type="number" min="1" class="input input-bordered input-sm w-full" x-model.number="width"></label>
                        <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Depth') }} (Z)</span><input type="number" min="1" class="input input-bordered input-sm w-full" x-model.number="depth"></label>
                    </div>

                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" class="toggle toggle-sm toggle-primary" x-model="hollow">
                        <span class="label-text">{{ __('Hollow (outline only)') }}</span>
                    </label>

                    <div x-show="!flat">
                        <span class="label-text text-xs mb-1 block">{{ __('Layer') }} <span x-text="Number(layer) + 1"></span> / <span x-text="size[1]"></span> ({{ __('bottom first') }})</span>
                        <input type="range" min="0" :max="size[1] - 1" class="range range-sm range-primary" x-model.number="layer">
                        <div class="flex gap-1 mt-1">
                            <button type="button" class="btn btn-xs flex-1" @click="layer = Math.max(0, layer - 1)"><i class="fa-solid fa-arrow-down"></i></button>
                            <button type="button" class="btn btn-xs flex-1" @click="layer = Math.min(size[1] - 1, layer + 1)"><i class="fa-solid fa-arrow-up"></i></button>
                        </div>
                    </div>

                    <div class="stats stats-vertical bg-base-200 w-full">
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Blocks in total') }}</div><div class="stat-value text-2xl" x-text="format(total, 0)"></div></div>
                        <div class="stat py-2" x-show="!flat"><div class="stat-title text-xs">{{ __('Blocks in this layer') }}</div><div class="stat-value text-lg" x-text="format(layerCount, 0)"></div></div>
                        <div class="stat py-2"><div class="stat-title text-xs">{{ __('Size') }}</div><div class="stat-value text-lg" x-text="size.join(' × ')"></div></div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <template x-for="b in breakdowns" :key="b.id">
                            <div class="rounded-box bg-base-200 px-3 py-2 text-xs font-semibold" x-text="breakdownText(b.parts)"></div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Picture --}}
            <div class="card bg-base-100 shadow-sm xl:col-span-3" data-testid="circle-canvas">
                <div class="card-body p-4 gap-2">
                    <div class="text-sm min-h-6">
                        <template x-if="hover">
                            <span>
                                X <span class="font-mono" x-text="hover.x"></span>, Z <span class="font-mono" x-text="hover.z"></span>
                                · {{ __('Row') }}: <span class="font-mono" x-text="hover.runs.length ? hover.runs.join(' · ') : '-'"></span>
                            </span>
                        </template>
                        <span class="text-base-content/50" x-show="!hover">{{ __('Point at a row to see its block runs.') }}</span>
                    </div>
                    <div class="overflow-auto">
                        <canvas x-ref="canvas" class="block" @mousemove="onMove($event)" @mouseleave="hover = null"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
