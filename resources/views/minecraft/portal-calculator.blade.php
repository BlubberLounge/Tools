@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/portalCalculator.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/portalCalculator.js --}}
    <script type="application/json" id="portal-calculator-config">
        {!! json_encode([
            'endpoints' => [
                'portals' => url('/api/v1/minecraft/portals'),
            ],
            'text' => [
                'overworld' => __('Overworld'),
                'nether' => __('Nether'),
                'loadError' => __('Could not load your portals.'),
                'saveError' => __('Could not save the portal.'),
                'deleteError' => __('Could not delete the portal.'),
                'confirmDelete' => __('Delete the portal ":name"?'),
                'status' => [
                    'linked' => __('linked'),
                    'oneway' => __('one-way'),
                    'new' => __('builds a new portal'),
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="portal-calculator" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-dungeon text-primary"></i>
                {{ __('Nether portal calculator') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Convert coordinates between Overworld and Nether, check which portals link to each other, count the obsidian for a frame and measure distances. Java Edition rules.') }}</p>
        </div>

        <div class="alert alert-error mb-4" x-show="error" x-cloak>
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span x-text="error"></span>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">

            {{-- Converter --}}
            <div class="card bg-base-100 shadow-sm xl:col-span-1" data-testid="converter">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-right-left text-primary"></i>{{ __('Coordinate converter') }}</h2>

                    <div class="join w-full">
                        <button type="button" class="btn btn-sm join-item flex-1" :class="from === OVERWORLD && 'btn-primary'" @click="from = OVERWORLD">{{ __('Overworld') }}</button>
                        <button type="button" class="btn btn-sm join-item flex-1" :class="from === NETHER && 'btn-primary'" @click="from = NETHER">{{ __('Nether') }}</button>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <label class="form-control"><span class="label-text text-xs mb-1">X</span><input type="number" class="input input-bordered input-sm w-full" x-model="position.x" data-testid="converter-x"></label>
                        <label class="form-control"><span class="label-text text-xs mb-1">Y</span><input type="number" class="input input-bordered input-sm w-full" x-model="position.y"></label>
                        <label class="form-control"><span class="label-text text-xs mb-1">Z</span><input type="number" class="input input-bordered input-sm w-full" x-model="position.z"></label>
                    </div>

                    <div class="rounded-box bg-base-200 p-3">
                        <div class="text-xs text-base-content/60" x-text="dimensionName(from === OVERWORLD ? NETHER : OVERWORLD)"></div>
                        <div class="font-mono text-lg font-semibold" x-text="coords(converted)"></div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn btn-ghost btn-xs" @click="swap()"><i class="fa-solid fa-arrows-rotate"></i>{{ __('Swap') }}</button>
                        <button type="button" class="btn btn-ghost btn-xs" @click="useConverterPosition()"><i class="fa-solid fa-plus"></i>{{ __('Save as portal') }}</button>
                        <button type="button" class="btn btn-ghost btn-xs" @click="usePartner()"><i class="fa-solid fa-plus"></i>{{ __('Save partner portal') }}</button>
                    </div>

                    <p class="text-xs text-base-content/60" x-show="partner.drift >= 8">
                        <i class="fa-solid fa-circle-info"></i>
                        {{ __('Back from the partner you land about') }} <span x-text="round(partner.drift, 0)"></span> {{ __('blocks away, because the Nether rounds to whole blocks.') }}
                    </p>

                    <div class="text-sm" x-show="worldPortals.length > 0" data-testid="converter-link">
                        <template x-if="previewLink.portal">
                            <p>{{ __('A portal here links to') }} <span class="font-semibold" x-text="previewLink.portal.name"></span> (<span x-text="round(previewLink.distance)"></span> {{ __('blocks from the target') }}).</p>
                        </template>
                        <template x-if="!previewLink.portal">
                            <p class="text-warning">{{ __('No saved portal in range: the game builds a new one near the target.') }}</p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Frame --}}
            <div class="card bg-base-100 shadow-sm" data-testid="frame">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-border-all text-primary"></i>{{ __('Frame size and material') }}</h2>
                    <p class="text-xs text-base-content/60">{{ __('Inner size of the portal, from 2 x 3 up to 21 x 21.') }}</p>

                    <div class="grid grid-cols-2 gap-2">
                        <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Width') }}</span><input type="number" class="input input-bordered input-sm w-full" :min="frameMin.width" :max="frameMax.width" x-model="frame.width"></label>
                        <label class="form-control"><span class="label-text text-xs mb-1">{{ __('Height') }}</span><input type="number" class="input input-bordered input-sm w-full" :min="frameMin.height" :max="frameMax.height" x-model="frame.height"></label>
                    </div>

                    <template x-if="materials">
                        <div class="stats stats-vertical bg-base-200 w-full">
                            <div class="stat py-2"><div class="stat-title text-xs">{{ __('Obsidian with corners') }}</div><div class="stat-value text-2xl" x-text="materials.withCorners"></div></div>
                            <div class="stat py-2"><div class="stat-title text-xs">{{ __('Obsidian without corners') }}</div><div class="stat-value text-2xl" x-text="materials.withoutCorners"></div></div>
                            <div class="stat py-2"><div class="stat-title text-xs">{{ __('Portal blocks') }}</div><div class="stat-value text-lg" x-text="materials.portalBlocks"></div><div class="stat-desc">{{ __('Outer size') }} <span x-text="materials.outer.width + ' x ' + materials.outer.height"></span></div></div>
                        </div>
                    </template>
                    <p class="text-sm text-error" x-show="!materials">{{ __('The game does not accept this size.') }}</p>
                </div>
            </div>

            {{-- Distance --}}
            <div class="card bg-base-100 shadow-sm" data-testid="distance">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-ruler-combined text-primary"></i>{{ __('3D distance') }}</h2>

                    <div class="join w-full">
                        <button type="button" class="btn btn-sm join-item flex-1" :class="distanceDimension === OVERWORLD && 'btn-primary'" @click="distanceDimension = OVERWORLD">{{ __('Overworld') }}</button>
                        <button type="button" class="btn btn-sm join-item flex-1" :class="distanceDimension === NETHER && 'btn-primary'" @click="distanceDimension = NETHER">{{ __('Nether') }}</button>
                    </div>

                    <template x-for="(p, label) in { A: a, B: b }" :key="label">
                        <div class="grid grid-cols-[1.5rem_1fr_1fr_1fr] gap-2 items-end">
                            <span class="font-semibold pb-1" x-text="label"></span>
                            <label class="form-control"><span class="label-text text-xs mb-1">X</span><input type="number" class="input input-bordered input-sm w-full" x-model="p.x"></label>
                            <label class="form-control"><span class="label-text text-xs mb-1">Y</span><input type="number" class="input input-bordered input-sm w-full" x-model="p.y"></label>
                            <label class="form-control"><span class="label-text text-xs mb-1">Z</span><input type="number" class="input input-bordered input-sm w-full" x-model="p.z"></label>
                        </div>
                    </template>
                    <button type="button" class="btn btn-ghost btn-xs self-start" @click="swapPoints()"><i class="fa-solid fa-arrows-rotate"></i>{{ __('Swap') }}</button>

                    <table class="table table-xs">
                        <tbody>
                            <tr><th>{{ __('Distance') }}</th><td class="font-mono font-semibold" x-text="round(distance.distance)"></td></tr>
                            <tr><th>{{ __('Horizontal') }}</th><td class="font-mono" x-text="round(distance.horizontal)"></td></tr>
                            <tr><th>{{ __('Blocks to walk (X+Y+Z)') }}</th><td class="font-mono" x-text="distance.manhattan"></td></tr>
                            <tr><th>&Delta; X / Y / Z</th><td class="font-mono" x-text="coords({ x: distance.dx, y: distance.dy, z: distance.dz })"></td></tr>
                            <tr><th>{{ __('Walking / sprinting') }}</th><td class="font-mono" x-text="duration(distance.time.walk) + ' / ' + duration(distance.time.sprint)"></td></tr>
                            <tr><th>{{ __('Same trip through the') }} <span x-text="dimensionName(distance.other.dimension)"></span></th><td class="font-mono" x-text="round(distance.other.horizontal) + ' (' + duration(distance.other.time.sprint) + ')'"></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Saved portals --}}
        <div class="card bg-base-100 shadow-sm" data-testid="portal-list">
            <div class="card-body p-4 gap-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="card-title text-sm"><i class="fa-solid fa-link text-primary"></i>{{ __('My portals') }}</h2>
                        <p class="text-xs text-base-content/60">{{ __('Enter one portal block per portal. The game searches 128 blocks around the target in the Overworld and 16 in the Nether and takes the closest portal.') }}</p>
                    </div>
                    <label class="form-control w-full sm:w-56" x-show="worlds.length > 1">
                        <span class="label-text text-xs mb-1">{{ __('World') }}</span>
                        <select class="select select-bordered select-sm w-full" x-model="world">
                            <template x-for="w in worlds" :key="w">
                                <option :value="w" x-text="w || @js(__('Default world'))"></option>
                            </template>
                        </select>
                    </label>
                </div>

                {{-- Form --}}
                <form class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-2 items-end" @submit.prevent="savePortal()" data-testid="portal-form">
                    <label class="form-control col-span-2">
                        <span class="label-text text-xs mb-1">{{ __('Name') }}</span>
                        <input type="text" class="input input-bordered input-sm w-full" maxlength="64" x-model="form.name" required>
                        <span class="text-xs text-error" x-text="formErrors.name?.[0]"></span>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('World') }}</span>
                        <input type="text" class="input input-bordered input-sm w-full" maxlength="64" x-model="form.world" placeholder="{{ __('Default world') }}">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Dimension') }}</span>
                        <select class="select select-bordered select-sm w-full" x-model="form.dimension">
                            <option value="overworld">{{ __('Overworld') }}</option>
                            <option value="nether">{{ __('Nether') }}</option>
                        </select>
                    </label>
                    <template x-for="axis in ['x', 'y', 'z']" :key="axis">
                        <label class="form-control">
                            <span class="label-text text-xs mb-1" x-text="axis.toUpperCase()"></span>
                            <input type="number" class="input input-bordered input-sm w-full" x-model="form[axis]" required>
                            <span class="text-xs text-error" x-text="formErrors[axis]?.[0]"></span>
                        </label>
                    </template>
                    <div class="flex gap-2 col-span-2 md:col-span-4 xl:col-span-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-1" :disabled="saving" x-text="form.id ? @js(__('Save')) : @js(__('Add'))"></button>
                        <button type="button" class="btn btn-ghost btn-sm" x-show="form.id" @click="resetForm()">{{ __('Cancel') }}</button>
                    </div>
                </form>

                <div class="alert" x-show="!loading && worldPortals.length === 0">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>{{ __('No portals saved yet.') }}</span>
                </div>

                <div class="alert alert-warning" x-show="problemCount > 0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><span x-text="problemCount"></span> {{ __('portal(s) do not lead back to themselves. Move the partner portal closer to the target shown.') }}</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" x-show="worldPortals.length > 0">
                    <template x-for="dimension in [OVERWORLD, NETHER]" :key="dimension">
                        <div>
                            <h3 class="font-semibold text-sm mb-2" x-text="dimensionName(dimension)"></h3>
                            <ul class="divide-y divide-base-300 rounded-box border border-base-300">
                                <template x-for="link in linksOf(dimension)" :key="link.portal.id">
                                    <li class="p-3 flex flex-wrap items-start gap-2">
                                        <div class="grow min-w-48">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold" x-text="link.portal.name"></span>
                                                <span class="badge badge-sm" :class="statusClass(link.status)" x-text="statusLabel(link.status)"></span>
                                            </div>
                                            <div class="font-mono text-xs text-base-content/60" x-text="coords(link.portal)"></div>
                                            <div class="text-xs mt-1">
                                                <span class="text-base-content/60">{{ __('Target') }}</span>
                                                <span class="font-mono" x-text="coords(link.target)"></span>
                                                <template x-if="link.destination">
                                                    <span>&rarr; <span class="font-semibold" x-text="link.destination.name"></span></span>
                                                </template>
                                            </div>
                                            <div class="text-xs text-warning" x-show="link.status === 'oneway'">
                                                {{ __('Leads back to') }} <span class="font-semibold" x-text="link.returnsTo?.name"></span>
                                            </div>
                                        </div>
                                        <div class="flex gap-1">
                                            <button type="button" class="btn btn-ghost btn-xs" @click="editPortal(link.portal)" title="{{ __('Edit') }}"><i class="fa-solid fa-pen"></i></button>
                                            <button type="button" class="btn btn-ghost btn-xs text-error" @click="deletePortal(link.portal)" title="{{ __('Delete') }}"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
