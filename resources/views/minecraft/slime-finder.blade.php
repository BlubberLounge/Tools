@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/slimeFinder.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/slimeFinder.js --}}
    <script type="application/json" id="slime-finder-config">
        {!! json_encode([
            'text' => [],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT) !!}
    </script>

    <div id="slime-finder" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-droplet text-primary"></i>
                {{ __('Slime chunk finder') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Slime chunks for a world seed (Java Edition). Slimes spawn there below Y 40, except in mushroom fields and the deep dark. Get the seed with /seed.') }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">

            {{-- Settings --}}
            <div class="flex flex-col gap-4">
                <div class="card bg-base-100 shadow-sm" data-testid="slime-settings">
                    <div class="card-body p-4 gap-3">
                        <h2 class="card-title text-sm"><i class="fa-solid fa-seedling text-primary"></i>{{ __('World') }}</h2>
                        <label class="form-control">
                            <span class="label-text text-xs mb-1">{{ __('Seed') }}</span>
                            <input type="text" class="input input-bordered input-sm w-full font-mono" x-model.debounce.300ms="seed" placeholder="-4172144997902289642" data-testid="slime-seed">
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="form-control"><span class="label-text text-xs mb-1">X</span><input type="number" class="input input-bordered input-sm w-full" x-model.debounce.300ms="x"></label>
                            <label class="form-control"><span class="label-text text-xs mb-1">Z</span><input type="number" class="input input-bordered input-sm w-full" x-model.debounce.300ms="z"></label>
                        </div>
                        <p class="text-xs text-base-content/60">{{ __('Chunk') }} <span class="font-mono" x-text="center.x + ', ' + center.z"></span></p>
                        <div>
                            <span class="label-text text-xs mb-1 block">{{ __('Map radius in chunks') }}</span>
                            <div class="join w-full">
                                <template x-for="r in radii" :key="r">
                                    <button type="button" class="btn btn-sm join-item flex-1" :class="Number(radius) === r && 'btn-primary'" @click="radius = r" x-text="r"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm" data-testid="slime-results" x-show="valid">
                    <div class="card-body p-4 gap-3">
                        <h2 class="card-title text-sm"><i class="fa-solid fa-location-crosshairs text-primary"></i>{{ __('AFK spot') }}</h2>
                        <p class="text-sm"><span class="font-semibold" x-text="inRange"></span> {{ __('slime chunks 24 to 128 blocks from your position.') }}</p>
                        <template x-if="best">
                            <button type="button" class="rounded-box bg-base-200 px-3 py-2 text-left text-sm hover:bg-base-300" @click="goTo(best)">
                                <span class="text-xs text-base-content/60 block">{{ __('Best spot on the map (orange)') }}</span>
                                <span class="font-semibold" x-text="'X ' + best.bx + ', Z ' + best.bz"></span>:
                                <span x-text="best.count"></span> {{ __('slime chunks') }}
                            </button>
                        </template>

                        <h2 class="card-title text-sm mt-2"><i class="fa-solid fa-list text-primary"></i>{{ __('Nearest slime chunks') }}</h2>
                        <ul class="text-sm flex flex-col gap-1">
                            <template x-for="c in nearest" :key="c.x + ',' + c.z">
                                <li>
                                    <button type="button" class="link link-hover text-left" @click="goTo(c)">
                                        <span class="font-mono" x-text="blocks(c)"></span>
                                        <span class="text-base-content/60">(<span x-text="format(c.distance)"></span> {{ __('blocks') }})</span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Map --}}
            <div class="card bg-base-100 shadow-sm xl:col-span-3" data-testid="slime-map">
                <div class="card-body p-4 gap-2">
                    <p class="text-sm text-warning" x-show="!valid">{{ __('Enter a seed to see the slime chunks.') }}</p>
                    <div class="text-sm min-h-6" x-show="valid">
                        <template x-if="hover">
                            <span>
                                {{ __('Chunk') }} <span class="font-mono" x-text="hover.x + ', ' + hover.z"></span>
                                · <span class="font-mono" x-text="blocks(hover)"></span>
                                <span class="badge badge-success badge-sm" x-show="hover.slime">{{ __('slime chunk') }}</span>
                            </span>
                        </template>
                        <span class="text-base-content/50" x-show="!hover">{{ __('Green: slime chunk. Red: your position with the 24 and 128 block spawn ring.') }}</span>
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
