@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/farmCatalogue.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/farmCatalogue.js --}}
    <script type="application/json" id="farm-catalogue-config">
        {!! json_encode([
            'versions' => $versions,
            'endpoints' => [
                'farms' => url('/api/v1/minecraft/farms/__VERSION__'),
                'design' => url('/api/v1/minecraft/farms/__VERSION__/designs/__DESIGN__'),
            ],
            'text' => [
                'loadError' => __('Could not load the farm catalogue.'),
                'invalidFilter' => __('The filter is not valid.'),
                'unknown' => __('unknown'),
                'perHour' => __('/h'),
                'basis' => [
                    'designer' => __('stated by the designer'),
                    'measured' => __('measured by Max'),
                    'unknown' => __('rate not published'),
                ],
                'difficulty' => [
                    'easy' => __('easy'),
                    'medium' => __('medium'),
                    'hard' => __('hard'),
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <div id="farm-catalogue" x-cloak>

        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-wheat-awn text-primary"></i>
                    {{ __('Farm module catalogue') }}
                </h1>
                <p class="text-base-content/60 text-sm max-w-2xl">{{ __('Public, community-documented farm designs with their rate per module, footprint, materials and source. Rates differ a lot between designs and test conditions, so check the condition and the source.') }}</p>
            </div>
            <label class="form-control w-full sm:w-56">
                <span class="label-text text-xs mb-1">{{ __('Minecraft version') }}</span>
                <select class="select select-bordered select-sm w-full" x-model="version" data-testid="version-picker">
                    <template x-for="v in versions" :key="v.id">
                        <option :value="v.id" x-text="v.name" :selected="v.id === version"></option>
                    </template>
                </select>
            </label>
        </div>

        <div class="alert alert-error mb-4" x-show="error" x-cloak>
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span x-text="error"></span>
        </div>

        {{-- Filters --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body p-4 gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    <label class="form-control lg:col-span-2">
                        <span class="label-text text-xs mb-1">{{ __('Produced item') }}</span>
                        <label class="input input-bordered input-sm flex items-center gap-2 w-full">
                            <i class="fa-solid fa-magnifying-glass text-xs opacity-50"></i>
                            <input type="search" class="grow min-w-0" x-model="item" placeholder="{{ __('e.g. iron ingot') }}" data-testid="item-search">
                        </label>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Farm type') }}</span>
                        <select class="select select-bordered select-sm w-full" x-model="type" data-testid="type-filter">
                            <option value="">{{ __('All types') }}</option>
                            <template x-for="t in (meta ? meta.farm_types : [])" :key="t.id">
                                <option :value="t.id" x-text="t.name" :selected="t.id === type"></option>
                            </template>
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Difficulty') }}</span>
                        <select class="select select-bordered select-sm w-full" x-model="difficulty" data-testid="difficulty-filter">
                            <option value="">{{ __('Any') }}</option>
                            <option value="easy">{{ __('easy') }}</option>
                            <option value="medium">{{ __('medium') }}</option>
                            <option value="hard">{{ __('hard') }}</option>
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Max. footprint (longest side)') }}</span>
                        <input type="number" min="1" max="10000" class="input input-bordered input-sm w-full" x-model="maxFootprint" placeholder="{{ __('blocks') }}" data-testid="footprint-filter">
                    </label>
                    <div class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Sort by') }}</span>
                        <div class="join w-full">
                            <select class="select select-bordered select-sm join-item grow" x-model="sort" data-testid="sort">
                                <option value="rate">{{ __('Rate') }}</option>
                                <option value="footprint">{{ __('Footprint') }}</option>
                                <option value="name">{{ __('Name') }}</option>
                            </select>
                            <button type="button" class="btn btn-sm join-item" @click="toggleDirection()" :title="currentDirection === 'asc' ? '{{ __('ascending') }}' : '{{ __('descending') }}'" aria-label="{{ __('Sort direction') }}">
                                <i class="fa-solid" :class="currentDirection === 'asc' ? 'fa-arrow-up-short-wide' : 'fa-arrow-down-wide-short'"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-base-content/60">
                    <span>
                        <span class="loading loading-spinner loading-xs align-middle" x-show="loading"></span>
                        <span x-text="designs.length"></span> {{ __('of') }} <span x-text="total"></span> {{ __('designs') }}
                    </span>
                    <button type="button" class="btn btn-ghost btn-xs" x-show="hasFilters" @click="resetFilters()">{{ __('Reset filters') }}</button>
                </div>
            </div>
        </div>

        {{-- List --}}
        <div class="card bg-base-100 shadow-sm">
            {{-- column heads (desktop) --}}
            <div class="hidden md:grid grid-cols-12 gap-3 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-base-content/50 border-b border-base-300">
                <div class="col-span-4">{{ __('Design') }}</div>
                <div class="col-span-3">{{ __('Produced item') }}</div>
                <div class="col-span-2 text-right">{{ __('Rate per module') }}</div>
                <div class="col-span-2">{{ __('Footprint') }}</div>
                <div class="col-span-1">{{ __('Difficulty') }}</div>
            </div>
            <ul class="divide-y divide-base-300" data-testid="design-list">
                <template x-for="d in designs" :key="d.id">
                    <li>
                        <button type="button" class="w-full text-left px-4 py-3 hover:bg-base-200 transition grid grid-cols-2 md:grid-cols-12 gap-x-3 gap-y-1 items-center" @click="openDesign(d.id)">
                            <div class="col-span-2 md:col-span-4 min-w-0">
                                <div class="font-medium truncate" x-text="d.name"></div>
                                <div class="text-xs text-base-content/50 truncate"><span x-text="d.type_name"></span> &middot; <span x-text="d.author"></span></div>
                            </div>
                            <div class="md:col-span-3 text-sm truncate" x-text="d.listed_product.name"></div>
                            <div class="md:col-span-2 text-sm text-right md:text-right font-semibold tabular-nums">
                                <span x-text="rateLabel(d.listed_product.per_hour)" :class="d.listed_product.per_hour === null && 'text-base-content/40 font-normal'"></span>
                                <i class="fa-solid fa-flask text-success text-xs ml-1" x-show="d.listed_product.basis === 'measured'" :title="basisLabel('measured')"></i>
                            </div>
                            <div class="md:col-span-2 text-xs text-base-content/70 tabular-nums" x-text="footprintLabel(d.footprint)"></div>
                            <div class="md:col-span-1 text-right md:text-left">
                                <span class="badge badge-sm badge-soft" :class="difficultyClass(d.difficulty)" x-text="difficultyLabel(d.difficulty)"></span>
                            </div>
                        </button>
                    </li>
                </template>
            </ul>
            <p class="p-6 text-center text-sm text-base-content/50" x-show="!loading && designs.length === 0">{{ __('No designs match the filters.') }}</p>
        </div>

        {{-- Data source --}}
        <div class="text-xs text-base-content/50 px-1 mt-3 space-y-1" x-show="meta">
            <p x-text="meta && meta.notes"></p>
            <p>
                {{ __('Data') }}: <span x-text="meta && meta.name"></span>, {{ __('updated') }} <span x-text="meta && meta.updated"></span> &middot;
                <template x-for="(source, index) in (meta ? meta.sources : [])" :key="source.url">
                    <span><a class="link" :href="source.url" target="_blank" rel="noopener" x-text="source.title"></a><span x-show="index < meta.sources.length - 1">, </span></span>
                </template>
            </p>
        </div>

        {{-- Detail (full screen on phones) --}}
        <div class="modal" :class="detail && 'modal-open'" role="dialog" aria-modal="true" @keydown.escape.window="closeDetail()">
            <div class="modal-box p-0 w-full max-w-3xl h-full max-h-full rounded-none sm:h-auto sm:max-h-[90vh] sm:rounded-box flex flex-col" x-ref="detailBox" data-testid="design-detail">
                <template x-if="detail">
                    <div class="flex flex-col min-h-0 grow">
                        <div class="sticky top-0 z-10 bg-base-100 border-b border-base-300 px-4 py-3 flex items-start gap-3">
                            <div class="min-w-0 grow">
                                <h2 class="font-bold text-lg leading-tight" x-text="detail.name"></h2>
                                <p class="text-xs text-base-content/60">
                                    <span x-text="detail.type_name"></span> &middot; <span x-text="detail.author"></span><span x-show="detail.community"> (<span x-text="detail.community"></span>)</span>
                                </p>
                            </div>
                            <button type="button" class="btn btn-sm btn-circle btn-ghost" @click="closeDetail()" aria-label="{{ __('Close') }}"><i class="fa-solid fa-xmark"></i></button>
                        </div>

                        <div class="overflow-y-auto px-4 py-4 space-y-5">
                            {{-- Facts --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <div class="text-xs text-base-content/50">{{ __('Footprint') }} (x × y × z)</div>
                                    <div class="font-semibold tabular-nums" x-text="footprintLabel(detail.footprint)"></div>
                                </div>
                                <div>
                                    <div class="text-xs text-base-content/50">{{ __('Difficulty') }}</div>
                                    <span class="badge badge-sm badge-soft" :class="difficultyClass(detail.difficulty)" x-text="difficultyLabel(detail.difficulty)"></span>
                                </div>
                                <div>
                                    <div class="text-xs text-base-content/50">{{ __('Versions') }}</div>
                                    <div class="font-semibold" x-text="versionRange(detail.versions)"></div>
                                </div>
                                <div>
                                    <div class="text-xs text-base-content/50">{{ __('Source') }}</div>
                                    <a class="link link-primary text-sm" :href="detail.source.url" target="_blank" rel="noopener" data-testid="source-link">
                                        <span x-text="detail.source.kind === 'video' ? '{{ __('Video') }}' : (detail.source.kind === 'wiki' ? 'Wiki' : '{{ __('Schematic') }}')"></span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                </div>
                            </div>

                            {{-- Production --}}
                            <section>
                                <h3 class="font-semibold text-sm mb-2"><i class="fa-solid fa-box-open text-primary mr-1"></i>{{ __('Production per module') }}</h3>
                                <ul class="space-y-2">
                                    <template x-for="p in detail.produces" :key="p.item">
                                        <li class="rounded-box border border-base-300 p-2">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <span class="font-medium text-sm" x-text="p.name"></span>
                                                <span class="flex items-center gap-2">
                                                    <span class="badge badge-sm badge-soft" :class="basisClass(p.basis)" x-text="basisLabel(p.basis)"></span>
                                                    <span class="font-semibold tabular-nums" x-text="rateLabel(p.per_hour)"></span>
                                                </span>
                                            </div>
                                            <p class="text-xs text-base-content/60 mt-1" x-show="p.note" x-text="p.note"></p>
                                        </li>
                                    </template>
                                </ul>
                            </section>

                            {{-- Condition --}}
                            <section>
                                <h3 class="font-semibold text-sm mb-1"><i class="fa-solid fa-clipboard-check text-primary mr-1"></i>{{ __('Measurement condition') }}</h3>
                                <p class="text-sm text-base-content/80" x-text="detail.condition"></p>
                                <p class="text-sm text-base-content/60 mt-1" x-show="detail.notes" x-text="detail.notes"></p>
                            </section>

                            {{-- Materials --}}
                            <section>
                                <h3 class="font-semibold text-sm mb-2"><i class="fa-solid fa-cubes text-primary mr-1"></i>{{ __('Build materials') }}</h3>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 text-sm">
                                    <template x-for="mat in detail.materials" :key="mat.item">
                                        <li class="flex justify-between gap-2 border-b border-dashed border-base-300 py-1">
                                            <span x-text="mat.name"></span>
                                            <span class="tabular-nums text-base-content/70" x-text="countLabel(mat.count)" :title="mat.count === null ? '{{ __('Amount not published') }}' : ''"></span>
                                        </li>
                                    </template>
                                </ul>
                                <p class="text-xs text-base-content/50 mt-1">{{ __('Main blocks from the schematic or tutorial; ? = amount not published.') }}</p>
                            </section>

                            {{-- Same-item comparison --}}
                            <section>
                                <h3 class="font-semibold text-sm mb-2">
                                    <i class="fa-solid fa-scale-balanced text-primary mr-1"></i>{{ __('Other designs for') }} <span x-text="detail.produces[0].name"></span>
                                </h3>
                                <ol class="space-y-1" data-testid="comparison">
                                    <template x-for="c in comparison" :key="c.id">
                                        <li>
                                            <button type="button" class="w-full text-left rounded-box border border-base-300 px-3 py-2 text-sm hover:bg-base-200 flex flex-wrap items-center justify-between gap-2" @click="openDesign(c.id)">
                                                <span class="font-medium" x-text="c.name"></span>
                                                <span class="flex items-center gap-2 text-xs">
                                                    <span class="text-base-content/60 tabular-nums" x-text="footprintLabel(c.footprint)"></span>
                                                    <span class="font-semibold tabular-nums text-sm" x-text="rateLabel(c.per_hour)"></span>
                                                </span>
                                            </button>
                                        </li>
                                    </template>
                                </ol>
                                <p class="text-sm text-base-content/50" x-show="comparison.length === 0">{{ __('No other design in this version makes this item.') }}</p>
                            </section>

                            <p class="text-xs text-base-content/40"><span x-text="detail.source.title"></span>, {{ __('retrieved') }} <span x-text="detail.source.retrieved"></span></p>
                        </div>
                    </div>
                </template>
            </div>
            <div class="modal-backdrop" @click="closeDetail()"></div>
        </div>
    </div>
</div>
@endsection
