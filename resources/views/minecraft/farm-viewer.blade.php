@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/farmViewer.js'])
@endpush

@push('styles')
    @vite(['resources/css/minecraft-farm-viewer.css'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/farmViewer.js --}}
    <script type="application/json" id="farm-viewer-config">
        {!! json_encode([
            'models' => $models,
            'initial' => $selected['id'] ?? null,
            'atlasUrl' => asset('minecraft/atlas/atlas.json'),
            'background' => '#9ec5f8',
            'text' => [
                'loadError' => __('Could not load the farm model.'),
                'webglError' => __('3D view is not available in this browser (WebGL).'),
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="farm-viewer" x-cloak>

        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-cubes text-primary"></i>
                    {{ __('3D farm viewer') }}
                </h1>
                <p class="text-base-content/60 text-sm max-w-2xl">{{ __('Look at a farm design from every side, fly through it and step through it layer by layer.') }}</p>
            </div>
            @if(count($models) > 0)
                <label class="form-control w-full sm:w-72">
                    <span class="label-text text-xs mb-1">{{ __('Farm design') }}</span>
                    <select class="select select-bordered select-sm w-full" x-model="modelId" @change="selectModel($event.target.value)" data-testid="design-picker">
                        @foreach($models as $model)
                            <option value="{{ $model['id'] }}" @selected(($selected['id'] ?? null) === $model['id'])>{{ $model['name'] }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
        </div>

        @if($notFound)
            <div class="alert alert-warning mb-4" data-testid="design-not-found">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ __('There is no 3D model for this design yet.') }}</span>
            </div>
        @endif

        @if(count($models) === 0)
            <div class="alert">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ __('No farm models are available yet.') }}</span>
            </div>
        @else

        <div class="alert alert-error mb-4" x-show="error" x-cloak>
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span x-text="error"></span>
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <div class="join" role="tablist">
                <button type="button" class="btn btn-sm join-item" :class="view === '3d' ? 'btn-primary' : ''" @click="setView('3d')" role="tab" :aria-selected="view === '3d'">
                    <i class="fa-solid fa-cube"></i>{{ __('3D view') }}
                </button>
                <button type="button" class="btn btn-sm join-item" :class="view === 'slice' ? 'btn-primary' : ''" @click="setView('slice')" role="tab" :aria-selected="view === 'slice'">
                    <i class="fa-solid fa-layer-group"></i>{{ __('Slice view') }}
                </button>
            </div>

            <template x-if="view === '3d'">
                <div class="join">
                    <button type="button" class="btn btn-sm join-item" :class="camMode === 'orbit' ? 'btn-active' : ''" @click="setCamMode('orbit')" title="O">
                        <i class="fa-solid fa-rotate"></i>{{ __('Orbit') }}
                    </button>
                    <button type="button" class="btn btn-sm join-item" :class="camMode === 'fly' ? 'btn-active' : ''" @click="setCamMode('fly')" x-show="canFly" title="F">
                        <i class="fa-solid fa-paper-plane"></i>{{ __('Fly') }}
                    </button>
                    <button type="button" class="btn btn-sm join-item" @click="topDown()" title="T">
                        <i class="fa-solid fa-arrows-down-to-line"></i>{{ __('Top-down') }}
                    </button>
                    <button type="button" class="btn btn-sm join-item" @click="resetView()" title="R">
                        <i class="fa-solid fa-house"></i><span class="sr-only">{{ __('Reset view') }}</span>
                    </button>
                </div>
            </template>

            <button type="button" class="btn btn-sm btn-ghost ml-auto" @click="helpOpen = !helpOpen" title="H">
                <i class="fa-solid fa-keyboard"></i>{{ __('Help') }}
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">

            {{-- Main: 3D stage or slice --}}
            <div class="xl:col-span-3 space-y-3">
                <div class="relative">

                    {{-- 3D view --}}
                    <div class="mc-viewer-stage rounded-box border border-base-300" x-ref="stage" x-show="view === '3d'" data-testid="viewer-stage">
                        <div class="mc-viewer-overlay" x-show="loading">
                            <span class="loading loading-spinner loading-md"></span>
                            <span class="text-sm" x-text="progress + ' %'"></span>
                        </div>
                        <div class="mc-viewer-hint" x-show="camMode === 'fly' && !pointerLocked && !loading">
                            {{ __('Click the view to look around. WASD to move, Space and Shift for up and down, Esc to release the mouse.') }}
                        </div>
                        <div class="mc-viewer-badge" x-show="camMode === 'fly'">
                            <i class="fa-solid fa-gauge"></i> <span x-text="speed"></span> {{ __('blocks/s') }}
                        </div>
                    </div>

                    {{-- Slice view --}}
                    <div x-show="view === 'slice'" class="card bg-base-100 shadow-sm" data-testid="slice-view">
                        <div class="card-body p-3 gap-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="join">
                                    <template x-for="a in ['y', 'x', 'z']" :key="a">
                                        <button type="button" class="btn btn-xs join-item uppercase" :class="axis === a ? 'btn-primary' : ''" @click="setAxis(a)" x-text="a"></button>
                                    </template>
                                </div>
                                <button type="button" class="btn btn-xs btn-ghost" @click="setLayer(layer - 1)" :disabled="layer <= 0" aria-label="{{ __('Previous layer') }}"><i class="fa-solid fa-minus"></i></button>
                                <input type="range" class="range range-xs range-primary grow min-w-32" min="0" :max="layerMax" :value="layer" @input="setLayer($event.target.value)" aria-label="{{ __('Layer') }}">
                                <button type="button" class="btn btn-xs btn-ghost" @click="setLayer(layer + 1)" :disabled="layer >= layerMax" aria-label="{{ __('Next layer') }}"><i class="fa-solid fa-plus"></i></button>
                                <span class="text-sm font-mono whitespace-nowrap"><span class="uppercase" x-text="axis"></span> = <span x-text="layer"></span> / <span x-text="layerMax"></span></span>
                            </div>
                            <div class="mc-slice-wrap">
                                <canvas x-ref="slice" class="mc-slice-canvas" @mousemove="sliceHover($event)" @mouseleave="hover = null"></canvas>
                            </div>
                            <div class="text-xs text-base-content/70 min-h-4">
                                <span x-show="hover" x-text="hover"></span>
                                <span x-show="!hover">{{ __('Lower layers are shown faded.') }}</span>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold mb-1">
                                    {{ __('Blocks in this layer') }}: <span x-text="layerBlocks()"></span>
                                </h3>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="c in counts" :key="c.name">
                                        <span class="badge badge-sm badge-ghost gap-1"><span x-text="label(c.name)"></span><span class="font-mono" x-text="c.count"></span></span>
                                    </template>
                                    <span class="text-xs text-base-content/50" x-show="counts.length === 0">{{ __('Empty layer') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Help overlay --}}
                    <div class="mc-viewer-help" x-show="helpOpen" x-transition.opacity @click.self="helpOpen = false" data-testid="viewer-help">
                        <div class="card bg-base-100 shadow-lg max-w-lg w-full">
                            <div class="card-body p-4 gap-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <h2 class="card-title text-base">{{ __('Controls') }}</h2>
                                    <button type="button" class="btn btn-xs btn-ghost" @click="helpOpen = false" aria-label="{{ __('Close') }}"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                                <h3 class="font-semibold">{{ __('Orbit mode') }} <kbd class="kbd kbd-xs">O</kbd></h3>
                                <ul class="list-disc pl-5">
                                    <li>{{ __('Drag (one finger) to rotate, scroll (pinch) to zoom, right-drag (two fingers) to pan.') }}</li>
                                    <li><kbd class="kbd kbd-xs">T</kbd> {{ __('top-down view') }}, <kbd class="kbd kbd-xs">R</kbd> {{ __('reset view') }}</li>
                                </ul>
                                <template x-if="canFly">
                                    <div>
                                        <h3 class="font-semibold">{{ __('Fly mode') }} <kbd class="kbd kbd-xs">F</kbd></h3>
                                        <ul class="list-disc pl-5">
                                            <li><kbd class="kbd kbd-xs">W</kbd><kbd class="kbd kbd-xs">A</kbd><kbd class="kbd kbd-xs">S</kbd><kbd class="kbd kbd-xs">D</kbd> {{ __('move') }}, <kbd class="kbd kbd-xs">Space</kbd> {{ __('up') }}, <kbd class="kbd kbd-xs">Shift</kbd> {{ __('down') }}</li>
                                            <li>{{ __('Click the view to look around with the mouse, Esc releases it.') }}</li>
                                            <li>{{ __('Scroll changes the flying speed.') }}</li>
                                        </ul>
                                    </div>
                                </template>
                                <h3 class="font-semibold">{{ __('Slice view') }}</h3>
                                <ul class="list-disc pl-5">
                                    <li><kbd class="kbd kbd-xs">&uarr;</kbd> <kbd class="kbd kbd-xs">PgUp</kbd> {{ __('next layer') }}, <kbd class="kbd kbd-xs">&darr;</kbd> <kbd class="kbd kbd-xs">PgDn</kbd> {{ __('previous layer') }}</li>
                                    <li><kbd class="kbd kbd-xs">X</kbd> <kbd class="kbd kbd-xs">Y</kbd> <kbd class="kbd kbd-xs">Z</kbd> {{ __('slice axis') }}</li>
                                </ul>
                                <p class="text-base-content/60"><kbd class="kbd kbd-xs">H</kbd> {{ __('opens and closes this help.') }}</p>
                                <p class="text-xs text-base-content/60 border-t border-base-300 pt-2" x-show="atlasLoaded" data-testid="texture-credit">
                                    {{ __('Block textures: Minecraft by Mojang Studios, extracted from a local game copy. Not an official Minecraft product; not approved by or associated with Mojang or Microsoft.') }}
                                </p>
                                <p class="text-xs text-base-content/60 border-t border-base-300 pt-2" x-show="!atlasLoaded">
                                    {{ __('No texture atlas installed: blocks are shown in flat colours.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Side: model info and material list --}}
            <div class="space-y-4">
                <div class="card bg-base-100 shadow-sm" x-show="info">
                    <div class="card-body p-4 gap-2 text-sm">
                        <h2 class="card-title text-sm"><i class="fa-solid fa-circle-info text-primary"></i><span x-text="info?.name"></span></h2>
                        <div class="flex flex-wrap gap-1">
                            <span class="badge badge-sm badge-ghost" x-show="info?.version" x-text="'Java ' + info?.version"></span>
                            <span class="badge badge-sm badge-ghost font-mono" x-text="info ? info.size.join(' × ') : ''"></span>
                        </div>
                        <template x-if="info?.source">
                            <div class="text-xs text-base-content/70 space-y-1">
                                <div>{{ __('Source') }}: <a class="link" :href="info.source.url" target="_blank" rel="noopener" x-text="info.source.author || info.source.url"></a></div>
                                <div>{{ __('License') }}: <span x-text="info.source.license"></span></div>
                                <div x-show="info.source.note" x-text="info.source.note"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm" x-show="materials.length" data-testid="material-list">
                    <div class="card-body p-4 gap-2">
                        <h2 class="card-title text-sm"><i class="fa-solid fa-boxes-stacked text-primary"></i>{{ __('Material list') }}</h2>
                        <div class="overflow-x-auto">
                            <table class="table table-xs">
                                <thead>
                                    <tr>
                                        <th>{{ __('Item') }}</th>
                                        <th class="text-right">{{ __('Count') }}</th>
                                        <th class="text-right">{{ __('Stacks') }}</th>
                                        <th class="text-right">{{ __('Shulkers') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="m in materials" :key="m.item">
                                        <tr>
                                            <td x-text="label(m.item)"></td>
                                            <td class="text-right font-mono" x-text="m.count"></td>
                                            <td class="text-right font-mono whitespace-nowrap" x-text="m.stack === 1 ? '-' : (m.stacks + (m.rest ? ' + ' + m.rest : ''))"></td>
                                            <td class="text-right font-mono" x-text="m.shulkers < 0.01 ? '<0.01' : m.shulkers.toFixed(2)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-base-content/50">{{ __('Stacks of 64 (16 or 1 where the item stacks less), shulker box = 27 stacks.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
